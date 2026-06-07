<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // IMPORTANT: Supabase Auth owns user creation (auth.users -> public.users FK).
        // The profile rows in public.users must be created/synced via Supabase Auth or SQL function RPC.
        // Therefore, we skip user seeders here and only seed non-auth data.
        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}