<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOnlyShoppingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_customer_can_open_cart_and_memberships(): void
    {
        $this->get(route('cart.index'))->assertOk();
        $this->get(route('memberships.index'))->assertOk();

        $this->actingAs(User::factory()->create())
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('data-header-cart', false);

        $this->actingAs(User::factory()->create())
            ->get(route('memberships.index'))
            ->assertOk();
    }

    public function test_admin_cannot_use_customer_shopping_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->makeVisibleDish();

        $this->actingAs($admin)
            ->get(route('cart.index'))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->get(route('memberships.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('account.wallet'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('account.orders'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('account.show'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('account.favorites'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('account.invite'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('redeem.create'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->post(route('cart.add', $item))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->postJson(route('cart.add', $item))
            ->assertForbidden()
            ->assertJson(['ok' => false]);
    }

    public function test_restaurant_owner_cannot_use_customer_shopping_routes(): void
    {
        $owner = User::factory()->restaurantOwner()->create();
        Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الشريك',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => false,
            'verification_status' => Restaurant::VERIFICATION_PENDING,
        ]);
        $item = $this->makeVisibleDish();

        $this->actingAs($owner)
            ->get(route('cart.index'))
            ->assertRedirect(route('partner.dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($owner)
            ->get(route('memberships.index'))
            ->assertRedirect(route('partner.dashboard'));

        $this->actingAs($owner)
            ->postJson(route('cart.add', $item))
            ->assertForbidden();
    }

    public function test_admin_and_owner_do_not_see_customer_shopping_chrome(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->makeVisibleDish();
        $restaurant = $item->restaurant;

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-header-cart', false)
            ->assertDontSee('>المكافآت</a>', false)
            ->assertDontSee('رصيد المحفظة', false)
            ->assertSee('استعرض المطاعم', false);

        $this->actingAs($admin)
            ->get(route('restaurants.show', $restaurant))
            ->assertOk()
            ->assertDontSee('إضافة سريعة', false)
            ->assertDontSee('تخصيص', false)
            ->assertDontSee('سلة طلبك', false)
            ->assertDontSee('عضوية ذهبية', false)
            ->assertDontSee('إضافة للسلة الآن', false);

        $owner = User::factory()->restaurantOwner()->create();

        $this->actingAs($owner)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-header-cart', false)
            ->assertDontSee('>المكافآت</a>', false)
            ->assertSee('لوحتي', false);
    }

    public function test_admin_still_manages_customer_memberships_and_partner_opens_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->restaurantOwner()->create();
        Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الاشتراك',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => false,
            'verification_status' => Restaurant::VERIFICATION_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.memberships.index'))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk();
    }

    private function makeVisibleDish(): MenuItem
    {
        $restaurant = Restaurant::query()->create([
            'name' => 'مطعم الظاهر',
            'type' => 'restaurant',
            'area' => 'الرمال',
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        return MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'مسخن غزي',
            'category' => 'وجبات',
            'price' => 45,
            'is_available' => true,
        ]);
    }
}
