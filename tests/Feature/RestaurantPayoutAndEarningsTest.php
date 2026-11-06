<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantPayout;
use App\Models\Setting;
use App\Models\User;
use App\Support\Finance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantPayoutAndEarningsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $owner;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
        [$this->owner, $this->restaurant] = $this->approvedVenue();
    }

    public function test_partner_sees_orders_balance_and_period_tabs(): void
    {
        $this->deliverOrder(100);

        $this->actingAs($this->owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('الرصيد المتاح', false)
            ->assertSee('90.00', false);

        $this->actingAs($this->owner)
            ->get(route('partner.wallet', ['period' => 'today']))
            ->assertOk()
            ->assertSee('الرصيد المتاح للسحب', false)
            ->assertSee('اليوم', false)
            ->assertSee('الأسبوع', false)
            ->assertSee('الشهر', false)
            ->assertSee('ما تم سحبه', false)
            ->assertSee('عمولة المنصة 10%', false);
    }

    public function test_available_balance_is_net_after_commission_and_pending_does_not_deduct(): void
    {
        $this->deliverOrder(100);

        $this->assertSame(90.0, $this->restaurant->availableBalance());
        $this->assertSame(0.0, $this->restaurant->totalWithdrawn());

        $this->actingAs($this->owner)
            ->post(route('partner.wallet.payout'), $this->payoutPayload(90))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(90.0, $this->restaurant->fresh()->availableBalance());
        $this->assertSame(90.0, $this->restaurant->fresh()->pendingPayoutsAmount());
        $this->assertDatabaseHas('restaurant_payouts', [
            'restaurant_id' => $this->restaurant->id,
            'amount' => 90,
            'status' => RestaurantPayout::STATUS_PENDING,
            'period' => 'today',
        ]);
    }

    public function test_admin_confirm_deducts_and_reject_keeps_balance(): void
    {
        $this->deliverOrder(200);
        $this->actingAs($this->owner)->post(route('partner.wallet.payout'), $this->payoutPayload(180));
        $payout = RestaurantPayout::query()->first();

        $this->actingAs($this->admin)
            ->post(route('admin.finance.payouts.complete', $payout), [
                'receipt' => UploadedFile::fake()->image('transfer.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($payout->fresh()->isCompleted());
        $this->assertNotEmpty($payout->fresh()->transfer_receipt_path);
        $this->assertSame(0.0, $this->restaurant->fresh()->availableBalance());
        $this->assertSame(180.0, $this->restaurant->fresh()->totalWithdrawn());

        $this->deliverOrder(100);
        $this->actingAs($this->owner)->post(route('partner.wallet.payout'), $this->payoutPayload(90));
        $second = RestaurantPayout::query()->latest('id')->first();

        $this->actingAs($this->admin)
            ->post(route('admin.finance.payouts.reject', $second), [
                'admin_notes' => 'بيانات التحويل غير واضحة',
            ])
            ->assertRedirect();

        $this->assertTrue($second->fresh()->isRejected());
        $this->assertSame(90.0, $this->restaurant->fresh()->availableBalance());
    }

    public function test_admin_cannot_complete_payout_without_receipt_image(): void
    {
        $this->deliverOrder(100);
        $this->actingAs($this->owner)->post(route('partner.wallet.payout'), $this->payoutPayload(90));
        $payout = RestaurantPayout::query()->first();

        $this->actingAs($this->admin)
            ->post(route('admin.finance.payouts.complete', $payout))
            ->assertSessionHasErrors('receipt');

        $this->assertTrue($payout->fresh()->isPending());
    }

    public function test_admin_and_owner_can_view_payout_receipt_after_transfer(): void
    {
        $this->deliverOrder(100);
        $this->actingAs($this->owner)->post(route('partner.wallet.payout'), $this->payoutPayload(90));
        $payout = RestaurantPayout::query()->first();

        $this->actingAs($this->admin)
            ->post(route('admin.finance.payouts.complete', $payout), [
                'receipt' => UploadedFile::fake()->image('notice.png'),
            ])
            ->assertRedirect();

        $payout->refresh();
        $this->assertTrue($payout->hasReceiptFile());

        $this->actingAs($this->admin)
            ->get(route('admin.finance.payouts'))
            ->assertOk()
            ->assertSee('عرض إشعار التحويل', false);

        $this->actingAs($this->admin)
            ->get(route('admin.finance.payouts.receipt', $payout))
            ->assertOk();

        $this->actingAs($this->owner)
            ->get(route('partner.wallet.payout.receipt', $payout))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $this->actingAs($this->owner)
            ->get(route('partner.wallet'))
            ->assertOk()
            ->assertSee('عرض الإشعار', false);
    }

    public function test_admin_can_settle_restaurant_without_owner_request(): void
    {
        $this->deliverOrder(100);

        $this->actingAs($this->admin)
            ->get(route('admin.finance.restaurants.show', $this->restaurant))
            ->assertOk()
            ->assertSee('تسوية مباشرة للمطعم', false)
            ->assertSee('تأكيد التسوية والتحويل', false);

        $this->actingAs($this->admin)
            ->post(route('admin.finance.restaurants.payout', $this->restaurant), [
                'amount' => 90,
                'payout_method' => 'jawwal_pay',
                'transfer_details' => '0599000111 جوال باي باسم المطعم',
                'receipt' => UploadedFile::fake()->image('admin-settlement.jpg'),
            ])
            ->assertRedirect(route('admin.finance.payouts', ['payout_status' => 'completed']))
            ->assertSessionHas('success');

        $payout = RestaurantPayout::query()->first();
        $this->assertNotNull($payout);
        $this->assertTrue($payout->isCompleted());
        $this->assertSame(90.0, (float) $payout->amount);
        $this->assertNotEmpty($payout->transfer_receipt_path);
        $this->assertSame(0.0, $this->restaurant->fresh()->availableBalance());
        $this->assertSame(90.0, $this->restaurant->fresh()->totalWithdrawn());

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->owner->id,
            'title' => 'تم تحويل مستحقات المطعم',
        ]);

        $this->actingAs($this->owner)
            ->get(route('partner.wallet'))
            ->assertOk()
            ->assertSee('تم التحويل', false)
            ->assertSee('عرض الإشعار', false);
    }

    public function test_admin_cannot_settle_while_restaurant_has_pending_payout(): void
    {
        $this->deliverOrder(100);
        $this->actingAs($this->owner)->post(route('partner.wallet.payout'), $this->payoutPayload(40));

        $this->actingAs($this->admin)
            ->post(route('admin.finance.restaurants.payout', $this->restaurant), [
                'amount' => 50,
                'payout_method' => 'cash',
                'transfer_details' => 'تسليم نقدي في المطعم',
                'receipt' => UploadedFile::fake()->image('blocked.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1, RestaurantPayout::query()->count());
        $this->assertTrue(RestaurantPayout::query()->first()->isPending());
        $this->assertSame(90.0, $this->restaurant->fresh()->availableBalance());
    }

    public function test_cannot_request_second_payout_while_one_is_pending(): void
    {
        $this->deliverOrder(100);
        $this->actingAs($this->owner)->post(route('partner.wallet.payout'), $this->payoutPayload(50));

        $this->actingAs($this->owner)
            ->post(route('partner.wallet.payout'), $this->payoutPayload(40))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1, RestaurantPayout::query()->count());
    }

    public function test_admin_can_change_default_commission_percent(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'restaurant_commission_percent'],
            ['value' => '15', 'label' => 'النسبة العامة لعمولة المنصة من المطاعم (%)']
        );
        Setting::forgetCache();

        $this->assertSame(15.0, Finance::restaurantCommissionPercent());
        $this->deliverOrder(100);
        $this->assertSame(85.0, $this->restaurant->fresh()->availableBalance());

        $this->actingAs($this->admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('النسبة العامة لعمولة المنصة من المطاعم', false);
    }

    public function test_admin_can_set_a_different_commission_for_each_restaurant(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.restaurants.update', $this->restaurant), [
                'name' => $this->restaurant->name,
                'type' => 'restaurant',
                'is_active' => 1,
                'commission_percent' => '20',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->restaurant->refresh();
        $this->assertTrue($this->restaurant->hasCustomCommission());
        $this->assertSame(20.0, $this->restaurant->commissionPercent());
        $this->assertSame('20%', $this->restaurant->commissionLabel());

        $this->deliverOrder(100);
        $this->assertSame(80.0, $this->restaurant->fresh()->availableBalance());

        $otherOwner = User::factory()->restaurantOwner()->create();
        $other = Restaurant::query()->create([
            'owner_id' => $otherOwner->id,
            'name' => 'مطعم النسبة الثانية',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'commission_percent' => 5,
        ]);

        Order::query()->create([
            'user_id' => User::factory()->create()->id,
            'restaurant_id' => $other->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'address_details' => 'غزة',
            'phone' => '0599000002',
            'subtotal' => 100,
            'discount_amount' => 0,
            'delivery_fee' => 10,
            'total' => 110,
            'delivered_at' => now(),
        ]);

        $this->assertSame(95.0, $other->fresh()->availableBalance());

        $this->actingAs($this->admin)
            ->get(route('admin.restaurants.edit', $this->restaurant))
            ->assertOk()
            ->assertSee('نسبة عمولة المنصة من مبيعات هذا المطعم', false)
            ->assertSee('value="20"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.restaurants.index'))
            ->assertOk()
            ->assertSee('20%', false)
            ->assertSee('5%', false);
    }

    public function test_partner_cannot_change_restaurant_commission(): void
    {
        $this->restaurant->update(['commission_percent' => 12]);

        $this->actingAs($this->owner)
            ->get(route('partner.restaurant.edit'))
            ->assertOk()
            ->assertDontSee('نسبة عمولة المنصة من مبيعات هذا المطعم', false);

        $this->actingAs($this->owner)
            ->put(route('partner.restaurant.update'), [
                'name' => $this->restaurant->name,
                'type' => 'restaurant',
                'cuisine' => 'palestinian',
                'description' => 'مطبخ غزي بيتي يقدم المقلوبة والمسخن والقدرة لعائلة غزة.',
                'phone' => '0599000111',
                'owner_national_id' => '401234567',
                'area' => 'الرمال',
                'address' => 'شارع الجلاء، غزة',
                'opens_at' => '10:00',
                'closes_at' => '23:00',
                'working_days' => [6, 0, 1, 2, 3, 4],
                'commission_percent' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(12.0, (float) $this->restaurant->fresh()->commission_percent);
        $this->assertSame(12.0, $this->restaurant->fresh()->commissionPercent());
    }

    public function test_weekly_remaining_shows_withdrawn_versus_unpaid(): void
    {
        $this->deliverOrder(100);
        $earnings = $this->restaurant->earningsForPeriod('week');

        $this->assertSame(100.0, $earnings['sales']);
        $this->assertSame(10.0, $earnings['commission']);
        $this->assertSame(90.0, $earnings['net']);
        $this->assertSame(0.0, $earnings['withdrawn']);
        $this->assertSame(90.0, $earnings['remaining']);
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function approvedVenue(): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الرصيد',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'panel_suspended' => false,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'شاورما',
            'price' => 40,
            'is_available' => true,
        ]);

        return [$owner, $restaurant];
    }

    private function deliverOrder(float $subtotal): Order
    {
        return Order::query()->create([
            'user_id' => User::factory()->create()->id,
            'restaurant_id' => $this->restaurant->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'address_details' => 'غزة',
            'phone' => '0599000001',
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'delivery_fee' => 10,
            'total' => $subtotal + 10,
            'delivered_at' => now(),
        ]);
    }

    private function payoutPayload(float $amount, string $period = 'today'): array
    {
        return [
            'amount' => $amount,
            'period' => $period,
            'payout_method' => 'jawwal_pay',
            'transfer_details' => '0599000111 جوال باي باسم المطعم',
        ];
    }
}
