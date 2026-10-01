<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\CourierPayout;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierPayoutAndEarningsTest extends TestCase
{
    use RefreshDatabase;

    private function createRestaurant(): Restaurant
    {
        return Restaurant::query()->create([
            'name' => 'مطعم القدس',
            'type' => 'restaurant',
            'address' => 'غزة - شارع عمر المختار',
            'phone' => '0599112233',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);
    }

    public function test_courier_earnings_calculation_with_15_percent_deduction(): void
    {
        $courier = User::factory()->courier()->create();
        $customer = User::factory()->create();
        $restaurant = $this->createRestaurant();

        // Delivery 1: delivery_fee = 10
        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'delivered_at' => now(),
            'address_details' => 'حي النصر',
            'phone' => $customer->phone,
            'subtotal' => 50,
            'delivery_fee' => 10,
            'total' => 60,
        ]);

        // Delivery 2: delivery_fee = 15
        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'delivered_at' => now(),
            'address_details' => 'حي الرمال',
            'phone' => $customer->phone,
            'subtotal' => 40,
            'delivery_fee' => 15,
            'total' => 55,
        ]);

        // Total fees = 25
        // 15% platform fee = 3.75
        // 85% courier net = 21.25
        $this->assertEquals(25.0, $courier->courierTotalGrossDeliveryFees());
        $this->assertEquals(3.75, $courier->courierLifetimePlatformFee());
        $this->assertEquals(21.25, $courier->courierLifetimeNetEarnings());
        $this->assertEquals(21.25, $courier->courierAvailableBalance());

        $response = $this->actingAs($courier)->get(route('courier.wallet'));
        $response->assertOk()
            ->assertSee('21.25')
            ->assertSee('25.00')
            ->assertSee('طلب سحب الأرباح وتحويلها');
    }

    public function test_courier_period_filters_show_correct_earnings(): void
    {
        $courier = User::factory()->courier()->create();
        $customer = User::factory()->create();
        $restaurant = $this->createRestaurant();

        // Order today: fee = 10 (net 8.50)
        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'delivered_at' => today()->setHour(12),
            'address_details' => 'تل الهوى',
            'phone' => $customer->phone,
            'subtotal' => 30,
            'delivery_fee' => 10,
            'total' => 40,
        ]);

        // Order yesterday: fee = 20 (net 17.00)
        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'delivered_at' => today()->subDay()->setHour(14),
            'address_details' => 'الشيخ عجلين',
            'phone' => $customer->phone,
            'subtotal' => 60,
            'delivery_fee' => 20,
            'total' => 80,
        ]);

        // Check Today
        $todayEarnings = $courier->courierEarningsForPeriod('today');
        $this->assertEquals(1, $todayEarnings['count']);
        $this->assertEquals(10.0, $todayEarnings['total_fees']);
        $this->assertEquals(8.5, $todayEarnings['net_earnings']);

        // Check Yesterday
        $yesterdayEarnings = $courier->courierEarningsForPeriod('yesterday');
        $this->assertEquals(1, $yesterdayEarnings['count']);
        $this->assertEquals(20.0, $yesterdayEarnings['total_fees']);
        $this->assertEquals(17.0, $yesterdayEarnings['net_earnings']);

        // Check All
        $allEarnings = $courier->courierEarningsForPeriod('all');
        $this->assertEquals(2, $allEarnings['count']);
        $this->assertEquals(30.0, $allEarnings['total_fees']);
        $this->assertEquals(25.5, $allEarnings['net_earnings']);

        $this->actingAs($courier)
            ->get(route('courier.wallet', ['period' => 'yesterday']))
            ->assertOk()
            ->assertSee('17.00')
            ->assertSee('عرض كامل')
            ->assertSee(route('courier.wallet.statement', ['period' => 'yesterday'], false));

        $this->actingAs($courier)
            ->get(route('courier.wallet.statement', ['period' => 'today']))
            ->assertOk()
            ->assertSee('كشف حساب الأرباح')
            ->assertSee('8.50')
            ->assertSee('صافي حصتك');
    }

    public function test_courier_can_request_payout_and_admins_are_notified(): void
    {
        $admin = User::factory()->admin()->create();
        $courier = User::factory()->courier()->create();
        $customer = User::factory()->create();
        $restaurant = $this->createRestaurant();

        // 20 fee -> net 17.00
        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'delivered_at' => now(),
            'address_details' => 'الرمال',
            'phone' => $customer->phone,
            'subtotal' => 50,
            'delivery_fee' => 20,
            'total' => 70,
        ]);

        $this->assertEquals(17.0, $courier->courierAvailableBalance());

        $response = $this->actingAs($courier)->post(route('courier.wallet.payout'), [
            'amount' => 15.0,
            'payout_method' => 'jawwal_pay',
            'transfer_details' => 'محفظة جوال باي برقم 0599123456 باسم المندوب',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('courier_payouts', [
            'user_id' => $courier->id,
            'amount' => 15.0,
            'status' => CourierPayout::STATUS_PENDING,
            'payout_method' => 'jawwal_pay',
        ]);

        // Available balance is now 17 - 15 = 2.00
        $this->assertEquals(2.0, $courier->fresh()->courierAvailableBalance());
        $this->assertEquals(15.0, $courier->fresh()->courierPendingPayoutsAmount());

        // Courier profile should have saved default payout details
        $this->assertSame('jawwal_pay', $courier->fresh()->payout_method);
        $this->assertSame('محفظة جوال باي برقم 0599123456 باسم المندوب', $courier->fresh()->payout_details);

        // Admin received notification
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $admin->id,
        ]);
    }

    public function test_courier_cannot_request_payout_exceeding_available_balance(): void
    {
        $courier = User::factory()->courier()->create();
        $customer = User::factory()->create();
        $restaurant = $this->createRestaurant();

        // 10 fee -> net 8.50
        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'delivered_at' => now(),
            'address_details' => 'الرمال',
            'phone' => $customer->phone,
            'subtotal' => 20,
            'delivery_fee' => 10,
            'total' => 30,
        ]);

        $response = $this->actingAs($courier)->post(route('courier.wallet.payout'), [
            'amount' => 20.0, // more than 8.50
            'payout_method' => 'jawwal_pay',
            'transfer_details' => '0599000000',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('courier_payouts', 0);
    }

    public function test_admin_can_approve_payout_and_courier_receives_completion_notification(): void
    {
        $admin = User::factory()->admin()->create();
        $courier = User::factory()->courier()->create();
        $customer = User::factory()->create();
        $restaurant = $this->createRestaurant();

        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'delivered_at' => now(),
            'address_details' => 'الرمال',
            'phone' => $customer->phone,
            'subtotal' => 50,
            'delivery_fee' => 20,
            'total' => 70,
        ]);

        $payout = CourierPayout::create([
            'user_id' => $courier->id,
            'amount' => 17.0,
            'status' => CourierPayout::STATUS_PENDING,
            'payout_method' => 'jawwal_pay',
            'transfer_details' => '0599123456',
        ]);

        // Admin views payouts tab
        $this->actingAs($admin)
            ->get(route('admin.delivery.index', ['tab' => 'payouts']))
            ->assertOk()
            ->assertSee('17.00')
            ->assertSee('0599123456');

        // Admin completes the payout
        $response = $this->actingAs($admin)->post(route('admin.delivery.payouts.complete', $payout), [
            'admin_notes' => 'تم التحويل برقم حوالة #10293',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payout->refresh();
        $this->assertSame(CourierPayout::STATUS_COMPLETED, $payout->status);
        $this->assertEquals($admin->id, $payout->processed_by);
        $this->assertNotNull($payout->processed_at);
        $this->assertSame('تم التحويل برقم حوالة #10293', $payout->admin_notes);

        // Courier balance is 0, withdrawn is 17
        $this->assertEquals(0.0, $courier->fresh()->courierAvailableBalance());
        $this->assertEquals(17.0, $courier->fresh()->courierTotalWithdrawn());

        // Courier receives notification
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $courier->id,
            'title' => 'تم تحويل مستحقاتك بنجاح',
        ]);
    }

    public function test_admin_can_reject_payout_and_balance_is_restored(): void
    {
        $admin = User::factory()->admin()->create();
        $courier = User::factory()->courier()->create();
        $customer = User::factory()->create();
        $restaurant = $this->createRestaurant();

        Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'delivered_at' => now(),
            'address_details' => 'الرمال',
            'phone' => $customer->phone,
            'subtotal' => 50,
            'delivery_fee' => 20,
            'total' => 70,
        ]);

        $payout = CourierPayout::create([
            'user_id' => $courier->id,
            'amount' => 17.0,
            'status' => CourierPayout::STATUS_PENDING,
            'payout_method' => 'palpay',
            'transfer_details' => 'رقم خاطئ',
        ]);

        $this->assertEquals(0.0, $courier->fresh()->courierAvailableBalance());

        // Admin rejects payout
        $response = $this->actingAs($admin)->post(route('admin.delivery.payouts.reject', $payout), [
            'admin_notes' => 'رقم المحفظة المدخل غير صحيح، يرجى التحقق وإعادة الطلب.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payout->refresh();
        $this->assertSame(CourierPayout::STATUS_REJECTED, $payout->status);

        // Balance restored to 17.0
        $this->assertEquals(17.0, $courier->fresh()->courierAvailableBalance());

        // Courier receives notification
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $courier->id,
            'title' => 'تم رفض طلب سحب المستحقات',
        ]);
    }
}
