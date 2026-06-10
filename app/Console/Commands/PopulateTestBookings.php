<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\User;
use App\Models\Room;

class PopulateTestBookings extends Command
{
    protected $signature = 'db:populate-bookings {count=10 : The number of bookings to generate}';
    protected $description = 'Injects 100% complete, pending booking requests into the live system for testing';

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        if (User::count() === 0 || Room::count() === 0) {
            $this->error('Testing requirements missing: You need at least 1 user and 1 room in your Supabase database first.');
            return Command::FAILURE;
        }

        $this->info("Generating {$count} complete, pending test bookings...");

        // Fire the factory
        Booking::factory()->count($count)->create();

        $this->info("Successfully populated {$count} complete pending records! Refresh your browser to view them.");
        return Command::SUCCESS;
    }
}