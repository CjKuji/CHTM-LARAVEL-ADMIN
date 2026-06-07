<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_redirects_to_login(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertRedirect(route('login'));
    }

    public function test_reset_password_request_is_not_exposed_via_blade_ui(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertRedirect();
    }
}
