<?php

namespace App\Models;

use App\Models\Concerns\HasStoredReceipt;
use App\Support\Finance;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasStoredReceipt;

    public const STATUSES = [
        'pending_confirmation' => 'بانتظار تأكيد الدفع',
        'confirmed' => 'مؤكد',
        'preparing' => 'قيد التحضير',
        'delivering' => 'قيد التوصيل',
        'delivered' => 'تم التسليم',
        'rejected' => 'مرفوض',
        'cancelled' => 'ملغي',
    ];

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'restaurant_name',
        'group_order_id',
        'courier_id',
        'membership_id',
        'coupon_id',
        'coupon_code',
        'type',
        'payment_method',
        'status',
        'delivery_area',
        'address_details',
        'phone',
        'notes',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'delivery_fee',
        'total',
        'points_earned',
        'points_spent',
        'transfer_receipt_path',
        'rejection_reason',
        'confirmed_at',
        'prepared_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'prepared_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (filled($order->restaurant_name) || ! $order->restaurant_id) {
                return;
            }

            $order->restaurant_name = Restaurant::query()->where('id', $order->restaurant_id)->value('name');
        });
    }

    public function venueName(): string
    {
        $name = trim((string) ($this->restaurant_name ?: $this->restaurant?->name));

        return $name !== '' ? $name : 'مطعم محذوف';
    }

    public function isPrepared(): bool
    {
        return $this->prepared_at !== null;
    }

    public function foodTotal(): float
    {
        return max(0.0, round((float) $this->subtotal, 2));
    }

    /**
     * Payload for Windows/Chrome desktop toasts on admin and partner boards.
     *
     * @return array{
     *     id: int,
     *     customer: ?string,
     *     phone: ?string,
     *     restaurant: string,
     *     area: ?string,
     *     items: string,
     *     items_count: int,
     *     notes: ?string,
     *     total: float,
     *     payment: string,
     *     status: string,
     *     status_label: string
     * }
     */
    public function desktopAlertPayload(string $tone = 'admin'): array
    {
        $this->loadMissing(['user', 'restaurant', 'items']);

        $itemBits = $this->items->take(3)->map(function ($item) {
            $label = ((int) $item->quantity).'× '.$item->name;
            $choices = $item->choicesLabel();

            return $choices !== '' ? $label.' ('.$choices.')' : $label;
        })->all();

        if ($this->items->count() > 3) {
            $itemBits[] = '+'.($this->items->count() - 3);
        }

        $notes = trim((string) $this->notes);
        $itemNotes = $this->items
            ->filter(fn ($item) => filled($item->notes))
            ->take(2)
            ->map(fn ($item) => $item->name.': '.$item->notes)
            ->implode('، ');
        if ($itemNotes !== '') {
            $notes = $notes !== '' ? $notes.' — '.$itemNotes : $itemNotes;
        }
        if (mb_strlen($notes) > 90) {
            $notes = mb_substr($notes, 0, 88).'…';
        }

        return [
            'id' => (int) $this->id,
            'customer' => $this->user?->name,
            'phone' => $this->phone ?: $this->user?->phone,
            'restaurant' => $this->venueName(),
            'area' => $tone === 'partner' ? null : $this->deliveryAreaLabel(),
            'items' => implode('، ', $itemBits),
            'items_count' => $this->items->count(),
            'notes' => $notes !== '' ? $notes : null,
            'total' => $tone === 'partner' ? $this->foodTotal() : (float) $this->total,
            'payment' => $this->isPaidWithWallet() ? 'محفظة' : 'كاش',
            'status' => (string) $this->status,
            'status_label' => $this->statusLabel(),
        ];
    }

    public function isPurchase(): bool
    {
        return ($this->type ?? 'purchase') !== 'redemption';
    }

    public function isRedemption(): bool
    {
        return ($this->type ?? '') === 'redemption' || (int) $this->points_spent > 0;
    }

    public function commissionRate(): float
    {
        $this->loadMissing('restaurant');

        return $this->restaurant
            ? $this->restaurant->commissionRate()
            : Finance::restaurantCommissionRate();
    }

    public function commissionLabel(): string
    {
        $this->loadMissing('restaurant');

        return $this->restaurant
            ? $this->restaurant->commissionLabel()
            : Finance::restaurantCommissionLabel();
    }

    public function platformCommission(): float
    {
        return round($this->foodTotal() * $this->commissionRate(), 2);
    }

    public function restaurantNet(): float
    {
        return round($this->foodTotal() - $this->platformCommission(), 2);
    }

    public function courierFinanceShare(): float
    {
        return round($this->foodTotal() * Finance::COURIER_ORDER_SHARE_RATE, 2);
    }

    public function isPremiumGift(): bool
    {
        $this->loadMissing('membership');

        return $this->isRedemption()
            && (float) ($this->membership?->monthly_price ?? 0) >= Finance::PREMIUM_GIFT_MIN_PRICE;
    }

    public function giftCost(): float
    {
        if (! $this->isRedemption()) {
            return 0.0;
        }

        $this->loadMissing('items.menuItem');

        return round($this->items->sum(function (OrderItem $item) {
            $unit = (float) ($item->menuItem?->price ?: $item->price);

            return $unit * max(1, (int) $item->quantity);
        }), 2);
    }

    public function scopePurchase(Builder $query): Builder
    {
        return $query->where('type', '!=', 'redemption');
    }

    public function scopeDeliveredIn(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query->where('status', 'delivered')->where(function (Builder $inner) use ($start, $end) {
            $inner->whereBetween('delivered_at', [$start, $end])
                ->orWhere(function (Builder $fallback) use ($start, $end) {
                    $fallback->whereNull('delivered_at')->whereBetween('updated_at', [$start, $end]);
                });
        });
    }

    public function scopeIncompleteIn(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query->whereIn('status', ['cancelled', 'rejected'])
            ->whereBetween('updated_at', [$start, $end]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function groupOrder(): BelongsTo
    {
        return $this->belongsTo(GroupOrder::class);
    }

    public function isGroupOrder(): bool
    {
        return $this->group_order_id !== null;
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['pending_confirmation', 'confirmed'], true) && $this->courier_id === null;
    }

    public function isAvailableForCourier(): bool
    {
        return $this->courier_id === null
            && in_array($this->status, ['preparing', 'delivering'], true);
    }

    public function deliveryAreaLabel(): ?string
    {
        if (! $this->delivery_area) {
            return null;
        }

        $area = collect(Setting::allAreas())->firstWhere('key', $this->delivery_area);

        return $area['label'] ?? $this->delivery_area;
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function isPaidWithWallet(): bool
    {
        return $this->payment_method === 'wallet';
    }

    public function isPaymentConfirmed(): bool
    {
        if ($this->status === 'pending_confirmation') {
            return false;
        }

        return $this->confirmed_at !== null
            || in_array($this->status, ['confirmed', 'preparing', 'delivering', 'delivered'], true);
    }

    public function isVisibleToRestaurant(): bool
    {
        return $this->isPaymentConfirmed();
    }

    public function shouldShowPaidBadge(): bool
    {
        return $this->isPaymentConfirmed() && ! in_array($this->status, ['rejected', 'cancelled'], true);
    }

    public function scopeVisibleToRestaurant(Builder $query): Builder
    {
        return $query->where('status', '!=', 'pending_confirmation')
            ->where(function (Builder $inner) {
                $inner->whereNotNull('confirmed_at')
                    ->orWhereIn('status', ['confirmed', 'preparing', 'delivering', 'delivered']);
            });
    }

    public function paymentMethodLabel(): string
    {
        $label = match ($this->payment_method) {
            'wallet' => 'رصيد المحفظة',
            'receipt' => 'حوالة بنكية / جوال باي',
            'group' => 'طلب جماعي',
            default => $this->payment_method ?: 'حوالة',
        };

        if ($this->isGroupOrder()) {
            return 'طلب جماعي — كل واحد دفع نصيبه';
        }

        return $label;
    }

    public function nextStatuses(): array
    {
        return match ($this->status) {
            'pending_confirmation' => ['confirmed' => 'تأكيد الدفع وإرساله للمطعم', 'rejected' => 'رفض الطلب'],
            'confirmed' => ['preparing' => 'بدء التحضير'],
            'preparing' => ['delivering' => 'بدء التوصيل'],
            'delivering' => ['delivered' => 'تم التسليم'],
            default => [],
        };
    }

    public function boardColumn(): string
    {
        return match ($this->status) {
            'confirmed', 'preparing' => 'preparing',
            'delivering' => 'delivering',
            'delivered' => 'delivered',
            default => 'pending_confirmation',
        };
    }

    public static function groupForBoard($orders): array
    {
        $collection = collect($orders);

        return [
            'pending_confirmation' => $collection->where('status', 'pending_confirmation')->values(),
            'preparing' => $collection->whereIn('status', ['confirmed', 'preparing'])->values(),
            'delivering' => $collection->where('status', 'delivering')->values(),
            'delivered' => $collection->where('status', 'delivered')->values(),
        ];
    }
}
