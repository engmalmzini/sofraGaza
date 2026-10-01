<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeamAndAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_staff_limited_to_restaurants(): void
    {
        $super = User::factory()->admin()->create(['name' => 'مدير سفرة']);

        $response = $this->actingAs($super)->post(route('admin.team.store'), [
            'name' => 'محمد المزيني',
            'phone' => '0597111888',
            'password' => 'secret12',
            'permissions' => ['restaurants'],
        ]);

        $response->assertRedirect(route('admin.team.index'));

        $staff = User::query()->where('phone', '0597111888')->first();
        $this->assertNotNull($staff);
        $this->assertTrue($staff->isAdmin());
        $this->assertFalse($staff->isSuperAdmin());
        $this->assertSame(['restaurants'], $staff->adminPermissionKeys());
        $this->assertTrue($staff->canAccessAdmin('restaurants'));
        $this->assertFalse($staff->canAccessAdmin('finance'));
        $this->assertFalse($staff->canAccessAdmin('team'));
    }

    public function test_staff_admin_is_blocked_from_unassigned_modules(): void
    {
        $staff = User::factory()->staffAdmin(['restaurants'])->create(['name' => 'محمد المزيني']);

        $this->actingAs($staff)->get(route('admin.restaurants.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.finance.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.team.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.audit.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('المطاعم والكوفيهات')
            ->assertDontSee('فريق الإدارة')
            ->assertDontSee('مالية المطاعم')
            ->assertDontSee('سجل التعديلات');
    }

    public function test_admin_action_is_logged_with_actor_name_and_time(): void
    {
        $super = User::factory()->admin()->create(['name' => 'محمد المزيني']);
        $customer = User::factory()->create(['name' => 'أحمد الغزي']);

        $this->actingAs($super)
            ->from(route('admin.users.show', $customer))
            ->post(route('admin.users.points', $customer), [
                'points' => 40,
                'reason' => 'تعويض خطأ',
            ])
            ->assertRedirect();

        $log = AdminAuditLog::query()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame('محمد المزيني', $log->actor_name);
        $this->assertSame('admin.users.points', $log->action);
        $this->assertStringContainsString('محمد المزيني', $log->description);
        $this->assertStringContainsString('تعديل نقاط', $log->description);
        $this->assertStringContainsString('أحمد الغزي', $log->description);
        $this->assertNotNull($log->created_at);

        $this->actingAs($super)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('محمد المزيني')
            ->assertSee('تعديل نقاط زبون')
            ->assertSee($log->created_at->format('Y/m/d H:i'));
    }

    public function test_last_super_admin_cannot_be_deactivated(): void
    {
        $super = User::factory()->admin()->create(['name' => 'مدير سفرة']);

        $this->actingAs($super)
            ->from(route('admin.team.index'))
            ->delete(route('admin.team.destroy', $super))
            ->assertRedirect(route('admin.team.index'))
            ->assertSessionHas('error');

        $this->assertTrue($super->fresh()->isActiveAdmin());
        $this->assertTrue($super->fresh()->isSuperAdmin());
        $this->assertSame(0, AdminAuditLog::query()->where('action', 'admin.team.destroy')->count());
    }

    public function test_deactivated_admin_cannot_open_the_panel(): void
    {
        $staff = User::factory()->staffAdmin(['restaurants'])->create([
            'admin_active' => false,
        ]);

        $this->actingAs($staff)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.restaurants.index'))->assertForbidden();
    }
}
