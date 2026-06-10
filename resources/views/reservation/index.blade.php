<div x-data="{ showCalendarModal: false }" class="space-y-6">
    {{-- Global Notification Toast Track managed by Alpine --}}
    <div id="global-alert-container" class="fixed top-4 right-4 z-[100] space-y-2 pointer-events-none"
         x-data="{ 
            messages: [],
            addAlert(message, type) {
                const id = Date.now();
                this.messages.push({ id, message, type });
                setTimeout(() => { this.messages = this.messages.filter(m => m.id !== id) }, 4000);
            }
         }"
         @notify.window="addAlert($event.detail.message, 'success')">
        
        <template x-for="alert in messages" :key="alert.id">
            <div class="flex items-center justify-between rounded-xl border px-4 py-3 text-sm shadow-md transition-all duration-300 bg-green-50 border-green-200 text-green-800 pointer-events-auto">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-medium" x-text="alert.message"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- Header Content Canvas Topbar Summary Titles --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">Reservations Desk</h1>
            <p class="text-xs text-gray-500 font-medium">Manage bookings, room allocation matrices, and live room occupancy states.</p>
        </div>

        {{-- Calendar View Launcher Button --}}
        <button 
            type="button" 
            @click="showCalendarModal = true"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-xs transition hover:bg-gray-50 hover:text-gray-900 active:scale-95 cursor-pointer self-start sm:self-auto"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>View Availability Matrix</span>
        </button>
    </div>

    {{-- Horizontal Tab Navigation Control Track --}}
    <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm">
        <div class="flex gap-1 overflow-x-auto pb-1 sm:pb-0">
            <button type="button" wire:click="changeTab('pending')"
                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition-all cursor-pointer {{ $currentTab === 'pending' ? 'bg-pink-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                Pending Request
            </button>
            <button type="button" wire:click="changeTab('approved')"
                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition-all cursor-pointer {{ $currentTab === 'approved' ? 'bg-pink-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                Confirmed Matrix
            </button>
            <button type="button" wire:click="changeTab('checked_in')"
                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition-all cursor-pointer {{ $currentTab === 'checked_in' ? 'bg-pink-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                Checked In
            </button>
            <button type="button" wire:click="changeTab('checked_out')"
                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition-all cursor-pointer {{ $currentTab === 'checked_out' ? 'bg-pink-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                Archived History
            </button>
        </div>
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
                    @php $hasVisibleBookings = false; @endphp
                    
                    @foreach($bookings as $bookingItem)
                        @if(($bookingItem['status'] ?? '') === $currentTab)
                            @php $hasVisibleBookings = true; @endphp
                            
                            <tr class="transition-colors {{ ($bookingItem['is_conflicted'] ?? false) ? 'bg-red-50/40 opacity-70 hover:bg-red-50/60' : 'hover:bg-gray-50/60' }}">
                                <td class="p-4">
                                    <div class="font-bold text-gray-900">
                                        {{ $bookingItem['user']['full_name'] ?? 'Unknown Guest' }}
                                    </div>
                                    <div class="text-xs text-gray-400 font-medium mt-0.5">{{ $bookingItem['user']['email'] ?? 'no-email' }}</div>
                                    
                                    @if(($bookingItem['is_conflicted'] ?? false))
                                        <div class="inline-flex items-center gap-1 text-[10px] font-bold text-red-700 bg-red-100/80 px-2 py-0.5 rounded-md mt-1.5 uppercase tracking-wide">
                                            ⚠️ Blocked: Time Slot Occupied
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4 text-gray-900 font-semibold">
                                    <span>{{ ($bookingItem['room'] && $bookingItem['room']['room_type']) ? $bookingItem['room']['room_type']['name'] : '—' }}</span>
                                    <span class="ml-1 text-xs {{ ($bookingItem['is_conflicted'] ?? false) ? 'text-red-600 bg-red-100/50' : 'text-teal-600 bg-teal-50' }} px-2 py-0.5 rounded-md font-bold">#{{ $bookingItem['room'] ? $bookingItem['room']['room_number'] : 'N/A' }}</span>
                                </td>
                                <td class="p-4 text-xs text-gray-600 font-semibold">{{ $bookingItem['start_at_formatted'] ?? '—' }}</td>
                                <td class="p-4 text-xs text-gray-600">{{ $bookingItem['end_at_formatted'] ?? '—' }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold uppercase tracking-wider
                                        @if(($bookingItem['is_conflicted'] ?? false)) bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20
                                        @elseif(($bookingItem['status'] ?? '') === 'pending') bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20
                                        @elseif(($bookingItem['status'] ?? '') === 'approved') bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/20
                                        @elseif(($bookingItem['status'] ?? '') === 'checked_in') bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20
                                        @elseif(($bookingItem['status'] ?? '') === 'checked_out') bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/10 @endif">
                                        {{ ($bookingItem['is_conflicted'] ?? false) ? 'Conflicted' : str_replace('_', ' ', ($bookingItem['status'] ?? '')) }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-900 font-bold">₱{{ number_format(($bookingItem['total_amount'] ?? 0), 2) }}</td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-2">
                                      {{-- Replace your old button with this one --}}
<button 
    type="button" 
    @click="$dispatch('open-reservation-modal');"
    wire:click="viewDetails({{ $bookingItem['id'] }})"
    class="rounded-lg bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-gray-700 transition cursor-pointer"
>
    View Details
</button>
                                        
                                        @if(($bookingItem['status'] ?? '') === 'pending')
                                            @if(($bookingItem['is_conflicted'] ?? false))
                                                <button type="button" wire:click="rejectBooking({{ $bookingItem['id'] }})"
                                                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition cursor-pointer">
                                                    Reject Block
                                                </button>
                                            @else
                                                <button type="button" wire:click="approveBooking({{ $bookingItem['id'] }})"
                                                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition cursor-pointer">
                                                    Approve
                                                </button>
                                                <button type="button" wire:click="rejectBooking({{ $bookingItem['id'] }})"
                                                        class="rounded-lg bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-300 transition cursor-pointer">
                                                    Reject
                                                </button>
                                            @endif
                                        @endif

                                        @if(($bookingItem['status'] ?? '') === 'approved')
                                            <button type="button" wire:click="checkInBooking({{ $bookingItem['id'] }})"
                                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition cursor-pointer">
                                                Check In
                                            </button>
                                        @endif

                                        @if(($bookingItem['status'] ?? '') === 'checked_in')
                                            <button type="button" wire:click="checkOutBooking({{ $bookingItem['id'] }})"
                                                    class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-purple-700 transition cursor-pointer">
                                                Check Out
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    @if(!$hasVisibleBookings)
                        <tr>
                            <td colspan="7" class="p-12 text-center text-gray-400 font-medium">
                                <div class="text-3xl mb-2">📅</div>
                                No matching bookings located inside this data track segment.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Availability Matrix Calendar Modal Backdrop Canvas Wrapper --}}
    <div 
        x-show="showCalendarModal" 
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs" @click="showCalendarModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
            <div 
                x-show="showCalendarModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-5xl transform rounded-2xl bg-white p-6 shadow-2xl transition-all border border-gray-100"
            >
                <div class="absolute top-4 right-4 z-10">
                    <button 
                        @click="showCalendarModal = false" 
                        type="button" 
                        class="rounded-xl border border-gray-200 bg-white p-2 text-gray-400 shadow-2xs hover:text-gray-700 hover:bg-gray-50 transition active:scale-95 cursor-pointer"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-2">
                    <x-room-availability-calendar
                        :availability="$availability"
                        :rooms="$rooms"
                        :roomTypes="$roomTypes"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Target child layout initialization injection --}}
    <livewire:reservation-details-modal />
</div>