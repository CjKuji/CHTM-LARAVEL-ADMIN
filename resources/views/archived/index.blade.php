@extends('layouts.app')

@section('title', 'Archived Bookings')
@section('topbar_title', 'Archived Bookings')

@push('head')
<style>
    /* Clean print utility to ensure only the receipt folio isolates during physical print operations */
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-folio-card, #printable-folio-card * {
            visibility: visible;
        }
        #printable-folio-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        .print-hide {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6 p-4 sm:p-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Archived Bookings</h1>
        <p class="text-sm text-gray-500">Review completed and archived historical reservation records.</p>
    </div>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_450px]">
        <div class="flex h-[calc(100vh-220px)] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @if ($archivedBookings->isEmpty())
                <div class="flex flex-1 flex-col items-center justify-center gap-2 p-8 text-center text-sm text-gray-400">
                    <i class="ti ti-archive text-3xl text-gray-300"></i>
                    <span>No archived bookings found.</span>
                </div>
            @else
                <div class="flex-1 overflow-auto">
                    <table class="w-full divide-y divide-gray-200 text-sm">
                        <thead class="sticky top-0 z-10 bg-gray-50">
                            <tr>
                                <th class="px-4 py-3.5 text-left font-semibold text-gray-700">Guest</th>
                                <th class="px-4 py-3.5 text-left font-semibold text-gray-700">Room</th>
                                <th class="px-4 py-3.5 text-left font-semibold text-gray-700">Stay Dates</th>
                                <th class="px-4 py-3.5 text-left font-semibold text-gray-700">Total Charged</th>
                                <th class="px-4 py-3.5 text-left font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($archivedBookings as $booking)
                                <tr @click="window.location.href = '{{ route('archived', ['booking' => $booking->id]) }}'"
                                    class="cursor-pointer transition duration-150 {{ ($selectedBooking?->id ?? null) === $booking->id ? 'bg-teal-50/60 ring-1 ring-inset ring-teal-100 font-medium' : 'hover:bg-gray-50/80' }}">
                                    <td class="whitespace-nowrap px-4 py-3.5 text-gray-900">
                                        {{ trim(($booking->guest_fname ?? '').' '.($booking->guest_lname ?? '')) ?: 'Archived Guest' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-700">
                                        <span class="font-medium text-gray-900">#{{ $booking->room_number ?? '—' }}</span>
                                        <div class="text-xs text-gray-400 font-normal">{{ $booking->room_type_name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-gray-600">
                                        {{ $booking->start_at?->format('M j, Y') }} — {{ $booking->end_at?->format('M j, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-gray-900">
                                        ₱{{ number_format((float) $booking->total_amount, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <span class="inline-flex items-center rounded-md bg-teal-50 px-2 py-1 text-xs font-medium text-teal-700 ring-1 ring-inset ring-teal-600/10 capitalize">
                                            {{ $booking->status ?? 'archived' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-center gap-2 border-t bg-gray-50 px-4 py-3 text-xs font-medium uppercase tracking-wider text-gray-400">
                    <i class="ti ti-lock text-sm"></i> End of Historical Archive Records
                </div>
            @endif
        </div>

        <aside class="lg:sticky lg:top-24">
            @if ($selectedBooking)
                <div id="printable-folio-card" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200">
                    <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Guest Folio Invoice</h3>
                            <p class="text-xs font-medium text-gray-400 mt-0.5">Booking Reference Code: #{{ $selectedBooking->original_booking_id ?? $selectedBooking->id }}</p>
                        </div>
                        <i class="ti ti-receipt text-2xl text-gray-300 print-hide"></i>
                    </div>
                    
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex justify-between border-b border-dashed border-gray-100 pb-2">
                            <dt class="text-gray-400">Primary Guest</dt>
                            <dd class="font-semibold text-gray-900">{{ trim(($selectedBooking->guest_fname ?? '').' '.($selectedBooking->guest_lname ?? '')) }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-dashed border-gray-100 pb-2">
                            <dt class="text-gray-400">Assigned Quarters</dt>
                            <dd class="text-gray-900 text-right">
                                <span class="font-medium text-gray-900">Room #{{ $selectedBooking->room_number }}</span>
                                <div class="text-xs text-gray-400">{{ $selectedBooking->room_type_name }} (Floor {{ $selectedBooking->room_floor ?? '1' }})</div>
                            </dd>
                        </div>
                        <div class="flex justify-between border-b border-dashed border-gray-100 pb-2">
                            <dt class="text-gray-400">Check-In Timestamp</dt>
                            <dd class="font-medium text-gray-800">{{ $selectedBooking->checked_in_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-dashed border-gray-100 pb-2">
                            <dt class="text-gray-400">Check-Out Timestamp</dt>
                            <dd class="font-medium text-gray-800">{{ $selectedBooking->checked_out_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-dashed border-gray-100 pb-2">
                            <dt class="text-gray-400">Settlement Method</dt>
                            <dd class="font-medium uppercase tracking-wider text-gray-800 text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $selectedBooking->payment_method ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <dt class="text-base font-semibold text-gray-900">Total Final Balance</dt>
                            <dd class="text-xl font-bold text-teal-700">₱{{ number_format((float) $selectedBooking->total_amount, 2) }}</dd>
                        </div>
                    </dl>
                    
                    <button onclick="window.print()" class="print-hide mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                        <i class="ti ti-printer text-base"></i>
                        <span>Print Folio Receipt</span>
                    </button>
                </div>
            @else
                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-12 text-center text-gray-400">
                    <i class="ti ti-pointer text-3xl text-gray-300 mb-2"></i>
                    <p class="text-sm font-medium">Select an archived booking row to generate and view full folio details.</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection