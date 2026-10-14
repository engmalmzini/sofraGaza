<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\RestaurantBoost;
use App\Models\User;
use App\Support\Finance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantBoostAdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_partner_can_request_boost_and_price_is_calculated_from_days(): void
    {
        [$owner, $restaurant] = $this->approvedVenue('مطعم الإعلان');

        $this->actingAs($owner)
            ->get(route('partner.boosts.index'))
            ->assertOk()
            ->assertSee('اشترِ إعلاناً', false)
            ->assertSee('تصميم الإعلان', false)
            ->assertSee('20', false);

        $this->actingAs($owner)
            ->post(route('partner.boosts.store'), $this->boostRequest(5))
            ->assertRedirect(route('partner.boosts.index'));

        $this->assertDatabaseHas('restaurant_boosts', [
            'restaurant_id' => $restaurant->id,
            'days' => 5,
            'amount' => 100,
            'daily_rate' => Finance::BOOST_DAILY_RATE,
            'status' => 'pending',
        ]);

        $this->actingAs($owner)
            ->get(route('partner.boosts.index'))
            ->assertOk()
            ->assertSee('بانتظار تأكيد الإدارة', false)
            ->assertSee('100', false);
    }

    public function test_pending_boost_does_not_appear_first_until_admin_approves(): void
    {
        $admin = User::factory()->admin()->create();
        [$owner, $boosted] = $this->approvedVenue('مطعم الظاهر أولاً');
        $this->approvedVenue('مطعم عادي', now()->subHour());

        $this->actingAs($owner)
            ->post(route('partner.boosts.store'), $this->boostRequest(3))
            ->assertRedirect();

        $boost = RestaurantBoost::query()->first();

        $home = $this->get(route('home'));
        $home->assertOk();
        $this->assertFalse($boosted->fresh()->isBoosted());
        $home->assertDontSee('إعلانات تظهر أولاً', false);

        $this->actingAs($admin)
            ->get(route('admin.boosts.index'))
            ->assertOk()
            ->assertSee('مطعم الظاهر أولاً', false);

        $this->actingAs($admin)
            ->post(route('admin.boosts.approve', $boost))
            ->assertRedirect();

        $boost->refresh();
        $this->assertTrue($boost->isApproved());
        $this->assertTrue($boost->isLive());
        $this->assertTrue($boosted->fresh()->isBoosted());

        $home = $this->get(route('home'));
        $home->assertOk()
            ->assertDontSee('إعلانات تظهر أولاً', false)
            ->assertSee('إعلان مميز', false)
            ->assertSee('مطعم الظاهر أولاً', false)
            ->assertSee('sg-ad-hero', false);

        $html = $home->getContent();
        $adPos = mb_strpos($html, 'مطعم الظاهر أولاً');
        $plainPos = mb_strpos($html, 'مطعم عادي');
        $this->assertNotFalse($adPos);
        $this->assertNotFalse($plainPos);
        $this->assertLessThan($plainPos, $adPos);
        $this->assertSame(2, substr_count($html, 'إعلان مميز'));
        $home->assertSee('/storage/boosts/banners/', false);
        $home->assertSee(route('restaurants.show', $boosted, false), false);
        $home->assertSee('إعلان مطعم الظاهر أولاً', false);
    }

    public function test_boosted_restaurant_still_appears_when_outside_selected_delivery_area(): void
    {
        $admin = User::factory()->admin()->create();
        [$owner, $boosted] = $this->approvedVenue('أحمد علي', ['area' => 'خانيونس']);
        $this->approvedVenue('مطعم الرمال', ['area' => 'الرمال']);

        $this->actingAs($owner)
            ->post(route('partner.boosts.store'), $this->boostRequest(3));

        $boost = \App\Models\RestaurantBoost::query()->first();
        $this->actingAs($admin)->post(route('admin.boosts.approve', $boost));

        $this->withSession(['delivery_area' => ['key' => 'الرمال', 'label' => 'غزة • حي الرمال']])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('أحمد علي', false)
            ->assertSee('إعلان مميز', false)
            ->assertSee('sg-ad-hero', false)
            ->assertSee(route('restaurants.show', $boosted, false), false)
            ->assertSee('إعلان أحمد علي', false);
    }

    public function test_admin_can_reject_boost_request(): void
    {
        $admin = User::factory()->admin()->create();
        [$owner] = $this->approvedVenue('مطعم مرفوض إعلانه');

        $this->actingAs($owner)
            ->post(route('partner.boosts.store'), $this->boostRequest(2));

        $boost = RestaurantBoost::query()->first();

        $this->actingAs($admin)
            ->post(route('admin.boosts.reject', $boost), [
                'rejection_reason' => 'الإشعار غير واضح',
            ])
            ->assertRedirect();

        $this->assertSame('rejected', $boost->fresh()->status);

        $this->actingAs($owner)
            ->get(route('partner.boosts.index'))
            ->assertOk()
            ->assertSee('مرفوض', false);
    }

    public function test_unapproved_partner_cannot_buy_an_ad(): void
    {
        $owner = User::factory()->restaurantOwner()->create();
        Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم قيد المراجعة',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => false,
            'verification_status' => Restaurant::VERIFICATION_PENDING,
        ]);

        $this->actingAs($owner)
            ->post(route('partner.boosts.store'), $this->boostRequest(3))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('restaurant_boosts', 0);
    }

    /**
     * @return array{days: int, receipt: UploadedFile, banner: UploadedFile}
     */
    private function boostRequest(int $days): array
    {
        return [
            'days' => $days,
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
            'banner' => UploadedFile::fake()->image('ad-design.jpg', 1200, 300),
        ];
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function approvedVenue(string $name, $createdAt = null, array $extra = []): array
    {
        if (is_array($createdAt)) {
            $extra = $createdAt;
            $createdAt = null;
        }

        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create(array_merge([
            'owner_id' => $owner->id,
            'name' => $name,
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'panel_suspended' => false,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'created_at' => $createdAt ?? now(),
        ], $extra));

        return [$owner, $restaurant];
    }
}
