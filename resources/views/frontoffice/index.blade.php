@extends('layouts.app')

@section('title', 'Front Office')
@section('topbar_title', 'Front Office Desk')

@section('content')
@php
    $selected = $selectedBookingId ? $bookings->firstWhere('id', $selectedBookingId) : null;
    $statusCounts = $bookings->groupBy('status')->map->count();
@endphp

{{--
    CRITICAL FIX: 
    - Added 'max-w-full' and 'min-w-0' to keep the content from expanding the layout framework.
    - Added 'overflow-x-hidden' to ensure layout elements do not force global document scrolling.
--}}
<div class="space-y-6 p-4 sm:p-6 w-full max-w-full min-w-0 overflow-x-hidden">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between min-w-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl font-bold tracking-tight text-gray-900 truncate">Front Office Desk</h1>
            <p class="text-sm text-gray-500 mt-0.5 truncate">Manage reservations, update guest details, and verify payment receipts.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto flex-shrink-0">
            <span class="inline-flex items-center rounded-full bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200">
                Total: {{ $bookings->count() }}
            </span>
            <span class="inline-flex items-center rounded-full bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200">
                Pending: {{ $statusCounts['pending'] ?? 0 }}
            </span>
            <span class="inline-flex items-center rounded-full bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200">
                Approved: {{ $statusCounts['approved'] ?? 0 }}
            </span>
        </div>
    </div>

    {{-- Tip Banner + Tab Bar --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm space-y-4 min-w-0">
        <div class="rounded-xl border border-teal-100 bg-teal-50 p-3 text-sm text-teal-800">
            <span class="font-semibold">Tip:</span> Select a booking first, update the guest or date fields, then save. Switch tabs to verify receipts.
        </div>
        <div class="flex border-b border-gray-200 gap-2 overflow-x-auto pb-px">
            @foreach (['bookings' => 'Edit Booking', 'receipts' => 'Receipt Verification'] as $key => $label)
                <a href="{{ route('frontoffice', ['tab' => $key]) }}"
                   class="whitespace-nowrap border-b-2 py-2 px-4 text-sm font-medium transition-all
                          {{ $tab === $key
                              ? 'border-pink-600 text-pink-600'
                              : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ============================= --}}
    {{-- TAB: BOOKINGS                 --}}
    {{-- ============================= --}}
    @if ($tab === 'bookings')

        {{-- Use an explicit grid layout that prevents columns from breaking boundaries --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full max-w-full min-w-0 items-start">

            {{-- LEFT: Bookings Table Box Container --}}
            <section class="lg:col-span-7 xl:col-span-8 min-w-0 w-full bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col overflow-hidden">

                <div class="p-5 border-b border-gray-100 flex-shrink-0">
                    <h2 class="text-lg font-semibold text-gray-900">Bookings</h2>
                </div>

                {{-- Strict overflow container ensuring table cells stay inside this box component --}}
                <div class="overflow-x-auto w-full min-w-0 custom-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200 text-sm table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="w-[30%] min-w-[160px] px-4 py-3.5 text-left font-semibold text-gray-700">Guest</th>
                                <th scope="col" class="w-[25%] min-w-[130px] px-4 py-3.5 text-left font-semibold text-gray-700">Room</th>
                                <th scope="col" class="w-[25%] min-w-[150px] px-4 py-3.5 text-left font-semibold text-gray-700">Dates</th>
                                <th scope="col" class="w-[12%] min-w-[90px]  px-4 py-3.5 text-left font-semibold text-gray-700">Status</th>
                                <th scope="col" class="w-[8%]  min-w-[70px]  px-4 py-3.5 text-right font-semibold text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($bookings as $booking)
                                <tr class="hover:bg-gray-50/80 transition-colors {{ $selectedBookingId == $booking->id ? 'bg-pink-50/30' : '' }}">

                                    {{-- Guest Column --}}
                                    <td class="px-4 py-3.5 min-w-0 max-w-0 overflow-hidden">
                                        <div class="font-medium text-gray-900 truncate" title="{{ $booking->user?->fullName() }}">
                                            {{ $booking->user?->fullName() ?? 'Unknown Guest' }}
                                        </div>
                                        <div class="text-xs text-gray-500 truncate mt-0.5">
                                            <x-private-email :email="$booking->user?->email" fallback="no-email@system" />
                                        </div>
                                    </td>

                                    {{-- Room Column --}}
                                    <td class="px-4 py-3.5 min-w-0 max-w-0 overflow-hidden">
                                        <div class="font-medium text-gray-900 truncate" title="{{ $booking->room?->roomType?->name }}">
                                            {{ $booking->room?->roomType?->name ?? '—' }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5 truncate">
                                            Room {{ $booking->room?->room_number ?? 'N/A' }}
                                        </div>
                                    </td>

                                    {{-- Dates Column --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-900">
                                            {{ $booking->start_at?->format('M j, Y g:i A') ?? '—' }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ $booking->end_at?->format('M j, Y g:i A') ?? '—' }}
                                        </div>
                                    </td>

                                    {{-- Status Column --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize
                                            @if(($booking->status ?? '') === 'pending')  bg-yellow-50 text-yellow-800 ring-yellow-600/20
                                            @elseif(($booking->status ?? '') === 'approved') bg-green-50  text-green-800  ring-green-600/20
                                            @else bg-gray-50 text-gray-800 ring-gray-600/20 @endif">
                                            {{ str_replace('_', ' ', $booking->status ?? 'unknown') }}
                                        </span>
                                    </td>

                                    {{-- Action Column --}}
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('frontoffice', ['tab' => 'bookings', 'booking' => $booking->id]) }}"
                                           class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors
                                                   {{ $selectedBookingId == $booking->id ? 'bg-pink-700' : 'bg-pink-600 hover:bg-pink-700' }}">
                                            {{ $selectedBookingId == $booking->id ? 'Active' : 'Edit' }}
                                        </a>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-gray-400 font-medium italic">
                                        No bookings found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </section>

            {{-- RIGHT: Edit Form Data View Panel --}}
            <section class="lg:col-span-5 xl:col-span-4 min-w-0 w-full bg-white rounded-xl border border-gray-200 p-5 shadow-sm h-fit">

                <h2 class="text-lg font-semibold text-gray-900">Selected Party</h2>
                <p class="mb-4 text-sm text-gray-500">Edit guest details and booking information before saving.</p>

                @unless ($selected)
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-400 font-medium italic bg-gray-50/50">
                        Select a booking to start editing.
                    </div>
                @else
                    <form method="POST" action="{{ route('frontoffice.update', $selected) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-medium text-gray-700">First name</label>
                            <input name="guest_fname"
                                   value="{{ old('guest_fname', $selected->user?->fname) }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20"
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last name</label>
                            <input name="guest_lname"
                                   value="{{ old('guest_lname', $selected->user?->lname) }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="guest_email"
                                   value="{{ old('guest_email', $selected->user?->email) }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20"
                                   required>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start</label>
                                <input type="datetime-local" name="start_at"
                                       value="{{ old('start_at', $selected->start_at?->format('Y-m-d\TH:i')) }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20"
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End</label>
                                <input type="datetime-local" name="end_at"
                                       value="{{ old('end_at', $selected->end_at?->format('Y-m-d\TH:i')) }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20"
                                       required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Room</label>
                            <select name="room_id"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20"
                                    required>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" @selected($selected->room_id == $room->id)>
                                        {{ $room->roomType?->name }} — Room {{ $room->room_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Guests</label>
                                <input type="number" min="1" name="guests"
                                       value="{{ old('guests', $selected->guests) }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20"
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Extra beds</label>
                                <input type="number" min="0" max="2" name="extra_beds"
                                       value="{{ old('extra_beds', $selected->extra_beds) }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20">
                            </div>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-gray-100">
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer select-none">
                                    <input type="checkbox" name="has_child" value="1"
                                           @checked($selected->has_child)
                                           class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500/20">
                                    Has child
                                </label>
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer select-none">
                                    <input type="checkbox" name="has_pwd" value="1"
                                           @checked($selected->has_pwd)
                                           class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500/20">
                                    PWD guest
                                </label>
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer select-none">
                                    <input type="checkbox" name="has_senior" value="1"
                                           @checked($selected->has_senior)
                                           class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500/20">
                                    Senior guest
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Child age group</label>
                                <input name="child_age_group"
                                       value="{{ old('child_age_group', $selected->child_age_group) }}"
                                       placeholder="e.g., Toddler, 5–10 yrs"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                            <a href="{{ route('frontoffice') }}"
                               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition">
                                Save changes
                            </button>
                        </div>

                    </form>
                @endunless

            </section>

        </div>

    {{-- ============================= --}}
    {{-- TAB: RECEIPTS                 --}}
    {{-- ============================= --}}
    @else

        <section class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm min-w-0 w-full">
            <h2 class="text-lg font-semibold text-gray-900">Payment Receipt Verification</h2>

            <form method="POST"
                  action="{{ route('frontoffice.receipts.store') }}"
                  enctype="multipart/form-data"
                  class="mt-4 flex flex-col sm:flex-row items-start sm:items-end gap-4 rounded-xl border border-dashed border-gray-300 p-4 bg-gray-50/50">
                @csrf
                <div class="w-full sm:flex-1">
                    <label class="block text-sm font-medium text-gray-700">Upload receipt</label>
                    <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required
                           class="mt-1 block w-full cursor-pointer text-sm text-gray-600
                                  file:mr-3 file:rounded-md file:border-0 file:bg-pink-50
                                  file:px-3 file:py-2 file:text-xs file:font-semibold
                                  file:text-pink-700 file:hover:bg-pink-100 transition-colors">
                </div>
                <button type="submit"
                        class="w-full sm:w-auto rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 transition">
                    Upload
                </button>
            </form>

            @if ($receipts->isEmpty())
                <div class="mt-6 rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-400 font-medium italic">
                    No receipt uploads found.
                </div>
            @else
                <div class="mt-6 overflow-x-auto rounded-lg border border-gray-100 w-full min-w-0">
                    <table class="min-w-full divide-y divide-gray-200 text-sm table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="w-[45%] px-4 py-3.5 text-left font-semibold text-gray-700">File</th>
                                <th scope="col" class="w-[25%] px-4 py-3.5 text-left font-semibold text-gray-700">Updated</th>
                                <th scope="col" class="w-[15%] px-4 py-3.5 text-left font-semibold text-gray-700">Size</th>
                                <th scope="col" class="w-[15%] px-4 py-3.5 text-right font-semibold text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($receipts as $file)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-4 py-3.5 font-medium text-gray-900 truncate max-w-0" title="{{ $file->original_filename }}">
                                        {{ $file->original_filename }}
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-500 whitespace-nowrap">
                                        {{ $file->updated_at?->format('M j, Y g:i A') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-500 whitespace-nowrap">
                                        {{ number_format($file->size / 1024, 1) }} KB
                                    </td>
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('frontoffice.receipt', $file) }}"
                                           target="_blank"
                                           class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-500 transition">
                                            View
                                        </a>
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
