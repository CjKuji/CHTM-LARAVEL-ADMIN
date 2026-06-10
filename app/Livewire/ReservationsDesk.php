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

class ReservationsDesk extends Component
{
    // State Tracking Properties
    public string $currentTab = 'pending';
    public ?array $selectedBooking = null;

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
        $this->currentTab = $tabName;
    }

    /**
     * Fetches details and formats records for the master blade data table window
     */
    public function loadBookingsData(): void
    {
        $approvedBookings = Booking::whereIn('status', ['approved', 'checked_in'])
            ->get(['id', 'room_id', 'start_at', 'end_at']);

        $this->bookings = Booking::with(['user', 'room.roomType'])
            ->get()
            ->sortBy('start_at')
            ->map(function ($booking) use ($approvedBookings) {
                $userData = null;
                
                if ($booking->user) {
                    $userData = [
                        'id'         => (string) $booking->user->getKey(),
                        'full_name'  => $booking->user->fullName(),
                        'email'      => $booking->user->email,
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

                return [
                    'id'                  => $booking->id,
                    'status'              => $booking->status,
                    'total_amount'        => $booking->total_amount,
                    'guests'              => $booking->guests ?? 1,
                    'message'             => $booking->message ?? '',
                    'start_at'            => $booking->start_at,
                    'end_at'              => $booking->end_at,
                    'start_at_formatted'  => $booking->start_at ? Carbon::parse($booking->start_at)->format('M d, Y h:i A') : '—',
                    'end_at_formatted'    => $booking->end_at ? Carbon::parse($booking->end_at)->format('M d, Y h:i A') : '—',
                    'user'                => $userData,
                    'room'                => $booking->room ? $booking->room->toArray() : null,
                    'is_conflicted'       => $isConflicted, 
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
        if (!$booking->user) {
            Log::warning("Email Routing Skipped: Booking record ID {$bookingId} does not possess a linked User relation model profile.");
            $this->dispatch('notify', message: 'Confirmed! Note: No user profile linked to email.');
        } elseif (empty($booking->user->email)) {
            Log::warning("Email Routing Skipped: The linked User profile model matching Booking ID {$bookingId} contains an empty email data attribute.");
            $this->dispatch('notify', message: 'Confirmed! Note: Target user email property is empty.');
        } else {
            Log::info("Mail Dispatch Handshake Initialized: Sending confirmation to customer path [{$booking->user->email}]");
            
            Mail::to($booking->user->email)->send(new BookingStatusMail($booking, 'approved'));
            
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
            if (!$booking->user) {
                Log::warning("Email Routing Skipped: Rejection ID {$bookingId} does not possess a linked User relation model profile.");
            } elseif (empty($booking->user->email)) {
                Log::warning("Email Routing Skipped: The linked User profile model matching Rejection ID {$bookingId} contains an empty email data attribute.");
            } else {
                Log::info("Mail Dispatch Handshake Initialized: Sending cancellation notice to customer path [{$booking->user->email}]");
                
                Mail::to($booking->user->email)->send(new BookingStatusMail($booking, 'rejected'));
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
        return view('reservation.index');
    }
}