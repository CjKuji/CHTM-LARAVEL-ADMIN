<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
            
            // Read the SQL contents
            $sqlContent = File::get($path);

            // Strip out Supabase CLI specific backslash parameters dynamically
            $cleanSql = preg_replace('/^\\\restrict.*$/m', '', $sqlContent);
            $cleanSql = preg_replace('/^\\\unrestrict.*$/m', '', $cleanSql);
            
            // Execute the clean script
            DB::unprepared($cleanSql);
            
            $this->command->info('Database successfully synced with schema data records!');
        } else {
            $this->command->error("Could not locate data file target at: {$path}");
        }
    }
}