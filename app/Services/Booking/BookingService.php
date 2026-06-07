<?php

namespace App\Services\Booking;

use App\Models\ArchivedBooking;
use App\Models\Booking;
use App\Models\HousekeepingTask;
use App\Models\HousekeepingTaskItem;
use App\Models\HousekeepingTemplate;
use App\Models\Room;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BookingService
{
    public function __construct(
        private readonly BookingNotificationService $notifications,
    ) {}

    public function getCalendarAvailability(): array
    {
        return Booking::query()
            ->whereIn('status', ['approved', 'checked_in', 'in_progress'])
            ->whereNotNull('room_id')
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->get(['id', 'room_id', 'start_at', 'end_at', 'status'])
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'room_id' => $booking->room_id,
                'start_at' => $booking->start_at?->toIso8601String(),
                'end_at' => $booking->end_at?->toIso8601String(),
                'status' => $booking->status,
            ])
            ->values()
            ->all();
    }

    public function getAll(): Collection
    {
        return $this->baseQuery()->orderByDesc('created_at')->get();
    }

    public function getById(int $id): Booking
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function getByStatus(string $status): Collection
    {
        return $this->baseQuery()->where('status', $status)->orderByDesc('created_at')->get();
    }

    public function updateStatus(int $id, string $status, User $actor): Booking
    {
        return DB::transaction(function () use ($id, $status, $actor) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($id);
            $oldStatus = $booking->status;

            $booking->status = $status;

            if ($status === 'approved') {
                $booking->approved_by = $actor->id;
            }
            if ($status === 'checked_in') {
                $booking->checked_in_by = $actor->id;
                $booking->checked_in_at = now();
            }
            if ($status === 'checked_out') {
                $booking->checked_out_by = $actor->id;
                $booking->checked_out_at = now();
            }

            $booking->save();

            if ($booking->room_id) {
                $room = Room::query()->find($booking->room_id);
                if ($room) {
                    if ($status === 'checked_in') {
                        $room->update(['status' => 'occupied']);
                    } elseif ($status === 'checked_out') {
                        $room->update(['status' => 'needs_cleaning', 'make_up_room' => false, 'checkout_requested' => false]);
                    } elseif (in_array($status, ['cancelled', 'rejected'], true) && $oldStatus === 'checked_in') {
                        $room->update(['status' => 'available']);
                    }
                }
            }

            AuditLogger::log('bookings', $booking->id, strtoupper($status), ['status' => $oldStatus], ['status' => $status], $actor);

            $booking = $this->getById($booking->id);

            if (in_array($status, ['approved', 'checked_in', 'checked_out', 'rejected', 'cancelled'], true)) {
                $this->notifications->sendStatusUpdate($booking);
            }

            if ($status === 'checked_out') {
                $this->generateHousekeepingFromTemplate($booking);
                $this->archiveBooking($booking);
            }

            return $booking;
        });
    }

    public function updateBookingDetails(int $id, array $payload, User $actor): Booking
    {
        return DB::transaction(function () use ($id, $payload, $actor) {
            $booking = Booking::query()->with('user', 'room.roomType')->findOrFail($id);

            if ($booking->user && isset($payload['guest_fname'], $payload['guest_lname'], $payload['guest_email'])) {
                // FIXED: Automatically generate structural hash indices on profile update actions
                $booking->user->update([
                    'fname' => $payload['guest_fname'],
                    'lname' => $payload['guest_lname'],
                    'email' => $payload['guest_email'],
                    'email_hash' => User::hashEmail($payload['guest_email']),
                ]);
            }

            $start = isset($payload['start_at']) ? \Carbon\Carbon::parse($payload['start_at']) : $booking->start_at;
            $end = isset($payload['end_at']) ? \Carbon\Carbon::parse($payload['end_at']) : $booking->end_at;

            $totals = BookingCalculator::computeTotals(
                (float) $booking->price_at_booking,
                (int) ($payload['guests'] ?? $booking->guests),
                (int) ($payload['extra_beds'] ?? $booking->extra_beds),
                (bool) ($payload['has_pwd'] ?? $booking->has_pwd),
                (bool) ($payload['has_senior'] ?? $booking->has_senior),
                $start,
                $end
            );

            $booking->update([
                'start_at' => $start,
                'end_at' => $end,
                'room_id' => $payload['room_id'] ?? $booking->room_id,
                'guests' => $payload['guests'] ?? $booking->guests,
                'extra_beds' => $payload['extra_beds'] ?? $booking->extra_beds,
                'has_child' => $payload['has_child'] ?? $booking->has_child,
                'child_age_group' => $payload['child_age_group'] ?? $booking->child_age_group,
                'has_pwd' => $payload['has_pwd'] ?? $booking->has_pwd,
                'has_senior' => $payload['has_senior'] ?? $booking->has_senior,
                'total_amount' => $totals['total_amount'],
            ]);

            AuditLogger::log('bookings', $booking->id, 'UPDATE', null, $payload, $actor);

            return $this->getById($booking->id);
        });
    }

    public function archiveBooking(Booking $booking): void
    {
        $booking->loadMissing(['user', 'room.roomType']);

        $totals = BookingCalculator::computeTotals(
            (float) $booking->price_at_booking,
            (int) $booking->guests,
            (int) $booking->extra_beds,
            (bool) $booking->has_pwd,
            (bool) $booking->has_senior,
            $booking->start_at,
            $booking->end_at
        );

        // FIXED: Maps guest_email to the model and computes the matching guest_email_hash index parameter
        ArchivedBooking::query()->create([
            'original_booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'room_id' => $booking->room_id,
            'room_number' => $booking->room?->room_number,
            'room_type_name' => $booking->room?->roomType?->name,
            'room_type_id' => $booking->room?->room_type_id,
            'room_capacity' => $booking->room?->roomType?->capacity,
            'room_base_price' => $booking->room?->roomType?->base_price,
            'room_floor' => $booking->room?->floor,
            'start_at' => $booking->start_at,
            'end_at' => $booking->end_at,
            'checked_in_at' => $booking->checked_in_at,
            'checked_out_at' => $booking->checked_out_at,
            'guests' => $booking->guests,
            'status' => $booking->status,
            'message' => $booking->message,
            'payment_method' => $booking->payment_method,
            'total_amount' => $totals['total_amount'],
            'extra_beds' => $booking->extra_beds,
            'has_child' => $booking->has_child,
            'has_pwd' => $booking->has_pwd,
            'has_senior' => $booking->has_senior,
            'child_age_group' => $booking->child_age_group,
            'guest_fname' => $booking->user?->fname,
            'guest_lname' => $booking->user?->lname,
            'guest_email' => $booking->user?->email,
            'guest_email_hash' => $booking->user?->email ? User::hashEmail($booking->user->email) : null,
            'approved_by' => $booking->approved_by,
            'checked_in_by' => $booking->checked_in_by,
            'checked_out_by' => $booking->checked_out_by,
        ]);
    }

    public function generateHousekeepingFromTemplate(Booking $booking): void
    {
        $roomTypeId = $booking->room?->room_type_id;
        if (! $booking->room_id || ! $roomTypeId) {
            return;
        }

        $template = HousekeepingTemplate::query()->where('room_type_id', $roomTypeId)->first();
        if (! $template) {
            return;
        }

        $task = HousekeepingTask::query()->create([
            'room_id' => $booking->room_id,
            'template_id' => $template->id,
            'status' => 'pending',
        ]);

        foreach ($template->items as $item) {
            HousekeepingTaskItem::query()->create([
                'task_id' => $task->id,
                'item_name' => $item->item_name,
                'quantity' => $item->default_quantity,
                'is_done' => false,
            ]);
        }
    }

    private function baseQuery(): Builder
    {
        return Booking::query()->with([
            'user',
            'room.roomType.amenities',
            'approvedByUser',
            'checkedInByUser',
            'checkedOutByUser',
        ]);
    }
}