<?php

namespace App\Models;

use App\Services\PointsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    protected $fillable = [
        'name',
        'monthly_price',
        'discount_percent',
        'free_delivery',
        'points_multiplier',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'discount_percent' => 'integer',
            'free_delivery' => 'boolean',
            'points_multiplier' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MembershipSubscription::class);
    }

    public function includesDeliveryBenefit(): bool
    {
        return (bool) $this->free_delivery;
    }

    public function deliveryDiscountPercent(): int
    {
        if (! $this->includesDeliveryBenefit()) {
            return 0;
        }

        return min(100, max(0, (int) $this->discount_percent));
    }

    public function discountedDeliveryFee(float $baseFee): float
    {
        $baseFee = max(0.0, $baseFee);
        $percent = $this->deliveryDiscountPercent();

        if ($percent <= 0) {
            return $baseFee;
        }

        return round($baseFee * (1 - ($percent / 100)), 2);
    }

    public function deliveryBenefitLabel(): ?string
    {
        if (! $this->includesDeliveryBenefit()) {
            return null;
        }

        $percent = $this->deliveryDiscountPercent();
        if ($percent >= 100) {
            return 'توصيل مجاني';
        }

        if ($percent <= 0) {
            return null;
        }

        return "خصم {$percent}% على التوصيل";
    }

    public function benefitsList(): array
    {
        $benefits = [];

        if ($label = $this->deliveryBenefitLabel()) {
            $benefits[] = $label;
        }

        $benefits[] = $this->extraPointsLabel();

        return $benefits;
    }

    public function extraPointsLabel(): string
    {
        $points = app(PointsService::class);
        $per = $points->formatShekelRate($points->shekelsPerEarnPoint());
        $rate = rtrim(rtrim(number_format((float) $this->points_multiplier, 2, '.', ''), '0'), '.');

        if ((float) $this->points_multiplier <= 1) {
            return 'نقطة واحدة لكل '.$per.' شيكل';
        }

        return $rate.' نقطة لكل '.$per.' شيكل بدل نقطة واحدة';
    }
}
