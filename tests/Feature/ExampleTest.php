<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_login_screen_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guests_are_redirected_from_portal_to_login(): void
    {
        $response = $this->get('/portal');

        $response->assertRedirect('/');
    }

    public function test_authenticated_users_can_access_portal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/portal');

        $response->assertStatus(200);
    }
}
