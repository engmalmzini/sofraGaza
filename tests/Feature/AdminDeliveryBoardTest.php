<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeliveryBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_idle_and_busy_couriers_and_can_assign_order(): void
    {
        $admin = User::factory()->admin()->create();
        $idle = User::factory()->courier()->create(['name' => 'سامي الدلفري']);
        $busy = User::factory()->courier()->create(['name' => 'ماهر النجار']);
        $customer = User::factory()->create(['name' => 'ليلى الغزي']);
        $restaurant = $this->restaurant();

        $waiting = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'preparing',
            'address_details' => 'تل الهوى عمارة النور',
            'phone' => $customer->phone,
            'total' => 35,
        ]);

        $active = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $busy->id,
            'type' => 'purchase',
            'status' => 'delivering',
            'address_details' => 'الرمال',
            'phone' => $customer->phone,
            'total' => 20,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.delivery.index'))
            ->assertOk()
            ->assertSee('إدارة التوصيل', false)
            ->assertSee('سامي الدلفري', false)
            ->assertSee('ماهر النجار', false)
            ->assertSee('فاضي', false)
            ->assertSee('مشغول', false)
            ->assertSee('تل الهوى عمارة النور', false)
            ->assertSee('التوصيل والمندوبون', false);

        $this->actingAs($admin)
            ->post(route('admin.delivery.assign', $waiting), ['courier_id' => $idle->id])
            ->assertRedirect();

        $waiting->refresh();
        $this->assertSame($idle->id, $waiting->courier_id);
        $this->assertSame('delivering', $waiting->status);

        $this->actingAs($admin)
            ->post(route('admin.delivery.unassign', $active))
            ->assertRedirect();

        $this->assertNull($active->fresh()->courier_id);
    }

    public function test_admin_can_add_courier_from_delivery_board(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.delivery.store'), [
                'name' => 'خليل المندوب',
                'phone' => '0593111222',
                'password' => '123456',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'خليل المندوب',
            'phone' => '0593111222',
            'role' => 'courier',
        ]);
    }

    public function test_customer_cannot_open_delivery_board(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.delivery.index'))
            ->assertForbidden();
    }

    private function restaurant(): Restaurant
    {
        return Restaurant::query()->create([
            'name' => 'مطعم الكرم',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);
    }
}
