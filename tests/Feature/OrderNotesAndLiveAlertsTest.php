<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNotesAndLiveAlertsTest extends TestCase
{
    use RefreshDatabase;

    private function createRestaurantWithApprovedListing(): array
    {
        $owner = User::factory()->create([
            'role' => 'restaurant_owner',
            'phone' => '0599000001',
        ]);

        $restaurant = Restaurant::create([
            'owner_id' => $owner->id,
            'name' => 'مطعم النجوم',
            'type' => 'restaurant',
            'cuisine' => 'shawarma',
            'address' => 'غزة - الرمال',
            'area' => 'الرمال',
            'phone' => '0599111222',
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'is_active' => true,
            'panel_suspended' => false,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
        ]);

        \App\Models\RestaurantPlan::seedDefaults();
        $plan = \App\Models\RestaurantPlan::first();
        $restaurant->listingSubscriptions()->create([
            'restaurant_plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'بيتزا خضار سوبريم',
            'price' => 35.00,
            'is_available' => true,
        ]);

        return [$owner, $restaurant, $item];
    }

    public function test_customer_can_add_item_to_cart_with_custom_notes(): void
    {
        [$owner, $restaurant, $item] = $this->createRestaurantWithApprovedListing();
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)
            ->post(route('cart.add', $item), [
                'quantity' => 2,
                'notes' => 'زيادة فلفل حار وبدون جبنة',
            ]);

        $response->assertRedirect();

        $cartResponse = $this->actingAs($customer)->get(route('cart.index'));
        $cartResponse->assertOk()
            ->assertSee('بيتزا خضار سوبريم')
            ->assertSee('زيادة فلفل حار وبدون جبنة');
    }

    public function test_customer_can_update_item_notes_in_cart(): void
    {
        [$owner, $restaurant, $item] = $this->createRestaurantWithApprovedListing();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->post(route('cart.add', $item), [
            'quantity' => 1,
            'notes' => 'ملاحظة قديمة',
        ]);

        $this->actingAs($customer)->patch(route('cart.update'), [
            'item_id' => $item->id,
            'quantity' => 2,
            'notes' => 'ملاحظة جديدة معدلة',
        ]);

        $cartResponse = $this->actingAs($customer)->get(route('cart.index'));
        $cartResponse->assertOk()
            ->assertSee('ملاحظة جديدة معدلة')
            ->assertDontSee('ملاحظة قديمة');
    }

    public function test_order_creation_persists_item_notes_in_database(): void
    {
        [$owner, $restaurant, $item] = $this->createRestaurantWithApprovedListing();
        $customer = User::factory()->create([
            'role' => 'customer',
            'wallet_balance' => 100,
        ]);

        $this->actingAs($customer)->post(route('cart.add', $item), [
            'quantity' => 1,
            'notes' => 'بدون بصل وزيادة شطة',
        ]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'payment_method' => 'wallet',
            'area' => 'الرمال',
            'address_details' => 'غزة - شارع عمر المختار بجوار المسجد',
            'phone' => '0599333444',
            'notes' => 'يرجى التوصيل للطابق الثالث',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertCount(1, $order->items);

        $orderItem = $order->items->first();
        $this->assertEquals('بدون بصل وزيادة شطة', $orderItem->notes);
        $this->assertEquals('بيتزا خضار سوبريم', $orderItem->name);
    }

    public function test_courier_order_details_displays_exact_financials_and_item_notes(): void
    {
        [$owner, $restaurant, $item] = $this->createRestaurantWithApprovedListing();
        $customer = User::factory()->create(['role' => 'customer']);
        $courier = User::factory()->create([
            'role' => 'courier',
            'courier_status' => User::COURIER_APPROVED,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'payment_method' => 'receipt',
            'status' => 'delivering',
            'address_details' => 'حي النصر بجوار الصيدلية',
            'phone' => '0599112233',
            'subtotal' => 35.00,
            'delivery_fee' => 5.00,
            'total' => 40.00,
        ]);

        $order->items()->create([
            'menu_item_id' => $item->id,
            'name' => $item->name,
            'price' => 35.00,
            'quantity' => 1,
            'line_total' => 35.00,
            'notes' => 'زيادة فلفل حار وبدون بصل',
        ]);

        $response = $this->actingAs($courier)->get(route('courier.orders.show', $order));
        $response->assertOk()
            ->assertSee('تدفعه للمطعم (قيمة الأكل)')
            ->assertSee('35.00')
            ->assertSee('أجرة التوصيل (لك)')
            ->assertSee('5.00')
            ->assertSee('زيادة فلفل حار وبدون بصل')
            ->assertSee('ملاحظة الزبون للصنف');
    }

    public function test_partner_orders_live_endpoint_returns_json_and_new_order_state(): void
    {
        [$owner, $restaurant, $item] = $this->createRestaurantWithApprovedListing();
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'payment_method' => 'receipt',
            'status' => 'confirmed',
            'address_details' => 'غزة - الرمال',
            'phone' => '0599112233',
            'subtotal' => 35.00,
            'delivery_fee' => 5.00,
            'total' => 40.00,
        ]);

        $order->items()->create([
            'menu_item_id' => $item->id,
            'name' => $item->name,
            'price' => 35.00,
            'quantity' => 1,
            'line_total' => 35.00,
            'notes' => 'صلصة إضافية',
        ]);

        // When last_id is equal to latest order ID (already seen)
        $response = $this->actingAs($owner)->getJson(route('partner.orders.live', ['last_id' => $order->id]));
        $response->assertOk()
            ->assertJson([
                'latest_id' => $order->id,
                'active_count' => 1,
                'has_new' => false,
            ]);

        // When last_id is smaller than new order ID (new order arrived)
        $responseWithOldId = $this->actingAs($owner)->getJson(route('partner.orders.live', ['last_id' => $order->id - 1]));
        $responseWithOldId->assertOk()
            ->assertJson([
                'latest_id' => $order->id,
                'has_new' => true,
            ]);
    }

    public function test_admin_orders_live_endpoint_returns_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$owner, $restaurant, $item] = $this->createRestaurantWithApprovedListing();
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'payment_method' => 'receipt',
            'status' => 'pending_confirmation',
            'address_details' => 'غزة',
            'phone' => '0599112233',
            'subtotal' => 35.00,
            'total' => 40.00,
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.orders.live', ['last_id' => $order->id]));
        $response->assertOk()
            ->assertJson([
                'latest_id' => $order->id,
                'pending_count' => 1,
                'has_new' => false,
            ]);
    }

    public function test_customer_can_remove_item_via_json_cart_update(): void
    {
        [$owner, $restaurant, $item] = $this->createRestaurantWithApprovedListing();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->post(route('cart.add', $item), [
            'quantity' => 1,
        ]);

        $response = $this->actingAs($customer)->patchJson(route('cart.update'), [
            'item_id' => $item->id,
            'quantity' => 0,
        ]);

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'message' => 'تم تحديث السلة.',
                'cart' => [
                    'count' => 0,
                    'lines' => [],
                ],
            ]);
    }

    public function test_customer_can_clear_entire_cart_via_json_delete(): void
    {
        [$owner, $restaurant, $item] = $this->createRestaurantWithApprovedListing();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->post(route('cart.add', $item), [
            'quantity' => 2,
        ]);

        $response = $this->actingAs($customer)->deleteJson(route('cart.clear'));

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'message' => 'تم إفراغ السلة.',
                'cart' => [
                    'count' => 0,
                    'lines' => [],
                ],
            ]);
    }
}

