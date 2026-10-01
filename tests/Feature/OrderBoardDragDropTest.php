<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderBoardDragDropTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_orders_page_shows_kanban_columns(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->makeOrder('pending_confirmation');

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('بانتظار التأكيد', false)
            ->assertSee('قيد التحضير بالمطعم', false)
            ->assertSee('مع المندوب للتوصيل', false)
            ->assertSee('تم التسليم بنجاح', false)
            ->assertSee('#'.$order->id, false)
            ->assertSee('data-order-board', false)
            ->assertSee('data-order-drag-handle', false);
    }

    public function test_admin_can_drag_pending_order_into_preparing_column(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->makeOrder('pending_confirmation');

        $this->actingAs($admin)
            ->postJson(route('admin.orders.move', $order), ['column' => 'preparing'])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'status' => 'preparing',
                'column' => 'preparing',
            ]);

        $this->assertSame('preparing', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->confirmed_at);
    }

    public function test_admin_cannot_move_order_backwards_on_the_board(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->makeOrder('preparing');

        $this->actingAs($admin)
            ->postJson(route('admin.orders.move', $order), ['column' => 'pending_confirmation'])
            ->assertStatus(422);

        $this->assertSame('preparing', $order->fresh()->status);
    }

    public function test_partner_orders_and_dashboard_use_the_same_board(): void
    {
        [$owner, $order] = $this->makePartnerOrder('pending_confirmation');

        $this->actingAs($owner)
            ->get(route('partner.orders.index'))
            ->assertOk()
            ->assertSee('قيد التحضير بالمطعم', false)
            ->assertSee('#'.$order->id, false)
            ->assertSee('حساب الوجبات:', false)
            ->assertSee('/partner/orders/__ID__/move', false);

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('قيد التحضير بالمطعم', false)
            ->assertSee('#'.$order->id, false);
    }

    public function test_partner_can_drop_confirmed_order_into_preparing(): void
    {
        [$owner, $order] = $this->makePartnerOrder('confirmed');

        $this->actingAs($owner)
            ->postJson(route('partner.orders.move', $order), ['column' => 'preparing'])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'status' => 'preparing',
            ]);

        $this->assertSame('preparing', $order->fresh()->status);
    }

    public function test_partner_cannot_move_order_to_delivery_column(): void
    {
        [$owner, $order] = $this->makePartnerOrder('preparing');

        $this->actingAs($owner)
            ->postJson(route('partner.orders.move', $order), ['column' => 'delivering'])
            ->assertStatus(422);

        $this->assertSame('preparing', $order->fresh()->status);
    }

    public function test_customer_orders_page_shows_readonly_board(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder('preparing', $customer);

        $this->actingAs($customer)
            ->get(route('account.orders'))
            ->assertOk()
            ->assertSee('متابعة الطلبات', false)
            ->assertSee('قيد التحضير بالمطعم', false)
            ->assertSee('#'.$order->id, false)
            ->assertDontSee('data-move-url', false);
    }

    private function makeOrder(string $status, ?User $customer = null): Order
    {
        $customer ??= User::factory()->create();
        $restaurant = Restaurant::query()->create([
            'name' => 'مطعم الكرم',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        return Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'payment_method' => 'cash',
            'status' => $status,
            'address_details' => 'غزة - الرمال',
            'phone' => $customer->phone,
            'subtotal' => 28,
            'delivery_fee' => 0,
            'total' => 28,
            'confirmed_at' => in_array($status, ['confirmed', 'preparing', 'delivering', 'delivered'], true) ? now() : null,
        ]);
    }

    private function makePartnerOrder(string $status): array
    {
        $owner = User::factory()->create([
            'role' => 'restaurant_owner',
            'phone' => '0592001999',
        ]);

        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم دار الياسمين',
            'type' => 'restaurant',
            'address' => 'غزة',
            'phone' => '0592001999',
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'panel_suspended' => false,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $customer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'payment_method' => 'cash',
            'status' => $status,
            'address_details' => 'غزة - تل الهوى',
            'phone' => $customer->phone,
            'subtotal' => 20,
            'delivery_fee' => 8,
            'total' => 28,
            'confirmed_at' => in_array($status, ['confirmed', 'preparing', 'delivering', 'delivered'], true) ? now() : null,
        ]);

        $order->items()->create([
            'name' => 'شاورما',
            'price' => 20,
            'quantity' => 1,
            'line_total' => 20,
        ]);

        return [$owner, $order];
    }
}
