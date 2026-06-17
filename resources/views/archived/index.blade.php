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
    {{-- Header Content --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Archived Bookings</h1>
        <p class="text-xs text-gray-500 font-medium">Review completed and archived historical reservation records from the centralized ledger engine.</p>
    </div>

    {{-- Main Booking Records Table Layout Window Frame --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[1000px] w-full text-sm text-left">
                <thead class="border-b border-gray-200 bg-gray-50/70 text-xs font-bold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="p-4">Guest Information</th>
                        <th class="p-4">Assigned Room Unit</th>
                        <th class="p-4">Expected Check-In</th>
                        <th class="p-4">Expected Check-Out</th>
                        <th class="p-4">Status Token</th>
                        <th class="p-4">Total Amount</th>
                        <th class="p-4 text-center">Action Framework</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                    @if ($archivedBookings->isEmpty())
                        <tr>
                            <td colspan="7" class="p-12 text-center text-gray-400 font-medium">
                                <div class="text-3xl mb-2">📁</div>
                                No historical matching records located inside this archive dataset tracking matrix.
                            </td>
                        </tr>
                    @else
                        @foreach ($archivedBookings as $booking)
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                {{-- Guest Identity Tracks with Decrypted Fallback Labels --}}
                                <td class="p-4">
                                    <div class="font-bold text-gray-900">
                                        {{ trim(($booking->guest_fname ?? '').' '.($booking->guest_lname ?? '')) ?: 'Archived Guest' }}
                                    </div>
                                    <div class="text-xs text-gray-400 font-medium mt-0.5">
                                        <x-private-email :email="$booking->user->email ?? null" fallback="no-linked-email@system.internal" />
                                    </div>
                                </td>

                                {{-- Room Placement Architecture Data Fields --}}
                                <td class="p-4 text-gray-900 font-semibold">
                                    <span>{{ $booking->room_type_name ?? '—' }}</span>
                                    <span class="ml-1 text-xs text-teal-600 bg-teal-50 px-2 py-0.5 rounded-md font-bold">
                                        #{{ $booking->room_number ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- System Chronology Timestamps --}}
                                <td class="p-4 text-xs text-gray-600 font-semibold">
                                    {{ $booking->start_at?->format('M d, Y') ?? '—' }}
                                    <div class="text-[10px] text-gray-400 font-normal mt-0.5">Scheduled Arrival</div>
                                </td>
                                <td class="p-4 text-xs text-gray-600">
                                    {{ $booking->end_at?->format('M d, Y') ?? '—' }}
                                    <div class="text-[10px] text-gray-400 font-normal mt-0.5">Scheduled Release</div>
                                </td>

                                {{-- Immutable Status Token Flags --}}
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700 ring-1 ring-inset ring-teal-600/10">
                                        {{ $booking->status ?? 'archived' }}
                                    </span>
                                </td>

                                {{-- Total Ledger Charges --}}
                                <td class="p-4 text-gray-900 font-bold">
                                    ₱{{ number_format((float) ($booking->total_amount ?? 0), 2) }}
                                </td>

                                {{-- Execution Event Dispatches --}}
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button 
    type="button" 
    @click="$dispatch('open-archive-modal'); $dispatch('view-archive-details', { id: {{ $booking->id }} })"
    class="rounded-lg bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-gray-700 transition active:scale-95 cursor-pointer"
>
    View Details
</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Targeted Archive Isolation Modal Injection --}}
    <livewire:archived-reservation-details-modal />
</div>
@endsection
