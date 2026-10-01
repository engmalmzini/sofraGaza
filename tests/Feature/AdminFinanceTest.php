<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipSubscription;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantBoost;
use App\Models\User;
use App\Support\Finance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private Restaurant $restaurant;

    private MenuItem $menuItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->customer = User::factory()->create(['name' => 'زبون المالية']);
        $this->restaurant = Restaurant::query()->create([
            'name' => 'مطعم المالية',
            'type' => 'restaurant',
            'address' => 'غزة',
            'phone' => '0599111000',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
        $this->menuItem = MenuItem::query()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'شاورما',
            'price' => 40,
            'is_available' => true,
        ]);
    }

    public function test_guest_cannot_open_finance_pages(): void
    {
        $this->get(route('admin.finance.index'))->assertRedirect(route('login'));
        $this->get(route('admin.finance.orders'))->assertRedirect(route('login'));
        $this->get(route('admin.finance.restaurants'))->assertRedirect(route('login'));
    }

    public function test_orders_finance_shows_completed_breakdown_and_cancelled_losses(): void
    {
        $delivered = $this->createOrder([
            'status' => 'delivered',
            'subtotal' => 100,
            'discount_amount' => 0,
            'delivery_fee' => 10,
            'total' => 110,
            'delivered_at' => now(),
        ]);

        $cancelled = $this->createOrder([
            'status' => 'cancelled',
            'subtotal' => 50,
            'delivery_fee' => 5,
            'total' => 55,
            'rejection_reason' => 'الزبون ألغى بعد التأكيد',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.finance.orders', ['period' => 'month']))
            ->assertOk()
            ->assertSee('مالية الطلبات', false)
            ->assertSee('#'.$delivered->id, false)
            ->assertSee('10.00', false)
            ->assertSee('90.00', false)
            ->assertSee('#'.$cancelled->id, false)
            ->assertSee('الزبون ألغى بعد التأكيد', false)
            ->assertSee('ملغى', false);
    }

    public function test_restaurant_settlement_is_recorded_and_shown_in_history(): void
    {
        $this->createOrder([
            'status' => 'delivered',
            'subtotal' => 200,
            'delivery_fee' => 8,
            'total' => 208,
            'delivered_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.finance.restaurants'))
            ->assertOk()
            ->assertSee('مطعم المالية', false)
            ->assertSee('قيد الانتظار', false)
            ->assertSee('20.00', false)
            ->assertSee('180.00', false);

        $this->actingAs($this->admin)
            ->post(route('admin.finance.restaurants.settle', $this->restaurant), [
                'period' => 'month',
                'notes' => 'حوالة بنك فلسطين',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('restaurant_settlements', [
            'restaurant_id' => $this->restaurant->id,
            'sales_total' => 200,
            'commission_total' => 20,
            'net_total' => 180,
            'status' => 'paid',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.finance.restaurants.show', $this->restaurant))
            ->assertOk()
            ->assertSee('تم الدفع', false)
            ->assertSee('السجل التاريخي للتسويات', false)
            ->assertSee('حوالة بنك فلسطين', false);
    }

    public function test_money_in_and_money_out_include_commission_memberships_boosts_gifts_and_expenses(): void
    {
        $basic = Membership::query()->create([
            'name' => 'أساسية',
            'monthly_price' => 60,
            'discount_percent' => 5,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $premium = Membership::query()->create([
            'name' => 'مميزة',
            'monthly_price' => 100,
            'discount_percent' => 10,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        MembershipSubscription::query()->create([
            'user_id' => $this->customer->id,
            'membership_id' => $basic->id,
            'amount' => 60,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->endOfMonth(),
        ]);

        $premiumCustomer = User::factory()->create();
        MembershipSubscription::query()->create([
            'user_id' => $premiumCustomer->id,
            'membership_id' => $premium->id,
            'amount' => 100,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->endOfMonth(),
        ]);

        $courier = User::factory()->courier()->create(['name' => 'كابتن المالية']);

        $this->createOrder([
            'courier_id' => $courier->id,
            'status' => 'delivered',
            'subtotal' => 100,
            'delivery_fee' => 10,
            'total' => 110,
            'delivered_at' => now(),
        ]);

        Order::query()->create([
            'user_id' => $premiumCustomer->id,
            'restaurant_id' => $this->restaurant->id,
            'membership_id' => $premium->id,
            'type' => 'redemption',
            'status' => 'delivered',
            'address_details' => 'غزة',
            'phone' => '0599000111',
            'subtotal' => 0,
            'total' => 0,
            'points_spent' => 80,
            'delivered_at' => now(),
        ])->items()->create([
            'menu_item_id' => $this->menuItem->id,
            'name' => 'شاورما (استبدال نقاط)',
            'price' => 0,
            'quantity' => 1,
            'line_total' => 0,
        ]);

        RestaurantBoost::query()->create([
            'restaurant_id' => $this->restaurant->id,
            'starts_on' => now()->startOfMonth(),
            'ends_on' => now()->startOfMonth()->addDays(2),
            'daily_rate' => Finance::BOOST_DAILY_RATE,
            'title' => 'Boost تجريبي',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.finance.expenses.store'), [
                'period' => 'month',
                'title' => 'استضافة السيرفر',
                'amount' => 40,
                'spent_on' => now()->toDateString(),
                'category' => 'operating',
            ])
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->post(route('admin.finance.incomes.store'), [
                'period' => 'month',
                'title' => 'رسوم إعداد',
                'amount' => 25,
                'received_on' => now()->toDateString(),
                'category' => 'setup',
            ])
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['period' => 'month']))
            ->assertOk()
            ->assertSee('نظرة عامة', false)
            ->assertSee('الدخل الكلي', false)
            ->assertSee('المصروف الكلي', false)
            ->assertSee('الصافي الحقيقي', false)
            ->assertSee('مقارنة بالشهر السابق', false)
            ->assertSee('دخل الطلبات', false)
            ->assertSee('عمولة المنصة +10%', false)
            ->assertSee('أجرة الكابتن −15%', false)
            ->assertSee('دخل الاشتراكات', false)
            ->assertSee('اشتراكات فئة 60', false)
            ->assertSee('اشتراكات فئة 100', false)
            ->assertSee('دخل الإعلانات', false)
            ->assertSee('مصروف الكباتن', false)
            ->assertSee('كابتن المالية', false)
            ->assertSee('مستحق غير مدفوع', false)
            ->assertSee('مصروف مزايا الأعضاء', false)
            ->assertSee('استضافة السيرفر', false)
            ->assertSee('رسوم إعداد', false);

        $this->assertDatabaseHas('finance_expenses', [
            'title' => 'استضافة السيرفر',
            'amount' => 40,
        ]);
        $this->assertDatabaseHas('finance_incomes', [
            'title' => 'رسوم إعداد',
            'amount' => 25,
        ]);

        $page = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['period' => 'month']));

        $page->assertSee('10.00', false);
        $page->assertSee('60.00', false);
        $page->assertSee('100.00', false);
        $page->assertSee('60.00', false);
        $page->assertSee('15.00', false);
        $page->assertSee('40.00', false);
    }

    public function test_admin_can_create_boost_campaign_from_finance_page(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.finance.boosts.store'), [
                'period' => 'month',
                'restaurant_id' => $this->restaurant->id,
                'starts_on' => now()->toDateString(),
                'ends_on' => now()->addDays(4)->toDateString(),
                'daily_rate' => 20,
                'title' => 'إعلان الصفحة الرئيسية',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('restaurant_boosts', [
            'restaurant_id' => $this->restaurant->id,
            'title' => 'إعلان الصفحة الرئيسية',
            'daily_rate' => 20,
        ]);
    }

    private function createOrder(array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'user_id' => $this->customer->id,
            'restaurant_id' => $this->restaurant->id,
            'type' => 'purchase',
            'status' => 'pending_confirmation',
            'address_details' => 'غزة - الرمال',
            'phone' => $this->customer->phone,
            'subtotal' => 100,
            'discount_amount' => 0,
            'delivery_fee' => 0,
            'total' => 100,
        ], $overrides));
    }
}
