<?php

namespace Tests;

use App\Models\Restaurant;
use App\Models\RestaurantPlan;
use App\Models\RestaurantSubscription;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function grantPaidListing(Restaurant $restaurant, int $days = 30): RestaurantSubscription
    {
        RestaurantPlan::seedDefaults();
        $plan = RestaurantPlan::query()->where('duration_days', $days)->first()
            ?? RestaurantPlan::query()->first();

        return RestaurantSubscription::query()->create([
            'restaurant_id' => $restaurant->id,
            'restaurant_plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now(),
            'ends_at' => now()->addDays($days),
        ]);
    }
}
