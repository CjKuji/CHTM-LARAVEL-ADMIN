<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email_hash' => User::hashEmail('admin@chtm.local')],
            [
                'fname' => 'System',
                'lname' => 'Administrator',
                'email' => 'admin@chtm.local',
                // Supabase schema does not include a password column.
                // Password auth is handled via Supabase Auth; this Seeder only syncs profile fields.
                'role' => UserRole::SuperAdmin->value,
            ]
        );
    }
}