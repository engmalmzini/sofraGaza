<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesAndReorderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Restaurant, 1: MenuItem}
     */
    private function visibleDish(string $restaurantName = 'شاورما العمدة', string $dishName = 'شاورما دجاج'): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => $restaurantName,
            'type' => 'restaurant',
            'area' => 'الرمال',
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $dish = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => $dishName,
            'category' => 'سندويش',
            'price' => 18,
            'is_available' => true,
        ]);

        return [$restaurant, $dish];
    }

    public function test_customer_can_favorite_and_unfavorite_a_restaurant(): void
    {
        $customer = User::factory()->create();
        [$restaurant] = $this->visibleDish();

        $this->actingAs($customer)
            ->postJson(route('favorites.toggle'), [
                'type' => 'restaurant',
                'id' => $restaurant->id,
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'favorited' => true]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $customer->id,
            'favorable_id' => $restaurant->id,
        ]);

        $this->actingAs($customer)
            ->get(route('account.favorites'))
            ->assertOk()
            ->assertSee('شاورما العمدة', false);

        $this->actingAs($customer)
            ->postJson(route('favorites.toggle'), [
                'type' => 'restaurant',
                'id' => $restaurant->id,
            ])
            ->assertOk()
            ->assertJson(['favorited' => false]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $customer->id,
            'favorable_id' => $restaurant->id,
        ]);
    }

    public function test_customer_can_favorite_a_dish(): void
    {
        $customer = User::factory()->create();
        [, $dish] = $this->visibleDish('برجر الرمال', 'برجر دجاج');

        $this->actingAs($customer)
            ->postJson(route('favorites.toggle'), [
                'type' => 'menu_item',
                'id' => $dish->id,
            ])
            ->assertOk()
            ->assertJson(['favorited' => true]);

        $this->actingAs($customer)
            ->get(route('account.favorites'))
            ->assertOk()
            ->assertSee('برجر دجاج', false)
            ->assertSee('برجر الرمال', false);
    }

    public function test_guest_is_sent_to_login_when_favoriting(): void
    {
        [$restaurant] = $this->visibleDish();

        $this->post(route('favorites.toggle'), [
            'type' => 'restaurant',
            'id' => $restaurant->id,
        ])->assertRedirect(route('login'));
    }

    public function test_home_suggests_previous_orders_for_the_customer(): void
    {
        $customer = User::factory()->create(['name' => 'أحمد']);
        [$restaurant, $dish] = $this->visibleDish('مشاوي أبو العبد', 'كباب');

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'delivered',
            'address_details' => 'غزة',
            'phone' => $customer->phone,
            'subtotal' => 18,
            'total' => 28,
            'delivery_fee' => 10,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'menu_item_id' => $dish->id,
            'name' => $dish->name,
            'price' => 18,
            'quantity' => 2,
            'line_total' => 36,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('بناءً على طلباتك السابقة، جرب هذا', false)
            ->assertSee('كباب', false)
            ->assertSee('مشاوي أبو العبد', false);

        $this->post(route('logout'));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('بناءً على طلباتك السابقة، جرب هذا', false);
    }
}
