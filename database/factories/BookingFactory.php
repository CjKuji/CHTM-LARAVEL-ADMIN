<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startAt = now()->addDays(rand(1, 5))->setHour(14)->setMinute(0)->setSecond(0);
        $endAt = (clone $startAt)->addDays(rand(2, 4))->setHour(12)->setMinute(0)->setSecond(0);
        
        $priceAtBooking = 1850.00;
        $extraBeds = rand(0, 2);
        $extraBedCost = $extraBeds * 500.00;
        $totalAmount = $priceAtBooking + $extraBedCost;

        // Force look up your specific personal account profile setup first
        $targetUser = User::where('email', 'cjbugero@gmail.com')->first();

        return [
            'user_id'          => $targetUser ? $targetUser->id : User::factory(),
            'room_id'          => Room::inRandomOrder()->first()?->id ?? Room::factory(),
            'start_at'         => $startAt,
            'end_at'           => $endAt,
            'guests'           => rand(1, 4),
            'extra_beds'       => $extraBeds,
            'price_at_booking' => $priceAtBooking,
            'total_amount'     => $totalAmount,
            'message'          => $this->faker->paragraph(1),
            'status'           => 'pending', 
            'payment_method'   => $this->faker->randomElement(['Cash', 'Card', 'GCash']),
            'has_child'        => true, 
            'child_age_group'  => $this->faker->randomElement(['Infant (0-2)', 'Toddler (3-5)', 'Child (6-12)']),
            'has_pwd'          => $this->faker->boolean(50), 
            'has_senior'       => $this->faker->boolean(50),
            'checked_in_at'    => null,
            'checked_out_at'   => null,
            'approved_by'      => null,
            'rejected_by'      => null,
            'checked_in_by'    => null,
            'checked_out_by'   => null,
        ];
    }
}