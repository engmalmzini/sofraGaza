<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AreaDeliveryFeeTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@sofragaza.ps',
        ]);
    }

    private function customerUser(): User
    {
        return User::factory()->create([
            'role' => 'customer',
            'phone' => '0599000111',
        ]);
    }

    private function makeRestaurantAndItem(float $price = 30.0): array
    {
        $owner = User::factory()->create(['role' => 'restaurant']);
        $restaurant = Restaurant::create([
            'user_id' => $owner->id,
            'name' => 'مطعم النجوم',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'address' => 'شارع عمر المختار',
            'phone' => '0599111222',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'is_featured' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'شاورما دجاج',
            'price' => $price,
            'category' => 'وجبات',
            'is_available' => true,
        ]);

        return [$restaurant, $item];
    }

    public function test_setting_returns_area_delivery_fee_or_fallback(): void
    {
        Setting::query()->updateOrCreate(['key' => 'delivery_fee'], ['value' => '10', 'label' => 'رسوم التوصيل']);
        Setting::query()->updateOrCreate(['key' => 'delivery_fees_by_area'], [
            'value' => json_encode(['الرمال' => 10, 'خانيونس' => 20, 'دير البلح' => 15], JSON_UNESCAPED_UNICODE),
            'label' => 'رسوم التوصيل حسب المناطق',
        ]);
        Setting::forgetCache();

        $this->assertEquals(20.0, Setting::deliveryFeeForArea('خانيونس'));
        $this->assertEquals(15.0, Setting::deliveryFeeForArea('دير البلح'));
        $this->assertEquals(10.0, Setting::deliveryFeeForArea('الرمال'));
        // Unknown area falls back to default delivery_fee setting
        $this->assertEquals(10.0, Setting::deliveryFeeForArea('منطقة_غير_معروفة'));
    }

    public function test_admin_settings_page_displays_per_area_delivery_fees(): void
    {
        $admin = $this->adminUser();

        Setting::query()->updateOrCreate(['key' => 'delivery_fee'], ['value' => '10', 'label' => 'رسوم التوصيل (شيكل)']);
        Setting::query()->updateOrCreate(['key' => 'delivery_fees_by_area'], [
            'value' => json_encode(['الرمال' => 10, 'خانيونس' => 22], JSON_UNESCAPED_UNICODE),
            'label' => 'رسوم التوصيل حسب المناطق',
        ]);
        Setting::forgetCache();

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('إعدادات رسوم التوصيل');
        $response->assertSee('غزة • حي الرمال');
        $response->assertSee('غزة • خانيونس');
        $response->assertSee('delivery_fees_by_area[خانيونس]', false);
    }

    public function test_admin_can_update_delivery_fees_by_area(): void
    {
        $admin = $this->adminUser();

        Setting::query()->updateOrCreate(['key' => 'delivery_fee'], ['value' => '10', 'label' => 'رسوم التوصيل']);

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'settings' => [
                'delivery_fee' => '12',
            ],
            'delivery_fees_by_area' => [
                'الرمال' => '10',
                'خانيونس' => '25',
                'دير البلح' => '18',
            ],
        ]);

        $response->assertRedirect();
        Setting::forgetCache();

        $this->assertEquals('12', Setting::value('delivery_fee'));
        $this->assertEquals(25.0, Setting::deliveryFeeForArea('خانيونس'));
        $this->assertEquals(18.0, Setting::deliveryFeeForArea('دير البلح'));
        $this->assertEquals(10.0, Setting::deliveryFeeForArea('الرمال'));
    }

    public function test_admin_can_add_and_remove_custom_area(): void
    {
        $admin = $this->adminUser();

        Setting::query()->updateOrCreate(['key' => 'delivery_fee'], ['value' => '10', 'label' => 'رسوم التوصيل']);

        // Add custom area
        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'settings' => [
                'delivery_fee' => '10',
            ],
            'new_area_label' => 'غزة • الشيخ رضوان',
            'new_area_key' => 'الشيخ رضوان',
            'new_area_fee' => '14',
        ]);

        $response->assertRedirect();
        Setting::forgetCache();

        $this->assertEquals(14.0, Setting::deliveryFeeForArea('الشيخ رضوان'));
        $areas = Setting::allAreas();
        $this->assertNotEmpty(collect($areas)->firstWhere('key', 'الشيخ رضوان'));

        // Remove the custom area
        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'settings' => [
                'delivery_fee' => '10',
            ],
            'remove_area_key' => 'الشيخ رضوان',
        ]);

        $response->assertRedirect();
        Setting::forgetCache();

        $areasAfter = Setting::allAreas();
        $this->assertEmpty(collect($areasAfter)->firstWhere('key', 'الشيخ رضوان'));
    }

    public function test_cart_quote_reflects_delivery_area_fee(): void
    {
        $customer = $this->customerUser();
        [$restaurant, $item] = $this->makeRestaurantAndItem(40.0);

        Setting::query()->updateOrCreate(['key' => 'delivery_fee'], ['value' => '10', 'label' => 'رسوم التوصيل']);
        Setting::query()->updateOrCreate(['key' => 'delivery_fees_by_area'], [
            'value' => json_encode(['الرمال' => 10, 'خانيونس' => 20], JSON_UNESCAPED_UNICODE),
            'label' => 'رسوم التوصيل حسب المناطق',
        ]);
        Setting::forgetCache();

        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($item, 1);

        // Quote for الرمال
        $quoteRimal = $cart->quote($customer, 'الرمال');
        $this->assertEquals(10.0, $quoteRimal['delivery_fee']);
        $this->assertEquals(50.0, $quoteRimal['total']);

        // Quote for خانيونس
        $quoteKhan = $cart->quote($customer, 'خانيونس');
        $this->assertEquals(20.0, $quoteKhan['delivery_fee']);
        $this->assertEquals(60.0, $quoteKhan['total']);
    }

    public function test_checkout_places_order_with_area_delivery_fee_and_records_delivery_area(): void
    {
        $customer = $this->customerUser();
        [$restaurant, $item] = $this->makeRestaurantAndItem(50.0);

        Setting::query()->updateOrCreate(['key' => 'delivery_fee'], ['value' => '10', 'label' => 'رسوم التوصيل']);
        Setting::query()->updateOrCreate(['key' => 'delivery_fees_by_area'], [
            'value' => json_encode(['الرمال' => 10, 'خانيونس' => 25], JSON_UNESCAPED_UNICODE),
            'label' => 'رسوم التوصيل حسب المناطق',
        ]);
        Setting::forgetCache();

        // Put item in cart and set area to خانيونس
        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($item, 2); // 100 ₪

        $receipt = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'area' => 'خانيونس',
            'address_details' => 'خانيونس البلد قرب دوار أبو حميد بناية الصالحين',
            'phone' => '0599000111',
            'notes' => 'يرجى التوصيل بعد المغرب',
            'receipt' => $receipt,
        ]);

        $response->assertRedirect();
        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals('خانيونس', $order->delivery_area);
        $this->assertEquals(25.0, (float) $order->delivery_fee);
        $this->assertEquals(125.0, (float) $order->total); // 100 subtotal + 25 delivery
        $this->assertStringContainsString('خانيونس', $order->deliveryAreaLabel());
    }

    public function test_delivery_area_endpoint_updates_session_and_returns_json_with_new_fee(): void
    {
        Setting::query()->updateOrCreate(['key' => 'delivery_fee'], ['value' => '10', 'label' => 'رسوم التوصيل']);
        Setting::query()->updateOrCreate(['key' => 'delivery_fees_by_area'], [
            'value' => json_encode(['الرمال' => 10, 'دير البلح' => 15], JSON_UNESCAPED_UNICODE),
            'label' => 'رسوم التوصيل حسب المناطق',
        ]);
        Setting::forgetCache();

        $response = $this->postJson(route('delivery-area.update'), [
            'area' => 'دير البلح',
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'delivery_fee' => 15.0,
        ]);

        $this->assertEquals('دير البلح', session('delivery_area.key'));
    }
}
