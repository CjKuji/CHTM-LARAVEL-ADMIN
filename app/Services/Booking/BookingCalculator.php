<?php

namespace App\Services\Booking;

use Carbon\CarbonInterface;

final class BookingCalculator
{
    private const EXTRA_BED_PRICE = [
        0 => 0,
        1 => 700,
        2 => 1400,
    ];

    public static function extraBedFee(int $extraBeds): int
    {
        // Safe array extraction fallback to prevent index crashes
        return self::EXTRA_BED_PRICE[$extraBeds] ?? 0;
    }

    public static function nights(?CarbonInterface $start, ?CarbonInterface $end): int
    {
        if (! $start || ! $end) {
            return 1;
        }

        $diff = (int) $start->diffInDays($end, false);

        return max(1, $diff);
    }

    public static function computeTotals(
        float $priceAtBooking,
        int $guests,
        int $extraBeds,
        bool $hasPwd,
        bool $hasSenior,
        ?CarbonInterface $start,
        ?CarbonInterface $end
    ): array {
        $guests = max(1, $guests);
        $nights = self::nights($start, $end);
        $roomTotal = $priceAtBooking * $nights;
        $extraBedTotal = self::extraBedFee($extraBeds) * $nights;

        $eligibleCount = (int) $hasPwd + (int) $hasSenior;
        $eligiblePeople = min($eligibleCount, $guests);
        $discountPerPerson = ($priceAtBooking / $guests) * 0.2 * $nights;
        $discountAmount = round($discountPerPerson * $eligiblePeople, 2);

        $totalAmount = round(max(0, $roomTotal + $extraBedTotal - $discountAmount), 2);

        return [
            'nights' => $nights,
            'room_total' => $roomTotal,
            'extra_bed_total' => $extraBedTotal,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];
    }
}