<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesktopOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_worker_is_public_and_opens_the_order_on_click(): void
    {
        $this->get('/sw.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertSee('notificationclick', false)
            ->assertSee('SOFRA_OPEN_URL', false)
            ->assertSee('openWindow', false)
            ->assertSee('printUrl', false);
    }

    public function test_admin_orders_page_offers_desktop_notifications(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('تفعيل إشعارات الجهاز', false)
            ->assertSee('js-desktop-notify-btn', false)
            ->assertSee('navigator.serviceWorker.register', false)
            ->assertSee('showDesktopNotification', false)
            ->assertSee('/sw.js', false)
            ->assertSee('orderPrintUrl', false)
            ->assertSee('orderNotificationLines', false)
            ->assertSee('طباعة الطلب', false);
    }

    public function test_partner_dashboard_offers_desktop_notifications(): void
    {
        $owner = User::factory()->create([
            'role' => 'restaurant_owner',
            'phone' => '0599000111',
        ]);

        Restaurant::create([
            'owner_id' => $owner->id,
            'name' => 'مطعم الإشعارات',
            'type' => 'restaurant',
            'address' => 'غزة',
            'phone' => '0599000111',
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'panel_suspended' => false,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('تفعيل إشعارات الجهاز', false)
            ->assertSee('js-desktop-notify-btn', false)
            ->assertSee('navigator.serviceWorker.register', false)
            ->assertSee('مثل واتساب على كروم', false);
    }
}
