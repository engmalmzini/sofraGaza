<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_panel_and_hide_restaurant_while_keeping_data(): void
    {
        $admin = User::factory()->admin()->create();
        [$owner, $restaurant] = $this->makeApprovedVenue();

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
            ->assertOk()
            ->assertSee('تم إيقاف اللوحة', false)
            ->assertSee('بياناتك محفوظة', false);

        $this->actingAs($owner)
            ->get(route('partner.menu-items.index'))
            ->assertRedirect(route('partner.dashboard'));
    }

    public function test_admin_approval_publishes_restaurant_immediately(): void
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

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('جاري التحقق', false);

        $this->actingAs($admin)
            ->post(route('admin.restaurants.approve', $restaurant))
            ->assertRedirect(route('admin.restaurants.index'));

        $restaurant->refresh();
        $owner->refresh();
        $this->assertTrue($restaurant->isVisible());
        $this->assertTrue($restaurant->hasPaidAccess());
        $this->assertTrue($restaurant->isApproved());

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('ظاهر للزبائن', false)
            ->assertDontSee('اشتراكك', false);
    }

    public function test_approved_restaurant_stays_visible_after_old_listing_date_passes(): void
    {
        [, $restaurant] = $this->makeApprovedVenue();
        $restaurant->update(['expires_at' => now()->subDays(10)]);

        $restaurant->refresh();
        $this->assertTrue($restaurant->isVisible());
        $this->assertTrue(Restaurant::query()->visible()->whereKey($restaurant->id)->exists());

        $this->get(route('restaurants.show', $restaurant))->assertOk();
        $this->get(route('home'))->assertSee('مطعم الياسمين الموقوف', false);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('admin.restaurants.index'))
            ->assertOk()
            ->assertDontSee('ينتهي', false)
            ->assertDontSee('باقي', false);
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function makeApprovedVenue(): array
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

        return [$owner, $restaurant];
    }
}
