<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantRatingDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_without_reviews_has_no_fake_average(): void
    {
        $restaurant = $this->venue();

        $this->assertNull($restaurant->averageRating());
        $this->assertSame(0, $restaurant->reviewsCount());

        $this->get(route('restaurants.show', $restaurant))
            ->assertOk()
            ->assertSee('0 تقييم معتمد', false)
            ->assertSee('—', false);
    }

    public function test_restaurant_shows_only_real_customer_ratings(): void
    {
        $restaurant = $this->venue();
        $customer = User::factory()->create();

        Review::query()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'rating' => 4,
            'comment' => 'تجربة حقيقية من زبون',
            'is_approved' => true,
        ]);

        $this->assertSame(4.0, $restaurant->fresh()->averageRating());
        $this->assertSame(1, $restaurant->fresh()->reviewsCount());

        $this->get(route('restaurants.show', $restaurant))
            ->assertOk()
            ->assertSee('4.0', false)
            ->assertSee('تجربة حقيقية من زبون', false)
            ->assertSee('1 تقييم معتمد', false);
    }

    private function venue(): Restaurant
    {
        $owner = User::factory()->restaurantOwner()->create();

        return Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم التقييم',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
    }
}
