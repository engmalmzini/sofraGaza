<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnerReceivesOrderAfterPaymentConfirmationTest extends TestCase
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

    public function test_receipt_order_stays_hidden_from_partner_until_admin_confirms_payment(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        [$owner, $restaurant, $item] = $this->createPartnerWithRestaurant();
        $customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'أحمد الزبون',
        ]);

        $this->actingAs($customer)->post(route('cart.add', $item), ['quantity' => 1]);
        $this->actingAs($customer)->post(route('checkout.store'), [
            'payment_method' => 'receipt',
            'area' => 'الرمال',
            'address_details' => 'غزة - شارع عمر المختار بجوار المسجد',
            'phone' => '0599333444',
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertRedirect();

        $order = Order::query()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('pending_confirmation', $order->status);
        $this->assertNull($order->confirmed_at);
        $this->assertFalse($order->isVisibleToRestaurant());

        $this->assertDatabaseMissing('app_notifications', [
            'user_id' => $owner->id,
            'title' => 'طلب جديد مدفوع وارد لمطعمك',
        ]);

        $this->actingAs($owner)
            ->get(route('partner.orders.index'))
            ->assertOk()
            ->assertDontSee('#'.$order->id, false)
            ->assertDontSee('بانتظار تأكيد الدفع', false);

        $this->actingAs($owner)
            ->getJson(route('partner.orders.live', ['last_id' => 0]))
            ->assertOk()
            ->assertJson([
                'latest_id' => 0,
                'active_count' => 0,
                'has_new' => false,
            ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('تأكيد الدفع وإرساله للمطعم', false);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('confirmed', $order->status);
        $this->assertNotNull($order->confirmed_at);
        $this->assertTrue($order->isVisibleToRestaurant());
        $this->assertTrue($order->shouldShowPaidBadge());

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $owner->id,
            'title' => 'طلب جديد مدفوع وارد لمطعمك',
        ]);

        $this->actingAs($owner)
            ->get(route('partner.orders.index'))
            ->assertOk()
            ->assertSee('#'.$order->id, false)
            ->assertSee('تم الدفع', false);

        $this->actingAs($owner)
            ->get(route('partner.orders.show', $order))
            ->assertOk()
            ->assertSee('تم الدفع', false)
            ->assertSee('تم تأكيد الدفع من الإدارة', false);

        $this->actingAs($owner)
            ->getJson(route('partner.orders.live', ['last_id' => 0]))
            ->assertOk()
            ->assertJson([
                'latest_id' => $order->id,
                'active_count' => 1,
                'has_new' => true,
            ]);
    }

    public function test_wallet_order_reaches_partner_immediately_as_paid(): void
    {
        [$owner, $restaurant, $item] = $this->createPartnerWithRestaurant();
        $customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'سارة الزبونة',
            'wallet_balance' => 200,
        ]);

        $this->actingAs($customer)->post(route('cart.add', $item), ['quantity' => 1]);
        $this->actingAs($customer)->post(route('checkout.store'), [
            'payment_method' => 'wallet',
            'area' => 'الرمال',
            'address_details' => 'غزة - شارع عمر المختار بجوار المسجد',
            'phone' => '0599333444',
        ])->assertRedirect();

        $order = Order::query()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('confirmed', $order->status);
        $this->assertTrue($order->isVisibleToRestaurant());

        $this->assertTrue(
            AppNotification::query()
                ->where('user_id', $owner->id)
                ->where('title', 'طلب جديد مدفوع وارد لمطعمك')
                ->exists()
        );

        $this->actingAs($owner)
            ->get(route('partner.orders.show', $order))
            ->assertOk()
            ->assertSee('تم الدفع', false);
    }
}
