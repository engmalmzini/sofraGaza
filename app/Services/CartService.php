<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\MenuOffer;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Support\MenuOptions;
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

    public function add(MenuItem $item, int $qty = 1, ?string $notes = null, array $choices = []): void
    {
        if (! $item->isOrderable()) {
            throw new RuntimeException($item->is_available
                ? "الصنف {$item->name} نفدت كميته."
                : 'هذا الصنف غير متوفر حالياً.');
        }

        $this->assertSameRestaurant((int) $item->restaurant_id, $item->restaurant);

        $items = $this->items();
        $key = MenuOptions::lineKey((int) $item->id, $choices);
        $existing = $items[$key] ?? null;
        if ($existing === null && (string) $key === (string) $item->id) {
            $existing = $items[$item->id] ?? null;
        }
        $existingQty = is_array($existing) ? (int) ($existing['qty'] ?? 0) : (int) $existing;
        $existingNotes = is_array($existing) ? ($existing['notes'] ?? null) : null;

        $newQty = $existingQty + max(1, $qty);
        $item->assertCanSell($this->quantityForItem((int) $item->id, $items, $key, $newQty));
        $newNotes = filled($notes) ? trim($notes) : $existingNotes;

        unset($items[$key]);
        if ((string) $key === (string) $item->id) {
            unset($items[$item->id]);
        }
        $items[$key] = [
            'item_id' => (int) $item->id,
            'qty' => $newQty,
            'notes' => $newNotes,
            'choices' => $choices,
        ];

        Session::put('cart.restaurant_id', $item->restaurant_id);
        Session::put('cart.items', $items);
    }

    public function addOffer(MenuOffer $offer, int $qty = 1, ?string $notes = null): void
    {
        $offer->loadMissing(['restaurant', 'firstItem', 'secondItem']);
        $this->assertSameRestaurant((int) $offer->restaurant_id, $offer->restaurant);
        $offer->assertCanSell(max(1, $qty));

        $items = $this->items();
        $key = 'offer:'.$offer->id;
        $existing = $items[$key] ?? null;
        $existingQty = is_array($existing) ? (int) ($existing['qty'] ?? 0) : 0;
        $existingNotes = is_array($existing) ? ($existing['notes'] ?? null) : null;
        $newQty = $existingQty + max(1, $qty);

        foreach ($offer->includedItems() as $item) {
            $item->assertCanSell($this->quantityForItem((int) $item->id, $items, $key, $newQty));
        }

        $items[$key] = [
            'type' => 'offer',
            'offer_id' => (int) $offer->id,
            'item_id' => (int) $offer->first_item_id,
            'qty' => $newQty,
            'notes' => filled($notes) ? trim($notes) : $existingNotes,
            'choices' => [],
        ];

        Session::put('cart.restaurant_id', $offer->restaurant_id);
        Session::put('cart.items', $items);
    }

    public function update(int $itemId, int $qty, ?string $notes = null, bool $updateNotes = false, ?string $lineKey = null): void
    {
        $items = $this->items();
        $key = $this->resolveLineKey($items, $itemId, $lineKey);

        if ($key === null) {
            return;
        }

        if ($qty <= 0) {
            unset($items[$key]);
        } else {
            $existing = $items[$key] ?? null;

            if ($this->isOfferEntry($existing, (string) $key)) {
                $offer = MenuOffer::query()->with(['firstItem', 'secondItem'])->find((int) ($existing['offer_id'] ?? 0));
                if (! $offer) {
                    unset($items[$key]);
                } else {
                    foreach ($offer->includedItems() as $item) {
                        $item->assertCanSell($this->quantityForItem((int) $item->id, $items, (string) $key, $qty));
                    }
                    $existingNotes = $existing['notes'] ?? null;
                    $items[$key] = [
                        'type' => 'offer',
                        'offer_id' => (int) $offer->id,
                        'item_id' => (int) $offer->first_item_id,
                        'qty' => $qty,
                        'notes' => $updateNotes ? (filled($notes) ? trim($notes) : null) : $existingNotes,
                        'choices' => [],
                    ];
                }
            } else {
                $resolvedItemId = is_array($existing) ? (int) ($existing['item_id'] ?? $itemId) : $itemId;
                $item = MenuItem::query()->find($resolvedItemId);
                if ($item) {
                    $item->assertCanSell($this->quantityForItem($resolvedItemId, $items, $key, $qty));
                }

                $existingNotes = is_array($existing) ? ($existing['notes'] ?? null) : null;
                $existingChoices = is_array($existing) ? ($existing['choices'] ?? []) : [];
                $newNotes = $updateNotes ? (filled($notes) ? trim($notes) : null) : $existingNotes;

                $items[$key] = [
                    'item_id' => $resolvedItemId,
                    'qty' => $qty,
                    'notes' => $newNotes,
                    'choices' => $existingChoices,
                ];
            }
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
        $itemIds = [];
        $offerIds = [];
        foreach ($quantities as $key => $data) {
            if ($this->isOfferEntry($data, (string) $key)) {
                $offerIds[] = (int) ($data['offer_id'] ?? 0);
                continue;
            }
            $itemIds[] = is_array($data) ? (int) ($data['item_id'] ?? $key) : (int) $key;
        }
        $menuItems = MenuItem::query()
            ->whereIn('id', $itemIds ?: [0])
            ->get()
            ->keyBy('id');
        $offers = MenuOffer::query()
            ->with(['firstItem', 'secondItem'])
            ->whereIn('id', $offerIds ?: [0])
            ->get()
            ->keyBy('id');

        $lines = [];
        $subtotal = 0.0;

        foreach ($quantities as $key => $data) {
            $qty = is_array($data) ? (int) ($data['qty'] ?? 0) : (int) $data;
            if ($qty <= 0) {
                continue;
            }
            $notes = is_array($data) ? ($data['notes'] ?? null) : null;

            if ($this->isOfferEntry($data, (string) $key)) {
                $offer = $offers->get((int) ($data['offer_id'] ?? 0));
                if (! $offer || ! $offer->firstItem) {
                    continue;
                }
                $unitPrice = round((float) $offer->price, 2);
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;
                $lines[] = [
                    'key' => (string) $key,
                    'item' => $offer->firstItem,
                    'offer' => $offer,
                    'is_offer' => true,
                    'name' => $offer->cartName(),
                    'image_url' => $offer->imageUrl(),
                    'qty' => $qty,
                    'notes' => $notes,
                    'choices' => $offer->orderOptions(),
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'can_increase' => $offer->isOrderable(),
                ];
                continue;
            }

            $itemId = is_array($data) ? (int) ($data['item_id'] ?? $key) : (int) $key;
            $item = $menuItems->get($itemId);
            if (! $item) {
                continue;
            }

            $choices = is_array($data) ? (array) ($data['choices'] ?? []) : [];
            $unitPrice = round((float) $item->price + MenuOptions::extras($choices), 2);
            $lineTotal = $unitPrice * $qty;
            $subtotal += $lineTotal;
            $lines[] = [
                'key' => (string) $key,
                'item' => $item,
                'offer' => null,
                'is_offer' => false,
                'name' => $item->name,
                'image_url' => $item->imageUrl(),
                'qty' => $qty,
                'notes' => $notes,
                'choices' => $choices,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'can_increase' => $item->isUnlimitedStock() || $qty < (int) $item->remainingStock(),
            ];
        }

        $membership = $user?->activeMembership();
        $resolvedAreaKey = $areaKey ?: (session('delivery_area')['key'] ?? null);
        $areaDeliveryFee = (float) Setting::deliveryFeeForArea($resolvedAreaKey);
        $afterMembership = $membership
            ? $membership->discountedDeliveryFee($areaDeliveryFee)
            : $areaDeliveryFee;
        $vipDeliveryDiscount = max(0.0, round($areaDeliveryFee - $afterMembership, 2));

        $coupon = null;
        $couponMealDiscount = 0.0;
        $couponDeliveryDiscount = 0.0;
        $couponDiscount = 0.0;
        $codeToUse = $couponCode ?: Session::get('cart.coupon_code');
        $currentRestaurantId = $this->restaurantId();

        if ($codeToUse) {
            $candidate = \App\Models\Coupon::active()->with('restaurant')->where('code', strtoupper(trim($codeToUse)))->first();
            if ($candidate) {
                $validation = $candidate->validateForSubtotal($subtotal, $currentRestaurantId);
                if ($validation['valid']) {
                    $coupon = $candidate;
                    $allocation = $candidate->allocateDiscount($subtotal, $afterMembership);
                    $couponMealDiscount = $allocation['meal'];
                    $couponDeliveryDiscount = $allocation['delivery'];
                    $couponDiscount = $allocation['total'];
                }
            }
        }

        $mealDiscount = min($subtotal, $couponMealDiscount);
        $deliveryFee = max(0.0, round($afterMembership - $couponDeliveryDiscount, 2));
        $deliveryDiscount = round($vipDeliveryDiscount + ($afterMembership - $deliveryFee), 2);
        $discountAmount = round($mealDiscount + $deliveryDiscount, 2);
        $discountPercent = $membership?->deliveryDiscountPercent() ?? 0;
        $afterDiscount = max(0.0, $subtotal - $mealDiscount);
        $total = $afterDiscount + $deliveryFee;
        $multiplier = (float) ($membership?->points_multiplier ?? 1);
        $restaurant = Restaurant::find($this->restaurantId());
        $points = $this->points->previewEarn(
            $restaurant,
            $lines,
            $subtotal,
            $mealDiscount,
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
            'meal_discount' => $mealDiscount,
            'delivery_discount' => $deliveryDiscount,
            'delivery_fee_base' => $areaDeliveryFee,
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
            'vip_discount' => 0.0,
            'vip_delivery_discount' => $vipDeliveryDiscount,
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
            'meal_discount' => $quote['meal_discount'],
            'delivery_discount' => $quote['delivery_discount'],
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
                $choices = $line['choices'] ?? [];
                $isOffer = ! empty($line['is_offer']);

                return [
                    'id' => $item->id,
                    'key' => $line['key'] ?? (string) $item->id,
                    'name' => $line['name'] ?? $item->name,
                    'qty' => (int) $line['qty'],
                    'notes' => $line['notes'] ?? null,
                    'choices' => $choices,
                    'choices_label' => MenuOptions::label($choices),
                    'price' => (float) ($line['unit_price'] ?? $item->price),
                    'line_total' => $lineTotal,
                    'is_offer' => $isOffer,
                    'points' => $isOffer ? 0 : $this->points->previewLineEarn($item, (int) $line['qty']),
                ];
            }, $quote['lines']),
        ];
    }

    /**
     * @param  array<string|int, mixed>  $items
     */
    private function resolveLineKey(array $items, int $itemId, ?string $lineKey): ?string
    {
        if ($lineKey !== null && $lineKey !== '' && array_key_exists($lineKey, $items)) {
            return $lineKey;
        }

        if (array_key_exists($itemId, $items)) {
            return (string) $itemId;
        }

        if ($lineKey !== null && $lineKey !== '' && array_key_exists((int) $lineKey, $items)) {
            return (string) ((int) $lineKey);
        }

        foreach ($items as $key => $data) {
            $resolved = is_array($data) ? (int) ($data['item_id'] ?? $key) : (int) $key;
            if ($resolved === $itemId) {
                return (string) $key;
            }
        }

        return null;
    }

    /**
     * @param  array<string|int, mixed>  $items
     */
    private function quantityForItem(int $itemId, array $items, string $currentKey, int $newQtyForLine): int
    {
        $total = $newQtyForLine;
        foreach ($items as $key => $data) {
            if ((string) $key === (string) $currentKey) {
                continue;
            }
            $qty = is_array($data) ? (int) ($data['qty'] ?? 0) : (int) $data;
            if ($qty <= 0) {
                continue;
            }
            if ($this->isOfferEntry($data, (string) $key)) {
                $ids = array_filter([
                    (int) ($data['item_id'] ?? 0),
                    (int) ($data['second_item_id'] ?? 0),
                ]);
                $offer = MenuOffer::query()->find((int) ($data['offer_id'] ?? 0));
                if ($offer) {
                    $ids = $offer->includedItems()->pluck('id')->map(fn ($id) => (int) $id)->all();
                }
                if (in_array($itemId, $ids, true)) {
                    $total += $qty;
                }
                continue;
            }
            $resolved = is_array($data) ? (int) ($data['item_id'] ?? $key) : (int) $key;
            if ($resolved !== $itemId) {
                continue;
            }
            $total += $qty;
        }

        return $total;
    }

    /**
     * @param  mixed  $data
     */
    private function isOfferEntry($data, string $key): bool
    {
        if (str_starts_with($key, 'offer:')) {
            return true;
        }

        return is_array($data) && ($data['type'] ?? '') === 'offer';
    }

    private function assertSameRestaurant(int $restaurantId, ?Restaurant $restaurant): void
    {
        if (! $restaurant?->isVisible()) {
            throw new RuntimeException('هذا المطعم غير متاح للطلب حالياً.');
        }

        $restaurant->assertAcceptingOrders();

        $currentRestaurant = $this->restaurantId();
        if ($currentRestaurant && $currentRestaurant !== $restaurantId) {
            throw new RuntimeException('السلة تحتوي أصنافاً من مطعم آخر. أفرغ السلة أولاً أو أكمل الطلب الحالي.');
        }

        $groupId = Session::get('group_order_id');
        if ($groupId) {
            $groupRestaurantId = (int) Session::get('group_order_restaurant_id');
            if ($groupRestaurantId && $groupRestaurantId !== $restaurantId) {
                throw new RuntimeException('هذا طلب جماعي من مطعم آخر. اطلب من منيو الطلب الجماعي أو اخرج منه أولاً.');
            }
        }
    }
}
