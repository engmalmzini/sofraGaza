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

    protected $fillable = [
        'code',
        'restaurant_id',
        'type',
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

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = 0.0;

        if ($this->type === self::TYPE_PERCENT) {
            $discount = $subtotal * ((float) $this->value / 100);
            if ($this->max_discount && $discount > (float) $this->max_discount) {
                $discount = (float) $this->max_discount;
            }
        } else {
            $discount = min($subtotal, (float) $this->value);
        }

        return round($discount, 2);
    }

    public function formatDiscountLabel(): string
    {
        if ($this->type === self::TYPE_PERCENT) {
            $label = "خصم {$this->value}%";
            if ($this->max_discount) {
                $label .= " (بحد أقصى {$this->max_discount} ₪)";
            }

            return $label;
        }

        return "خصم {$this->value} ₪";
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
