<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OperatingHoursOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_customer_cannot_add_to_cart_when_restaurant_is_closed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-16 12:00:00')); // Friday noon
        [$dish] = $this->visibleDish([
            'opens_at' => '09:00',
            'closes_at' => '23:00',
            'working_days' => [6, 0, 1, 2, 3, 4],
        ]);
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->postJson(route('cart.add', $dish))
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }

    public function test_customer_can_add_to_cart_when_restaurant_is_open(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-15 12:00:00')); // Thursday noon
        [$dish] = $this->visibleDish([
            'opens_at' => '09:00',
            'closes_at' => '23:00',
            'working_days' => [6, 0, 1, 2, 3, 4],
        ]);
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->postJson(route('cart.add', $dish))
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_platform_hours_block_orders_even_if_restaurant_is_open(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-15 00:30:00')); // Thursday 12:30 AM
        $this->enablePlatformHours('09:00', '00:00', [6, 0, 1, 2, 3, 4, 5]);

        [$dish] = $this->visibleDish([
            'opens_at' => '12:00',
            'closes_at' => '01:00',
            'working_days' => [6, 0, 1, 2, 3, 4, 5],
        ]);
        $customer = User::factory()->create();

        $this->assertTrue($dish->restaurant->isWithinOwnHours());
        $this->assertFalse($dish->restaurant->isAcceptingOrders());

        $this->actingAs($customer)
            ->postJson(route('cart.add', $dish))
            ->assertStatus(422)
            ->assertJsonFragment(['ok' => false]);
    }

    public function test_checkout_is_blocked_when_restaurant_closes_after_cart_was_filled(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-15 22:00:00'));
        [$dish] = $this->visibleDish([
            'opens_at' => '09:00',
            'closes_at' => '23:00',
            'working_days' => [6, 0, 1, 2, 3, 4, 5],
        ]);
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->postJson(route('cart.add', $dish))
            ->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-10-15 23:30:00'));

        $this->actingAs($customer)
            ->get(route('checkout.create'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_admin_settings_page_shows_platform_hours(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('ساعات عمل منصة سفرة للتوصيل', false)
            ->assertSee('تفعيل ساعات عمل المنصة', false)
            ->assertSee('يغلق التوصيل الساعة', false);
    }

    public function test_admin_can_save_platform_hours(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'platform_hours_enabled' => '1',
                'platform_opens_at' => '09:00',
                'platform_closes_at' => '00:00',
                'platform_working_days' => [6, 0, 1, 2, 3, 4, 5],
            ])
            ->assertRedirect();

        Setting::forgetCache();
        $this->assertTrue(Setting::platformHoursEnabled());
        $this->assertSame('09:00', Setting::platformOpensAt());
        $this->assertSame('00:00', Setting::platformClosesAt());
    }

    public function test_partner_can_save_working_days_and_hours(): void
    {
        $owner = User::factory()->restaurantOwner()->create();
        Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الكرم',
            'type' => 'restaurant',
            'cuisine' => 'palestinian',
            'description' => 'مطبخ غزي بيتي يقدم المقلوبة والمسخن والقدرة لعائلة غزة.',
            'phone' => '0592002002',
            'owner_national_id' => '401234567',
            'area' => 'الرمال',
            'address' => 'شارع الجلاء، غزة',
            'opens_at' => '09:00',
            'closes_at' => '23:00',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        $this->actingAs($owner)
            ->get(route('partner.restaurant.edit'))
            ->assertOk()
            ->assertSee('أيام العمل', false)
            ->assertSee('يفتح الساعة', false);

        $this->actingAs($owner)
            ->put(route('partner.restaurant.update'), [
                'name' => 'مطعم الكرم',
                'type' => 'restaurant',
                'cuisine' => 'palestinian',
                'description' => 'مطبخ غزي بيتي يقدم المقلوبة والمسخن والقدرة لعائلة غزة.',
                'phone' => '0592002002',
                'owner_national_id' => '401234567',
                'area' => 'الرمال',
                'address' => 'شارع الجلاء، غزة',
                'opens_at' => '10:00',
                'closes_at' => '01:00',
                'working_days' => [6, 0, 1, 2, 3, 4],
            ])
            ->assertRedirect();

        $restaurant = Restaurant::query()->first();
        $this->assertSame('10:00', substr((string) $restaurant->opens_at, 0, 5));
        $this->assertSame('01:00', substr((string) $restaurant->closes_at, 0, 5));
        $this->assertEqualsCanonicalizing([6, 0, 1, 2, 3, 4], $restaurant->workingDays());
    }

    public function test_restaurant_page_shows_closed_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-16 12:00:00'));
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الجمعة مغلق',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'opens_at' => '09:00',
            'closes_at' => '23:00',
            'working_days' => [6, 0, 1, 2, 3, 4],
        ]);

        $this->get(route('restaurants.show', $restaurant))
            ->assertOk()
            ->assertSee('مغلق', false)
            ->assertDontSee('إضافة للسلة الآن', false);
    }

    /**
     * @param  array<string, mixed>  $hours
     * @return array{0: MenuItem, 1: Restaurant}
     */
    private function visibleDish(array $hours = []): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create(array_merge([
            'owner_id' => $owner->id,
            'name' => 'مطعم الدوام',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ], $hours));

        $dish = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'مسخن',
            'category' => 'وجبات',
            'price' => 30,
            'is_available' => true,
        ]);

        return [$dish, $restaurant];
    }

    /**
     * @param  list<int>  $days
     */
    private function enablePlatformHours(string $opens, string $closes, array $days): void
    {
        Setting::query()->updateOrCreate(['key' => 'platform_hours_enabled'], ['label' => 'تفعيل', 'value' => '1']);
        Setting::query()->updateOrCreate(['key' => 'platform_opens_at'], ['label' => 'فتح', 'value' => $opens]);
        Setting::query()->updateOrCreate(['key' => 'platform_closes_at'], ['label' => 'إغلاق', 'value' => $closes]);
        Setting::query()->updateOrCreate(
            ['key' => 'platform_working_days'],
            ['label' => 'أيام', 'value' => json_encode($days)]
        );
        Setting::forgetCache();
    }
}
