<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\GroupOrder;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GroupOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $host;
    private User $guest;
    private Restaurant $restaurant;
    private MenuItem $dish;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->host = User::factory()->create([
            'name' => 'سامي المضيف',
            'phone' => '0591112233',
            'wallet_balance' => 80,
        ]);
        $this->guest = User::factory()->create([
            'name' => 'كريم الضيف',
            'phone' => '0594445566',
            'wallet_balance' => 50,
        ]);

        $owner = User::factory()->restaurantOwner()->create();
        $this->restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الرفاق',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $this->dish = MenuItem::query()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'مسخن',
            'category' => 'وجبات',
            'price' => 20,
            'is_available' => true,
        ]);
    }

    public function test_restaurant_page_shows_group_order_button_for_customers(): void
    {
        $this->actingAs($this->host)
            ->get(route('restaurants.show', $this->restaurant))
            ->assertOk()
            ->assertSee('طلب جماعي', false);
    }

    public function test_host_cannot_invite_unregistered_phone(): void
    {
        $this->actingAs($this->host)
            ->post(route('group-orders.store'), [
                'restaurant_id' => $this->restaurant->id,
                'phones' => ['0590000111'],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, GroupOrder::query()->count());
    }

    public function test_group_order_from_invite_to_single_invoice(): void
    {
        $this->actingAs($this->host)
            ->post(route('group-orders.store'), [
                'restaurant_id' => $this->restaurant->id,
                'phones' => [$this->guest->phone],
            ])
            ->assertRedirect();

        $group = GroupOrder::query()->first();
        $this->assertNotNull($group);
        $this->assertSame(GroupOrder::STATUS_COLLECTING, $group->status);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->guest->id,
            'title' => 'طلب جماعي من سامي المضيف',
        ]);

        $this->actingAs($this->guest)
            ->post(route('cart.add', $this->dish), ['quantity' => 1])
            ->assertRedirect();

        $this->actingAs($this->guest)
            ->post(route('group-orders.pay.store', $group), [
                'payment_method' => 'wallet',
            ])
            ->assertRedirect(route('group-orders.show', $group));

        $this->assertEquals(30, (float) $this->guest->fresh()->wallet_balance);
        $this->assertTrue($group->fresh()->load('members')->allGuestsPaid());

        $this->actingAs($this->host)
            ->get(route('checkout.create'))
            ->assertRedirect(route('group-orders.checkout', $group));

        $this->actingAs($this->host)
            ->post(route('cart.add', $this->dish), ['quantity' => 1]);

        $this->actingAs($this->host)
            ->post(route('group-orders.pay.store', $group), [
                'payment_method' => 'wallet',
                'area' => 'الرمال',
                'address_details' => 'غزة - الرمال - برج وطني',
                'phone' => $this->host->phone,
            ])
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame($group->id, $order->group_order_id);
        $this->assertSame('pending_confirmation', $order->status);
        $this->assertCount(2, $order->items);
        $this->assertTrue($order->items->contains(fn ($item) => $item->ordered_by_name === 'كريم الضيف'));
        $this->assertTrue($order->items->contains(fn ($item) => $item->ordered_by_name === 'سامي المضيف'));

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('طلب جماعي', false)
            ->assertSee('كريم الضيف', false)
            ->assertSee('سامي المضيف', false);

        $this->actingAs($this->guest)
            ->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('مسخن', false);
    }

    public function test_guest_can_pay_share_with_receipt(): void
    {
        $this->actingAs($this->host)
            ->post(route('group-orders.store'), [
                'restaurant_id' => $this->restaurant->id,
                'phones' => [$this->guest->phone],
            ]);

        $group = GroupOrder::query()->first();

        $this->actingAs($this->guest)
            ->post(route('cart.add', $this->dish), ['quantity' => 1]);

        $this->actingAs($this->guest)
            ->post(route('group-orders.pay.store', $group), [
                'payment_method' => 'receipt',
                'receipt' => UploadedFile::fake()->image('share.jpg'),
            ])
            ->assertRedirect(route('group-orders.show', $group));

        $this->assertSame('paid', $group->fresh()->load('members')->memberFor($this->guest)->status);
        $this->assertNotNull($group->memberFor($this->guest)->transfer_receipt_path);
    }

    public function test_cancelling_group_order_refunds_guest_wallet(): void
    {
        $this->actingAs($this->host)
            ->post(route('group-orders.store'), [
                'restaurant_id' => $this->restaurant->id,
                'phones' => [$this->guest->phone],
            ]);
        $group = GroupOrder::query()->first();

        $this->actingAs($this->guest)
            ->post(route('cart.add', $this->dish), ['quantity' => 1]);
        $this->actingAs($this->guest)
            ->post(route('group-orders.pay.store', $group), ['payment_method' => 'wallet']);

        $this->actingAs($this->host)
            ->delete(route('group-orders.destroy', $group))
            ->assertRedirect(route('home'));

        $this->assertEquals(50, (float) $this->guest->fresh()->wallet_balance);
        $this->assertSame(GroupOrder::STATUS_CANCELLED, $group->fresh()->status);
    }
}
