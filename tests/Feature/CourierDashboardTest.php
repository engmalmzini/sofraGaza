<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_open_courier_board(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('courier.dashboard'))
            ->assertForbidden();
    }

    public function test_courier_sees_ready_orders_and_can_claim_then_deliver(): void
    {
        $courier = User::factory()->courier()->create();
        $other = User::factory()->courier()->create();
        $customer = User::factory()->create(['name' => 'ليلى الغزي']);
        $restaurant = Restaurant::query()->create([
            'name' => 'مطعم الكرم',
            'type' => 'restaurant',
            'address' => 'الرمال — شارع الجلاء',
            'phone' => '0592001099',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'preparing',
            'address_details' => 'تل الهوى عمارة النور',
            'phone' => $customer->phone,
            'total' => 35,
        ]);
        $order->items()->create([
            'name' => 'مسخن',
            'price' => 35,
            'quantity' => 1,
            'line_total' => 35,
        ]);

        $this->actingAs($courier)
            ->get(route('courier.dashboard'))
            ->assertOk()
            ->assertSee('طلبات جاهزة', false)
            ->assertSee('مطعم الكرم', false)
            ->assertSee('تل الهوى عمارة النور', false);

        $this->actingAs($courier)
            ->post(route('courier.orders.claim', $order))
            ->assertRedirect(route('courier.orders.show', $order));

        $order->refresh();
        $this->assertSame('delivering', $order->status);
        $this->assertSame($courier->id, $order->courier_id);

        $this->actingAs($other)
            ->post(route('courier.orders.claim', $order))
            ->assertRedirect();
        $this->assertSame($courier->id, $order->fresh()->courier_id);

        $this->actingAs($courier)
            ->get(route('courier.dashboard', ['tab' => 'mine']))
            ->assertOk()
            ->assertSee('مطعم الكرم', false);

        $this->actingAs($courier)
            ->post(route('courier.orders.complete', $order))
            ->assertRedirect(route('courier.dashboard', ['tab' => 'done']));

        $this->assertSame('delivered', $order->fresh()->status);
        $this->actingAs($courier)
            ->get(route('courier.dashboard', ['tab' => 'done']))
            ->assertSee('مطعم الكرم', false);
    }

    public function test_courier_login_goes_to_delivery_board(): void
    {
        $courier = User::factory()->courier()->create([
            'phone' => '0593003003',
            'password' => '123456',
        ]);

        $this->post(route('login'), [
            'phone' => '0593003003',
            'password' => '123456',
        ])->assertRedirect(route('courier.dashboard'));

        $this->assertAuthenticatedAs($courier);
    }
}
