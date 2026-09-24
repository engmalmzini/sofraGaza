<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourierRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_courier_can_register_and_admin_can_approve_or_reject(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->post(route('courier.register'), [
            'name' => 'خليل المندوب',
            'phone' => '0593111222',
            'password' => '123456',
            'password_confirmation' => '123456',
            'bike_type' => 'electric',
            'photo' => UploadedFile::fake()->image('me.jpg'),
            'bike_photo' => UploadedFile::fake()->image('bike.jpg'),
            'terms' => '1',
        ])->assertRedirect(route('courier.dashboard'));

        $courier = User::query()->where('phone', '0593111222')->first();
        $this->assertNotNull($courier);
        $this->assertTrue($courier->isCourierPending());
        $this->assertSame('electric', $courier->bike_type);
        $this->assertNotNull($courier->photo_path);
        $this->assertNotNull($courier->bike_photo_path);

        $this->actingAs($courier)
            ->get(route('courier.dashboard'))
            ->assertOk()
            ->assertSee('جاري التحقق', false);

        $this->assertTrue(
            AppNotification::query()->where('title', 'طلب انضمام مندوب توصيل')->exists()
        );

        $this->actingAs($admin)
            ->get(route('admin.delivery.index'))
            ->assertOk()
            ->assertSee('خليل المندوب', false)
            ->assertSee('طلبات انضمام المندوبين', false);

        $this->actingAs($admin)
            ->post(route('admin.delivery.approve', $courier))
            ->assertRedirect();

        $this->assertTrue($courier->fresh()->isCourierApproved());
        $this->assertTrue(
            AppNotification::query()
                ->where('user_id', $courier->id)
                ->where('title', 'تم قبولك كمندوب توصيل')
                ->exists()
        );

        $this->actingAs($courier->fresh())
            ->get(route('courier.dashboard'))
            ->assertOk()
            ->assertSee('لا طلبات مرسلة لك الآن', false);
    }

    public function test_rejected_courier_cannot_receive_orders(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->post(route('courier.register'), [
            'name' => 'سامر',
            'phone' => '0593222333',
            'password' => '123456',
            'password_confirmation' => '123456',
            'bike_type' => 'bicycle',
            'photo' => UploadedFile::fake()->image('me.jpg'),
            'bike_photo' => UploadedFile::fake()->image('bike.jpg'),
            'terms' => '1',
        ]);

        $courier = User::query()->where('phone', '0593222333')->first();

        $this->actingAs($admin)
            ->post(route('admin.delivery.reject', $courier), [
                'rejection_reason' => 'الصور غير واضحة بما يكفي للمراجعة.',
            ])
            ->assertRedirect();

        $this->assertTrue($courier->fresh()->isCourierRejected());
        $this->actingAs($courier->fresh())
            ->get(route('courier.dashboard'))
            ->assertSee('الصور غير واضحة بما يكفي للمراجعة.', false);
    }
}
