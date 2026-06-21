<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ArchivedBooking;
use App\Models\User;
use Livewire\Attributes\On;
use Illuminate\Contracts\View\View;

class ArchivedReservationDetailsModal extends Component
{
    public ?int $bookingId = null;
    public ?ArchivedBooking $booking = null;

    // Staff attribute name containers
    public ?string $approvedByName = null;
    public ?string $rejectedByName = null;
    public ?string $checkedInByName = null;
    public ?string $checkedOutByName = null;

    /**
     * Hydrates the archived booking record and maps operational staff identifier values to real names.
     */
    #[On('view-archive-details')]
    public function loadArchiveBooking(int $id): void
    {
        $this->bookingId = $id;
        $this->booking = ArchivedBooking::with('user')->find($id);

        if ($this->booking) {
            $this->approvedByName = $this->getStaffName($this->booking->approved_by);
            $this->rejectedByName = $this->getStaffName($this->booking->rejected_by);
            $this->checkedInByName = $this->getStaffName($this->booking->checked_in_by);
            $this->checkedOutByName = $this->getStaffName($this->booking->checked_out_by);
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
                // FIXED: We check the underlying physical class reflections to see if fullName() 
                // is explicitly declared as a method on your App\Models\User class. 
                // This completely bypasses Eloquent's __call magic forwarding mechanism.
                $reflection = new \ReflectionClass($user);
                if ($reflection->hasMethod('fullName')) {
                    return $user->fullName();
                }
            } catch (\Exception $e) {
                // Fallback gracefully if reflection fails for any unexpected environment reason
            }

            // Standard fallback chains for properties/database columns
            return $user->full_name ?? $user->name ?? "Staff Reference #{$staffId}";
        }

        return "Staff Reference #{$staffId}";
    }

    /**
     * Clears out all backend data tracks when the window drops out of focus.
     */
    public function resetModalState(): void
    {
        $this->reset([
            'bookingId', 
            'booking', 
            'approvedByName', 
            'rejectedByName', 
            'checkedInByName', 
            'checkedOutByName'
        ]);
    }

    public function render(): View
    {
        return view('modals.archived-reservation-modal');
    }
}