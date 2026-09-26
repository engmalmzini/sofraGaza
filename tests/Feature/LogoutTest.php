<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_logout_post_returns_home_as_guest(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_get_logout_does_not_show_page_expired(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('logout'))
            ->assertRedirect(route('home'))
            ->assertDontSee('PAGE EXPIRED', false);

        $this->assertGuest();
    }

    public function test_guest_logout_goes_home_instead_of_419(): void
    {
        $this->get('/logout')
            ->assertRedirect(route('home'))
            ->assertDontSee('PAGE EXPIRED', false);
    }
}
