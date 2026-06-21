<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\On;

class ReservationDetailsModal extends Component
{
    public ?int $bookingId = null;
    public bool $isOpen = false;

    // Operational staff name caching containers matching archive pattern
    public ?string $approvedByName = null;
    public ?string $rejectedByName = null;
    public ?string $checkedInByName = null;
    public ?string $checkedOutByName = null;

    /**
     * Catches the broadcasted target ID, maps reference user variables, and reveals the overlay framework.
     */
    #[On('openBookingDetails')]
    public function loadBooking(int $id): void
    {
        $this->bookingId = $id;
        $this->isOpen = true;

        // Fetch properties instantly to resolve actual staff names safely without model collision bottlenecks
        $booking = Booking::find($id);
        if ($booking) {
            $this->approvedByName = $this->getStaffName($booking->approved_by ?? null);
            $this->rejectedByName = $this->getStaffName($booking->rejected_by ?? null);
            $this->checkedInByName = $this->getStaffName($booking->checked_in_by ?? null);
            $this->checkedOutByName = $this->getStaffName($booking->checked_out_by ?? null);
        }
    }

    /**
     * Helper to resolve a staff reference signature (ID or UUID) to a viewable user name string.
     * Bypasses Eloquent magic __call traps using strict reflection method validation.
     */
    private function getStaffName(string|int|null $staffId): ?string
    {
        if (!$staffId) {
            return null;
        }

        $user = User::find($staffId);
        
        if ($user) {
            try {
                // strict Reflection verification bypasses Laravel's __call magic routing entirely.
                $reflection = new \ReflectionClass($user);
                if ($reflection->hasMethod('fullName')) {
                    return $user->fullName();
                }
            } catch (\Exception $e) {
                // Fallback gracefully on tracking environment failures
            }

            // Fallback attribute and column pipeline matching archive components
            return $user->full_name ?? $user->name ?? "Staff Reference #{$staffId}";
        }

        return "Staff Reference #{$staffId}";
    }

    /**
     * Completely purges the active component state parameters on close hooks.
     */
    #[On('closeBookingDetails')]
    #[On('actionExecutionCompleted')]
    public function resetModalState(): void
    {
        $this->reset([
            'bookingId', 
            'isOpen',
            'approvedByName',
            'rejectedByName',
            'checkedInByName',
            'checkedOutByName'
        ]);
    }

    /**
     * Triggers the validation and approval chain on the main ReservationsDesk layout.
     */
    public function approve(): void
    {
        if ($this->bookingId) {
            $this->dispatch('executeApprove', bookingId: $this->bookingId)->to(ReservationsDesk::class); 
        }
    }

    /**
     * Triggers the cancellation/rejection routine.
     */
    public function reject(): void
    {
        if ($this->bookingId) {
            $this->dispatch('executeReject', bookingId: $this->bookingId)->to(ReservationsDesk::class);
        }
    }

    /**
     * Dispatches the check-in operation routine.
     */
    public function checkIn(): void
    {
        if ($this->bookingId) {
            $this->dispatch('executeCheckIn', bookingId: $this->bookingId)->to(ReservationsDesk::class);
        }
    }

    /**
     * Signals the parent component to calculate total archiving fields.
     */
    public function checkOut(): void
    {
        if ($this->bookingId) {
            $this->dispatch('executeCheckOut', bookingId: $this->bookingId)->to(ReservationsDesk::class);
        }
    }

    public function render(): View
    {
        // Executes a clean, fresh relational lookup on-demand with all key relations
        $booking = $this->bookingId 
            ? Booking::with(['user', 'room.roomType'])->find($this->bookingId) 
            : null;

        return view('modals.reservation-modal', [
            'booking' => $booking,
            'approvedByName' => $this->approvedByName,
            'rejectedByName' => $this->rejectedByName,
            'checkedInByName' => $this->checkedInByName,
            'checkedOutByName' => $this->checkedOutByName,
        ]);
    }
}