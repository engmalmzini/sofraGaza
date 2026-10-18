<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipSubscription;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SafeDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_restaurant_without_losing_order_history(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $restaurant = $this->restaurant('مطعم الكرم');
        $item = $this->menuItem($restaurant, 'مسخن', 'وجبات');

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'address_details' => 'غزة',
            'phone' => $customer->phone,
            'total' => 42,
        ]);
        $order->items()->create([
            'menu_item_id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'quantity' => 1,
            'line_total' => $item->price,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.restaurants.index'))
            ->delete(route('admin.restaurants.destroy', $restaurant))
            ->assertRedirect(route('admin.restaurants.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'restaurant_id' => null,
            'restaurant_name' => 'مطعم الكرم',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'name' => 'مسخن',
            'menu_item_id' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('مطعم الكرم', false)
            ->assertSee('حذف الطلب', false);
    }

    public function test_admin_can_delete_order_user_and_courier(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['name' => 'زبون للحذف']);
        $courier = User::factory()->courier()->create(['name' => 'مندوب للحذف']);
        $restaurant = $this->restaurant();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier->id,
            'type' => 'purchase',
            'status' => 'delivering',
            'address_details' => 'غزة',
            'phone' => $customer->phone,
            'total' => 20,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);

        $this->actingAs($admin)
            ->from(route('admin.users.show', $customer))
            ->put(route('admin.users.update', $customer), [
                'name' => 'زبون بعد التعديل',
                'phone' => $customer->phone,
                'email' => $customer->email,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame('زبون بعد التعديل', $customer->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $customer))
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);

        $this->actingAs($admin)
            ->delete(route('admin.delivery.destroy', $courier))
            ->assertRedirect(route('admin.delivery.index'));
        $this->assertDatabaseMissing('users', ['id' => $courier->id]);
    }

    public function test_partner_can_delete_meal_category_without_breaking_past_orders(): void
    {
        [$owner, $restaurant] = $this->partnerVenue();
        $item = $this->menuItem($restaurant, 'مسخن دجاج', 'وجبات');
        $customer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'address_details' => 'غزة',
            'phone' => $customer->phone,
            'total' => 22,
        ]);
        $order->items()->create([
            'menu_item_id' => $item->id,
            'name' => 'مسخن دجاج',
            'price' => 22,
            'quantity' => 1,
            'line_total' => 22,
        ]);

        $this->actingAs($owner)
            ->get(route('partner.menu-items.index'))
            ->assertOk()
            ->assertSee('حذف التصنيف', false);

        $this->actingAs($owner)
            ->delete(route('partner.menu-categories.destroy'), ['category' => 'وجبات'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'name' => 'مسخن دجاج',
        ]);
        $this->assertDatabaseHas('restaurants', ['id' => $restaurant->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'restaurant_id' => $restaurant->id]);
    }

    public function test_partner_cannot_delete_orders(): void
    {
        [$owner, $restaurant] = $this->partnerVenue();
        $customer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'pending_confirmation',
            'address_details' => 'غزة',
            'phone' => $customer->phone,
            'total' => 10,
        ]);

        $this->actingAs($owner)
            ->delete(route('admin.orders.destroy', $order))
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_admin_can_delete_membership_that_has_subscriptions(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $membership = Membership::query()->create([
            'name' => 'ذهبية',
            'monthly_price' => 50,
            'discount_percent' => 10,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $subscription = MembershipSubscription::query()->create([
            'user_id' => $customer->id,
            'membership_id' => $membership->id,
            'amount' => 50,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.memberships.destroy', $membership))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('memberships', ['id' => $membership->id]);
        $this->assertDatabaseMissing('membership_subscriptions', ['id' => $subscription->id]);
    }

    public function test_admin_restaurant_and_user_pages_show_delete_buttons(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $restaurant = $this->restaurant('كافي تِرا');

        $this->actingAs($admin)
            ->get(route('admin.restaurants.index'))
            ->assertOk()
            ->assertSee(route('admin.restaurants.destroy', $restaurant), false);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $customer))
            ->assertOk()
            ->assertSee(route('admin.users.destroy', $customer), false)
            ->assertSee(route('admin.users.update', $customer), false);
    }

    private function restaurant(string $name = 'مطعم الاختبار'): Restaurant
    {
        return Restaurant::query()->create([
            'name' => $name,
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
    }

    private function menuItem(Restaurant $restaurant, string $name, string $category): MenuItem
    {
        return $restaurant->menuItems()->create([
            'name' => $name,
            'category' => $category,
            'price' => 22,
            'is_available' => true,
        ]);
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function partnerVenue(): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الشريك',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
        $this->grantPaidListing($restaurant);

        return [$owner, $restaurant];
    }
}
