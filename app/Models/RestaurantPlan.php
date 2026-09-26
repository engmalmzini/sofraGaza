<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantPlan extends Model
{
    protected $fillable = [
        'name',
        'duration_days',
        'price',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(RestaurantSubscription::class);
    }

    public function durationLabel(): string
    {
        return match ((int) $this->duration_days) {
            30 => 'شهر واحد',
            180 => '6 أشهر',
            365 => 'سنة',
            default => $this->duration_days.' يوماً',
        };
    }

    public static function seedDefaults(): void
    {
        $plans = [
            [
                'name' => 'باقة شهر',
                'duration_days' => 30,
                'price' => 100,
                'description' => 'ظهور على المنصة لمدة شهر.',
                'sort_order' => 1,
            ],
            [
                'name' => 'باقة 6 أشهر',
                'duration_days' => 180,
                'price' => 500,
                'description' => 'ظهور على المنصة لمدة ستة أشهر.',
                'sort_order' => 2,
            ],
            [
                'name' => 'باقة سنة',
                'duration_days' => 365,
                'price' => 1000,
                'description' => 'ظهور على المنصة لمدة سنة كاملة.',
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            static::query()->updateOrCreate(
                ['duration_days' => $plan['duration_days']],
                $plan + ['is_active' => true]
            );
        }
    }
}
