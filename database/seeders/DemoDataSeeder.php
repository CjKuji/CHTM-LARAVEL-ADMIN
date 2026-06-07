<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\HousekeepingTemplate;
use App\Models\HousekeepingTemplateItem;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\Booking\BookingCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email_hash', User::hashEmail('admin@chtm.local'))->first();

        // FIXED: Replaced raw string role declarations with corresponding UserRole Enum definitions
        User::query()->updateOrCreate(
            ['email_hash' => User::hashEmail('reservation@chtm.local')],
            ['fname' => 'Ria', 'lname' => 'Reservation', 'email' => 'reservation@chtm.local', 'password' => Hash::make('password'), 'role' => UserRole::Reservation->value]
        );
        User::query()->updateOrCreate(
            ['email_hash' => User::hashEmail('frontoffice@chtm.local')],
            ['fname' => 'Faye', 'lname' => 'FrontOffice', 'email' => 'frontoffice@chtm.local', 'password' => Hash::make('password'), 'role' => UserRole::FrontOffice->value]
        );
        User::query()->updateOrCreate(
            ['email_hash' => User::hashEmail('housekeeper@chtm.local')],
            ['fname' => 'Helen', 'lname' => 'Housekeeper', 'email' => 'housekeeper@chtm.local', 'password' => Hash::make('password'), 'role' => UserRole::Housekeeper->value]
        );

        $guest = User::query()->updateOrCreate(
            ['email_hash' => User::hashEmail('guest@chtm.local')],
            [
                'fname' => 'Maria',
                'lname' => 'Santos',
                'email' => 'guest@chtm.local',
                'password' => Hash::make('password'),
                'role' => UserRole::User->value,
            ]
        );

        $guest2 = User::query()->updateOrCreate(
            ['email_hash' => User::hashEmail('juan.delacruz@chtm.local')],
            [
                'fname' => 'Juan',
                'lname' => 'Del Cruz',
                'email' => 'juan.delacruz@chtm.local',
                'password' => Hash::make('password'),
                'role' => UserRole::User->value,
            ]
        );

        $wifi = Amenity::query()->firstOrCreate(['name' => 'Wi-Fi']);
        $tv = Amenity::query()->firstOrCreate(['name' => 'Smart TV']);

        $standard = RoomType::query()->firstOrCreate(
            ['name' => 'Standard Room'],
            ['description' => 'Comfortable standard accommodation', 'capacity' => 2, 'base_price' => 2500, 'min_guests' => 1]
        );
        $deluxe = RoomType::query()->firstOrCreate(
            ['name' => 'Deluxe Suite'],
            ['description' => 'Spacious suite with premium amenities', 'capacity' => 4, 'base_price' => 4500, 'min_guests' => 2]
        );

        $standard->amenities()->syncWithoutDetaching([$wifi->id, $tv->id]);
        $deluxe->amenities()->syncWithoutDetaching([$wifi->id, $tv->id]);

        $roomsData = [
            ['room_type_id' => $standard->id, 'room_number' => '101', 'floor' => 1, 'status' => 'available'],
            ['room_type_id' => $standard->id, 'room_number' => '102', 'floor' => 1, 'status' => 'occupied'],
            ['room_type_id' => $deluxe->id, 'room_number' => '201', 'floor' => 2, 'status' => 'needs_cleaning'],
            ['room_type_id' => $deluxe->id, 'room_number' => '202', 'floor' => 2, 'status' => 'available'],
        ];

        // FIXED: Swapped map execution to structural all() array conversion to resolve offset mapping bugs
        $rooms = collect($roomsData)->map(function ($data) {
            return Room::query()->firstOrCreate(['room_number' => $data['room_number']], $data);
        })->all();

        $template = HousekeepingTemplate::query()->firstOrCreate(
            ['room_type_id' => $standard->id],
            ['name' => 'Standard Checkout Checklist']
        );

        foreach (['Replace towels', 'Change linens', 'Sanitize bathroom', 'Vacuum floor'] as $item) {
            HousekeepingTemplateItem::query()->firstOrCreate(
                ['template_id' => $template->id, 'item_name' => $item],
                ['default_quantity' => 1]
            );
        }

        HousekeepingTemplate::query()->firstOrCreate(
            ['room_type_id' => $deluxe->id],
            ['name' => 'Deluxe Checkout Checklist']
        );

        $this->seedBooking($guest, $rooms[0]->id, 'pending', $standard->base_price, now()->addDays(3), now()->addDays(5));
        $this->seedBooking($guest2, $rooms[1]->id, 'checked_in', $standard->base_price, now()->subDay(), now()->addDays(2), $admin?->id);
        $this->seedBooking($guest, $rooms[3]->id, 'approved', $deluxe->base_price, now()->addDays(1), now()->addDays(4), $admin?->id);
    }

    private function seedBooking(
        User $user,
        int $roomId,
        string $status,
        float $price,
        $start,
        $end,
        ?int $approvedBy = null
    ): void {
        if (Booking::query()->where('user_id', $user->id)->where('room_id', $roomId)->where('status', $status)->exists()) {
            return;
        }

        $totals = BookingCalculator::computeTotals($price, 2, 0, false, false, $start, $end);

        Booking::query()->create([
            'user_id' => $user->id,
            'room_id' => $roomId,
            'start_at' => $start,
            'end_at' => $end,
            'guests' => 2,
            'extra_beds' => 0,
            'price_at_booking' => $price,
            'total_amount' => $totals['total_amount'],
            'status' => $status,
            'payment_method' => 'Gcash',
            'approved_by' => $approvedBy,
            'checked_in_at' => $status === 'checked_in' ? now()->subHours(6) : null,
            'checked_in_by' => $status === 'checked_in' ? $approvedBy : null,
        ]);
    }
}