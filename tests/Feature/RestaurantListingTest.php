<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\RestaurantPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_panel_and_hide_restaurant_while_keeping_data(): void
    {
        $admin = User::factory()->admin()->create();
        [$owner, $restaurant] = $this->makePaidVenue();

        $this->actingAs($admin)
            ->post(route('admin.restaurants.suspend', $restaurant))
            ->assertRedirect();

        $restaurant->refresh();
        $this->assertTrue($restaurant->panel_suspended);
        $this->assertFalse($restaurant->is_active);
        $this->assertFalse($restaurant->isVisible());
        $this->assertFalse($restaurant->hasPaidAccess());
        $this->assertDatabaseHas('restaurants', ['id' => $restaurant->id, 'name' => 'مطعم الياسمين الموقوف']);

        $this->post('/logout');

        $this->get(route('home'))->assertDontSee('مطعم الياسمين الموقوف', false);
        $this->get(route('restaurants.show', $restaurant))->assertNotFound();

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertRedirect(route('partner.subscription.index'));

        $this->actingAs($owner)
            ->get(route('partner.subscription.index'))
            ->assertOk()
            ->assertSee('قم بتجديد الاشتراك', false)
            ->assertSee('بياناتك محفوظة', false);
    }

    public function test_admin_confirms_transfer_and_partner_sees_remaining_days(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'كافي تِرا',
            'type' => 'cafe',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => false,
            'verification_status' => Restaurant::VERIFICATION_PENDING,
        ]);
        RestaurantPlan::seedDefaults();
        Storage::fake('public');
        $plan = RestaurantPlan::query()->where('duration_days', 180)->first();

        $this->actingAs($owner)
            ->post(route('partner.subscription.store', $plan), [
                'receipt' => UploadedFile::fake()->image('hawala.jpg'),
            ])
            ->assertRedirect(route('partner.subscription.index'));

        $this->assertDatabaseHas('restaurant_subscriptions', [
            'restaurant_id' => $restaurant->id,
            'status' => 'pending',
            'amount' => 500,
        ]);

        $listing = $restaurant->pendingListing();

        $this->actingAs($admin)
            ->get(route('admin.listings.show', $listing))
            ->assertOk()
            ->assertSee('حوالة', false);

        $this->actingAs($admin)
            ->post(route('admin.listings.approve', $listing))
            ->assertRedirect();

        $restaurant->refresh();
        $this->assertTrue($restaurant->isVisible());
        $this->assertTrue($restaurant->hasPaidAccess());

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('باقة 6 أشهر', false)
            ->assertSee('باقي', false);
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function makePaidVenue(): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الياسمين الموقوف',
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
