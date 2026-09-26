<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerVenueMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_owner_sees_categories_and_grouped_menu(): void
    {
        [$owner, $restaurant] = $this->makeVenue('مطعم الكرم', 'restaurant');

        MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'مسخن دجاج',
            'category' => 'وجبات',
            'price' => 22,
            'is_available' => true,
        ]);
        MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'ليموناضة',
            'category' => 'مشروبات',
            'price' => 8,
            'is_available' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('لوحة المطعم', false)
            ->assertSee('تصنيفات المنيو', false)
            ->assertSee('وجبات', false)
            ->assertSee('مشروبات', false)
            ->assertSee('مسخن دجاج', false)
            ->assertSee('المنيو والتصنيفات', false);

        $this->actingAs($owner)
            ->get(route('partner.menu-items.index'))
            ->assertOk()
            ->assertSee('مسخن دجاج', false)
            ->assertSee('ليموناضة', false);

        $this->actingAs($owner)
            ->get(route('partner.menu-items.index', ['category' => 'وجبات']))
            ->assertOk()
            ->assertSee('مسخن دجاج', false)
            ->assertDontSee('ليموناضة', false);
    }

    public function test_cafe_owner_sees_cafe_categories_and_wording(): void
    {
        [$owner] = $this->makeVenue('كافي تِرا', 'cafe');

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('لوحة الكافي', false)
            ->assertSee('كافيك', false)
            ->assertSee('مشروبات ساخنة', false)
            ->assertSee('معجنات', false)
            ->assertDontSee('مقبلات', false);
    }

    public function test_owner_can_add_custom_menu_category(): void
    {
        [$owner] = $this->makeVenue('فرن النصر', 'restaurant');

        $this->actingAs($owner)
            ->post(route('partner.menu-items.store'), [
                'name' => 'مناقيش زعتر',
                'category' => '__custom__',
                'category_custom' => 'فطور',
                'price' => 8,
                'is_available' => 1,
            ])
            ->assertRedirect(route('partner.menu-items.index'));

        $this->assertDatabaseHas('menu_items', [
            'name' => 'مناقيش زعتر',
            'category' => 'فطور',
        ]);

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertSee('فطور', false)
            ->assertSee('مناقيش زعتر', false);
    }

    public function test_owner_dashboard_has_no_orders_and_edits_notify_admin(): void
    {
        $admin = User::factory()->admin()->create();
        [$owner, $restaurant] = $this->makeVenue('مشاوي أبو العبد', 'restaurant');

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertDontSee('آخر الطلبات', false)
            ->assertDontSee('طلبات بانتظار التأكيد', false)
            ->assertSee('عرض صفحة', false);

        $this->actingAs($owner)
            ->post(route('partner.menu-items.store'), [
                'name' => 'كباب',
                'category' => 'مشاوي',
                'price' => 25,
                'is_available' => 1,
            ])
            ->assertRedirect(route('partner.menu-items.index'));

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $admin->id,
            'title' => 'تعديل منيو يحتاج مراجعة',
        ]);
    }

    /**
     * @return array{0: User, 1: Restaurant}
     */
    private function makeVenue(string $name, string $type): array
    {
        $owner = User::factory()->restaurantOwner()->create();
        $restaurant = Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => $name,
            'type' => $type,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);
        $this->grantPaidListing($restaurant);

        return [$owner, $restaurant];
    }
}
