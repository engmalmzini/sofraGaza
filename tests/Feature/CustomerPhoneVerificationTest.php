<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CustomerPhoneVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerPhoneVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.tweetsms.endpoint' => 'https://www.tweetsms.ps/api.php',
            'services.tweetsms.key' => 'test-key',
            'services.tweetsms.sender' => 'TweetTEST',
        ]);

        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    public function test_register_page_starts_with_phone_verification(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('إرسال رمز التحقق', false)
            ->assertSee('05XXXXXXXX', false)
            ->assertDontSee('إنشاء حساب</button>', false);
    }

    public function test_phone_must_be_ten_digits_starting_with_05(): void
    {
        Http::fake();

        foreach (['059123456', '05912345678', '0491234567', '1234567890'] as $phone) {
            $this->from(route('register'))
                ->post(route('register.phone'), ['phone' => $phone])
                ->assertRedirect(route('register'))
                ->assertSessionHasErrors('phone');
        }

        Http::assertNothingSent();
    }

    public function test_ooredoo_and_jawwal_numbers_can_receive_a_code(): void
    {
        foreach (['0561234567', '0591234567'] as $phone) {
            Http::fake(['www.tweetsms.ps/*' => Http::response('1', 200)]);

            $this->post(route('register.phone'), ['phone' => $phone])
                ->assertRedirect(route('register'))
                ->assertSessionHas('success');

            $this->get(route('register'))
                ->assertOk()
                ->assertSee('تأكيد الرمز', false);
        }
    }

    public function test_sms_uses_tweetsms_sender_and_blocks_registration_until_the_code_matches(): void
    {
        $query = [];
        Http::fake(function ($request) use (&$query) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return Http::response('1:39425866:972597572783:16349769<br />', 200);
        });

        $this->post(route('register'), [
            'name' => 'أحمد الغزي',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ])->assertRedirect(route('register'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('users', 0);

        $this->post(route('register.phone'), ['phone' => '0597000111'])
            ->assertRedirect(route('register'));

        $this->assertSame('sendsms', $query['comm'] ?? null);
        $this->assertSame('test-key', $query['api_key'] ?? null);
        $this->assertSame('TweetTEST', $query['sender'] ?? null);
        $this->assertSame('0597000111', $query['to'] ?? null);
        $this->assertMatchesRegularExpression('/\d{6}/', (string) ($query['message'] ?? ''));
        preg_match('/(\d{6})/', (string) $query['message'], $matches);
        $code = $matches[1];

        $this->assertIsArray(session(CustomerPhoneVerification::SESSION_KEY));
        $this->assertStringNotContainsString($code, json_encode(session(CustomerPhoneVerification::SESSION_KEY)));

        $this->post(route('register.verify'), ['code' => '000000'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->post(route('register.verify'), ['code' => $code])
            ->assertRedirect(route('register'))
            ->assertSessionHas('success');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('0597000111', false)
            ->assertSee('الاسم الكامل', false)
            ->assertDontSee('البريد الإلكتروني', false)
            ->assertDontSee('الاسم الأول', false);

        $this->post(route('register'), [
            'name' => 'أحمد الغزي',
            'phone' => '0560000000',
            'email' => 'ahmad@example.com',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'أحمد الغزي',
            'phone' => '0597000111',
            'email' => null,
            'role' => 'customer',
        ]);
        $this->assertNull(session(CustomerPhoneVerification::SESSION_KEY));
    }

    public function test_registered_phone_is_not_sent_an_sms(): void
    {
        User::factory()->create(['phone' => '0591112233']);
        Http::fake();

        $this->from(route('register'))
            ->post(route('register.phone'), ['phone' => '0591112233'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('phone');

        Http::assertNothingSent();
    }

    public function test_gateway_error_keeps_the_customer_on_the_phone_step(): void
    {
        Http::fake(['www.tweetsms.ps/*' => Http::response('-115', 200)]);

        $this->from(route('register'))
            ->post(route('register.phone'), ['phone' => '0592223344'])
            ->assertRedirect(route('register'))
            ->assertSessionHas('error');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('إرسال رمز التحقق', false);

        $this->assertNull(session(CustomerPhoneVerification::SESSION_KEY));
    }

    public function test_wrong_codes_are_limited_and_a_new_code_can_be_sent_after_the_wait(): void
    {
        Http::fake(['www.tweetsms.ps/*' => Http::response('42', 200)]);

        $this->post(route('register.phone'), ['phone' => '0593334455']);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('register.verify'), ['code' => '11111'.$attempt])
                ->assertSessionHas('error');
        }

        $this->from(route('register'))
            ->post(route('register.phone.resend'))
            ->assertRedirect(route('register'))
            ->assertSessionHas('error', 'انتظر دقيقة قبل طلب رمز جديد.');

        $this->travel(61)->seconds();

        $this->post(route('register.phone.resend'))
            ->assertRedirect(route('register'))
            ->assertSessionHas('success');

        $this->travelBack();
    }
}
