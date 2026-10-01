<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReferralProgramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.tweetsms.endpoint' => 'https://www.tweetsms.ps/api.php',
            'services.tweetsms.key' => 'test-key',
            'services.tweetsms.sender' => 'Sofra Gaza',
        ]);

        Http::preventStrayRequests();
    }

    public function test_new_customer_gets_a_referral_code(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->referral_code);
        $this->assertSame(6, strlen($user->referral_code));
    }

    public function test_invite_page_shows_code_and_both_sides_reward(): void
    {
        $customer = User::factory()->create(['name' => 'أحمد الغزي']);

        $this->actingAs($customer)
            ->get(route('account.invite'))
            ->assertOk()
            ->assertSee('ادعُ صديق، وكلاكما ياخذ نقاط', false)
            ->assertSee($customer->fresh()->referral_code, false)
            ->assertSee('شخص دعاك', false);
    }

    public function test_register_link_with_ref_remembers_the_code(): void
    {
        $inviter = User::factory()->create();

        $this->get(route('register', ['ref' => $inviter->referral_code]))
            ->assertOk()
            ->assertSee('كود الدعوة محفوظ', false)
            ->assertSee($inviter->referral_code, false);
    }

    public function test_both_get_points_when_invitee_registers_with_code(): void
    {
        $inviter = User::factory()->create(['name' => 'أحمد الغزي', 'points_balance' => 10]);

        $this->registerCustomer('0597000222', 'سارة الغزي', $inviter->referral_code)
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $invitee = User::query()->where('phone', '0597000222')->first();
        $this->assertNotNull($invitee);
        $this->assertSame($inviter->id, $invitee->referred_by_id);
        $this->assertSame(60, $inviter->fresh()->points_balance);
        $this->assertSame(50, $invitee->points_balance);
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $inviter->id,
            'points' => 50,
        ]);
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $invitee->id,
            'points' => 50,
        ]);
    }

    public function test_invalid_referral_code_is_rejected_before_creating_the_account(): void
    {
        $this->beginVerifiedPhone('0597000333');

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'خالد الجديد',
                'password' => 'secret1',
                'password_confirmation' => 'secret1',
                'referral_code' => 'NOCODE',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('referral_code');

        $this->assertDatabaseMissing('users', ['phone' => '0597000333']);
    }

    public function test_registration_without_code_does_not_award_referral_points(): void
    {
        $this->registerCustomer('0597000444', 'منى الغزي')
            ->assertRedirect(route('home'));

        $user = User::query()->where('phone', '0597000444')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->referred_by_id);
        $this->assertSame(0, $user->points_balance);
    }

    public function test_owner_referral_code_cannot_be_used(): void
    {
        $owner = User::factory()->restaurantOwner()->create();

        $this->beginVerifiedPhone('0597000555');

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'ضيف المطعم',
                'password' => 'secret1',
                'password_confirmation' => 'secret1',
                'referral_code' => $owner->referral_code,
            ])
            ->assertSessionHasErrors('referral_code');
    }

    public function test_second_friend_with_same_code_also_rewards_the_inviter(): void
    {
        $inviter = User::factory()->create(['points_balance' => 0]);

        $this->registerCustomer('0597000666', 'صديق أول', $inviter->referral_code);
        $this->post(route('logout'));
        $this->registerCustomer('0597000777', 'صديق ثاني', $inviter->referral_code);

        $this->assertSame(100, $inviter->fresh()->points_balance);
        $this->assertSame(2, $inviter->referredUsers()->count());
    }

    public function test_admin_cannot_open_invite_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('account.invite'))
            ->assertRedirect(route('admin.dashboard'));
    }

    private function registerCustomer(string $phone, string $name, ?string $referralCode = null)
    {
        $this->beginVerifiedPhone($phone);

        $payload = [
            'name' => $name,
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ];

        if ($referralCode !== null) {
            $payload['referral_code'] = $referralCode;
        }

        return $this->post(route('register'), $payload);
    }

    private function beginVerifiedPhone(string $phone): string
    {
        $query = [];
        Http::fake(function ($request) use (&$query) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return Http::response('1', 200);
        });

        $this->post(route('register.phone'), ['phone' => $phone])->assertRedirect(route('register'));
        preg_match('/(\d{6})/', (string) ($query['message'] ?? ''), $matches);
        $code = $matches[1] ?? '';
        $this->assertNotSame('', $code);

        $this->post(route('register.verify'), ['code' => $code])->assertRedirect(route('register'));

        return $code;
    }
}
