<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class CartService
{
    public function restaurantId(): ?int
    {
        $id = Session::get('cart.restaurant_id');

        return $id ? (int) $id : null;
    }

    public function items(): array
    {
        return Session::get('cart.items', []);
    }

    public function add(MenuItem $item, int $qty = 1): void
    {
        if (! $item->is_available) {
            throw new RuntimeException('هذا الصنف غير متوفر حالياً.');
        }

        if (! $item->restaurant?->isVisible()) {
            throw new RuntimeException('هذا المطعم غير متاح للطلب حالياً.');
        }

        $currentRestaurant = $this->restaurantId();
        if ($currentRestaurant && $currentRestaurant !== $item->restaurant_id) {
            throw new RuntimeException('السلة تحتوي أصنافاً من مطعم آخر. أفرغ السلة أولاً أو أكمل الطلب الحالي.');
        }

        $items = $this->items();
        $items[$item->id] = ($items[$item->id] ?? 0) + max(1, $qty);

        Session::put('cart.restaurant_id', $item->restaurant_id);
        Session::put('cart.items', $items);
    }

    public function update(int $itemId, int $qty): void
    {
        $items = $this->items();

        if ($qty <= 0) {
            unset($items[$itemId]);
        } else {
            $items[$itemId] = $qty;
        }

        if ($items === []) {
            $this->clear();

            return;
        }

        Session::put('cart.items', $items);
    }

    public function clear(): void
    {
        Session::forget('cart');
    }

    public function count(): int
    {
        return (int) array_sum($this->items());
    }

    public function quote(?User $user = null): array
    {
        $quantities = $this->items();
        $menuItems = MenuItem::query()
            ->whereIn('id', array_keys($quantities) ?: [0])
            ->get()
            ->keyBy('id');

        $lines = [];
        $subtotal = 0.0;

        foreach ($quantities as $id => $qty) {
            $item = $menuItems->get($id);
            if (! $item) {
                continue;
            }

            $lineTotal = (float) $item->price * (int) $qty;
            $subtotal += $lineTotal;
            $lines[] = [
                'item' => $item,
                'qty' => (int) $qty,
                'line_total' => $lineTotal,
            ];
        }

        $membership = $user?->activeMembership();
        $discountPercent = (int) ($membership?->discount_percent ?? 0);
        $discountAmount = round($subtotal * ($discountPercent / 100), 2);
        $afterDiscount = $subtotal - $discountAmount;
        $deliveryFee = $membership?->free_delivery
            ? 0.0
            : (float) Setting::value('delivery_fee', 10);
        $total = $afterDiscount + $deliveryFee;
        $multiplier = (float) ($membership?->points_multiplier ?? 1);
        $points = $this->calculatePoints($afterDiscount, $multiplier);

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'items_total' => $afterDiscount,
            'points' => $points,
            'membership' => $membership,
            'restaurant' => Restaurant::find($this->restaurantId()),
        ];
    }

    public function calculatePoints(float $amount, float $multiplier = 1): int
    {
        $per = max(1, (float) Setting::value('points_per_amount', 1));

        return (int) floor(($amount / $per) * $multiplier);
    }

    public function payload(?User $user = null): array
    {
        $quote = $this->quote($user);

        return [
            'count' => $this->count(),
            'subtotal' => $quote['subtotal'],
            'discount_percent' => $quote['discount_percent'],
            'discount_amount' => $quote['discount_amount'],
            'delivery_fee' => $quote['delivery_fee'],
            'total' => $quote['total'],
            'items_total' => $quote['items_total'],
            'points' => $quote['points'],
            'restaurant' => $quote['restaurant'] ? [
                'id' => $quote['restaurant']->id,
                'name' => $quote['restaurant']->name,
            ] : null,
            'lines' => array_map(function (array $line) {
                $item = $line['item'];
                $lineTotal = (float) $line['line_total'];

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'qty' => (int) $line['qty'],
                    'price' => (float) $item->price,
                    'line_total' => $lineTotal,
                    'points' => max(1, (int) floor($lineTotal / 10)),
                ];
            }, $quote['lines']),
        ];
    }
}
