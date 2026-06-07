<?php

namespace App\Http\Controllers;

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

    public function index(Request $request): View
    {
        $tab = $request->string('tab', 'bookings')->toString();

        return view('frontoffice.index', [
            'activeMenu'        => 'frontoffice',
            'tab'               => in_array($tab, ['bookings', 'receipts'], true) ? $tab : 'bookings',
            'bookings'          => $this->bookings->getAll(),
            'rooms'             => $this->rooms->getRooms(),
            'selectedBookingId' => $request->integer('booking') ?: null,
            'receipts'          => PaymentReceipt::query()->latest()->get(),
        ]);
    }

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

        $this->bookings->updateBookingDetails($booking, [
            ...$validated,
            'has_child'  => $request->boolean('has_child'),
            'has_pwd'    => $request->boolean('has_pwd'),
            'has_senior' => $request->boolean('has_senior'),
        ], $request->user());

        return redirect()
            ->route('frontoffice', ['tab' => 'bookings', 'booking' => $booking])
            ->with('status', 'Booking updated successfully.');
    }

    public function storeReceipt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $file = $validated['receipt'];
        $path = $file->store('receipts', 'local');

        PaymentReceipt::query()->create([
            'original_filename' => $file->getClientOriginalName(),
            'storage_path'      => $path,
            'size'              => $file->getSize() ?: 0,
        ]);

        return redirect()
            ->route('frontoffice', ['tab' => 'receipts'])
            ->with('status', 'Receipt uploaded successfully.');
    }

    public function viewReceipt(int $receipt): StreamedResponse
    {
        $record = PaymentReceipt::query()->findOrFail($receipt);
        $path = $record->storage_path;

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        // FIXED: Explicitly type-hinted the concrete adapter instance above to satisfy static analysis engines
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return $disk->download($path, $record->original_filename, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $record->original_filename . '"',
        ]);
    }
}