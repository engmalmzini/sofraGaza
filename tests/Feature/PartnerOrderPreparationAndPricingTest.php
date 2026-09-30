<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Membership;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerOrderPreparationAndPricingTest extends TestCase
{
    use RefreshDatabase;

    private function createPartnerWithRestaurant(): array
    {
        $owner = User::factory()->create([
            'role' => 'restaurant_owner',
            'phone' => '0599000111',
        ]);

        $restaurant = Restaurant::create([
            'owner_id' => $owner->id,
            'name' => 'مطعم السعادة',
            'type' => 'restaurant',
            'cuisine' => 'shawarma',
            'address' => 'غزة - شارع الجلاء',
            'phone' => '0599000111',
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'panel_suspended' => false,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
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
            'name' => 'شاورما دجاج',
            'price' => 35.00,
            'is_available' => true,
        ]);

        return [$owner, $restaurant, $item];
    }

    public function test_partner_does_not_see_delivery_fee_and_sees_food_items_only(): void
    {
        [$owner, $restaurant, $item] = $this->createPartnerWithRestaurant();
        $customer = User::factory()->create();

        $order = Order::create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'payment_method' => 'wallet',
            'status' => 'preparing',
            'delivery_area' => 'gaza_al_rimal',
            'address_details' => 'عمارة النور - شقة 5',
            'phone' => $customer->phone,
            'subtotal' => 35.00,
            'delivery_fee' => 10.00,
            'total' => 45.00,
            'confirmed_at' => now(),
        ]);

        $order->items()->create([
            'menu_item_id' => $item->id,
            'name' => $item->name,
            'price' => 35.00,
            'quantity' => 1,
            'line_total' => 35.00,
        ]);

        $this->assertEquals(35.0, $order->foodTotal());

        // Visit partner order show page
        $response = $this->actingAs($owner)->get(route('partner.orders.show', $order));
        $response->assertOk()
            ->assertSee('شاورما دجاج')
            ->assertSee('إجمالي حساب الوجبات')
            ->assertSee('35.00')
            ->assertDontSee('التوصيل (غزة')
            ->assertDontSee('45.00');

        // Visit partner orders index
        $indexResponse = $this->actingAs($owner)->get(route('partner.orders.index'));
        $indexResponse->assertOk()
            ->assertSee('حساب الوجبات:')
            ->assertSee('35.00');
    }

    public function test_partner_can_mark_order_prepared_and_admin_and_courier_are_notified(): void
    {
        $admin = User::factory()->admin()->create();
        $courier = User::factory()->courier()->create();
        [$owner, $restaurant, $item] = $this->createPartnerWithRestaurant();
        $customer = User::factory()->create();

        $order = Order::create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'payment_method' => 'wallet',
            'status' => 'preparing',
            'address_details' => 'غزة - الرمال',
            'phone' => $customer->phone,
            'subtotal' => 35.00,
            'delivery_fee' => 10.00,
            'total' => 45.00,
            'confirmed_at' => now(),
        ]);

        $order->items()->create([
            'menu_item_id' => $item->id,
            'name' => $item->name,
            'price' => 35.00,
            'quantity' => 1,
            'line_total' => 35.00,
        ]);

        $this->assertFalse($order->isPrepared());

        // Partner sees "تم تجهيز الطلب" button
        $this->actingAs($owner)->get(route('partner.orders.show', $order))
            ->assertOk()
            ->assertSee('تم تجهيز الطلب (جاهز للاستلام)');

        // Partner clicks "تم تجهيز الطلب"
        $response = $this->actingAs($owner)->post(route('partner.orders.prepared', $order));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertTrue($order->isPrepared());
        $this->assertNotNull($order->prepared_at);

        // Admin received notification
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $admin->id,
            'title' => "الطلب #{$order->id} جاهز للاستلام بالمطعم 🍳",
        ]);

        // Courier received notification
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $courier->id,
            'title' => "الطلب #{$order->id} جاهز للاستلام 🍳",
        ]);

        // Partner order show now displays ready state
        $this->actingAs($owner)->get(route('partner.orders.show', $order))
            ->assertOk()
            ->assertSee('الوجبات جاهزة في المطبخ للاستلام')
            ->assertDontSee('تم تجهيز الطلب (جاهز للاستلام)');

        // Admin delivery board shows "جهّز بالمطعم" badge
        $this->actingAs($admin)->get(route('admin.delivery.index', ['tab' => 'all']))
            ->assertOk()
            ->assertSee('جهز بالمطعم');
    }
}
