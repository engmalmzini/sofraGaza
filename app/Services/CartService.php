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
    public function __construct(
        private PointsService $points,
    ) {}

    public function restaurantId(): ?int
    {
        $id = Session::get('cart.restaurant_id');

        return $id ? (int) $id : null;
    }

    public function items(): array
    {
        return Session::get('cart.items', []);
    }

    public function add(MenuItem $item, int $qty = 1, ?string $notes = null): void
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

        $groupId = Session::get('group_order_id');
        if ($groupId) {
            $groupRestaurantId = (int) Session::get('group_order_restaurant_id');
            if ($groupRestaurantId && $groupRestaurantId !== (int) $item->restaurant_id) {
                throw new RuntimeException('هذا طلب جماعي من مطعم آخر. اطلب من منيو الطلب الجماعي أو اخرج منه أولاً.');
            }
        }

        $items = $this->items();
        $existing = $items[$item->id] ?? null;
        $existingQty = is_array($existing) ? (int) ($existing['qty'] ?? 0) : (int) $existing;
        $existingNotes = is_array($existing) ? ($existing['notes'] ?? null) : null;

        $newQty = $existingQty + max(1, $qty);
        $newNotes = filled($notes) ? trim($notes) : $existingNotes;

        $items[$item->id] = [
            'qty' => $newQty,
            'notes' => $newNotes,
        ];

        Session::put('cart.restaurant_id', $item->restaurant_id);
        Session::put('cart.items', $items);
    }

    public function update(int $itemId, int $qty, ?string $notes = null, bool $updateNotes = false): void
    {
        $items = $this->items();

        if ($qty <= 0) {
            unset($items[$itemId]);
        } else {
            $existing = $items[$itemId] ?? null;
            $existingNotes = is_array($existing) ? ($existing['notes'] ?? null) : null;
            $newNotes = $updateNotes ? (filled($notes) ? trim($notes) : null) : $existingNotes;

            $items[$itemId] = [
                'qty' => $qty,
                'notes' => $newNotes,
            ];
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

    public function appliedCoupon(): ?\App\Models\Coupon
    {
        $code = Session::get('cart.coupon_code');
        if (! $code) {
            return null;
        }

        $coupon = \App\Models\Coupon::active()->with('restaurant')->where('code', $code)->first();
        if (! $coupon) {
            Session::forget('cart.coupon_code');

            return null;
        }

        $currentRestaurantId = $this->restaurantId();
        if (! $coupon->appliesToRestaurant($currentRestaurantId)) {
            Session::forget('cart.coupon_code');

            return null;
        }

        return $coupon;
    }

    public function applyCoupon(string $code, float $subtotal, ?int $restaurantId = null): array
    {
        $normalized = strtoupper(trim($code));
        $coupon = \App\Models\Coupon::with('restaurant')->where('code', $normalized)->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'كود الخصم غير موجود أو غير صحيح.'];
        }

        $targetRestaurantId = $restaurantId ?? $this->restaurantId();
        $validation = $coupon->validateForSubtotal($subtotal, $targetRestaurantId);
        if (! $validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        Session::put('cart.coupon_code', $coupon->code);

        return [
            'success' => true,
            'message' => 'تم تفعيل كود الخصم بنجاح!',
            'coupon' => $coupon,
        ];
    }

    public function removeCoupon(): void
    {
        Session::forget('cart.coupon_code');
    }

    public function count(): int
    {
        $total = 0;
        foreach ($this->items() as $entry) {
            $total += is_array($entry) ? (int) ($entry['qty'] ?? 0) : (int) $entry;
        }

        return $total;
    }

    public function quote(?User $user = null, ?string $areaKey = null, ?string $couponCode = null): array
    {
        $quantities = $this->items();
        $menuItems = MenuItem::query()
            ->whereIn('id', array_keys($quantities) ?: [0])
            ->get()
            ->keyBy('id');

        $lines = [];
        $subtotal = 0.0;

        foreach ($quantities as $id => $data) {
            $item = $menuItems->get($id);
            if (! $item) {
                continue;
            }

            $qty = is_array($data) ? (int) ($data['qty'] ?? 0) : (int) $data;
            if ($qty <= 0) {
                continue;
            }
            $notes = is_array($data) ? ($data['notes'] ?? null) : null;

            $lineTotal = (float) $item->price * $qty;
            $subtotal += $lineTotal;
            $lines[] = [
                'item' => $item,
                'qty' => $qty,
                'notes' => $notes,
                'line_total' => $lineTotal,
            ];
        }

        $membership = $user?->activeMembership();
        $vipPercent = (int) ($membership?->discount_percent ?? 0);
        $vipDiscount = round($subtotal * ($vipPercent / 100), 2);

        // Check for applied coupon
        $coupon = null;
        $couponDiscount = 0.0;
        $codeToUse = $couponCode ?: Session::get('cart.coupon_code');
        $currentRestaurantId = $this->restaurantId();

        if ($codeToUse) {
            $candidate = \App\Models\Coupon::active()->with('restaurant')->where('code', strtoupper(trim($codeToUse)))->first();
            if ($candidate) {
                $validation = $candidate->validateForSubtotal($subtotal, $currentRestaurantId);
                if ($validation['valid']) {
                    $coupon = $candidate;
                    $couponDiscount = $candidate->calculateDiscount($subtotal);
                }
            }
        }

        $discountAmount = min($subtotal, round($vipDiscount + $couponDiscount, 2));
        $discountPercent = $subtotal > 0 ? (int) round(($discountAmount / $subtotal) * 100) : 0;
        $afterDiscount = max(0.0, $subtotal - $discountAmount);

        $resolvedAreaKey = $areaKey ?: (session('delivery_area')['key'] ?? null);
        $deliveryFee = $membership?->free_delivery
            ? 0.0
            : (float) Setting::deliveryFeeForArea($resolvedAreaKey);
        $total = $afterDiscount + $deliveryFee;
        $multiplier = (float) ($membership?->points_multiplier ?? 1);
        $restaurant = Restaurant::find($this->restaurantId());
        $points = $this->points->previewEarn(
            $restaurant,
            $lines,
            $subtotal,
            $discountAmount,
            $deliveryFee,
            $multiplier,
        );

        $currentArea = collect(Setting::allAreas())->firstWhere('key', $resolvedAreaKey)
            ?? ['key' => $resolvedAreaKey, 'label' => $resolvedAreaKey ?: 'المنطقة المحددة'];

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'delivery_fee' => $deliveryFee,
            'delivery_area' => $currentArea,
            'total' => $total,
            'items_total' => $afterDiscount,
            'points' => $points,
            'membership' => $membership,
            'restaurant' => $restaurant,
            'coupon' => $coupon,
            'coupon_code' => $coupon?->code,
            'coupon_discount' => $couponDiscount,
            'vip_discount' => $vipDiscount,
        ];
    }

    public function calculatePoints(float $amount, float $multiplier = 1, ?Restaurant $restaurant = null): int
    {
        $per = $this->points->shekelsPerEarnPoint($restaurant);

        return (int) floor(($amount / $per) * $multiplier);
    }

    public function payload(?User $user = null, ?string $areaKey = null): array
    {
        $quote = $this->quote($user, $areaKey);

        return [
            'count' => $this->count(),
            'subtotal' => $quote['subtotal'],
            'discount_percent' => $quote['discount_percent'],
            'discount_amount' => $quote['discount_amount'],
            'delivery_fee' => $quote['delivery_fee'],
            'delivery_area' => $quote['delivery_area'],
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
                    'notes' => $line['notes'] ?? null,
                    'price' => (float) $item->price,
                    'line_total' => $lineTotal,
                    'points' => $this->points->previewLineEarn($item, (int) $line['qty']),
                ];
            }, $quote['lines']),
        ];
    }
}
