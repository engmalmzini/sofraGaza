<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_request_site_card(): void
    {
        $user = User::factory()->create();
        $plan = Membership::query()->create([
            'name' => 'العضوية الأساسية',
            'monthly_price' => 50,
            'discount_percent' => 5,
            'free_delivery' => false,
            'points_multiplier' => 1.25,
            'is_active' => true,
            'sort_order' => 1,
        ]);

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
            ->assertSee('داخل أي مطعم مشترك', false)
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

    public function test_partner_can_verify_card_and_see_discount(): void
    {
        $customer = User::factory()->create(['name' => 'يحيى داود']);
        $owner = User::factory()->restaurantOwner()->create();
        $plan = Membership::query()->create([
            'name' => 'العضوية الأساسية',
            'monthly_price' => 50,
            'discount_percent' => 5,
            'free_delivery' => false,
            'points_multiplier' => 1.25,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $subscription = MembershipSubscription::query()->create([
            'user_id' => $customer->id,
            'membership_id' => $plan->id,
            'amount' => 50,
            'status' => 'approved',
            'transfer_receipt_path' => 'receipts/test.jpg',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(29),
        ]);

        \App\Models\Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مشاوي أبو العبد',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => \App\Models\Restaurant::VERIFICATION_APPROVED,
        ]);

        $this->actingAs($owner)
            ->get(route('partner.cards.show', ['q' => $subscription->cardNumber()]))
            ->assertOk()
            ->assertSee('يحيى داود', false)
            ->assertSee('5%', false)
            ->assertSee('الخصم داخل المطعم', false);
    }
}
