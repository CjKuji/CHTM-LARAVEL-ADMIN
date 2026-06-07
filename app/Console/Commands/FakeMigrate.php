<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FakeMigrate extends Command
{
    protected $signature = 'migrate:fake';
    protected $description = 'Marks all local migration files as completed on Supabase without running them.';

    public function handle()
    {
        // 1. Get all the migration files in your project directory
        $files = File::files(database_path('migrations'));
        
        $this->info('Syncing migrations tracking table with Supabase...');
        $batch = 1;

        foreach ($files as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);

            // 2. Check if this specific migration is already logged
            $exists = DB::table('migrations')->where('migration', $migrationName)->exists();

            if (!$exists) {
                // 3. Inject the file name straight into the log
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch
                ]);
                $this->line("<info>Marked as Ran:</info> {$migrationName}");
            } else {
                $this->line("<comment>Already Tracked:</comment> {$migrationName}");
            }
        }

        $this->info('Database status successfully synchronized!');
    }
}