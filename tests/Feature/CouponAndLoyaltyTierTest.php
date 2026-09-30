<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponAndLoyaltyTierTest extends TestCase
{
    use RefreshDatabase;

    private function makeRestaurantAndItem(float $price = 50.0): array
    {
        $owner = User::factory()->create(['role' => 'restaurant']);
        $restaurant = Restaurant::create([
            'user_id' => $owner->id,
            'name' => 'مطعم القدس',
            'slug' => 'al-quds',
            'type' => 'restaurant',
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'وجبة شاورما عائلي',
            'category' => 'وجبات',
            'price' => $price,
            'is_available' => true,
        ]);

        return [$restaurant, $item];
    }

    public function test_customer_can_apply_and_remove_coupon_in_cart(): void
    {
        $user = User::factory()->create();
        [$restaurant, $item] = $this->makeRestaurantAndItem(50.0);

        Coupon::create([
            'code' => 'TEST10',
            'type' => 'fixed',
            'value' => 10,
            'min_subtotal' => 30,
            'is_active' => true,
        ]);

        // Add item to cart
        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($item, 1);

        // Apply valid coupon
        $response = $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'TEST10']);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'coupon' => [
                    'code' => 'TEST10',
                ],
            ]);

        // Verify quote includes coupon discount
        $quote = $cart->quote($user);
        $this->assertEquals(10.0, $quote['coupon_discount']);
        $this->assertEquals(10.0, $quote['discount_amount']);
        $this->assertEquals(40.0, $quote['subtotal'] - $quote['discount_amount']);

        // Remove coupon
        $removeResponse = $this->actingAs($user)
            ->deleteJson(route('cart.coupon.remove'));

        $removeResponse->assertOk()
            ->assertJson(['success' => true]);

        $quoteAfter = $cart->quote($user);
        $this->assertEquals(0, $quoteAfter['coupon_discount']);
    }

    public function test_user_loyalty_tier_calculates_correctly_based_on_spending(): void
    {
        $user = User::factory()->create();
        [$restaurant, $item] = $this->makeRestaurantAndItem();

        // Initially starter (< 150)
        $this->assertSame('starter', $user->tier()['key']);
        $this->assertSame('المستوى المبتدئ', $user->tier()['name']);

        // Order 150 shekels -> Bronze
        Order::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'name' => 'أحمد غزة',
            'phone' => '0599000111',
            'delivery_area' => 'الرمال',
            'address_details' => 'الرمال الجنوبي',
            'subtotal' => 140,
            'delivery_fee' => 10,
            'total' => 150,
            'status' => 'delivered',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $this->assertSame('bronze', $user->fresh()->tier()['key']);
        $this->assertSame('المستوى البرونزي', $user->fresh()->tier()['name']);
        $this->assertEquals(150.0, $user->fresh()->tier()['current_spent']);

        // Another order 200 (total subtotal 350) -> Silver (>= 300)
        Order::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'name' => 'أحمد غزة',
            'phone' => '0599000111',
            'delivery_area' => 'الرمال',
            'address_details' => 'الرمال الجنوبي',
            'subtotal' => 190,
            'delivery_fee' => 10,
            'total' => 200,
            'status' => 'delivered',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $this->assertSame('silver', $user->fresh()->tier()['key']);
        $this->assertSame('المستوى الفضي', $user->fresh()->tier()['name']);
        $this->assertEquals(350.0, $user->fresh()->tier()['current_spent']);
    }

    public function test_all_tier_icons_and_account_tier_roadmap_render_correctly(): void
    {
        $tiers = ['starter', 'bronze', 'silver', 'gold', 'platinum'];

        foreach ($tiers as $tier) {
            $rendered = view('partials.tier-icon', ['tier' => $tier, 'class' => 'w-10 h-10'])->render();
            $this->assertStringContainsString('<svg', $rendered);
            $this->assertStringContainsString('w-10 h-10', $rendered);
        }

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('account.show'));
        $response->assertOk();
        $response->assertSee('خريطة مستويات الزبائن في سفرة غزة:');
        $response->assertSee('مبتدئ');
        $response->assertSee('برونزي');
        $response->assertSee('فضي');
        $response->assertSee('ذهبي');
        $response->assertSee('بلاتيني (VIP)');
    }

    public function test_admin_can_create_coupon_for_specific_restaurant_or_all_restaurants(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$restaurant] = $this->makeRestaurantAndItem();

        // 1. Create coupon for specific restaurant
        $response1 = $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'QUDS20',
            'type' => 'percent',
            'value' => 20,
            'restaurant_id' => $restaurant->id,
        ]);
        $response1->assertRedirect();

        $coupon1 = Coupon::where('code', 'QUDS20')->first();
        $this->assertNotNull($coupon1);
        $this->assertEquals($restaurant->id, $coupon1->restaurant_id);
        $this->assertTrue($coupon1->isSpecificToRestaurant());
        $this->assertTrue($coupon1->appliesToRestaurant($restaurant->id));
        $this->assertFalse($coupon1->appliesToRestaurant($restaurant->id + 99));

        // 2. Create coupon for all restaurants (global)
        $response2 = $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'ALL10',
            'type' => 'fixed',
            'value' => 10,
            'restaurant_id' => '',
        ]);
        $response2->assertRedirect();

        $coupon2 = Coupon::where('code', 'ALL10')->first();
        $this->assertNotNull($coupon2);
        $this->assertNull($coupon2->restaurant_id);
        $this->assertFalse($coupon2->isSpecificToRestaurant());
        $this->assertTrue($coupon2->appliesToRestaurant($restaurant->id));
        $this->assertTrue($coupon2->appliesToRestaurant(999));
    }

    public function test_restaurant_specific_coupon_only_works_for_its_restaurant(): void
    {
        $user = User::factory()->create();
        [$restaurantA, $itemA] = $this->makeRestaurantAndItem(60.0);

        // Make second restaurant and item
        $ownerB = User::factory()->create(['role' => 'restaurant']);
        $restaurantB = Restaurant::create([
            'user_id' => $ownerB->id,
            'name' => 'شاورما العمدة',
            'slug' => 'omda',
            'type' => 'restaurant',
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
        ]);
        $itemB = MenuItem::create([
            'restaurant_id' => $restaurantB->id,
            'name' => 'ساندوتش شاورما',
            'category' => 'وجبات',
            'price' => 25.0,
            'is_available' => true,
        ]);

        // Create coupon specifically for Restaurant A
        Coupon::create([
            'code' => 'QUDS_ONLY',
            'type' => 'fixed',
            'value' => 15,
            'restaurant_id' => $restaurantA->id,
            'is_active' => true,
        ]);

        $cart = app(CartService::class);

        // 1. User has items from Restaurant B in cart
        $cart->clear();
        $cart->add($itemB, 2); // 50 shekels

        // Try applying Restaurant A's coupon to Restaurant B's cart
        $failResponse = $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'QUDS_ONLY']);

        $failResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
        $this->assertStringContainsString('مطعم القدس', $failResponse->json('message'));

        $quoteB = $cart->quote($user);
        $this->assertEquals(0, $quoteB['coupon_discount']);

        // 2. User has items from Restaurant A in cart
        $cart->clear();
        $cart->add($itemA, 1); // 60 shekels

        $successResponse = $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'QUDS_ONLY']);

        $successResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $quoteA = $cart->quote($user);
        $this->assertEquals(15.0, $quoteA['coupon_discount']);
    }
}
