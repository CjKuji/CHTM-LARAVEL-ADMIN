<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Grab some references so the foreign keys don't break
        $guest = User::first() ?? User::factory()->create();
        $room = Room::first() ?? Room::factory()->create();

        $bookings = [
            [
                'user_id' => $guest->id,
                'room_id' => $room->id,
                'status' => 'pending',
                'total_amount' => 4500.00,
                'guests' => 2,
                'message' => 'Prefer a room with a nice view if possible!',
                'start_at' => Carbon::now()->addDays(2),
                'end_at' => Carbon::now()->addDays(5),
            ],
            [
                'user_id' => $guest->id,
                'room_id' => $room->id,
                'status' => 'approved',
                'total_amount' => 12000.00,
                'guests' => 3,
                'message' => 'Late check-in expected.',
                'start_at' => Carbon::now()->subDays(1),
                'end_at' => Carbon::now()->addDays(3),
            ],
        ];

        foreach ($bookings as $bookingData) {
            Booking::create($bookingData);
        }
    }
}