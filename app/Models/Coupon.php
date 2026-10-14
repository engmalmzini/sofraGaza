<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    public const APPLIES_MEAL = 'meal';
    public const APPLIES_DELIVERY = 'delivery';
    public const APPLIES_TOTAL = 'total';

    public const APPLIES_TO = [
        self::APPLIES_MEAL => 'سعر الوجبة فقط',
        self::APPLIES_DELIVERY => 'رسوم التوصيل فقط',
        self::APPLIES_TOTAL => 'الطلب كامل (الوجبة + التوصيل)',
    ];

    protected $fillable = [
        'code',
        'restaurant_id',
        'type',
        'applies_to',
        'value',
        'min_order_amount',
        'max_discount',
        'expires_at',
        'usage_limit',
        'used_count',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'restaurant_id' => 'integer',
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'used_count' => 'integer',
            'usage_limit' => 'integer',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isSpecificToRestaurant(): bool
    {
        return $this->restaurant_id !== null;
    }

    public function appliesToRestaurant(?int $restaurantId): bool
    {
        if ($this->restaurant_id === null) {
            return true;
        }

        return $restaurantId !== null && (int) $this->restaurant_id === (int) $restaurantId;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedLimit(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    public function validateForSubtotal(float $subtotal, ?int $restaurantId = null): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'message' => 'كود الخصم غير مفعّل حالياً.'];
        }

        if ($this->isExpired()) {
            return ['valid' => false, 'message' => 'انتهت صلاحية كود الخصم هذا.'];
        }

        if ($this->hasReachedLimit()) {
            return ['valid' => false, 'message' => 'استُنفد الحد الأقصى لاستخدام كود الخصم.'];
        }

        if ($this->restaurant_id !== null) {
            if ($restaurantId === null || (int) $this->restaurant_id !== (int) $restaurantId) {
                $restaurantName = $this->restaurant?->name ?? 'المطعم المحدد';

                return [
                    'valid' => false,
                    'message' => "كود الخصم هذا مخصص فقط لطلبات مطعم \"{$restaurantName}\".",
                ];
            }
        }

        if ($this->min_order_amount && $subtotal < (float) $this->min_order_amount) {
            return [
                'valid' => false,
                'message' => "الحد الأدنى لقيمة الطلب لتطبيق هذا الكود هو {$this->min_order_amount} ₪.",
            ];
        }

        return ['valid' => true, 'message' => null];
    }

    public function appliesToTarget(): string
    {
        $target = (string) ($this->applies_to ?: self::APPLIES_MEAL);

        return array_key_exists($target, self::APPLIES_TO) ? $target : self::APPLIES_MEAL;
    }

    public function appliesToLabel(): string
    {
        return self::APPLIES_TO[$this->appliesToTarget()] ?? self::APPLIES_TO[self::APPLIES_MEAL];
    }

    public function discountBase(float $subtotal, float $deliveryFee = 0.0): float
    {
        return match ($this->appliesToTarget()) {
            self::APPLIES_DELIVERY => max(0.0, $deliveryFee),
            self::APPLIES_TOTAL => max(0.0, $subtotal + $deliveryFee),
            default => max(0.0, $subtotal),
        };
    }

    public function calculateDiscount(float $subtotal, float $deliveryFee = 0.0): float
    {
        return $this->allocateDiscount($subtotal, $deliveryFee)['total'];
    }

    /**
     * @return array{meal: float, delivery: float, total: float}
     */
    public function allocateDiscount(float $subtotal, float $deliveryFee = 0.0): array
    {
        $subtotal = max(0.0, $subtotal);
        $deliveryFee = max(0.0, $deliveryFee);
        $base = $this->discountBase($subtotal, $deliveryFee);
        $total = $this->amountOnBase($base);

        if ($total <= 0) {
            return ['meal' => 0.0, 'delivery' => 0.0, 'total' => 0.0];
        }

        return match ($this->appliesToTarget()) {
            self::APPLIES_DELIVERY => [
                'meal' => 0.0,
                'delivery' => $total,
                'total' => $total,
            ],
            self::APPLIES_TOTAL => $this->splitAcrossOrder($total, $subtotal, $deliveryFee),
            default => [
                'meal' => $total,
                'delivery' => 0.0,
                'total' => $total,
            ],
        };
    }

    public function formatDiscountLabel(): string
    {
        $target = $this->appliesToLabel();

        if ($this->type === self::TYPE_PERCENT) {
            $label = "خصم {$this->value}% على {$target}";
            if ($this->max_discount) {
                $label .= " (بحد أقصى {$this->max_discount} ₪)";
            }

            return $label;
        }

        return "خصم {$this->value} ₪ على {$target}";
    }

    private function amountOnBase(float $base): float
    {
        if ($base <= 0) {
            return 0.0;
        }

        if ($this->type === self::TYPE_PERCENT) {
            $discount = $base * ((float) $this->value / 100);
            if ($this->max_discount && $discount > (float) $this->max_discount) {
                $discount = (float) $this->max_discount;
            }
        } else {
            $discount = min($base, (float) $this->value);
        }

        return round($discount, 2);
    }

    /**
     * @return array{meal: float, delivery: float, total: float}
     */
    private function splitAcrossOrder(float $total, float $subtotal, float $deliveryFee): array
    {
        $combined = $subtotal + $deliveryFee;
        if ($combined <= 0) {
            return ['meal' => 0.0, 'delivery' => 0.0, 'total' => 0.0];
        }

        $meal = round($total * ($subtotal / $combined), 2);
        if ($meal > $subtotal) {
            $meal = $subtotal;
        }
        $delivery = round($total - $meal, 2);
        if ($delivery > $deliveryFee) {
            $delivery = $deliveryFee;
            $meal = round($total - $delivery, 2);
        }

        return [
            'meal' => $meal,
            'delivery' => $delivery,
            'total' => round($meal + $delivery, 2),
        ];
    }

    public function scopeLabel(): string
    {
        return $this->restaurant ? "مطعم: {$this->restaurant->name}" : 'جميع المطاعم';
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
