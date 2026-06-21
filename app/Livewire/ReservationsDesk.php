<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\ArchivedBooking;
use App\Models\User;
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
        $this->loadBookingsData();
    }

    /**
     * Fetches details and formats records for the master blade data table window
     */
    public function loadBookingsData(): void
    {
        $approvedBookings = Booking::whereIn('status', ['approved', 'checked_in'])
            ->get(['id', 'room_id', 'start_at', 'end_at']);

        $placeholder = 'Encrypted (AES-256-GCM)';

        $this->bookings = Booking::with(['user', 'room.roomType'])
            ->where('status', $this->currentTab)
            ->get()
            ->sortBy('start_at')
            ->map(function ($booking) use ($approvedBookings, $placeholder) {
                $userData = null;

                if ($booking->user) {
                    $user = $booking->user;
                    $userId = (string) $user->getKey(); // Force dynamic UUID conversion string representation
                    
                    // 1. Force explicit UUID lookups directly via raw DB query to dodge Eloquent conversion traps
                    $dbRow = DB::table('users')->where('id', $userId)->first();
                    $rawEmail = $dbRow ? $dbRow->email : null;

                    $plainEmail = null;
                    $needsUpdate = false;

                    if (is_string($rawEmail) && $rawEmail !== '') {
                        $rawEmail = trim($rawEmail);

                        if (str_contains($rawEmail, '@')) {
                            // Plaintext found - Needs encryption
                            $plainEmail = strtolower($rawEmail);
                            $needsUpdate = true;
                        } else {
                            // Check if payload is already encrypted but needs visual extraction validation
                            try {
                                $decrypted = $this->decryptEncryptedString($rawEmail);
                                if ($decrypted && str_contains($decrypted, '@')) {
                                    $plainEmail = strtolower(trim($decrypted));
                                }
                            } catch (\Throwable $e) {
                                $plainEmail = null;
                            }
                        }
                    }

                    // 2. Execute JIT encryption and hash writing 
                    if ($needsUpdate && !empty($plainEmail)) {
                        try {
                            $encrypter = Aes256GcmEncrypter::fromConfiguration();
                            $encryptedValue = $encrypter->encrypt($plainEmail);
                            $hashedValue = User::hashEmail($plainEmail);

                            DB::table('users')
                                ->where('id', $userId)
                                ->update([
                                    'email'      => $encryptedValue,
                                    'email_hash' => $hashedValue,
                                    'updated_at' => now(),
                                ]);

                            // Sync memory records instantly for the execution scope context
                            $user->setAttribute('email', $plainEmail);
                            $user->setAttribute('email_hash', $hashedValue);
                            $user->syncOriginalAttribute('email', $encryptedValue);
                            $user->syncOriginalAttribute('email_hash', $hashedValue);
                        } catch (\Throwable $e) {
                            Log::error("JIT Encryption update sequence failed for UUID User #{$userId}: " . $e->getMessage());
                        }
                    }

                    // Extract the presentation email state securely
                    try {
                        $decryptedEmail = $user->email;
                    } catch (\Throwable $e) {
                        $decryptedEmail = null;
                    }

                    if (empty($decryptedEmail) || !str_contains($decryptedEmail, '@')) {
                        $decryptedEmail = $plainEmail ?? $placeholder;
                    }

                    $userData = [
                        'id'        => $userId,
                        'full_name' => $user->full_name ?? $user->fullName() ?? 'Unknown Guest',
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

                $safe = function (string $attr, $default = null) use ($booking, $placeholder) {
                    try {
                        $val = $booking->{$attr};
                        return $val === null ? $default : $val;
                    } catch (\Throwable $e) {
                        return $placeholder;
                    }
                };

                $decryptNumeric = function (string $attr, $default = null) use ($booking, $placeholder) {
                    try {
                        $raw = $booking->getRawOriginal($attr) ?? $booking->{$attr} ?? null;

                        if ($raw === null) {
                            return $default;
                        }

                        if (is_numeric($raw)) {
                            return number_format((float) $raw, 2, '.', '');
                        }

                        if (is_string($raw) && $raw !== '') {
                            $dec = $this->decryptEncryptedString($raw);
                            if ($dec === null) {
                                return $placeholder;
                            }
                            if (is_numeric($dec)) {
                                return number_format((float) $dec, 2, '.', '');
                            }
                            return $dec;
                        }

                        return $default;
                    } catch (\Throwable $e) {
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
                    'user_name' => $booking->user ? ($booking->user->full_name ?? $booking->user->fullName()) : 'Unknown Guest',
                    'start'     => $booking->start_at ? Carbon::parse($booking->start_at)->toDateString() : null, 
                    'end'       => $booking->end_at ? Carbon::parse($booking->end_at)->toDateString() : null,   
                ];
            })
            ->toArray();
    }

    /**
     * Broadcasts a targeted event down to the child modal component structure
     */
    public function viewDetails(string $bookingId): void
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
                } catch (\Throwable $e) {}
            }

            try {
                $decrypted = $aes->decrypt($raw);

                if (is_string($decrypted) && $decrypted !== '' && $decrypted !== $raw) {
                    return $decrypted;
                }
            } catch (\Throwable $e) {}
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($raw);
        } catch (\Throwable $e) {
            return str_contains($raw, '@') ? $raw : null;
        }
    }

    /**
     * Approves a targeted booking reservation if slots are cleared
     */
    #[On('executeApprove')]
    public function approveBooking(string $bookingId): void
    {
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

        $recipientEmail = $this->sendableEmailForUser($booking->user);

        if (!$booking->user) {
            Log::warning("Email Routing Skipped: Booking record ID {$bookingId} does not possess a linked User relation.");
            $this->dispatch('notify', message: 'Confirmed! Note: No user profile linked.');
        } elseif (!$recipientEmail) {
            Log::warning("Email Routing Skipped: Booking ID {$bookingId} has no valid recipient email.");
            $this->dispatch('notify', message: 'Confirmed! Note: Email cannot be verified.');
        } else {
            Log::info("Mail Dispatch Initialized: Sending confirmation to [{$recipientEmail}]");
            Mail::to($recipientEmail)->send(new BookingStatusMail($booking, 'approved'));
            $this->dispatch('notify', message: 'Booking reservation confirmed and email sent successfully!');
        }

        $this->refreshComponentState();
    }

    /**
     * Rejects an incoming booking inquiry
     */
    #[On('executeReject')]
    public function rejectBooking(string $bookingId): void
    {
        $booking = Booking::with(['user'])->find($bookingId);
        
        if ($booking) {
            $booking->update([
                'status'      => 'rejected',
                'rejected_by' => Auth::id() ? (string) Auth::id() : null,
            ]);

            $recipientEmail = $this->sendableEmailForUser($booking->user);

            if (!$booking->user) {
                Log::warning("Email Routing Skipped: Rejection ID {$bookingId} has no linked User.");
            } elseif (!$recipientEmail) {
                Log::warning("Email Routing Skipped: Rejection ID {$bookingId} has no valid email.");
            } else {
                Log::info("Mail Dispatch Initialized: Sending cancellation notice to [{$recipientEmail}]");
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
    public function checkInBooking(string $bookingId): void
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
    public function checkOutBooking(string $bookingId): void
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
     * ⚡ EVENT-DRIVEN LISTENERS:
     */
    #[On('echo:reservations-desk,.BookingUpdated')]
    #[On('echo:reservations-desk,.BookingCreated')]
    public function refreshComponentState(): void
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