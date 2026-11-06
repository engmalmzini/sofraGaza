<?php

namespace Tests\Feature;

use App\Models\MenuOffer;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedItemsAndOffersTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_and_admin_can_mark_an_item_featured_and_it_appears_first(): void
    {
        [$owner, $restaurant] = $this->venue();
        $rice = $restaurant->menuItems()->create([
            'name' => 'رز قدر',
            'category' => 'الأرز',
            'price' => 10,
            'is_available' => true,
        ]);
        $soup = $restaurant->menuItems()->create([
            'name' => 'شوربة عدس',
            'category' => 'الشوربات',
            'price' => 8,
            'is_available' => true,
            'is_featured' => false,
        ]);

        $this->actingAs($owner)
            ->post(route('partner.menu-items.featured', $soup))
            ->assertRedirect();

        $this->assertTrue($soup->fresh()->is_featured);

        $html = $this->get(route('restaurants.show', $restaurant))
            ->assertOk()
            ->assertSee('مميز', false)
            ->assertSee('شوربة عدس', false)
            ->getContent();

        $this->assertLessThan(
            strpos($html, 'رز قدر'),
            strpos($html, 'data-menu-section="featured"')
        );

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->from(route('admin.restaurants.menu-items.edit', [$restaurant, $rice]))
            ->put(route('admin.restaurants.menu-items.update', [$restaurant, $rice]), [
                'name' => 'رز قدر',
                'category' => 'الأرز',
                'price' => 10,
                'is_available' => 1,
                'is_featured' => 1,
                'stock_mode' => 'unlimited',
            ])
            ->assertRedirect(route('admin.restaurants.menu-items.index', $restaurant));

        $this->assertTrue($rice->fresh()->is_featured);
    }

    public function test_partner_can_create_a_two_item_offer_and_customer_can_order_it(): void
    {
        [$owner, $restaurant] = $this->venue();
        $coffee = $restaurant->menuItems()->create([
            'name' => 'قهوة تركية',
            'category' => 'مشروبات',
            'price' => 8,
            'is_available' => true,
            'stock_unlimited' => false,
            'stock_qty' => 5,
        ]);
        $cake = $restaurant->menuItems()->create([
            'name' => 'كيك فستق',
            'category' => 'حلويات',
            'price' => 12,
            'is_available' => true,
            'stock_unlimited' => false,
            'stock_qty' => 4,
        ]);
        $restaurant->menuItems()->create([
            'name' => 'كرواسون زعتر',
            'category' => 'معجنات',
            'price' => 6,
            'is_available' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('partner.menu-offers.store'), [
                'name' => 'عرض القهوة والكيك',
                'description' => 'الصباح أحلى',
                'first_item_id' => $coffee->id,
                'second_item_id' => $cake->id,
                'price' => 15,
                'is_active' => 1,
            ])
            ->assertRedirect(route('partner.menu-items.index'));

        $offer = MenuOffer::query()->first();
        $this->assertNotNull($offer);
        $this->assertSame(15.0, (float) $offer->price);

        $customer = User::factory()->create(['wallet_balance' => 80]);
        $page = $this->actingAs($customer)
            ->get(route('restaurants.show', $restaurant))
            ->assertOk()
            ->assertSee('العروض', false)
            ->assertSee('عرض القهوة والكيك', false)
            ->assertSee('15', false)
            ->assertSee('cart/offers/'.$offer->id, false)
            ->getContent();

        $this->assertLessThan(
            strpos($page, 'كرواسون زعتر') ?: PHP_INT_MAX,
            strpos($page, 'data-menu-section="offers"') ?: PHP_INT_MAX
        );

        $this->actingAs($customer)
            ->post(route('cart.add-offer', $offer), ['quantity' => 1])
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('عرض القهوة والكيك', false)
            ->assertSee('قهوة تركية', false)
            ->assertSee('كيك فستق', false);

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'payment_method' => 'wallet',
                'area' => 'الرمال',
                'address_details' => 'غزة - الرمال',
                'phone' => $customer->phone,
            ])
            ->assertRedirect();

        $order = Order::query()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('عرض القهوة والكيك', $order->items->first()->name);
        $this->assertEquals(15, (float) $order->items->first()->line_total);
        $this->assertSame(4, $coffee->fresh()->stock_qty);
        $this->assertSame(3, $cake->fresh()->stock_qty);
    }

    public function test_offer_price_must_be_lower_than_item_total(): void
    {
        [$owner, $restaurant] = $this->venue();
        $item = $restaurant->menuItems()->create([
            'name' => 'كابتشينو',
            'category' => 'مشروبات',
            'price' => 10,
            'is_available' => true,
        ]);

        $this->actingAs($owner)
            ->from(route('partner.menu-offers.create'))
            ->post(route('partner.menu-offers.store'), [
                'name' => 'عرض وهمي',
                'first_item_id' => $item->id,
                'price' => 10,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('price');
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function venue(): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'كافي الوليمة',
            'type' => 'cafe',
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
