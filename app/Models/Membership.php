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

    public function benefitsList(): array
    {
        $benefits = ["خصم {$this->discount_percent}% على كل طلب"];

        if ($this->free_delivery) {
            $benefits[] = 'توصيل مجاني';
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
