<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_download_sql_backup(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'مدير النسخة']);
        $customer = User::factory()->create(['name' => 'زبون للنسخة']);
        Restaurant::query()->create([
            'name' => 'مطعم النسخة',
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.settings.backup'));

        $response->assertOk();
        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('.sql', $disposition);
        $this->assertStringContainsString('attachment', $disposition);

        $sql = $response->streamedContent();
        $this->assertStringContainsString('DROP TABLE IF EXISTS `users`', $sql);
        $this->assertStringContainsString('INSERT INTO `users`', $sql);
        $this->assertStringContainsString('مدير النسخة', $sql);
        $this->assertStringContainsString('زبون للنسخة', $sql);
        $this->assertStringContainsString('مطعم النسخة', $sql);
        $this->assertStringContainsString('SET FOREIGN_KEY_CHECKS=0', $sql);
    }

    public function test_staff_admin_cannot_download_backup(): void
    {
        $staff = User::factory()->staffAdmin(['settings'])->create();

        $this->actingAs($staff)
            ->post(route('admin.settings.backup'))
            ->assertForbidden();
    }

    public function test_dashboard_shows_backup_button_for_super_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.settings.backup'), false);
    }
}
