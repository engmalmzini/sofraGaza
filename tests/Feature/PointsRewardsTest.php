<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsRewardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_redemption_cost_matches_item_price_and_home_hides_locked_teasers(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'points_per_amount'],
            ['value' => '1', 'label' => 'كل كم شيكل = نقطة واحدة']
        );

        $restaurant = Restaurant::query()->create([
            'name' => 'شاورما العمدة',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'ساندوتش شاورما دجاج',
            'category' => 'ساندويش',
            'price' => 18,
            'is_available' => true,
        ]);

        $this->assertSame(18, app(PointsService::class)->redeemCost($item));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('ساندوتش شاورما دجاج', false)
            ->assertSee('18 نقطة', false)
            ->assertDontSee('متبقي', false)
            ->assertDontSee('بيتزا مارغريتا عائلية', false);
    }

    public function test_guest_does_not_see_fake_remaining_points(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('متبقي 35 نقطة', false);
    }

    public function test_restaurant_rewards_only_include_its_own_menu(): void
    {
        $grill = Restaurant::query()->create([
            'name' => 'مشاوي أبو العبد',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        MenuItem::query()->create([
            'restaurant_id' => $grill->id,
            'name' => 'عيران',
            'category' => 'مشروبات',
            'price' => 5,
            'is_available' => true,
        ]);

        $sweets = Restaurant::query()->create([
            'name' => 'حلويات الدحدوح',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        MenuItem::query()->create([
            'restaurant_id' => $sweets->id,
            'name' => 'كنافة نابلسية',
            'category' => 'حلويات',
            'price' => 16,
            'is_available' => true,
        ]);

        $this->get(route('restaurants.show', $grill))
            ->assertOk()
            ->assertSee('عيران', false)
            ->assertDontSee('كنافة نابلسية', false)
            ->assertDontSee('صحن كنافة عربية غزة', false);
    }

    public function test_restaurant_rewards_come_only_from_that_restaurant_menu(): void
    {
        $grill = Restaurant::query()->create([
            'name' => 'مشاوي أبو العبد',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
        MenuItem::query()->create([
            'restaurant_id' => $grill->id,
            'name' => 'عيران',
            'category' => 'مشروبات',
            'price' => 5,
            'is_available' => true,
        ]);

        $sweets = Restaurant::query()->create([
            'name' => 'حلويات الدحدوح',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
        MenuItem::query()->create([
            'restaurant_id' => $sweets->id,
            'name' => 'كنافة نابلسية',
            'category' => 'حلويات',
            'price' => 16,
            'is_available' => true,
        ]);

        $this->get(route('restaurants.show', $grill))
            ->assertOk()
            ->assertSee('عيران', false)
            ->assertDontSee('كنافة نابلسية', false)
            ->assertDontSee('صحن كنافة عربية غزة', false);
    }
}
