<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_prompt_redirects_to_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_verified_users_are_sent_to_dashboard_from_prompt(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('dashboard'));
    }
}
