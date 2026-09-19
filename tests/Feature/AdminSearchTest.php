<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_orders_from_header_suggest(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['name' => 'أحمد الغزي']);
        $restaurant = $this->restaurant();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'status' => 'pending_confirmation',
            'address_details' => 'غزة',
            'phone' => $customer->phone,
            'total' => 42,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.search.suggest', ['q' => 'أحمد', 'scope' => 'orders']))
            ->assertOk()
            ->assertJsonPath('results.0.title', '#'.$order->id.' — أحمد الغزي')
            ->assertJsonPath('results.0.url', route('admin.orders.show', $order));
    }

    public function test_user_scope_does_not_return_restaurants(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'كافي تِرا زبون']);
        $this->restaurant('كافي تِرا');

        $this->actingAs($admin)
            ->getJson(route('admin.search.suggest', ['q' => 'تِرا', 'scope' => 'users']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.icon', 'person');
    }

    public function test_orders_page_keeps_only_header_search(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('admin-search-wrap', false)
            ->assertDontSee('بحث بالاسم أو الهاتف أو الرقم', false);
    }

    public function test_guest_cannot_use_admin_search(): void
    {
        $this->get(route('admin.search.suggest', ['q' => 'أحمد']))
            ->assertRedirect(route('login'));
    }

    private function restaurant(string $name = 'كافي تِرا'): Restaurant
    {
        return Restaurant::query()->create([
            'name' => $name,
            'type' => 'cafe',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);
    }
}
