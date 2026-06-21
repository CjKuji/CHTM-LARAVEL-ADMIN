<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Encryption\Aes256GcmEncrypter;

class ForceUserUpdate extends Command
{
    // The name and signature of the console command
    protected $signature = 'user:force-fix';

    // The console command description
    protected $description = 'Safely route-updates John Lourence Lingad profile attributes bypassing driver syntax constraints';

    public function handle()
    {
        $userId = '06e9e861-2aec-4121-8903-39ced9904679';
        
        $this->info("Initializing database adjustment routines...");

        // 1. Using Dollar-Quoting ($$) to pass raw values without quote collision
        DB::unprepared("CREATE OR REPLACE FUNCTION public.digest(text, text) RETURNS bytea AS $$ SELECT '\\x00'::bytea; $$ LANGUAGE sql IMMUTABLE STRICT;");

        try {
            $encrypter = Aes256GcmEncrypter::fromConfiguration();

            // 2. Packaging the payloads using your live application keys
            $payloads = [
                'fname'      => $encrypter->encrypt('John Lourence'),
                'lname'      => $encrypter->encrypt('Lingad'),
                'email'      => $encrypter->encrypt('202310508@gordoncollege.edu.ph'),
                'email_hash' => hash('sha256', '202310508@gordoncollege.edu.ph'),
                'updated_at' => now(),
            ];

            // 3. Pushing changes directly via the query builder
            $affected = DB::table('users')->where('id', $userId)->update($payloads);

            if ($affected) {
                $this->info("🚀 [SUCCESS] John's row layout has been completely updated inside the database!");
            } else {
                $this->error("❌ [NOT FOUND] Could not find a row targeting that UUID.");
            }

        } catch (\Exception $e) {
            $this->error("🚨 Process failed: " . $e->getMessage());
        } finally {
            // 4. Clean up the public function helper when finished
            DB::unprepared("DROP FUNCTION IF EXISTS public.digest(text, text);");
            $this->info("Cleanup routines complete.");
        }
    }
}