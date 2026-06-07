<?php

namespace App\Http\Controllers;

use App\Services\Booking\BookingService;
use App\Services\Room\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly RoomService $rooms,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->string('tab', 'pending')->toString();
        $allowed = ['pending', 'approved', 'checked_in', 'checked_out'];
        if (! in_array($tab, $allowed, true)) {
            $tab = 'pending';
        }

        $bookings = $this->bookings->getByStatus($tab, 25);
        $selected = $request->integer('booking')
            ? $this->bookings->getById($request->integer('booking'))
            : null;

        return view('reservation.index', [
            'activeMenu'       => 'reservation',
            'tab'              => $tab,
            'bookings'         => $bookings,
            'rooms'            => $this->rooms->getRooms(),
            'roomTypes'        => $this->rooms->getRoomTypes(),
            'availability'     => $this->bookings->getCalendarAvailability(),
            'selectedBooking'  => $selected,
        ]);
    }

    public function approve(Request $request, int $booking): RedirectResponse
    {
        $this->bookings->updateStatus($booking, 'approved', $request->user());

        return back()->with('status', 'Booking approved.');
    }

    public function checkIn(Request $request, int $booking): RedirectResponse
    {
        $this->bookings->updateStatus($booking, 'checked_in', $request->user());

        return back()->with('status', 'Guest checked in.');
    }

    public function checkOut(Request $request, int $booking): RedirectResponse
    {
        $this->bookings->updateStatus($booking, 'checked_out', $request->user());

        return back()->with('status', 'Guest checked out.');
    }
}
