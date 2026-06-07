<?php

namespace App\Services\Dashboard;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class DashboardService
{
    public function forUser(User $user): array
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        // FIXED: Combined 3 queries into 1 aggregated database lookup to kill connection overhead
        $roomStats = Room::query()
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'occupied' THEN 1 END) as occupied,
                COUNT(CASE WHEN status = 'needs_cleaning' THEN 1 END) as needs_cleaning
            ")
            ->first();

        $total = (int) ($roomStats->total ?? 0);
        $occupied = (int) ($roomStats->occupied ?? 0);
        $needsCleaning = (int) ($roomStats->needs_cleaning ?? 0);
        $available = max(0, $total - $occupied - $needsCleaning);

        $occupiedBookings = Booking::query()
            ->with(['user', 'room.roomType'])
            ->where('status', 'checked_in')
            ->get();

        $upcomingBookings = Booking::query()
            ->with(['user', 'room.roomType'])
            ->where('status', 'approved')
            ->where('start_at', '>', $now)
            ->orderBy('start_at')
            ->limit(8)
            ->get();

        $pendingCount = Booking::where('status', 'pending')->count();
        
        $checkoutsToday = Booking::query()
            ->where('status', 'checked_in')
            ->whereBetween('end_at', [$todayStart, $todayEnd])
            ->count();

        $recentActivity = AuditLog::query()
            ->with('changer')
            ->latest()
            ->limit(6)
            ->get();

        return [
            'user' => $user,
            'roomStatus' => [
                'total' => $total,
                'occupied' => $occupied,
                'available' => $available,
                'needsCleaning' => $needsCleaning,
                'occupancyPct' => $total > 0 ? (int) round(($occupied / $total) * 100) : 0,
            ],
            'occupiedRooms' => $this->mapOccupied($occupiedBookings),
            'upcomingBookings' => $this->mapUpcoming($upcomingBookings),
            'recentActivity' => $recentActivity,
            'pendingCount' => $pendingCount,
            'checkoutsToday' => $checkoutsToday,
        ];
    }

    private function mapOccupied(Collection $bookings): array
    {
        return $bookings->map(function (Booking $booking) {
            $checkedIn = $booking->checked_in_at;

            return [
                'id' => $booking->id,
                'guest_name' => $booking->user?->fullName() ?? 'Unknown',
                'room_number' => $booking->room?->room_number ?? '—',
                'room_type' => $booking->room?->roomType?->name ?? 'Unknown',
                'start_at' => $booking->start_at,
                'end_at' => $booking->end_at,
                'checked_in_at' => $checkedIn,
                'nights_so_far' => $checkedIn
                    ? max(1, (int) $checkedIn->diffInDays(now()))
                    : 1,
            ];
        })->all();
    }

    private function mapUpcoming(Collection $bookings): array
    {
        return $bookings->map(function (Booking $booking) {
            $nights = $booking->start_at && $booking->end_at
                ? max(1, (int) $booking->start_at->diffInDays($booking->end_at))
                : 1;

            return [
                'id' => $booking->id,
                'guest_name' => $booking->user?->fullName() ?? 'Unknown',
                'room_number' => $booking->room?->room_number ?? '—',
                'room_type' => $booking->room?->roomType?->name ?? 'Unknown',
                'start_at' => $booking->start_at,
                'end_at' => $booking->end_at,
                'nights' => $nights,
            ];
        })->all();
    }
}