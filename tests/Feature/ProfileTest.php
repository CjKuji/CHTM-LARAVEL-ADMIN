<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('profile'))
            ->patch('/profile', [
                'fname' => 'Jane',
                'lname' => 'Doe',
                'email' => 'jane@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile'));

        $user->refresh();

        $this->assertSame('Jane', $user->fname);
        $this->assertSame('Doe', $user->lname);
        $this->assertSame('jane@example.com', $user->email);
    }

    public function test_profile_rejects_duplicate_email(): void
    {
        $existing = User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('profile'))
            ->patch('/profile', [
                'fname' => $user->fname,
                'lname' => $user->lname,
                'email' => 'taken@example.com',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertNotSame('taken@example.com', $user->fresh()->email);
    }
}
