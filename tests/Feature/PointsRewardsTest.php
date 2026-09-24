<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
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

    public function test_delivered_order_earns_points_on_food_and_delivery_at_global_rate(): void
    {
        $this->seedPointSettings();
        [$user, $restaurant, $item] = $this->makeOrderContext(30);

        $order = $this->deliveredOrder($user, $restaurant, $item, 30, 10);

        app(PointsService::class)->earnForOrder($order);

        $this->assertSame(40, $order->fresh()->points_earned);
        $this->assertSame(40, $user->fresh()->points_balance);
    }

    public function test_restaurant_earn_rate_overrides_global_setting(): void
    {
        $this->seedPointSettings();
        [$user, $restaurant, $item] = $this->makeOrderContext(30);
        $restaurant->update(['points_per_amount' => 2]);

        $order = $this->deliveredOrder($user, $restaurant, $item, 30, 10);
        app(PointsService::class)->earnForOrder($order);

        $this->assertSame(20, $order->fresh()->points_earned);
    }

    public function test_item_earn_points_override_price_and_restaurant_rate(): void
    {
        $this->seedPointSettings();
        [$user, $restaurant, $item] = $this->makeOrderContext(30);
        $item->update(['earn_points' => 5]);

        $order = $this->deliveredOrder($user, $restaurant, $item, 30, 10);
        app(PointsService::class)->earnForOrder($order);

        $this->assertSame(15, $order->fresh()->points_earned);
    }

    public function test_item_redeem_points_override_price(): void
    {
        $this->seedPointSettings();
        $restaurant = $this->visibleRestaurant();
        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'بيتزا خضار',
            'category' => 'وجبات',
            'price' => 30,
            'is_available' => true,
            'redeem_points' => 12,
        ]);

        $this->assertSame(12, app(PointsService::class)->redeemCost($item));
    }

    public function test_admin_can_set_global_and_restaurant_and_item_points(): void
    {
        $this->seedPointSettings();
        $admin = User::factory()->admin()->create();
        $restaurant = $this->visibleRestaurant();
        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'بيتزا خضار',
            'category' => 'وجبات',
            'price' => 30,
            'is_available' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'settings' => [
                    'points_per_amount' => '1',
                    'points_include_delivery' => '1',
                    'points_redeem_per_amount' => '1',
                ],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->put(route('admin.restaurants.update', $restaurant), [
                'name' => $restaurant->name,
                'type' => 'restaurant',
                'starts_at' => $restaurant->starts_at->format('Y-m-d'),
                'expires_at' => $restaurant->expires_at->format('Y-m-d'),
                'is_active' => 1,
                'points_per_amount' => '2',
                'points_redeem_per_amount' => '3',
            ])
            ->assertRedirect();

        $this->assertEquals(2, (float) $restaurant->fresh()->points_per_amount);
        $this->assertEquals(3, (float) $restaurant->fresh()->points_redeem_per_amount);

        $this->actingAs($admin)
            ->put(route('admin.restaurants.menu-items.update', [$restaurant, $item]), [
                'name' => $item->name,
                'category' => 'وجبات',
                'price' => 30,
                'is_available' => 1,
                'earn_points' => 7,
                'redeem_points' => 9,
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertSame(7, $item->earn_points);
        $this->assertSame(9, $item->redeem_points);
    }

    public function test_redeem_page_shows_admin_rates_for_selected_restaurant(): void
    {
        $this->seedPointSettings();
        Setting::query()->where('key', 'points_per_amount')->update(['value' => '1']);
        Setting::forgetCache();

        $user = User::factory()->create(['points_balance' => 80]);
        $restaurant = $this->visibleRestaurant();
        $restaurant->update([
            'points_per_amount' => 2.5,
            'points_redeem_per_amount' => 3,
        ]);

        MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'برجر لحم كلاسيك',
            'category' => 'وجبات',
            'price' => 30,
            'is_available' => true,
        ]);

        $this->actingAs($user)
            ->get(route('redeem.create', ['restaurant_id' => $restaurant->id]))
            ->assertOk()
            ->assertSee('كل 2.5 شيكل = نقطة', false)
            ->assertSee('كل 3 شيكل = نقطة استبدال', false)
            ->assertSee('10', false)
            ->assertDontSee('سعر الطبق نفسه بالنقاط', false);
    }

    public function test_partner_cannot_set_item_points(): void
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الشريك',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'شاورما',
            'category' => 'وجبات',
            'price' => 18,
            'is_available' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('partner.menu-items.edit', $item))
            ->assertOk()
            ->assertDontSee('تخصيص النقاط لهذا الصنف', false);

        $this->actingAs($owner)
            ->put(route('partner.menu-items.update', $item), [
                'name' => 'شاورما',
                'category' => 'وجبات',
                'price' => 18,
                'is_available' => 1,
                'earn_points' => 99,
                'redeem_points' => 88,
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertNull($item->earn_points);
        $this->assertNull($item->redeem_points);
    }

    private function seedPointSettings(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'points_per_amount'],
            ['value' => '1', 'label' => 'اكتساب']
        );
        Setting::query()->updateOrCreate(
            ['key' => 'points_redeem_per_amount'],
            ['value' => '1', 'label' => 'استبدال']
        );
        Setting::query()->updateOrCreate(
            ['key' => 'points_include_delivery'],
            ['value' => '1', 'label' => 'توصيل']
        );
        Setting::forgetCache();
    }

    private function visibleRestaurant(): Restaurant
    {
        return Restaurant::query()->create([
            'name' => 'بيتزا مرزامار',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
    }

    /**
     * @return array{0: User, 1: Restaurant, 2: MenuItem}
     */
    private function makeOrderContext(float $price): array
    {
        $user = User::factory()->create();
        $restaurant = $this->visibleRestaurant();
        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'بيتزا خضار',
            'category' => 'وجبات',
            'price' => $price,
            'is_available' => true,
        ]);

        return [$user, $restaurant, $item];
    }

    private function deliveredOrder(User $user, Restaurant $restaurant, MenuItem $item, float $food, float $delivery): Order
    {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'address_details' => 'حي الرمال',
            'phone' => $user->phone,
            'subtotal' => $food,
            'discount_amount' => 0,
            'delivery_fee' => $delivery,
            'total' => $food + $delivery,
            'delivered_at' => now(),
        ]);

        $order->items()->create([
            'menu_item_id' => $item->id,
            'name' => $item->name,
            'price' => $food,
            'quantity' => 1,
            'line_total' => $food,
        ]);

        return $order->fresh(['items.menuItem', 'restaurant', 'user']);
    }
}
