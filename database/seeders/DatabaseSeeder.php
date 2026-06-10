<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $path = database_path('schema/supabase_data.sql');

        if (File::exists($path)) {
            $this->command->info('Executing Supabase raw data payload seed track...');
            
            // 1. Read the raw SQL content
            $sqlContent = File::get($path);

            // 2. Strip out Supabase CLI specific backslash parameters dynamically
            $cleanSql = preg_replace('/^\\\restrict.*$/m', '', $sqlContent);
            $cleanSql = preg_replace('/^\\\unrestrict.*$/m', '', $cleanSql);
            
            // 3. SECURE PASSHASH: Generate your true Laravel-compatible password hash
            $laravelHash = Hash::make('guest123');

            // 4. TEXT REPLACEMENT: Intercept your raw SQL record line item directly
            $oldLine = "INSERT INTO public.users VALUES ('0546918a-6cea-41a9-8970-d2e7a199639d', 'Bugero,', 'Carl james L.', '2026-03-07 08:15:15.075649', 'user', '202310393@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');";
            
            $newLine = "INSERT INTO public.users VALUES ('0546918a-6cea-41a9-8970-d2e7a199639d', 'Bugero,', 'Carl james L.', '2026-03-07 08:15:15.075649', 'user', 'cjbugero@gmail.com', '{$laravelHash}', '2026-06-07 03:53:15.034578+00');";

            if (str_contains($cleanSql, '202310393@gordoncollege.edu.ph')) {
                $cleanSql = str_replace($oldLine, $newLine, $cleanSql);
                $this->command->info('Successfully swapped school email with cjbugero@gmail.com and hashed password inside raw stream!');
            } else {
                $this->command->warn('Could not find specific school email line string, checking alternative structural replace...');
                $cleanSql = str_replace('202310393@gordoncollege.edu.ph', 'cjbugero@gmail.com', $cleanSql);
            }
            
            // 5. Execute the modified SQL dump cleanly
            DB::unprepared($cleanSql);
            $this->command->info('Database successfully synced with schema data records!');

            // 6. Run your bookings seeder safely
            $this->call(BookingSeeder::class);
            
        } else {
            $this->command->error("Could not locate data file target at: {$path}");
        }
    }
}