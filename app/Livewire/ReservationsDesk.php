<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\ArchivedBooking;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Log;
use App\Mail\BookingStatusMail;       
use Illuminate\View\View;
use Carbon\Carbon;
use App\Services\Encryption\Aes256GcmEncrypter;

class ReservationsDesk extends Component
{
    // State Tracking Properties
    public string $currentTab = 'pending';
    public ?array $selectedBooking = null;

    public const RESERVATION_TABS = [
        'pending'     => 'Pending Request',
        'approved'    => 'Confirmed',
        'checked_in'  => 'Checked In',
        'checked_out' => 'Archived History',
    ];

    // Type-hinted state variables for Alpine Synchronization
    public int $selectedMonth;
    public int $selectedYear;

    // Database Payload Arrays
    public array $bookings = [];
    public array $availability = [];
    public array $rooms = [];
    public array $roomTypes = [];

    /**
     * Component Lifecycle Initializer
     */
    public function mount(): void
    {
        // Match JavaScript's 0-indexed month system (0 = January, 5 = June)
        $this->selectedMonth = (int) now()->month - 1; 
        $this->selectedYear = (int) now()->year;

        $this->loadCalendarData();
        $this->loadBookingsData();
    }

    /**
     * Swaps out the context active tab segment filter tracking
     */
    public function changeTab(string $tabName): void
    {
        if (! array_key_exists($tabName, self::RESERVATION_TABS)) {
            return;
        }

        $this->currentTab = $tabName;

        // Ensure the table re-renders immediately for the new tab.
        // Without this, wire:poll may be too slow and it can look like clicks do nothing.
        $this->loadBookingsData();
    }

    /**
     * Fetches details and formats records for the master blade data table window
     */
    public function loadBookingsData(): void
    {
        $approvedBookings = Booking::whereIn('status', ['approved', 'checked_in'])
            ->get(['id', 'room_id', 'start_at', 'end_at']);

        $this->bookings = Booking::with(['user', 'room.roomType'])
            ->where('status', $this->currentTab)
            ->get()
            ->sortBy('start_at')
            ->map(function ($booking) use ($approvedBookings) {
                $userData = null;

                // prepare Aes256Gcm encrypter
                $aes = null;
                try {
                    $aes = Aes256GcmEncrypter::fromConfiguration();
                } catch (\Throwable $e) {
                    $aes = null;
                }

                // Robust decrypt attempt using the project's AES-256-GCM encrypter first.
                // Handles both:
                //  - base64(iv|tag|ciphertext)
                //  - base64(json{iv,tag,value}) where iv/tag/value are themselves base64
                $attemptDecrypt = function (?string $raw) use ($aes) {
                    if (empty($raw) || !is_string($raw)) {
                        return null;
                    }

                    // Helper: decrypt Laravel-style envelope if present
                    $tryDecryptLaravelEnvelope = function (string $payload) use ($aes) : ?string {
                        // Payload itself is base64-encoded JSON
                        $maybeDecoded = @base64_decode($payload, true);
                        if ($maybeDecoded === false) {
                            return null;
                        }

                        $maybeJson = json_decode($maybeDecoded, true);
                        if (!is_array($maybeJson)) {
                            return null;
                        }

                        // Case A: json contains iv/tag/value (all base64). Our encrypter expects base64(iv|tag|ciphertext)
                        if (isset($maybeJson['iv'], $maybeJson['tag'], $maybeJson['value']) && $aes) {
                            try {
                                $iv = base64_decode((string)$maybeJson['iv'], true);
                                $tag = base64_decode((string)$maybeJson['tag'], true);
                                $cipher = base64_decode((string)$maybeJson['value'], true);

                                if ($iv === false || $tag === false || $cipher === false) {
                                    return null;
                                }

                                // Our encrypter expects base64(iv|tag|ciphertext)
                                $packed = $iv . $tag . $cipher; // binary
                                $reencoded = base64_encode($packed);

                                $out = $aes->decrypt($reencoded);
                                if ($out !== null && $out !== '' && $out !== $reencoded) {
                                    return $out;
                                }

                                // If auth failed, decrypt() returns original payload; ignore here
                                return null;
                            } catch (\Throwable) {
                                return null;
                            }
                        }

                        // Case B (less common): json contains value that is itself a payload
                        if (isset($maybeJson['value']) && is_string($maybeJson['value']) && $aes) {
                            $candidate = $maybeJson['value'];
                            try {
                                $out = $aes->decrypt($candidate);
                                if ($out !== null && $out !== '' && $out !== $candidate) {
                                    return $out;
                                }
                            } catch (\Throwable) {
                                return null;
                            }
                        }

                        return null;
                    };

                    // 1) Try Laravel envelope first (this matches your DB examples)
                    if ($aes) {
                        $out = $tryDecryptLaravelEnvelope($raw);
                        if ($out !== null) {
                            return $out;
                        }
                    }

                    // 2) Try AES encrypter on raw payload (base64(iv|tag|ciphertext))
                    if ($aes) {
                        try {
                            $out = $aes->decrypt($raw);
                            if ($out !== null && $out !== '' && $out !== $raw) {
                                return $out;
                            }
                        } catch (\Throwable) {}

                        // try decode-then-decode again (some payloads are double-encoded)
                        $decodedRaw = @base64_decode($raw, true);
                        if ($decodedRaw !== false) {
                            try {
                                $out2 = $aes->decrypt(base64_encode($decodedRaw));
                                if ($out2 !== null && $out2 !== '' && $out2 !== $raw) {
                                    return $out2;
                                }
                            } catch (\Throwable) {}
                        }
                    }

                    // 3) Fallback to Laravel Crypt facade
                    try {
                        return \Illuminate\Support\Facades\Crypt::decryptString($raw);
                    } catch (\Throwable) {}

                    return null;
                };

                if ($booking->user) {
                    try {
                        $rawEmail = $booking->user->getRawOriginal('email') ?? $booking->user->email ?? null;
                    } catch (\Throwable) {
                        $rawEmail = $booking->user->email ?? null;
                    }

                    $decryptedEmail = $attemptDecrypt($rawEmail);

                    // If decryption failed, but raw looks like plain email, use it; otherwise placeholder
                    if ($decryptedEmail === null) {
                        $decryptedEmail = is_string($rawEmail) && strpos($rawEmail, '@') !== false ? $rawEmail : 'Encrypted (AES-256-GCM)';
                    }

                    $userData = [
                        'id'        => (string) $booking->user->getKey(),
                        'full_name' => $booking->user->fullName(),
                        'email'     => $decryptedEmail,
                    ];
                }

                $isConflicted = false;
                if ($booking->start_at && $booking->end_at && $booking->status === 'pending') {
                    $bookingStart = Carbon::parse($booking->start_at)->startOfDay();
                    $bookingEnd = Carbon::parse($booking->end_at)->endOfDay();

                    $isConflicted = $approvedBookings->contains(function ($approved) use ($booking, $bookingStart, $bookingEnd) {
                        if ($approved->id === $booking->id) return false;
                        if ($approved->room_id !== $booking->room_id) return false;

                        $approvedStart = Carbon::parse($approved->start_at)->startOfDay();
                        $approvedEnd = Carbon::parse($approved->end_at)->endOfDay();

                        return $bookingStart->lt($approvedEnd) && $bookingEnd->gt($approvedStart);
                    });
                }

                $placeholder = 'Encrypted (AES-256-GCM)';

                $safe = function (string $attr, $default = null) use ($booking, $placeholder) {
                    try {
                        $val = $booking->{$attr};
                        return $val === null ? $default : $val;
                    } catch (\Throwable) {
                        return $placeholder;
                    }
                };

                // Decrypt numeric fields: prefer raw DB value to avoid casts, then attempt decrypt
                $decryptNumeric = function (string $attr, $default = null) use ($booking, $attemptDecrypt, $placeholder) {
                    try {
                        $raw = null;
                        try {
                            $raw = $booking->getRawOriginal($attr);
                        } catch (\Throwable) {
                            $raw = $booking->{$attr} ?? null;
                        }

                        if ($raw === null) {
                            return $default;
                        }

                        if (is_numeric($raw)) {
                            return number_format((float) $raw, 2, '.', '');
                        }

                        if (is_string($raw) && $raw !== '') {
                            $dec = $attemptDecrypt($raw);
                            if ($dec === null) {
                                return $placeholder;
                            }
                            if (is_numeric($dec)) {
                                return number_format((float) $dec, 2, '.', '');
                            }
                            return $dec;
                        }

                        return $default;
                    } catch (\Throwable) {
                        return $placeholder;
                    }
                };

                $totalAmount = $decryptNumeric('total_amount', null);
                $priceAtBooking = $decryptNumeric('price_at_booking', null);

                return [
                    'id'                 => $booking->id,
                    'status'             => $booking->status,
                    'total_amount'       => $totalAmount,
                    'price_at_booking'   => $priceAtBooking,
                    'has_child'          => $safe('has_child'),
                    'has_pwd'            => $safe('has_pwd'),
                    'has_senior'         => $safe('has_senior'),
                    'guests'             => $safe('guests', 1),
                    'extra_beds'         => $safe('extra_beds', 0),
                    'message'            => $booking->message ?? '',
                    'start_at'           => $booking->start_at,
                    'end_at'             => $booking->end_at,
                    'start_at_formatted' => $booking->start_at ? Carbon::parse($booking->start_at)->format('M d, Y h:i A') : '—',
                    'end_at_formatted'   => $booking->end_at ? Carbon::parse($booking->end_at)->format('M d, Y h:i A') : '—',
                    'user'               => $userData,
                    'room'               => $booking->room ? $booking->room->toArray() : null,
                    'is_conflicted'      => $isConflicted,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Gathers spatial mapping layout structures for the grid matrix visualizer
     */
    public function loadCalendarData(): void
    {
        $this->rooms = Room::with('roomType')->get()->toArray();
        $this->roomTypes = RoomType::get()->toArray();
        
        $this->availability = Booking::with('user')
            ->whereIn('status', ['approved', 'checked_in'])
            ->get()
            ->map(function ($booking) {
                return [
                    'room_id'   => $booking->room_id,
                    'status'    => $booking->status, 
                    'user_name' => $booking->user ? $booking->user->fullName() : 'Unknown Guest',
                    'start'     => $booking->start_at ? Carbon::parse($booking->start_at)->toDateString() : null, 
                    'end'       => $booking->end_at ? Carbon::parse($booking->end_at)->toDateString() : null,   
                ];
            })
            ->toArray();
    }

    /**
     * Broadcasts a targeted event down to the child modal component structure
     */
    public function viewDetails(int $bookingId): void
    {
        $this->dispatch('openBookingDetails', id: $bookingId)->to(ReservationDetailsModal::class);
    }

    public function closeDetails(): void
    {
        $this->selectedBooking = null;
    }

    private function sendableEmailForUser($user): ?string
    {
        if (!$user) {
            return null;
        }

        try {
            $rawEmail = $user->getRawOriginal('email') ?? $user->email ?? null;
        } catch (\Throwable) {
            $rawEmail = $user->email ?? null;
        }

        $email = $this->decryptEncryptedString(is_string($rawEmail) ? $rawEmail : null);

        if (!$email && is_string($user->email ?? null)) {
            $email = $user->email;
        }

        $email = is_string($email) ? trim($email) : null;

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function decryptEncryptedString(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $aes = null;
        try {
            $aes = Aes256GcmEncrypter::fromConfiguration();
        } catch (\Throwable) {
            $aes = null;
        }

        if ($aes) {
            $decodedEnvelope = @base64_decode($raw, true);
            $envelope = $decodedEnvelope === false ? null : json_decode($decodedEnvelope, true);

            if (is_array($envelope) && isset($envelope['iv'], $envelope['tag'], $envelope['value'])) {
                try {
                    $iv = base64_decode((string) $envelope['iv'], true);
                    $tag = base64_decode((string) $envelope['tag'], true);
                    $cipher = base64_decode((string) $envelope['value'], true);

                    if ($iv !== false && $tag !== false && $cipher !== false) {
                        $packedPayload = base64_encode($iv . $tag . $cipher);
                        $decrypted = $aes->decrypt($packedPayload);

                        if (is_string($decrypted) && $decrypted !== '' && $decrypted !== $packedPayload) {
                            return $decrypted;
                        }
                    }
                } catch (\Throwable) {
                    // Fall through to the remaining decryption strategies.
                }
            }

            try {
                $decrypted = $aes->decrypt($raw);

                if (is_string($decrypted) && $decrypted !== '' && $decrypted !== $raw) {
                    return $decrypted;
                }
            } catch (\Throwable) {
                // Fall through to Laravel Crypt compatibility.
            }
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($raw);
        } catch (\Throwable) {
            return str_contains($raw, '@') ? $raw : null;
        }
    }

    /**
     * Approves a target booking reservation if slots are cleared
     */
    #[On('executeApprove')]
    public function approveBooking(int $bookingId): void
    {
        // Eager load relations so that they are instantly available inside the email payload
        $booking = Booking::with(['user', 'room.roomType'])->find($bookingId);
        if (!$booking) return;

        $hasConflict = Booking::where('room_id', $booking->room_id)
            ->whereIn('status', ['approved', 'checked_in'])
            ->where('start_at', '<', $booking->end_at)
            ->where('end_at', '>', $booking->start_at)
            ->exists();

        if ($hasConflict) {
            $this->dispatch('notify', message: 'Error: Cannot approve. Time slot is already occupied!');
            return;
        }

        $booking->update([
            'status'      => 'approved',
            'approved_by' => Auth::id() ? (string) Auth::id() : null,
        ]);

        // --- DIAGNOSTIC BREAKDOWN FOR TESTING ENVIRONMENT ---
        $recipientEmail = $this->sendableEmailForUser($booking->user);

        if (!$booking->user) {
            Log::warning("Email Routing Skipped: Booking record ID {$bookingId} does not possess a linked User relation model profile.");
            $this->dispatch('notify', message: 'Confirmed! Note: No user profile linked to email.');
        } elseif (!$recipientEmail) {
            Log::warning("Email Routing Skipped: Booking ID {$bookingId} has no decryptable RFC-compliant recipient email address.");
            $this->dispatch('notify', message: 'Confirmed! Note: Target user email could not be decrypted.');
        } else {
            Log::info("Mail Dispatch Handshake Initialized: Sending confirmation to customer path [{$recipientEmail}]");
            
            Mail::to($recipientEmail)->send(new BookingStatusMail($booking, 'approved'));
            
            $this->dispatch('notify', message: 'Booking reservation confirmed and email sent successfully!');
        }

        $this->refreshComponentState();
    }

    /**
     * Rejects an incoming booking inquiry
     */
    #[On('executeReject')]
    public function rejectBooking(int $bookingId): void
    {
        // Eager load customer data to target dispatch delivery parameters
        $booking = Booking::with(['user'])->find($bookingId);
        
        if ($booking) {
            $booking->update([
                'status'      => 'rejected',
                'rejected_by' => Auth::id() ? (string) Auth::id() : null,
            ]);

            // --- DIAGNOSTIC BREAKDOWN FOR TESTING ENVIRONMENT ---
            $recipientEmail = $this->sendableEmailForUser($booking->user);

            if (!$booking->user) {
                Log::warning("Email Routing Skipped: Rejection ID {$bookingId} does not possess a linked User relation model profile.");
            } elseif (!$recipientEmail) {
                Log::warning("Email Routing Skipped: Rejection ID {$bookingId} has no decryptable RFC-compliant recipient email address.");
            } else {
                Log::info("Mail Dispatch Handshake Initialized: Sending cancellation notice to customer path [{$recipientEmail}]");
                
                Mail::to($recipientEmail)->send(new BookingStatusMail($booking, 'rejected'));
            }

            $this->dispatch('notify', message: 'Reservation request rejected and notification email processed.');
            $this->refreshComponentState();
        }
    }

    /**
     * Checks in a guest onto the current living layout assignment
     */
    #[On('executeCheckIn')]
    public function checkInBooking(int $bookingId): void
    {
        $booking = Booking::find($bookingId);
        if ($booking) {
            $authUserId = Auth::id() ? (string) Auth::id() : null;

            $booking->update([
                'status'        => 'checked_in',
                'checked_in_at' => now(),
                'checked_in_by' => $authUserId,
            ]);
            $this->dispatch('notify', message: 'Guest has checked in to assigned unit layout space.');
            $this->refreshComponentState();
        }
    }

   /**
     * Transitions active room configurations safely over to historical archive logs
     */
    #[On('executeCheckOut')]
    public function checkOutBooking(int $bookingId): void
    {
        $booking = Booking::with(['user', 'room.roomType'])->find($bookingId);

        if (!$booking) {
            $this->dispatch('notify', message: 'Error: Target reservation record not found.');
            return;
        }

        DB::transaction(function () use ($booking) {
            $now = now();
            $authUserId = Auth::id() ? (string) Auth::id() : null;

            ArchivedBooking::create([
                'original_booking_id' => $booking->id,
                'user_id'             => $booking->user_id,
                'room_id'             => $booking->room_id,
                'room_number'         => $booking->room->room_number ?? 'N/A',
                'room_type_name'      => $booking->room->roomType->name ?? 'N/A',
                'room_type_id'        => $booking->room->room_type_id ?? null,
                'room_capacity'       => $booking->room->capacity ?? ($booking->room->roomType->capacity ?? 2),
                'room_base_price'     => $booking->room->price ?? ($booking->room->roomType->price ?? 0.00),
                'room_floor'          => $booking->room->floor ?? 1,
                'start_at'            => $booking->start_at,
                'end_at'              => $booking->end_at,
                'checked_in_at'       => $booking->checked_in_at ?? $booking->start_at,
                'checked_out_at'      => $now,
                'guests'              => $booking->guests,
                'status'              => 'checked_out',
                'message'             => $booking->message,
                'payment_method'      => $booking->payment_method,
                'total_amount'        => $booking->total_amount,
                'extra_beds'          => $booking->extra_beds,
                'has_child'           => $booking->has_child,
                'has_pwd'             => $booking->has_pwd,
                'has_senior'          => $booking->has_senior,
                'child_age_group'     => $booking->child_age_group,
                'guest_fname'         => $booking->user->fname ?? 'Unknown',
                'guest_lname'         => $booking->user->lname ?? 'Guest',
                'guest_email_hash'    => $booking->user->email_hash ?? null,
                'approved_by'         => $booking->approved_by,
                'rejected_by'         => $booking->rejected_by,
                'checked_in_by'       => $booking->checked_in_by,
                'checked_out_by'      => $authUserId,
            ]);

            $booking->update([
                'status'         => 'checked_out',
                'checked_out_at' => $now,
                'checked_out_by' => $authUserId,
            ]);
        }); 

        $this->dispatch('notify', message: 'Guest checked out successfully and stay details archived.');
        $this->refreshComponentState();
    }

    /**
     * Refreshes internal engine state parameters and updates child layout systems
     */
    private function refreshComponentState(): void
    {
        $this->loadBookingsData();
        $this->loadCalendarData();
        $this->closeDetails();
        
        $this->dispatch('actionExecutionCompleted')->to(ReservationDetailsModal::class);
    }

    public function render(): View
    {
        return view('reservation.index', [
            'reservationTabs' => self::RESERVATION_TABS,
        ]);
    }
}
