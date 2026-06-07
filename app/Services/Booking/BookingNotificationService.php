<?php

namespace App\Services\Booking;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class BookingNotificationService
{
    public function sendStatusUpdate(Booking $booking): void
    {
        $booking->loadMissing(['user', 'room.roomType']);

        // Safe evaluation of the encrypted user email model binding
        $email = $booking->user?->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $name = $booking->user?->fullName() ?? 'Guest';
        $roomType = $booking->room?->roomType?->name ?? 'Room';
        $roomNumber = $booking->room?->room_number ?? 'N/A';
        $checkIn = $booking->start_at?->timezone('Asia/Manila')->format('F d, Y • h:i A') ?? 'N/A';
        $guests = (string) ($booking->guests ?? 'N/A');
        $total = '₱' . number_format((float) $booking->total_amount, 2);

        $extraBedsStr = match ((int) $booking->extra_beds) {
            1 => '1 Extra Bed',
            2 => '2 Extra Beds',
            default => 'No Extra Bed',
        };

        $status = strtoupper(str_replace('_', ' ', (string) $booking->status));
        $statusColor = match ($booking->status) {
            'approved' => '#16a34a',
            'rejected', 'cancelled' => '#dc2626',
            default => '#2563eb',
        };

        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 620px; margin: auto; padding: 24px; color: #333;'>
                <h2 style='margin-bottom: 6px;'>Hello {$name},</h2>
                <p style='font-size: 15px;'>Your booking has been <strong style='color: {$statusColor};'>{$status}</strong>.</p>
                <div style='margin-top: 16px; background: #f6f7f9; padding: 16px; border-radius: 10px;'>
                    <h3 style='margin-top: 0;'>Reservation Details</h3>
                    <p><strong>Room Type:</strong> {$roomType}</p>
                    <p><strong>Room Number:</strong> {$roomNumber}</p>
                    <hr style='margin: 10px 0;' />
                    <p><strong>Check-in:</strong> {$checkIn}</p>
                    <p><strong>Guests:</strong> {$guests}</p>
                    <p><strong>Extra Bed:</strong> {$extraBedsStr}</p>
                    <p><strong>Total Amount:</strong> {$total}</p>
                </div>
                <p style='margin-top: 18px; font-size: 14px; line-height: 1.6;'>
                    We are looking forward to welcoming you. If you need assistance, our front desk is available 24/7.
                </p>
                <p style='margin-top: 28px; font-size: 13px; color: #777;'>— Hotel Management</p>
            </div>
        ";

        try {
            Mail::html($html, function ($message) use ($email, $status, $roomType) {
                $message->to($email)
                    ->subject("Booking {$status} • {$roomType}");
            });
        } catch (\Throwable $e) {
            Log::warning('Booking status email failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}