<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeAppDownloadSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_app_download_section_after_places(): void
    {
        $html = $this->get(route('home'))
            ->assertOk()
            ->assertSee('حمّل', false)
            ->assertSee('تطبيقنا', false)
            ->assertSee('Google Play', false)
            ->assertSee('App Store', false)
            ->assertSee('4.5/5', false)
            ->assertSee('4.8/5', false)
            ->getContent();

        $placesPos = strpos($html, 'id="places-section"');
        $this->assertNotFalse($placesPos);

        $appAfterPlaces = strpos($html, 'aria-label="حمّل تطبيق سفرة غزة"', $placesPos);
        $goldPos = strpos($html, 'برنامج الولاء الأول في غزة');

        $this->assertNotFalse($appAfterPlaces, 'App section should appear after the places section.');
        $this->assertNotFalse($goldPos);
        $this->assertTrue($appAfterPlaces < $goldPos, 'App section should appear before the gold club.');
        $this->assertSame(1, substr_count($html, 'aria-label="حمّل تطبيق سفرة غزة"'), 'App section should appear only on the large-screen homepage.');

        $partnersPos = strpos($html, 'id="partners-section"');
        $this->assertNotFalse($partnersPos);
        $this->assertTrue($appAfterPlaces < $partnersPos, 'Partners strip should appear after the app section.');
        $this->assertTrue($partnersPos < $goldPos, 'Partners strip should appear before the gold club.');
        $this->assertStringContainsString('شركاء يثقون بنا', $html);
    }
}
