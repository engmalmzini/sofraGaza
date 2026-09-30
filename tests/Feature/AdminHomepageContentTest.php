<?php

namespace Tests\Feature;

use App\Models\HomePartner;
use App\Models\User;
use App\Support\HomeContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHomepageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_homepage_content_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.homepage.index'))
            ->assertOk()
            ->assertSee('محتوى الصفحة الرئيسية', false)
            ->assertSee('شعارات الشراكات', false)
            ->assertSee('قسم الهيرو', false);
    }

    public function test_guest_cannot_open_homepage_content_page(): void
    {
        $this->get(route('admin.homepage.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_update_homepage_copy_and_see_it_on_home(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.homepage.update'), [
                'content' => [
                    'hero_title_line1' => 'عنوان تجريبي للهيرو',
                    'partners_title' => 'شركاؤنا الذهبيون',
                ],
            ])
            ->assertRedirect();

        $this->assertSame('عنوان تجريبي للهيرو', HomeContent::get('hero_title_line1'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('عنوان تجريبي للهيرو', false)
            ->assertSee('شركاؤنا الذهبيون', false);
    }

    public function test_admin_can_add_partner_logo_shown_on_home(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.homepage.partners.store'), [
                'name' => 'مطعم التجربة',
                'image' => UploadedFile::fake()->image('partner.png', 200, 80),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('home_partners', [
            'name' => 'مطعم التجربة',
            'is_active' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('مطعم التجربة', false)
            ->assertSee('id="partners-section"', false);
    }

    public function test_admin_can_delete_partner(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = HomePartner::query()->create([
            'name' => 'شريك للحذف',
            'image_path' => 'partners/old.png',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.homepage.partners.destroy', $partner))
            ->assertRedirect();

        $this->assertDatabaseMissing('home_partners', ['id' => $partner->id]);
    }
}
