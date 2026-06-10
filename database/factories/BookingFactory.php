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
        // Generate realistic consecutive dates (Check-in tomorrow, stay for 3 days)
        $startAt = now()->addDays(rand(1, 5))->setHour(14)->setMinute(0)->setSecond(0);
        $endAt = (clone $startAt)->addDays(rand(2, 4))->setHour(12)->setMinute(0)->setSecond(0);
        
        $priceAtBooking = 1850.00;
        $extraBeds = rand(0, 2);
        $extraBedCost = $extraBeds * 500.00;
        $totalAmount = $priceAtBooking + $extraBedCost;

        return [
            // Structural Relation Foreign Keys
            'user_id'          => User::inRandomOrder()->first()?->id ?? User::factory(),
            'room_id'          => Room::inRandomOrder()->first()?->id ?? Room::factory(),
            
            // Reservation Windows
            'start_at'         => $startAt,
            'end_at'           => $endAt,
            
            // Headcount & Space Metrics
            'guests'           => rand(1, 4),
            'extra_beds'       => $extraBeds,
            'price_at_booking' => $priceAtBooking,
            'total_amount'     => $totalAmount,
            
            // Communication & Meta Fields
            'message'          => $this->faker->paragraph(1),
            'status'           => 'pending', 
            
            // FIXED: Reverted back to payment_method but keeping proper database check casing
            'payment_method'   => $this->faker->randomElement(['Cash', 'Card', 'GCash']),
            
            // Accessibility & Demographics Toggles
            'has_child'        => true, 
            'child_age_group'  => $this->faker->randomElement(['Infant (0-2)', 'Toddler (3-5)', 'Child (6-12)']),
            'has_pwd'          => $this->faker->boolean(50), 
            'has_senior'       => $this->faker->boolean(50),
            
            // Base workflow markers initialized cleanly as null
            'checked_in_at'    => null,
            'checked_out_at'   => null,
            'approved_by'      => null,
            'rejected_by'      => null,
            'checked_in_by'    => null,
            'checked_out_by'   => null,
        ];
    }
}