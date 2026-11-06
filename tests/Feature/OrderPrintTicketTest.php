<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPrintTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_can_print_a_visible_order_ticket(): void
    {
        [$owner, $order] = $this->makePartnerOrder('confirmed');

        $this->actingAs($owner)
            ->get(route('partner.orders.print', $order))
            ->assertOk()
            ->assertSee('طباعة على الطابعة', false)
            ->assertSee('window.print', false)
            ->assertSee('#'.$order->id, false)
            ->assertSee('شاورما دجاج', false)
            ->assertSee('بدون بصل', false)
            ->assertSee('حساب الوجبات', false)
            ->assertDontSee('التوصيل', false);
    }

    public function test_partner_cannot_print_an_order_waiting_for_payment(): void
    {
        [$owner, $order] = $this->makePartnerOrder('pending_confirmation');

        $this->actingAs($owner)
            ->get(route('partner.orders.print', $order))
            ->assertNotFound();
    }

    public function test_admin_print_ticket_includes_delivery_and_button_on_order_page(): void
    {
        $admin = User::factory()->admin()->create();
        [, $order] = $this->makePartnerOrder('confirmed');

        $this->actingAs($admin)
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('شاورما دجاج', false)
            ->assertSee('التوصيل', false)
            ->assertSee('الإجمالي', false);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('طباعة الطلب', false)
            ->assertSee(route('admin.orders.print', $order, false), false);
    }

    public function test_partner_order_page_has_print_button(): void
    {
        [$owner, $order] = $this->makePartnerOrder('confirmed');

        $this->actingAs($owner)
            ->get(route('partner.orders.show', $order))
            ->assertOk()
            ->assertSee('طباعة الطلب', false)
            ->assertSee(route('partner.orders.print', $order, false), false);
    }

    /**
     * @return array{0: User, 1: Order}
     */
    private function makePartnerOrder(string $status): array
    {
        $owner = User::factory()->create([
            'role' => 'restaurant_owner',
            'phone' => '0592001888',
        ]);

        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم المطبعة',
            'type' => 'restaurant',
            'address' => 'غزة',
            'phone' => '0592001888',
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'panel_suspended' => false,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $customer = User::factory()->create(['name' => 'سامي الزبون']);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'type' => 'purchase',
            'payment_method' => 'cash',
            'status' => $status,
            'delivery_area' => 'الرمال',
            'address_details' => 'غزة - تل الهوى',
            'phone' => $customer->phone,
            'subtotal' => 22,
            'delivery_fee' => 8,
            'total' => 30,
            'confirmed_at' => in_array($status, ['confirmed', 'preparing', 'delivering', 'delivered'], true) ? now() : null,
        ]);

        $order->items()->create([
            'name' => 'شاورما دجاج',
            'price' => 22,
            'quantity' => 1,
            'line_total' => 22,
            'notes' => 'بدون بصل',
        ]);

        return [$owner, $order];
    }
}
