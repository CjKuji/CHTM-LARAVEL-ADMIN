<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use Illuminate\View\View;

class ReservationDetailsModal extends Component
{
    public ?int $bookingId = null;
    public bool $isOpen = false;

    // Listens for structural ecosystem updates across component barriers
    protected $listeners = [
        'openBookingDetails'       => 'loadBooking',
        'closeBookingDetails'      => 'resetModalState',
        'actionExecutionCompleted' => 'resetModalState' // Controlled closure event hook
    ];

    /**
     * Catches the broadcasted target ID and reveals the window overlay frame
     */
    public function loadBooking(int $id): void
    {
        $this->bookingId = $id;
        $this->isOpen = true;
    }

    /**
     * Completely purges the active component state parameters
     */
    public function resetModalState(): void
    {
        $this->reset(['bookingId', 'isOpen']);
    }

   public function approve(): void
{
    if ($this->bookingId) {
        $this->dispatch('executeApprove', bookingId: $this->bookingId)->to(ReservationsDesk::class); 
    }
}

public function reject(): void
{
    if ($this->bookingId) {
        $this->dispatch('executeReject', bookingId: $this->bookingId)->to(ReservationsDesk::class);
    }
}

public function checkIn(): void
{
    if ($this->bookingId) {
        $this->dispatch('executeCheckIn', bookingId: $this->bookingId)->to(ReservationsDesk::class);
    }
}

public function checkOut(): void
{
    if ($this->bookingId) {
        $this->dispatch('executeCheckOut', bookingId: $this->bookingId)->to(ReservationsDesk::class);
    }
}
public function render()
    {
        // Executes a clean, fresh relational lookup on-demand with all key relations
        $booking = $this->bookingId 
            ? Booking::with(['user', 'room.roomType', 'approvedByUser', 'checkedInByUser'])->find($this->bookingId) 
            : null;

        // FIX: Point this directly to your actual custom views directory path
        return view('modals.reservation-modal', [
            'booking' => $booking
        ]);
    }
}