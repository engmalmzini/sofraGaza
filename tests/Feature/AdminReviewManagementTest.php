<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createRestaurant(): Restaurant
    {
        $owner = User::factory()->create(['role' => 'restaurant']);
        return Restaurant::create([
            'user_id' => $owner->id,
            'name' => 'مطعم الأصالة',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'address' => 'شارع الوحدة',
            'phone' => '0599111222',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
    }

    public function test_admin_can_view_reviews_page_with_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $restaurant = $this->createRestaurant();
        $user = User::factory()->create();

        Review::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'rating' => 5,
            'comment' => 'طعام رائع جدا وممتاز',
            'is_approved' => true,
        ]);

        Review::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'rating' => 1,
            'comment' => 'غير لائق',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reviews.index'));

        $response->assertOk();
        $response->assertSee('طعام رائع جدا وممتاز');
        $response->assertSee('غير لائق');
        $response->assertSee('إجمالي التقييمات');
        $response->assertSee('متوسط التقييم العام');

        // Test status filter
        $approvedResponse = $this->actingAs($admin)->get(route('admin.reviews.index', ['status' => 'approved']));
        $approvedResponse->assertOk();
        $approvedResponse->assertSee('طعام رائع جدا وممتاز');
        $approvedResponse->assertDontSee('غير لائق');
    }

    public function test_admin_can_toggle_and_delete_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $restaurant = $this->createRestaurant();
        $user = User::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'rating' => 5,
            'comment' => 'تجربة رائعة',
            'is_approved' => false,
        ]);

        $toggleResponse = $this->actingAs($admin)->post(route('admin.reviews.toggle', $review));
        $toggleResponse->assertRedirect();
        $this->assertTrue($review->fresh()->is_approved);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
