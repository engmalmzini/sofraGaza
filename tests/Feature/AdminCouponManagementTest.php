<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createRestaurant(): Restaurant
    {
        $owner = User::factory()->create(['role' => 'restaurant']);
        return Restaurant::create([
            'user_id' => $owner->id,
            'name' => 'مطعم القدس',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'address' => 'شارع الجلاء',
            'phone' => '0599123456',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
    }

    public function test_admin_can_view_coupons_page_with_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $restaurant = $this->createRestaurant();

        Coupon::create([
            'code' => 'ACTIVE20',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 20,
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'EXPIRED10',
            'type' => Coupon::TYPE_FIXED,
            'value' => 10,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.coupons.index'));

        $response->assertOk();
        $response->assertSee('ACTIVE20');
        $response->assertSee('EXPIRED10');
        $response->assertSee('إجمالي الكوبونات');
        $response->assertSee('الكوبونات الفعّالة');
        $response->assertSee('تعديل');
    }

    public function test_admin_can_update_coupon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $restaurant = $this->createRestaurant();

        $coupon = Coupon::create([
            'code' => 'ORIGINAL10',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.coupons.update', $coupon), [
            'code' => 'MODIFIED15',
            'restaurant_id' => $restaurant->id,
            'type' => Coupon::TYPE_PERCENT,
            'value' => 15,
            'min_order_amount' => 40,
            'max_discount' => 30,
            'usage_limit' => 50,
            'is_active' => 1,
            'description' => 'تحديث وصف الكوبون الجديد',
        ]);

        $response->assertSessionHas('success');
        $coupon->refresh();

        $this->assertEquals('MODIFIED15', $coupon->code);
        $this->assertEquals($restaurant->id, $coupon->restaurant_id);
        $this->assertEquals(15.0, (float) $coupon->value);
        $this->assertEquals(40.0, (float) $coupon->min_order_amount);
        $this->assertEquals(30.0, (float) $coupon->max_discount);
        $this->assertEquals(50, $coupon->usage_limit);
        $this->assertEquals('تحديث وصف الكوبون الجديد', $coupon->description);
    }

    public function test_admin_can_toggle_and_delete_coupon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $coupon = Coupon::create([
            'code' => 'TOGGLE25',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 25,
            'is_active' => true,
        ]);

        // Toggle to inactive
        $this->actingAs($admin)->post(route('admin.coupons.toggle', $coupon));
        $coupon->refresh();
        $this->assertFalse($coupon->is_active);

        // Toggle back to active
        $this->actingAs($admin)->post(route('admin.coupons.toggle', $coupon));
        $coupon->refresh();
        $this->assertTrue($coupon->is_active);

        // Delete
        $this->actingAs($admin)->delete(route('admin.coupons.destroy', $coupon));
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_guest_or_customer_cannot_access_coupon_admin_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $coupon = Coupon::create([
            'code' => 'SECRET',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 10,
            'is_active' => true,
        ]);

        $this->get(route('admin.coupons.index'))->assertRedirect(route('login'));
        $this->actingAs($customer)->get(route('admin.coupons.index'))->assertForbidden();
        $this->actingAs($customer)->put(route('admin.coupons.update', $coupon), [
            'code' => 'HACKED',
            'type' => 'fixed',
            'value' => 100,
        ])->assertForbidden();
    }
}
