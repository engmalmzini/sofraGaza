<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_sees_redesigned_notifications_page(): void
    {
        $customer = User::factory()->create();
        AppNotification::query()->create([
            'user_id' => $customer->id,
            'title' => 'تم تأكيد طلبك',
            'body' => 'المطعم بدأ بتجهيز الطلب.',
            'link' => route('account.orders'),
        ]);

        $this->actingAs($customer)
            ->get(route('account.notifications'))
            ->assertOk()
            ->assertSee('تابع حالة طلباتك', false)
            ->assertSee('تم تأكيد طلبك', false)
            ->assertSee('تعليم الكل كمقروء', false);
    }

    public function test_restaurant_owner_has_a_dedicated_notifications_page(): void
    {
        $owner = User::factory()->restaurantOwner()->create();
        Restaurant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الإشعارات',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => false,
            'verification_status' => Restaurant::VERIFICATION_PENDING,
        ]);
        AppNotification::query()->create([
            'user_id' => $owner->id,
            'title' => 'تم استلام طلب انضمامك',
            'body' => 'حسابك قيد التحقق.',
        ]);

        $this->actingAs($owner)
            ->get(route('account.notifications'))
            ->assertRedirect(route('partner.notifications.index'));

        $this->actingAs($owner)
            ->get(route('partner.notifications.index'))
            ->assertOk()
            ->assertSee('حالة التحقق', false)
            ->assertSee('تم استلام طلب انضمامك', false);
    }

    public function test_admin_has_a_dedicated_notifications_page(): void
    {
        $admin = User::factory()->admin()->create();
        AppNotification::query()->create([
            'user_id' => $admin->id,
            'title' => 'طلب انضمام مطعم جديد',
            'body' => 'بانتظار المراجعة.',
        ]);

        $this->actingAs($admin)
            ->get(route('account.notifications'))
            ->assertRedirect(route('admin.notifications.index'));

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('طلبات الانضمام', false)
            ->assertSee('طلب انضمام مطعم جديد', false);
    }

    public function test_opening_a_notification_marks_it_read(): void
    {
        $customer = User::factory()->create();
        $note = AppNotification::query()->create([
            'user_id' => $customer->id,
            'title' => 'نقاط جديدة',
            'body' => 'أضيفت نقاط لحسابك.',
            'link' => route('account.points'),
        ]);

        $this->actingAs($customer)
            ->get(route('account.notifications.open', $note))
            ->assertRedirect(route('account.points'));

        $this->assertNotNull($note->fresh()->read_at);
    }
}
