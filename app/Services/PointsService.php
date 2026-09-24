<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function shekelsPerPoint(?Restaurant $restaurant = null): float
    {
        return $this->shekelsPerEarnPoint($restaurant);
    }

    public function shekelsPerEarnPoint(?Restaurant $restaurant = null): float
    {
        $fromRestaurant = $restaurant?->points_per_amount;
        if ($fromRestaurant !== null && (float) $fromRestaurant > 0) {
            return (float) $fromRestaurant;
        }

        return max(0.01, (float) Setting::value('points_per_amount', 1));
    }

    public function shekelsPerRedeemPoint(?Restaurant $restaurant = null): float
    {
        $fromRestaurant = $restaurant?->points_redeem_per_amount;
        if ($fromRestaurant !== null && (float) $fromRestaurant > 0) {
            return (float) $fromRestaurant;
        }

        return max(0.01, (float) Setting::value('points_redeem_per_amount', Setting::value('points_per_amount', 1)));
    }

    public function includesDelivery(): bool
    {
        return (string) Setting::value('points_include_delivery', '1') !== '0';
    }

    public function formatShekelRate(float $shekelsPerPoint): string
    {
        $formatted = rtrim(rtrim(number_format($shekelsPerPoint, 2, '.', ''), '0'), '.');

        return $formatted === '' ? '1' : $formatted;
    }

    public function earnRateLabel(?Restaurant $restaurant = null): string
    {
        return 'كل '.$this->formatShekelRate($this->shekelsPerEarnPoint($restaurant)).' شيكل = نقطة';
    }

    public function redeemRateLabel(?Restaurant $restaurant = null): string
    {
        return 'كل '.$this->formatShekelRate($this->shekelsPerRedeemPoint($restaurant)).' شيكل = نقطة استبدال';
    }

    public function redeemCost(MenuItem $item): int
    {
        if ($item->redeem_points !== null) {
            return max(0, (int) $item->redeem_points);
        }

        $per = $this->shekelsPerRedeemPoint($item->restaurant);

        return max(1, (int) round(((float) $item->price) / $per));
    }

    public function previewLineEarn(MenuItem $item, int $qty = 1): int
    {
        $qty = max(1, $qty);

        if ($item->earn_points !== null) {
            return max(0, (int) $item->earn_points * $qty);
        }

        $per = $this->shekelsPerEarnPoint($item->restaurant);

        return (int) floor((((float) $item->price) * $qty) / $per);
    }

    /**
     * @param  iterable<int, array{item?: ?MenuItem, qty?: int, quantity?: int, line_total: float|int|string}>  $lines
     */
    public function previewEarn(
        ?Restaurant $restaurant,
        iterable $lines,
        float $subtotal,
        float $discountAmount,
        float $deliveryFee,
        float $multiplier = 1,
    ): int {
        $custom = 0;
        $rated = 0.0;

        foreach ($lines as $line) {
            $item = $line['item'] ?? null;
            $qty = (int) ($line['qty'] ?? $line['quantity'] ?? 1);
            $lineTotal = (float) ($line['line_total'] ?? 0);

            if ($item instanceof MenuItem && $item->earn_points !== null) {
                $custom += (int) $item->earn_points * max(1, $qty);
            } else {
                $rated += $lineTotal;
            }
        }

        if ($subtotal > 0 && $discountAmount > 0 && $rated > 0) {
            $rated *= max(0, $subtotal - $discountAmount) / $subtotal;
        }

        if ($this->includesDelivery()) {
            $rated += max(0, $deliveryFee);
        }

        $per = $this->shekelsPerEarnPoint($restaurant);
        $fromRated = (int) floor(($rated / $per) * $multiplier);
        $fromCustom = (int) floor($custom * $multiplier);

        return max(0, $fromRated + $fromCustom);
    }

    public function featuredRewards(int $limit = 3): array
    {
        return MenuItem::query()
            ->where('is_available', true)
            ->whereHas('restaurant', fn ($query) => $query->visible())
            ->with('restaurant')
            ->orderBy('price')
            ->get()
            ->unique('restaurant_id')
            ->take($limit)
            ->map(fn (MenuItem $item) => $this->rewardPayload($item))
            ->values()
            ->all();
    }

    public function rewardsForRestaurant(Restaurant $restaurant, int $limit = 4): array
    {
        $items = $restaurant->relationLoaded('menuItems')
            ? $restaurant->menuItems
            : $restaurant->menuItems()->get();

        return $items
            ->where('is_available', true)
            ->sortBy('price')
            ->take($limit)
            ->values()
            ->map(function (MenuItem $item) use ($restaurant) {
                $item->setRelation('restaurant', $restaurant);

                return $this->rewardPayload($item);
            })
            ->all();
    }

    public function rewardPayload(MenuItem $item): array
    {
        $restaurant = $item->restaurant;

        return [
            'name' => $item->name,
            'place' => $restaurant->name,
            'subtitle' => $restaurant->name,
            'points' => $this->redeemCost($item),
            'image' => $item->imageUrl() ?: $restaurant->coverUrl(),
            'url' => route('redeem.create', [
                'restaurant_id' => $restaurant->id,
                'menu_item_id' => $item->id,
            ]),
        ];
    }

    public function earnForOrder(Order $order): void
    {
        if ($order->points_earned > 0 || $order->status !== 'delivered') {
            return;
        }

        $order->loadMissing(['items.menuItem', 'restaurant', 'membership', 'user']);

        $user = $order->user;
        if (! $user) {
            return;
        }

        $membership = $order->membership;
        $multiplier = (float) ($membership?->points_multiplier ?? 1);
        $lines = $order->items->map(fn ($item) => [
            'item' => $item->menuItem,
            'qty' => (int) $item->quantity,
            'line_total' => (float) $item->line_total,
        ]);

        $points = $this->previewEarn(
            $order->restaurant,
            $lines,
            (float) $order->subtotal,
            (float) $order->discount_amount,
            (float) $order->delivery_fee,
            $multiplier,
        );

        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $order, $points) {
            $user->increment('points_balance', $points);
            $order->update(['points_earned' => $points]);
            $user->pointTransactions()->create([
                'order_id' => $order->id,
                'type' => 'earn',
                'points' => $points,
                'description' => 'نقاط طلب رقم #'.$order->id,
            ]);
        });

        $this->notifications->notify(
            $user,
            'تم إضافة نقاط',
            "حصلت على {$points} نقطة من طلبك رقم #{$order->id}. رصيدك الآن: ".($user->fresh()->points_balance).' نقطة.',
            route('account.points')
        );
    }

    public function spend(User $user, int $points, string $description, ?int $orderId = null): void
    {
        if ($points <= 0) {
            return;
        }

        if ($user->points_balance < $points) {
            throw new \RuntimeException('رصيد النقاط غير كافٍ.');
        }

        DB::transaction(function () use ($user, $points, $description, $orderId) {
            $user->decrement('points_balance', $points);
            $user->pointTransactions()->create([
                'order_id' => $orderId,
                'type' => 'redeem',
                'points' => -$points,
                'description' => $description,
            ]);
        });
    }

    public function refund(User $user, int $points, string $description, ?int $orderId = null): void
    {
        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $points, $description, $orderId) {
            $user->increment('points_balance', $points);
            $user->pointTransactions()->create([
                'order_id' => $orderId,
                'type' => 'adjust',
                'points' => $points,
                'description' => $description,
            ]);
        });
    }

    public function adjust(User $user, int $points, string $reason): void
    {
        DB::transaction(function () use ($user, $points, $reason) {
            $next = max(0, $user->points_balance + $points);
            $applied = $next - $user->points_balance;
            $user->update(['points_balance' => $next]);
            $points = $applied;

            $user->pointTransactions()->create([
                'type' => 'adjust',
                'points' => $points,
                'description' => $reason,
            ]);
        });
    }
}
