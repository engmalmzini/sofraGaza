<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_can_save_optional_item_sections_with_prices(): void
    {
        [$owner, $restaurant] = $this->venue();

        $this->actingAs($owner)
            ->get(route('partner.menu-items.create'))
            ->assertOk()
            ->assertSee('أقسام وأسعار للصنف', false);

        $this->actingAs($owner)
            ->post(route('partner.menu-items.store'), [
                'name' => 'مسخن',
                'category' => 'وجبات',
                'price' => 50,
                'is_available' => 1,
                'stock_mode' => 'unlimited',
                'option_groups' => [
                    [
                        'name' => 'النوع',
                        'type' => 'single',
                        'required' => '1',
                        'choices' => [
                            ['name' => 'نص عادي', 'price' => 0],
                            ['name' => 'نص باكج', 'price' => 10],
                            ['name' => 'كامل عادي', 'price' => 50],
                            ['name' => 'كامل باكج', 'price' => 70],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('partner.menu-items.index'));

        $item = $restaurant->menuItems()->first();
        $this->assertTrue($item->hasOptionGroups());
        $this->assertSame(50.0, $item->displayPrice());
        $this->assertSame('النوع', $item->optionGroups()[0]['name']);
        $this->assertCount(4, $item->optionGroups()[0]['choices']);
    }

    public function test_restaurant_page_embeds_parseable_item_types(): void
    {
        [, $restaurant] = $this->venue();
        $restaurant->menuItems()->create([
            'name' => 'مسخن',
            'category' => 'وجبات',
            'price' => 50,
            'is_available' => true,
            'stock_unlimited' => true,
            'option_groups' => [
                [
                    'name' => 'النوع',
                    'type' => 'single',
                    'required' => true,
                    'choices' => [
                        ['name' => 'نص عادي', 'price' => 0],
                        ['name' => 'نص باكج', 'price' => 10],
                    ],
                ],
            ],
        ]);

        $html = $this->get(route('restaurants.show', $restaurant))
            ->assertOk()
            ->assertSee('اختر النوع', false)
            ->getContent();

        $this->assertStringNotContainsString('&amp;quot;', $html);
        $this->assertTrue(preg_match('/data-option-groups="([^"]+)"/', $html, $match) === 1);
        $raw = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $groups = json_decode($raw, true);
        $this->assertIsArray(
            $groups,
            json_last_error_msg().' :: '.mb_substr($raw, 0, 180)
        );
        $this->assertSame('النوع', $groups[0]['name'] ?? null);
        $this->assertSame('نص باكج', $groups[0]['choices'][1]['name'] ?? null);
    }

    public function test_customer_must_choose_required_section_and_pays_extra(): void
    {
        [, $restaurant] = $this->venue();
        $item = $restaurant->menuItems()->create([
            'name' => 'مسخن',
            'category' => 'وجبات',
            'price' => 50,
            'is_available' => true,
            'stock_unlimited' => true,
            'option_groups' => [
                [
                    'name' => 'النوع',
                    'type' => 'single',
                    'required' => true,
                    'choices' => [
                        ['name' => 'نص عادي', 'price' => 0],
                        ['name' => 'كامل باكج', 'price' => 70],
                    ],
                ],
            ],
        ]);
        $customer = User::factory()->create(['wallet_balance' => 200]);

        $this->actingAs($customer)
            ->post(route('cart.add', $item), ['quantity' => 1])
            ->assertSessionHas('error');

        $this->actingAs($customer)
            ->post(route('cart.add', $item), [
                'quantity' => 1,
                'option_groups' => [0 => 1],
            ])
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('كامل باكج', false)
            ->assertSee('120.00', false);

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'payment_method' => 'wallet',
                'area' => 'الرمال',
                'address_details' => 'غزة - الرمال',
                'phone' => $customer->phone,
            ])
            ->assertRedirect();

        $orderItem = Order::query()->latest('id')->first()->items->first();
        $this->assertSame(120.0, (float) $orderItem->price);
        $this->assertSame(120.0, (float) $orderItem->line_total);
        $this->assertSame('كامل باكج', $orderItem->choicesLabel());
    }

    public function test_plain_item_still_adds_without_sections(): void
    {
        [, $restaurant] = $this->venue();
        $item = $restaurant->menuItems()->create([
            'name' => 'شاي',
            'category' => 'مشروبات',
            'price' => 5,
            'is_available' => true,
            'stock_unlimited' => true,
        ]);
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('cart.add', $item), ['quantity' => 2])
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('10.00', false);
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function venue(): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الأقسام',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        return [$owner, $restaurant];
    }
}
