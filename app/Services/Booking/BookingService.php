<?php

namespace App\Services\Booking;

use App\Models\Booking;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Authenticatable;

class BookingService
{
    public function getByStatus(string $status, int $perPage = 25): LengthAwarePaginator
    {
        return Booking::with(['user','room.roomType'])
            ->where('status', $status)
            ->orderBy('start_at')
            ->paginate($perPage);
    }

    public function getById(int $id): ?Booking
    {
        return Booking::with(['user','room.roomType'])->find($id);
    }

    public function getAll(): Collection
    {
        return Booking::with(['user', 'room.roomType'])
            ->orderBy('start_at')
            ->get();
    }

    public function getCalendarAvailability(): array
    {
        return Booking::whereIn('status', ['pending','approved','checked_in'])
            ->get(['id','room_id','start_at','end_at','status'])
            ->map(function($b){
                return [
                    'id' => $b->id,
                    'room_id' => $b->room_id,
                    'start' => $b->start_at?->toIso8601String(),
                    'end' => $b->end_at?->toIso8601String(),
                    'status' => $b->status,
                ];
            })->toArray();
    }

    public function updateStatus(int $id, string $status, Authenticatable $user): void
    {
        $booking = Booking::findOrFail($id);
        $booking->status = $status;
        if ($status === 'approved') {
            $booking->approved_by = $user->getAuthIdentifier();
        } elseif ($status === 'checked_in') {
            $booking->checked_in_by = $user->getAuthIdentifier();
            $booking->checked_in_at = now();
        } elseif ($status === 'checked_out') {
            $booking->checked_out_by = $user->getAuthIdentifier();
            $booking->checked_out_at = now();
        }
        $booking->save();
    }
}
