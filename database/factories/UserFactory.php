<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $email = fake()->unique()->safeEmail();

        return [
            'fname' => fake()->firstName(),
            'lname' => fake()->lastName(),
            'email' => $email,
            'email_hash' => User::hashEmail($email),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::User->value, // Synchronized with Enum class architectures
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        // FIXED: Stripped the unused $attributes argument out of the state definition closure context
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}