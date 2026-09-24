<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipSubscription;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Services\ExpiryNoticeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MembershipCardTest extends TestCase
{
    use RefreshDatabase;

    private function basicPlan(): Membership
    {
        return Membership::query()->create([
            'name' => 'العضوية الأساسية',
            'monthly_price' => 50,
            'discount_percent' => 5,
            'free_delivery' => false,
            'points_multiplier' => 1.25,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_member_can_request_site_card(): void
    {
        $user = User::factory()->create();
        $plan = $this->basicPlan();

        MembershipSubscription::query()->create([
            'user_id' => $user->id,
            'membership_id' => $plan->id,
            'amount' => 50,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(29),
        ]);

        $this->actingAs($user)
            ->get(route('memberships.index'))
            ->assertOk()
            ->assertSee('تفاصيل اشتراكك', false)
            ->assertSee('طلب بطاقة المطعم', false)
            ->assertSee('بطاقة عضوية رقمية', false)
            ->assertSee('SG-00001', false);

        $this->actingAs($user)
            ->post(route('memberships.card'), ['card_note' => 'حي الرمال'])
            ->assertRedirect();

        $this->assertDatabaseHas('membership_subscriptions', [
            'user_id' => $user->id,
            'card_status' => 'pending',
            'card_note' => 'حي الرمال',
        ]);
    }

    public function test_digital_card_appears_on_profile_after_admin_approves_transfer(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['name' => 'سارة العالول', 'phone' => '0591234567']);
        $admin = User::factory()->admin()->create();
        $plan = $this->basicPlan();

        $this->actingAs($user)
            ->post(route('memberships.subscribe', $plan), [
                'receipt' => UploadedFile::fake()->image('receipt.jpg'),
            ])
            ->assertRedirect(route('memberships.index'));

        $subscription = MembershipSubscription::query()->first();

        $this->actingAs($admin)
            ->post(route('admin.subscriptions.approve', $subscription))
            ->assertRedirect();

        $subscription->refresh();

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('سارة العالول', false)
            ->assertSee('العضوية الأساسية', false)
            ->assertSee('بطاقة عضوية رقمية', false)
            ->assertSee($subscription->cardNumber(), false)
            ->assertSee($subscription->starts_at->format('Y/m/d'), false)
            ->assertSee($subscription->ends_at->format('Y/m/d'), false);

        $this->actingAs($admin)
            ->get(route('admin.subscriptions.show', $subscription))
            ->assertOk()
            ->assertSee('إرسال نسخة البطاقة عبر واتساب', false)
            ->assertSee('wa.me/972591234567', false);
    }

    public function test_membership_plans_show_extra_points_rate(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'points_per_amount'],
            ['value' => '10', 'label' => 'كل كم شيكل = نقطة']
        );
        Setting::forgetCache();

        $this->basicPlan();
        Membership::query()->create([
            'name' => 'العضوية المميزة',
            'monthly_price' => 100,
            'discount_percent' => 10,
            'free_delivery' => true,
            'points_multiplier' => 1.50,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->get(route('memberships.index'))
            ->assertOk()
            ->assertSee('50', false)
            ->assertSee('100', false)
            ->assertSee('خصم 5% على كل طلب', false)
            ->assertSee('خصم 10% على كل طلب', false)
            ->assertSee('توصيل مجاني', false)
            ->assertSee('1.25 نقطة لكل 10 شيكل بدل نقطة واحدة', false)
            ->assertSee('1.5 نقطة لكل 10 شيكل بدل نقطة واحدة', false);
    }

    public function test_admin_can_pause_a_membership(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = $this->basicPlan();

        $this->actingAs($admin)
            ->post(route('admin.memberships.toggle', $plan))
            ->assertRedirect();

        $this->assertFalse($plan->fresh()->is_active);

        $this->get(route('memberships.index'))
            ->assertOk()
            ->assertDontSee('العضوية الأساسية', false);
    }

    public function test_checkout_summary_shows_original_discount_and_final(): void
    {
        $user = User::factory()->create();
        $plan = $this->basicPlan();
        MembershipSubscription::query()->create([
            'user_id' => $user->id,
            'membership_id' => $plan->id,
            'amount' => 50,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(29),
        ]);

        $restaurant = Restaurant::query()->create([
            'name' => 'مشاوي أبو العبد',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'كباب',
            'category' => 'مشاوي',
            'price' => 40,
            'is_available' => true,
        ]);

        $this->actingAs($user)
            ->from(route('restaurants.show', $restaurant))
            ->post(route('cart.add', $item), ['quantity' => 1])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('السعر الأصلي', false)
            ->assertSee('نسبة الخصم 5%', false)
            ->assertSee('السعر النهائي', false);
    }

    public function test_member_gets_expiry_warning_before_subscription_ends(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'membership_expiry_warning_days'],
            ['value' => '3', 'label' => 'تنبيه']
        );
        Setting::forgetCache();

        $user = User::factory()->create();
        $plan = $this->basicPlan();
        MembershipSubscription::query()->create([
            'user_id' => $user->id,
            'membership_id' => $plan->id,
            'amount' => 50,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now()->subDays(27),
            'ends_at' => now()->addDays(3)->endOfDay(),
        ]);

        cache()->flush();
        app(ExpiryNoticeService::class)->dispatch();

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $user->id,
            'title' => 'قرب انتهاء عضويتك',
        ]);

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('عضويتك تنتهي خلال', false);
    }

    public function test_partner_can_verify_card_and_see_discount(): void
    {
        $customer = User::factory()->create(['name' => 'يحيى داود']);
        $owner = User::factory()->restaurantOwner()->create();
        $plan = $this->basicPlan();
        $subscription = MembershipSubscription::query()->create([
            'user_id' => $customer->id,
            'membership_id' => $plan->id,
            'amount' => 50,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(29),
        ]);

        Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مشاوي أبو العبد',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        $this->actingAs($owner)
            ->get(route('partner.cards.show', ['q' => $subscription->cardNumber()]))
            ->assertOk()
            ->assertSee('يحيى داود', false)
            ->assertSee('5%', false)
            ->assertSee('الخصم داخل المطعم', false);
    }
}
