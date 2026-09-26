<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\RestaurantPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantOwnerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_owner_must_subscribe_before_menu_and_listing(): void
    {
        $admin = User::factory()->admin()->create();
        RestaurantPlan::seedDefaults();
        Storage::fake('public');

        $response = $this->post(route('partner.register'), $this->payload());

        $response->assertRedirect(route('partner.subscription.index'));
        $this->assertAuthenticated();

        $owner = User::query()->where('phone', '0598887777')->first();
        $this->assertSame('restaurant_owner', $owner->role);

        $restaurant = $owner->ownedRestaurant;
        $this->assertNotNull($restaurant);
        $this->assertTrue($restaurant->isPending());
        $this->assertFalse($restaurant->is_active);
        $this->assertFalse($restaurant->isVisible());
        $this->assertFalse($restaurant->hasPaidAccess());

        $this->get(route('home'))->assertDontSee('مطعم الكرم الغزي', false);
        $this->get(route('restaurants.show', $restaurant))->assertNotFound();

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertRedirect(route('partner.subscription.index'));

        $this->actingAs($owner)
            ->get(route('partner.subscription.index'))
            ->assertOk()
            ->assertSee('لازم تشترك', false)
            ->assertSee('باقة شهر', false)
            ->assertSee('باقة 6 أشهر', false)
            ->assertSee('باقة سنة', false);

        $this->actingAs($owner)->post(route('partner.menu-items.store'), [
            'name' => 'مسخن دجاج',
            'category' => 'وجبات',
            'description' => 'مسخن على أصوله',
            'price' => 22,
            'is_available' => 1,
        ])->assertRedirect(route('partner.subscription.index'));

        $this->assertDatabaseMissing('menu_items', [
            'restaurant_id' => $restaurant->id,
            'name' => 'مسخن دجاج',
        ]);

        $plan = RestaurantPlan::query()->where('duration_days', 30)->first();

        $this->actingAs($owner)
            ->post(route('partner.subscription.store', $plan), [
                'receipt' => UploadedFile::fake()->image('receipt.jpg'),
            ])
            ->assertRedirect(route('partner.subscription.index'));

        $this->actingAs($admin)
            ->post(route('admin.restaurants.approve', $restaurant), ['listing_days' => 90])
            ->assertRedirect(route('admin.restaurants.index'));

        $restaurant->refresh();
        $this->assertTrue($restaurant->isApproved());
        $this->assertFalse($restaurant->isVisible());

        $listing = $restaurant->pendingListing();
        $this->actingAs($admin)
            ->post(route('admin.listings.approve', $listing))
            ->assertRedirect();

        $restaurant->refresh();
        $this->assertTrue($restaurant->hasPaidAccess());
        $this->assertTrue($restaurant->isVisible());

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('باقة شهر', false);

        $this->actingAs($owner)->post(route('partner.menu-items.store'), [
            'name' => 'مسخن دجاج',
            'category' => 'وجبات',
            'description' => 'مسخن على أصوله',
            'price' => 22,
            'is_available' => 1,
        ])->assertRedirect(route('partner.menu-items.index'));

        $this->get(route('home'))->assertSee('مطعم الكرم الغزي', false);
        $this->get(route('restaurants.show', $restaurant))->assertOk();
        $this->assertTrue(MenuItem::query()->where('name', 'مسخن دجاج')->exists());
    }

    public function test_pending_restaurant_does_not_appear_in_public_listing(): void
    {
        $owner = User::factory()->restaurantOwner()->create();
        Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم غير منشور',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => false,
            'verification_status' => Restaurant::VERIFICATION_PENDING,
        ]);

        $this->get(route('restaurants.index'))->assertDontSee('مطعم غير منشور', false);
        $this->get(route('home'))->assertDontSee('مطعم غير منشور', false);
    }

    public function test_failed_registration_keeps_password_for_the_next_attempt(): void
    {
        $payload = $this->payload();
        $payload['description'] = 'قصير';

        $this->from(route('partner.register'))
            ->post(route('partner.register'), $payload)
            ->assertRedirect(route('partner.register'))
            ->assertSessionHasErrors('description')
            ->assertSessionDoesntHaveErrors(['password']);

        $this->get(route('partner.register'))
            ->assertOk()
            ->assertSee('value="secret12"', false);

        $retry = $payload;
        $retry['description'] = 'مطبخ غزي بيتي يقدم المقلوبة والمسخن والقدرة لعائلة غزة.';
        unset($retry['password'], $retry['password_confirmation']);

        $this->post(route('partner.register'), $retry)
            ->assertRedirect(route('partner.subscription.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['phone' => '0598887777']);
    }

    public function test_customer_cannot_open_partner_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('partner.dashboard'))
            ->assertForbidden();
    }

    private function payload(): array
    {
        return [
            'owner_name' => 'خالد الغزي',
            'phone' => '0598887777',
            'email' => 'khaled@example.com',
            'owner_national_id' => '401234567',
            'password' => 'secret12',
            'password_confirmation' => 'secret12',
            'restaurant_name' => 'مطعم الكرم الغزي',
            'type' => 'restaurant',
            'cuisine' => 'palestinian',
            'description' => 'مطبخ غزي بيتي يقدم المقلوبة والمسخن والقدرة لعائلة غزة.',
            'restaurant_phone' => '0592002002',
            'license_number' => 'LIC-22',
            'area' => 'الرمال',
            'address' => 'شارع الجلاء، غزة',
            'opens_at' => '10:00',
            'closes_at' => '23:00',
            'terms' => '1',
        ];
    }
}
