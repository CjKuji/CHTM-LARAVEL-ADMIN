@extends('layouts.app')

@section('title', 'Front Office')
@section('topbar_title', 'Front Office Desk')

@section('content')
@php
    $selected = $selectedBookingId ? $bookings->firstWhere('id', $selectedBookingId) : null;
    $statusCounts = $bookings->groupBy('status')->map->count();
@endphp
<div class="space-y-6 p-4 sm:p-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Front Office Desk</h1>
            <p class="text-sm text-gray-500">Manage reservations, update guest details, and verify payment receipts.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="rounded-full bg-white px-4 py-2 text-sm text-gray-700 shadow-sm border border-gray-100">Total: {{ $bookings->count() }}</span>
            <span class="rounded-full bg-white px-4 py-2 text-sm text-gray-700 shadow-sm border border-gray-100">Pending: {{ $statusCounts['pending'] ?? 0 }}</span>
            <span class="rounded-full bg-white px-4 py-2 text-sm text-gray-700 shadow-sm border border-gray-100">Approved: {{ $statusCounts['approved'] ?? 0 }}</span>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
        <div class="mb-3 rounded-2xl border border-teal-100 bg-teal-50 p-3 text-sm text-teal-800">
            Tip: select a booking first, update the guest or date fields, then save. Switch tabs to verify receipts.
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach (['bookings' => 'Edit Booking', 'receipts' => 'Receipt Verification'] as $key => $label)
                <a href="{{ route('frontoffice', ['tab' => $key]) }}"
                   class="rounded-full px-4 py-2 text-sm font-medium transition {{ $tab === $key ? 'bg-pink-600 text-white shadow' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @if ($tab === 'bookings')
    <div class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Bookings</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Guest</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Room</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Dates</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($bookings as $booking)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $booking->user?->fullName() }}</div>
                                <div class="text-xs text-gray-500">{{ $booking->user?->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $booking->room?->roomType?->name }}
                                <div class="text-xs text-gray-500 font-medium">Room {{ $booking->room?->room_number }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $booking->start_at?->format('M j, Y g:i A') }}
                                <div class="text-xs text-gray-500 font-medium">{{ $booking->end_at?->format('M j, Y g:i A') }}</div>
                            </td>
                            <td class="px-4 py-3 capitalize text-gray-700">
                                {{ str_replace('_', ' ', $booking->status) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('frontoffice', ['tab' => 'bookings', 'booking' => $booking->id]) }}" 
                                   class="inline-block rounded-xl bg-pink-600 px-4 py-2 text-sm text-white hover:bg-pink-700 transition shadow-sm font-medium">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm h-fit">
            <h2 class="text-lg font-semibold text-gray-900">Selected Party</h2>
            <p class="mb-4 text-sm text-gray-500">Edit guest details and booking information before saving.</p>
            @unless($selected)
                <div class="rounded-2xl border border-dashed border-gray-300 p-8 text-center text-gray-500 font-medium italic">
                    Select a booking to start editing.
                </div>
            @else
                <form method="POST" action="{{ route('frontoffice.update', $selected) }}" class="space-y-4">
                    @csrf 
                    @method('PUT')
                    <div>
                        <label class="text-sm font-medium text-gray-700">First name</label>
                        <input name="guest_fname" value="{{ old('guest_fname', $selected->user?->fname) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Last name</label>
                        <input name="guest_lname" value="{{ old('guest_lname', $selected->user?->lname) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="guest_email" value="{{ old('guest_email', $selected->user?->email) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20" required>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Start</label>
                            <input type="datetime-local" name="start_at" value="{{ old('start_at', $selected->start_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20" required>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">End</label>
                            <input type="datetime-local" name="end_at" value="{{ old('end_at', $selected->end_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20" required>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Room</label>
                        <select name="room_id" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20" required>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected($selected->room_id == $room->id)>{{ $room->roomType?->name }} — Room {{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Guests</label>
                            <input type="number" min="1" name="guests" value="{{ old('guests', $selected->guests) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20" required>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Extra beds</label>
                            <input type="number" min="0" max="2" name="extra_beds" value="{{ old('extra_beds', $selected->extra_beds) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20">
                        </div>
                    </div>
                    
                    <div class="space-y-2 pt-2 border-t border-gray-100">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer select-none">
                                <input type="checkbox" name="has_child" value="1" @checked($selected->has_child) class="rounded border-gray-300 text-pink-600 focus:ring-pink-500/20"> 
                                Has child
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer select-none">
                                <input type="checkbox" name="has_pwd" value="1" @checked($selected->has_pwd) class="rounded border-gray-300 text-pink-600 focus:ring-pink-500/20"> 
                                PWD guest
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer select-none">
                                <input type="checkbox" name="has_senior" value="1" @checked($selected->has_senior) class="rounded border-gray-300 text-pink-600 focus:ring-pink-500/20"> 
                                Senior guest
                            </label>
                        </div>
                        <div class="pt-1">
                            <label class="text-sm font-medium text-gray-700">Child age group</label>
                            <input name="child_age_group" value="{{ old('child_age_group', $selected->child_age_group) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20" placeholder="e.g., Toddler, 5-10 yrs">
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('frontoffice') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                        <button type="submit" class="rounded-xl bg-pink-600 px-4 py-2 text-sm font-medium text-white hover:bg-pink-700 transition shadow-sm">Save changes</button>
                    </div>
                </form>
            @endunless
        </section>
    </div>
    @else
    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">Payment Receipt Verification</h2>
        <form method="POST" action="{{ route('frontoffice.receipts.store') }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-3 rounded-2xl border border-dashed border-gray-300 p-4 bg-gray-50/30">
            @csrf
            <div class="min-w-[220px] flex-1">
                <label class="text-sm font-medium text-gray-700">Upload receipt</label>
                <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required
                       class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-pink-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-pink-700 file:hover:bg-pink-100 file:transition-colors cursor-pointer">
            </div>
            <button type="submit" class="rounded-xl bg-pink-600 px-4 py-2 text-sm font-medium text-white hover:bg-pink-700 transition shadow-sm">Upload</button>
        </form>
        @if ($receipts->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500 font-medium italic">No receipt uploads found.</div>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">File</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Updated</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Size</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($receipts as $file)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $file->original_filename }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $file->updated_at?->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ number_format($file->size / 1024, 1) }} KB</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('frontoffice.receipt', $file) }}" class="inline-block rounded-xl bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition shadow-sm" target="_blank">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif
</div>
@endsection