<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_wipe_all_data_except_admin_accounts(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'مدير النظام']);
        $staff = User::factory()->staffAdmin(['orders'])->create(['name' => 'موظف']);
        $customer = User::factory()->create(['name' => 'زبون']);
        $owner = User::factory()->restaurantOwner()->create();
        $courier = User::factory()->courier()->create();

        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم للحذف',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'مسخن',
            'category' => 'وجبات',
            'price' => 20,
            'is_available' => true,
        ]);
        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'pending_confirmation',
            'address_details' => 'غزة',
            'phone' => $customer->phone,
            'total' => 20,
        ]);

        Setting::query()->updateOrCreate(
            ['key' => 'delivery_fee'],
            ['label' => 'رسوم التوصيل', 'value' => '10']
        );

        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.settings.wipe'), [
                'confirmation' => 'حذف الكل',
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'role' => 'admin']);
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertDatabaseMissing('users', ['id' => $courier->id]);
        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]);
        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('settings', ['key' => 'delivery_fee', 'value' => '10']);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_staff_admin_cannot_wipe_platform(): void
    {
        $staff = User::factory()->staffAdmin(['settings'])->create();
        User::factory()->create();

        $this->actingAs($staff)
            ->post(route('admin.settings.wipe'), [
                'confirmation' => 'حذف الكل',
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('users', 2);
    }

    public function test_wipe_requires_exact_confirmation_phrase(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.settings.wipe'), [
                'confirmation' => 'حذف',
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }

    public function test_dashboard_shows_wipe_button_for_super_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.settings.wipe'), false);
    }
}
