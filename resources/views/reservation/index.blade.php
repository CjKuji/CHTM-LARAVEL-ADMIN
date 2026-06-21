<div 
    x-data="{ showCalendarModal: false }" 
    @booking-updated.window="$wire.refreshComponentState()"
    class="space-y-4 max-w-(screen-2xl) mx-auto px-4 sm:px-6 lg:px-8 py-3"
>

    {{-- Real-Time Dynamic Notification Alerts --}}
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
            <div class="flex items-center justify-between rounded-xl border px-3.5 py-2.5 text-xs shadow-md transition-all duration-300 bg-emerald-50 border-emerald-200 text-emerald-800 pointer-events-auto">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-bold" x-text="alert.message"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- Top Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 pb-3.5">
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight md:text-2xl">Reservations Desk</h1>
            <p class="text-xs text-gray-500 mt-0.5">Monitor guest bookings, assign available rooms, and manage current occupancy stats.</p>
        </div>

        {{-- View Matrix Button --}}
        <button 
            type="button" 
            @click="showCalendarModal = true"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-700 shadow-xs transition-all hover:bg-gray-50 hover:text-gray-900 active:scale-98 cursor-pointer w-full sm:w-auto self-start sm:self-center"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-pink-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Availability Matrix</span>
        </button>
    </div>

    {{-- Tab Filter Navigation --}}
    <div class="rounded-xl border border-gray-200 bg-white p-1 shadow-xs">
        <div class="flex gap-0.5 overflow-x-auto scrollbar-none snap-x">
            @foreach($reservationTabs as $tabStatus => $tabLabel)
                <button type="button" wire:click="changeTab('{{ $tabStatus }}')"
                        class="whitespace-nowrap snap-clamp rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all duration-150 cursor-pointer min-w-[90px] text-center {{ $currentTab === $tabStatus ? 'bg-pink-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    {{ $tabLabel }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Main Ledger Table Section --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
        <div class="overflow-x-auto w-full">
            <table class="min-w-[1000px] w-full text-xs text-left border-collapse">
                <thead class="border-b border-gray-200 bg-gray-50/70 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th scope="col" class="p-3 pl-5 w-1/4">Guest Name</th>
                        <th scope="col" class="p-3 w-1/5">Assigned Room</th>
                        <th scope="col" class="p-3 w-1/8">Check-In</th>
                        <th scope="col" class="p-3 w-1/8">Check-Out</th>
                        <th scope="col" class="p-3 w-1/10">Status</th>
                        <th scope="col" class="p-3 w-1/8">Total Price</th>
                        <th scope="col" class="p-3 text-center pr-5 w-1/6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-bold">
                    @forelse($bookings as $bookingItem)
                        <tr class="transition-colors hover:bg-gray-50/40 {{ ($bookingItem['is_conflicted'] ?? false) ? 'bg-red-50/40 hover:bg-red-50/80' : '' }}">
                            <td class="p-3 pl-5">
                                <div class="font-black text-gray-900 text-sm">
                                    {{ $bookingItem['user']['full_name'] ?? 'Unknown Guest' }}
                                </div>
                                
                                @if(($bookingItem['is_conflicted'] ?? false))
                                    <div class="inline-flex items-center gap-1 text-[9px] font-black text-red-700 bg-red-100/80 px-1.5 py-0.5 rounded-md mt-0.5 uppercase tracking-wide">
                                        ⚠️ Schedule Conflict
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 text-gray-900">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold">{{ $bookingItem['room']['room_type']['name'] ?? $bookingItem['room']['room_type_name'] ?? '—' }}</span>
                                    <span class="inline-self-start mt-0.5 text-[10px] font-black px-1.5 py-0.5 rounded-md {{ ($bookingItem['is_conflicted'] ?? false) ? 'text-red-700 bg-red-100/60' : 'text-teal-700 bg-teal-50' }}">
                                        Room #{{ $bookingItem['room']['room_number'] ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-3 text-gray-600 font-medium">{{ $bookingItem['start_at_formatted'] ?? '—' }}</td>
                            <td class="p-3 text-gray-500 font-medium">{{ $bookingItem['end_at_formatted'] ?? '—' }}</td>
                            <td class="p-3">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider
                                    @if($bookingItem['is_conflicted'] ?? false) bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20
                                    @elseif(($bookingItem['status'] ?? '') === 'pending') bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20
                                    @elseif(($bookingItem['status'] ?? '') === 'approved') bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/20
                                    @elseif(($bookingItem['status'] ?? '') === 'checked_in') bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20
                                    @elseif(($bookingItem['status'] ?? '') === 'checked_out') bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/10
                                    @elseif(($bookingItem['status'] ?? '') === 'cancelled') bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20
                                    @elseif(($bookingItem['status'] ?? '') === 'rejected') bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20 @endif">
                                    {{ ($bookingItem['is_conflicted'] ?? false) ? 'Conflict' : ($reservationTabs[$bookingItem['status'] ?? ''] ?? str_replace('_', ' ', ($bookingItem['status'] ?? ''))) }}
                                </span>
                            </td>
                            <td class="p-3 text-gray-900 font-black text-sm">
                                @if(is_numeric($bookingItem['total_amount'] ?? null))
                                    ₱{{ number_format((float) $bookingItem['total_amount'], 2) }}
                                @else
                                    <span class="text-amber-600">{{ $bookingItem['total_amount'] ?? '0.00' }}</span>
                                @endif
                            </td>
                            <td class="p-3 pr-5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        type="button" 
                                        wire:click="viewDetails({{ $bookingItem['id'] }})"
                                        class="rounded-lg bg-gray-900 px-2.5 py-1.5 text-[11px] font-bold text-white shadow-xs hover:bg-gray-800 transition active:scale-95 cursor-pointer"
                                    >
                                        Details
                                    </button>
                                    
                                    @if(($bookingItem['status'] ?? '') === 'pending')
                                        <button type="button" wire:click="approveBooking({{ $bookingItem['id'] }})"
                                                class="rounded-lg bg-blue-600 px-2.5 py-1.5 text-[11px] font-bold text-white shadow-xs hover:bg-blue-700 transition active:scale-95 cursor-pointer">
                                            Approve
                                        </button>
                                        <button type="button" wire:click="rejectBooking({{ $bookingItem['id'] }})"
                                                class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-[11px] font-bold text-gray-700 hover:bg-gray-200 transition active:scale-95 cursor-pointer">
                                            Decline
                                        </button>
                                    @endif

                                    @if(($bookingItem['status'] ?? '') === 'approved')
                                        <button type="button" wire:click="checkInBooking({{ $bookingItem['id'] }})"
                                                class="rounded-lg bg-emerald-600 px-2.5 py-1.5 text-[11px] font-bold text-white shadow-xs hover:bg-emerald-700 transition active:scale-95 cursor-pointer">
                                            Check In
                                        </button>
                                    @endif

                                    @if(($bookingItem['status'] ?? '') === 'checked_in')
                                        <button type="button" wire:click="checkOutBooking({{ $bookingItem['id'] }})"
                                                class="rounded-lg bg-purple-600 px-2.5 py-1.5 text-[11px] font-bold text-white shadow-xs hover:bg-purple-700 transition active:scale-95 cursor-pointer">
                                            Check Out
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-gray-400 font-medium">
                                <div class="flex justify-center mb-2.5">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-black text-sm">No bookings found</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">There are no records listed inside this status category tab layout.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Availability Matrix Calendar Modal Backdrop Container --}}
    <div 
        x-show="showCalendarModal" 
        wire:ignore.self
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 lg:p-8"
        style="display: none;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs" @click="showCalendarModal = false"></div>

        <div 
            x-show="showCalendarModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full max-w-7xl max-h-[85vh] overflow-y-auto transform rounded-2xl bg-white p-4 sm:p-5 shadow-2xl transition-all border border-gray-100 z-10"
        >
            <div class="absolute top-4 right-4 z-20">
                <button 
                    @click="showCalendarModal = false" 
                    type="button" 
                    class="rounded-xl border border-gray-200 bg-white p-1.5 text-gray-400 shadow-2xs hover:text-gray-700 hover:bg-gray-50 transition active:scale-95 cursor-pointer"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
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

    {{-- Target Child Component Modal Injection --}}
    <div wire:ignore>
        <livewire:reservation-details-modal />
    </div>
</div>