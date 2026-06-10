<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PaymentReceipt;
use App\Services\Booking\BookingService;
use App\Services\Room\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FrontOfficeController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly RoomService $rooms,
    ) {}

    /**
     * Display the front office interface with lazy tab isolation.
     */
    public function index(Request $request): View
    {
        // 1. Determine active layout viewport context cleanly
        $tab = $request->string('tab', 'bookings')->toString();
        if (!in_array($tab, ['bookings', 'receipts'], true)) {
            $tab = 'bookings';
        }

        // 2. High-Performance Lazy Allocation: Eliminate cross-ocean network overhead
        $bookingsPayload = [];
        $roomsPayload = [];
        $receiptsPayload = [];

        if ($tab === 'bookings') {
            // Load live dynamic tracking streams
            $bookingsPayload = $this->bookings->getAll();
            $roomsPayload = $this->rooms->getRooms();
        } elseif ($tab === 'receipts') {
            // Only pull receipts from cloud infrastructure if the user is looking at the tab
            $receiptsPayload = PaymentReceipt::query()
                ->latest()
                ->get();
        }

        return view('frontoffice.index', [
            'activeMenu'        => 'frontoffice',
            'tab'               => $tab,
            'bookings'          => $bookingsPayload,
            'rooms'             => $roomsPayload,
            'receipts'          => $receiptsPayload,
            'selectedBookingId' => $request->integer('booking') ?: null,
        ]);
    }

    /**
     * Update systemic record layouts cleanly using standard Model features.
     */
    public function update(Request $request, int $booking): RedirectResponse
    {
        $validated = $request->validate([
            'guest_fname'     => ['required', 'string', 'max:255'],
            'guest_lname'     => ['required', 'string', 'max:255'],
            'guest_email'     => ['required', 'email'],
            'start_at'        => ['required', 'date'],
            'end_at'          => ['required', 'date', 'after:start_at'],
            'room_id'         => ['required', 'exists:rooms,id'],
            'guests'          => ['required', 'integer', 'min:1'],
            'extra_beds'      => ['nullable', 'integer', 'min:0', 'max:2'],
            'has_child'       => ['nullable', 'boolean'],
            'child_age_group' => ['nullable', 'string'],
            'has_pwd'         => ['nullable', 'boolean'],
            'has_senior'      => ['nullable', 'boolean'],
        ]);

        // FIXED: Replaced non-existent updateBookingDetails method with standard Eloquent lifecycle mechanics
        $bookingModel = Booking::findOrFail($booking);
        
        $bookingModel->update([
            ...$validated,
            'has_child'  => $request->boolean('has_child'),
            'has_pwd'    => $request->boolean('has_pwd'),
            'has_senior' => $request->boolean('has_senior'),
        ]);

        return redirect()
            ->route('frontoffice', ['tab' => 'bookings', 'booking' => $booking])
            ->with('status', 'Booking updated successfully.');
    }

    /**
     * Write multi-part binary streaming files straight to application disk layers.
     */
    public function storeReceipt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount'     => ['required', 'numeric', 'min:0'],
            'receipt'    => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $file = $validated['receipt'];
        $path = $file->store('receipts', 'local');

        // FIXED: Aligned payload tracking schema with your precise PostgreSQL data definitions
        PaymentReceipt::query()->create([
            'booking_id'       => (int) $validated['booking_id'],
            'amount'           => $validated['amount'],
            'reference_number' => $path, // Safely stores references matching layout structures
        ]);

        return redirect()
            ->route('frontoffice', ['tab' => 'receipts'])
            ->with('status', 'Receipt uploaded successfully.');
    }

    /**
     * Download or view storage items safely.
     */
    public function viewReceipt(string $receipt): StreamedResponse
    {
        $record = PaymentReceipt::query()->findOrFail($receipt);
        $path = $record->reference_number;

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return $disk->download($path, basename($path), [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}