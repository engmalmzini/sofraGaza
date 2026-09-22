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

    public function shekelsPerPoint(): float
    {
        return max(1, (float) Setting::value('points_per_amount', 1));
    }

    public function redeemCost(MenuItem $item): int
    {
        return max(1, (int) round((float) $item->price));
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

        $user = $order->user;
        $membership = $order->membership;
        $multiplier = (float) ($membership?->points_multiplier ?? 1);
        $amount = (float) $order->subtotal - (float) $order->discount_amount;
        $per = $this->shekelsPerPoint();
        $points = (int) floor(($amount / $per) * $multiplier);

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
