<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_must_choose_unlimited_or_limited_stock_when_adding_item(): void
    {
        [$owner] = $this->venue();

        $this->actingAs($owner)
            ->post(route('partner.menu-items.store'), [
                'name' => 'مسخن',
                'category' => 'وجبات',
                'price' => 20,
                'is_available' => 1,
            ])
            ->assertSessionHasErrors('stock_mode');
    }

    public function test_partner_can_add_limited_and_unlimited_items(): void
    {
        [$owner, $restaurant] = $this->venue();

        $this->actingAs($owner)
            ->from(route('partner.menu-items.create'))
            ->post(route('partner.menu-items.store'), [
                'name' => 'كباب',
                'category' => 'وجبات',
                'price' => 25,
                'is_available' => 1,
                'stock_mode' => 'limited',
                'stock_qty' => 4,
            ])
            ->assertRedirect(route('partner.menu-items.index'));

        $this->assertDatabaseHas('menu_items', [
            'restaurant_id' => $restaurant->id,
            'name' => 'كباب',
            'stock_unlimited' => 0,
            'stock_qty' => 4,
        ]);

        $this->actingAs($owner)
            ->post(route('partner.menu-items.store'), [
                'name' => 'ليموناضة',
                'category' => 'مشروبات',
                'price' => 6,
                'is_available' => 1,
                'stock_mode' => 'unlimited',
            ])
            ->assertRedirect(route('partner.menu-items.index'));

        $this->assertDatabaseHas('menu_items', [
            'name' => 'ليموناضة',
            'stock_unlimited' => 1,
        ]);
    }

    public function test_limited_stock_blocks_cart_and_decrements_on_checkout(): void
    {
        [$owner, $restaurant] = $this->venue();
        $item = $restaurant->menuItems()->create([
            'name' => 'مسخن',
            'category' => 'وجبات',
            'price' => 22,
            'is_available' => true,
            'stock_unlimited' => false,
            'stock_qty' => 2,
        ]);
        $customer = User::factory()->create(['wallet_balance' => 100]);

        $this->actingAs($customer)
            ->post(route('cart.add', $item), ['quantity' => 3])
            ->assertSessionHas('error');

        $this->actingAs($customer)
            ->post(route('cart.add', $item), ['quantity' => 2])
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'payment_method' => 'wallet',
                'area' => 'الرمال',
                'address_details' => 'غزة - الرمال',
                'phone' => $customer->phone,
            ])
            ->assertRedirect();

        $this->assertSame(0, $item->fresh()->stock_qty);
        $this->assertFalse($item->fresh()->isOrderable());
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
        ]);
    }

    public function test_cancelled_order_restores_limited_stock(): void
    {
        [$owner, $restaurant] = $this->venue();
        $item = $restaurant->menuItems()->create([
            'name' => 'شاورما',
            'category' => 'وجبات',
            'price' => 18,
            'is_available' => true,
            'stock_unlimited' => false,
            'stock_qty' => 5,
        ]);
        $customer = User::factory()->create(['wallet_balance' => 80]);

        $this->actingAs($customer)->post(route('cart.add', $item), ['quantity' => 2]);
        $this->actingAs($customer)->post(route('checkout.store'), [
            'payment_method' => 'wallet',
            'area' => 'الرمال',
            'address_details' => 'غزة - حي الرمال',
            'phone' => $customer->phone,
        ]);

        $order = Order::query()->latest('id')->first();
        $this->assertSame(3, $item->fresh()->stock_qty);

        $this->actingAs($customer)
            ->post(route('account.orders.cancel', $order))
            ->assertRedirect();

        $this->assertSame(5, $item->fresh()->stock_qty);
    }

    public function test_admin_menu_form_shows_stock_options(): void
    {
        $admin = User::factory()->admin()->create();
        [, $restaurant] = $this->venue();

        $this->actingAs($admin)
            ->get(route('admin.restaurants.menu-items.create', $restaurant))
            ->assertOk()
            ->assertSee('لا نهائية', false)
            ->assertSee('كمية محددة', false);
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function venue(): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الكمية',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'area' => 'الرمال',
        ]);
        $this->grantPaidListing($restaurant);

        return [$owner, $restaurant];
    }
}
