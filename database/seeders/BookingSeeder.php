<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Executing raw custom booking seed bindings...');

        // Query the ID directly from the public schema path
        $userRow = DB::select("SELECT id FROM public.users WHERE email = 'cjbugero@gmail.com' LIMIT 1");
        $roomRow = DB::select("SELECT id FROM public.rooms LIMIT 1"); 

        if (empty($userRow)) {
            $this->command->error('Could not map cjbugero account id root sequence.');
            return;
        }

        $userId = $userRow[0]->id;
        $roomId = !empty($roomRow) ? $roomRow[0]->id : '1'; 

        $startAt1 = Carbon::now()->addDays(2)->toDateTimeString();
        $endAt1 = Carbon::now()->addDays(5)->toDateTimeString();

        // FIXED: Added extra_beds and price_at_booking to meet the table schema requirements
        DB::statement("
            INSERT INTO public.bookings (
                user_id, 
                room_id, 
                status, 
                price_at_booking, 
                extra_beds, 
                total_amount, 
                guests, 
                message, 
                start_at, 
                end_at, 
                payment_method, 
                has_child, 
                child_age_group, 
                has_pwd, 
                has_senior, 
                created_at
            )
            VALUES (
                '{$userId}',
                '{$roomId}',
                'pending',
                4500.00,
                0,
                4500.00,
                2,
                'Prefer a room with a nice view if possible!',
                '{$startAt1}',
                '{$endAt1}',
                'Cash',
                true,
                'Toddler (3-5)',
                false,
                false,
                NOW()
            )
        ");

        $this->command->info('Raw custom bookings cleanly attached!');
    }
}