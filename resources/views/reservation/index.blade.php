@extends('layouts.app')

@section('title', 'Reservations')
@section('topbar_title', 'Reservations')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">Reservations Desk</h1>
            <p class="text-xs text-gray-500 font-medium">Manage bookings, room allocation matrices, and live room occupancy states.</p>
        </div>
    </div>

    {{-- Horizontal Tab Navigation Control Track --}}
    <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm">
        <div class="flex gap-1 overflow-x-auto pb-1 sm:pb-0">
            @foreach (['pending' => 'Pending Request', 'approved' => 'Confirmed Matrix', 'checked_in' => 'Checked In', 'checked_out' => 'Archived History'] as $id => $label)
                <a href="{{ route('reservation', ['tab' => $id]) }}"
                   class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition-all {{ $tab === $id ? 'bg-pink-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Main Booking Records Table --}}
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
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-gray-900">{{ $booking->user?->fullName() ?? 'Unknown Guest' }}</div>
                                <div class="text-xs text-gray-400 font-medium mt-0.5">{{ $booking->user?->email ?? 'no-email' }}</div>
                            </td>
                            <td class="p-4 text-gray-900 font-semibold">
                                {{ $booking->room?->roomType?->name ?? '—' }} 
                                <span class="ml-1 text-xs text-teal-600 bg-teal-50 px-2 py-0.5 rounded-md font-bold">#{{ $booking->room?->room_number ?? 'N/A' }}</span>
                            </td>
                            <td class="p-4 text-xs text-gray-600">{{ $booking->start_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="p-4 text-xs text-gray-600">{{ $booking->end_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="p-4">
                                <x-booking-status :status="$booking->status" />
                            </td>
                            <td class="p-4 text-gray-900 font-bold">₱{{ number_format((float) $booking->total_amount, 2) }}</td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('reservation', ['tab' => $tab, 'booking' => $booking->id]) }}"
                                       class="rounded-lg bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-gray-700 transition">View Details</a>
                                    
                                    @if ($booking->status === 'pending')
                                        <form method="POST" action="{{ route('reservation.approve', $booking) }}">
                                            @csrf
                                            <button class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">Approve</button>
                                        </form>
                                    @endif
                                    @if ($booking->status === 'approved')
                                        <form method="POST" action="{{ route('reservation.check-in', $booking) }}">
                                            @csrf
                                            <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition">Check In</button>
                                        </form>
                                    @endif
                                    @if ($booking->status === 'checked_in')
                                        <form method="POST" action="{{ route('reservation.check-out', $booking) }}">
                                            @csrf
                                            <button class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-purple-700 transition">Check Out</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-gray-400 font-medium">
                                <div class="text-3xl mb-2">📅</div>
                                No matching bookings located inside this data target track segment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Room Scheduling Calendar Visualizer Component Integration --}}
    <x-room-availability-calendar
        :availability="$availability"
        :rooms="$rooms"
        :room-types="$roomTypes"
    />
</div>

{{-- Detail Verification Dialog Frame Modal Drawer --}}
@if ($selectedBooking)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" 
     x-data 
     x-on:keydown.escape.window="window.location='{{ route('reservation', ['tab' => $tab]) }}'">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl border border-gray-100 flex flex-col">
        <div class="mb-5 flex items-start justify-between border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Booking Slip #{{ $selectedBooking->id }}</h3>
                <p class="text-xs font-medium text-gray-400 mt-0.5">Primary Ledger Account Record</p>
            </div>
            <a href="{{ route('reservation', ['tab' => $tab]) }}" class="text-gray-400 hover:text-gray-600 text-lg font-bold transition p-1">✕</a>
        </div>
        
        <dl class="grid gap-4 text-sm sm:grid-cols-2 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
            <div>
                <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide">Registered Guest</dt>
                <dd class="font-bold text-gray-900 mt-0.5">{{ $selectedBooking->user?->fullName() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide">Target Unit Designation</dt>
                <dd class="font-bold text-gray-900 mt-0.5">{{ $selectedBooking->room?->roomType?->name }} · Room #{{ $selectedBooking->room?->room_number }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide">Status Vector</dt>
                <dd class="mt-1"><x-booking-status :status="$selectedBooking->status" /></dd>
            </div>
            <div>
                <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide">Total Dynamic Cost</dt>
                <dd class="font-black text-teal-700 mt-0.5">₱{{ number_format((float) $selectedBooking->total_amount, 2) }}</dd>
            </div>
            <div class="border-t border-gray-100 pt-2 sm:col-span-2"></div>
            <div>
                <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide">Check-in Operational Window</dt>
                <dd class="text-gray-700 mt-0.5 font-medium">{{ $selectedBooking->start_at?->format('M j, Y g:i A') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide">Check-out Operational Window</dt>
                <dd class="text-gray-700 mt-0.5 font-medium">{{ $selectedBooking->end_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide">Declared Headcount Capacity</dt>
                <dd class="text-gray-700 mt-0.5 font-bold">{{ $selectedBooking->guests }} Registered Pax</dd>
            </div>
            @if ($selectedBooking->message)
                <div class="sm:col-span-2 border-t border-gray-100 pt-3">
                    <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide">Guest Verification Message Notes</dt>
                    <dd class="text-xs text-gray-600 bg-white border rounded-lg p-2.5 mt-1 font-medium italic">{{ $selectedBooking->message }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">
            @if ($selectedBooking->status === 'pending')
                <form method="POST" action="{{ route('reservation.approve', $selectedBooking) }}">@csrf<button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Approve Slip</button></form>
            @endif
            @if ($selectedBooking->status === 'approved')
                <form method="POST" action="{{ route('reservation.check-in', $selectedBooking) }}">@csrf<button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">Execute Check-In</button></form>
            @endif
            @if ($selectedBooking->status === 'checked_in')
                <form method="POST" action="{{ route('reservation.check-out', $selectedBooking) }}">@csrf<button class="rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-700">Finalize Check-Out</button></form>
            @endif
            <a href="{{ route('reservation', ['tab' => $tab]) }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Close View</a>
        </div>
    </div>
</div>
@endif
@endsection