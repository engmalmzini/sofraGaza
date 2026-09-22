<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuisineFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_category_links_filter_by_cuisine_not_full_label(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('restaurants?cuisine=shawarma', false)
            ->assertDontSee('q='.rawurlencode('شاورما وصاج'));
    }

    public function test_cuisine_filter_matches_tagged_and_keyword_restaurants(): void
    {
        $tagged = $this->visibleRestaurant('شاورما العمدة', ['cuisine' => 'shawarma']);
        $byMenu = $this->visibleRestaurant('أبو علي', ['cuisine' => null]);
        MenuItem::query()->create([
            'restaurant_id' => $byMenu->id,
            'name' => 'ساندوتش شاورما دجاج',
            'category' => 'ساندويش',
            'price' => 18,
            'is_available' => true,
        ]);
        $unrelated = $this->visibleRestaurant('مطعم السمك الأزرق', ['cuisine' => 'seafood']);

        $this->get(route('restaurants.index', ['cuisine' => 'shawarma', 'area' => '']))
            ->assertOk()
            ->assertSee('شاورما العمدة', false)
            ->assertSee('أبو علي', false)
            ->assertDontSee('مطعم السمك الأزرق', false);

        $this->assertTrue($tagged->isVisible());
        $this->assertTrue($unrelated->isVisible());
    }

    private function visibleRestaurant(string $name, array $extra = []): Restaurant
    {
        return Restaurant::query()->create(array_merge([
            'name' => $name,
            'type' => 'restaurant',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
        ], $extra));
    }
}
