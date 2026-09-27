<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WalletAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Restaurant $restaurant;
    private MenuItem $menuItem;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'مسؤول النظام',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'أحمد الغزاوي',
            'wallet_balance' => 0.0,
        ]);

        $owner = User::factory()->create(['role' => 'restaurant']);
        $this->restaurant = Restaurant::create([
            'user_id' => $owner->id,
            'name' => 'مطعم التاج',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'address' => 'شارع الوحدة',
            'phone' => '0599111222',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        $this->menuItem = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'شاورما دجاج عائلي',
            'price' => 35.0,
            'category' => 'وجبات',
            'is_available' => true,
        ]);
    }

    public function test_customer_can_view_wallet_and_request_topup(): void
    {
        $this->actingAs($this->customer);

        $response = $this->get(route('account.wallet'));
        $response->assertOk();
        $response->assertSee('0.00');

        $receipt = UploadedFile::fake()->create('receipt.jpg', 50, 'image/jpeg');

        $response = $this->post(route('account.wallet.topup.store'), [
            'amount' => 100,
            'payment_method' => 'jawwal_pay',
            'transfer_reference' => 'TX-987654',
            'customer_notes' => 'تحويل جوال باي',
            'receipt' => $receipt,
        ]);

        $response->assertRedirect(route('account.wallet'));
        $this->assertDatabaseHas('wallet_topups', [
            'user_id' => $this->customer->id,
            'amount' => 100,
            'payment_method' => 'jawwal_pay',
            'status' => 'pending',
        ]);

        $receipt2 = UploadedFile::fake()->create('receipt2.jpg', 50, 'image/jpeg');
        $response2 = $this->post(route('account.wallet.topup.store'), [
            'amount' => 50,
            'payment_method' => 'palpay',
            'receipt' => $receipt2,
        ]);
        $response2->assertRedirect(route('account.wallet'));
        $this->assertDatabaseHas('wallet_topups', [
            'user_id' => $this->customer->id,
            'amount' => 50,
            'payment_method' => 'palpay',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_wallet_topup(): void
    {
        $topup = WalletTopup::create([
            'user_id' => $this->customer->id,
            'amount' => 150.0,
            'payment_method' => 'bank_palestine',
            'transfer_receipt_path' => 'receipts/fake.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.wallet-topups.approve', $topup), [
            'admin_notes' => 'تم استلام الحوالة في الحساب البنكي',
        ]);

        $response->assertRedirect();
        $this->customer->refresh();
        $this->assertEquals(150.0, (float) $this->customer->wallet_balance);

        $topup->refresh();
        $this->assertEquals('approved', $topup->status);
        $this->assertNotNull($topup->reviewed_at);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->customer->id,
            'type' => 'topup',
            'amount' => 150.0,
            'balance_after' => 150.0,
        ]);
    }

    public function test_customer_can_pay_order_with_wallet_balance(): void
    {
        // Credit the customer with 100 NIS
        $this->customer->update(['wallet_balance' => 100.0]);

        $this->actingAs($this->customer);

        // Put item in cart via CartService
        app(\App\Services\CartService::class)->add($this->menuItem, 2);

        $response = $this->post(route('checkout.store'), [
            'payment_method' => 'wallet',
            'phone' => '0599111222',
            'area' => 'الرمال',
            'address_details' => 'الرمال الجنوبي - مفترق حسنين بجوار المسجد',
        ]);

        $response->assertRedirect();
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('wallet', $order->payment_method);
        $this->assertEquals('confirmed', $order->status); // wallet orders are immediately confirmed

        $this->customer->refresh();
        // Grand total was 70 (items) + delivery fee for الرمال
        $this->assertTrue($this->customer->wallet_balance < 100.0);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->customer->id,
            'type' => 'order_payment',
        ]);
    }

    public function test_wallet_balance_is_refunded_when_order_is_cancelled(): void
    {
        $this->customer->update(['wallet_balance' => 25.0]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'restaurant_id' => $this->restaurant->id,
            'status' => 'confirmed',
            'payment_method' => 'wallet',
            'phone' => '0599111222',
            'address_details' => 'الرمال شارع الوحدة',
            'subtotal' => 70.0,
            'delivery_fee' => 5.0,
            'total' => 75.0,
            'confirmed_at' => now(),
        ]);

        // Cancel order via OrderService
        app(OrderService::class)->changeStatus($order, 'cancelled');

        $this->customer->refresh();
        // Refunded 75 NIS -> 25 + 75 = 100 NIS
        $this->assertEquals(100.0, (float) $this->customer->wallet_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->customer->id,
            'type' => 'refund',
            'amount' => 75.0,
            'balance_after' => 100.0,
        ]);
    }

    public function test_customer_can_submit_real_review_and_rating(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'restaurant_id' => $this->restaurant->id,
            'status' => 'delivered',
            'payment_method' => 'wallet',
            'recipient_name' => 'أحمد الغزاوي',
            'phone' => '0599111222',
            'delivery_area' => 'gaza_city',
            'address_details' => 'الرمال',
            'items_total' => 35.0,
            'delivery_fee' => 5.0,
            'grand_total' => 40.0,
            'delivered_at' => now(),
        ]);

        $this->actingAs($this->customer);

        $response = $this->post(route('restaurants.reviews.store', $this->restaurant), [
            'rating' => 5,
            'comment' => 'تجربة ممتازة والأكل وصل ساخن والتوصيل سريع جداً بارك الله فيكم',
            'order_id' => $order->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->customer->id,
            'restaurant_id' => $this->restaurant->id,
            'order_id' => $order->id,
            'rating' => 5,
            'is_approved' => true,
        ]);

        $this->assertEquals(5.0, $this->restaurant->averageRating());
        $this->assertEquals(1, $this->restaurant->reviewsCount());
    }

    public function test_admin_can_update_payment_accounts_and_qr(): void
    {
        $this->actingAs($this->admin);

        $qrFile = UploadedFile::fake()->create('qr_code.png', 50, 'image/png');
        $palpayQrFile = UploadedFile::fake()->create('palpay_qr.png', 50, 'image/png');

        $response = $this->post(route('admin.settings.update'), [
            'settings' => [
                'bank_name' => 'بنك فلسطين ش.م.ع',
                'bank_account_number' => '1234567',
                'bank_iban' => 'PS00PALS00000000001234567001',
                'bank_beneficiary_name' => 'سفرة غزة للتجارة',
                'jawwal_pay_number' => '0599123456',
                'jawwal_pay_name' => 'محفظة سفرة غزة الرسمية',
                'palpay_number' => '0599654321',
                'palpay_name' => 'محفظة بال باي الرسمية',
                'payment_instructions_note' => 'يرجى إرفاق إشعار الدفع لتسريع التأكيد',
            ],
            'jawwal_pay_qr' => $qrFile,
            'palpay_qr' => $palpayQrFile,
        ]);

        $response->assertRedirect();
        $settings = Setting::paymentAccounts();
        $this->assertEquals('1234567', $settings['bank_account_number']);
        $this->assertEquals('0599123456', $settings['jawwal_pay_number']);
        $this->assertEquals('0599654321', $settings['palpay_number']);
        $this->assertEquals('محفظة بال باي الرسمية', $settings['palpay_name']);
        $this->assertNotNull($settings['jawwal_pay_qr_url']);
        $this->assertNotNull($settings['palpay_qr_url']);
    }
}
