<div 
    x-data="{ open: false, hasData: false }" 
    @open-archive-modal.window="open = true; hasData = false;"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
>
    {{-- Background backdrop interceptor: closes instantly on tap --}}
    <div class="absolute inset-0" @click="open = false; hasData = false; $wire.resetModalState()"></div>

    <div 
        class="relative min-h-[350px] max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl border border-gray-100 flex flex-col z-10"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
    >
        {{-- Structural Loader Display: Visible instantly when clicked until state matches fully --}}
        <div x-show="!hasData" wire:loading.flex class="absolute inset-0 bg-white/90 flex items-center justify-center z-50 rounded-2xl">
            <div class="flex flex-col items-center space-y-2">
                <svg class="animate-spin h-8 w-8 text-pink-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-bold text-gray-500 tracking-wide">Syncing Ledger Status Matrix...</span>
            </div>
        </div>

        @if($booking)
            {{-- Flags Alpine to drop the loader block as soon as the model yields valid blade compilation output --}}
            <div x-init="hasData = true" class="contents" id="printable-folio-card">
                
                <div class="mb-5 flex items-start justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 tracking-tight">
                            Archived Record Verified #{{ $booking->original_booking_id ?? $booking->id }}
                        </h3>
                        <p class="text-xs font-medium text-gray-400 mt-0.5">Historical Read-Only Database Archive Ledger</p>
                    </div>
                    <button @click="open = false; hasData = false; $wire.resetModalState()" type="button" class="text-gray-400 hover:text-gray-600 rounded-lg p-1 hover:bg-gray-50 transition print-hide cursor-pointer">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                    {{-- Stay Information Left Box Block --}}
                    <div class="space-y-3 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                        <h4 class="font-bold text-gray-800 uppercase tracking-wider text-xs text-pink-500">Stay Information</h4>
                        <p><strong>Status:</strong> 
                            <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase bg-teal-50 text-teal-800 ring-1 ring-teal-600/10">
                                {{ str_replace('_', ' ', $booking->status ?? 'archived') }}
                            </span>
                        </p>
                        <p><strong>Room Number:</strong> Room #{{ $booking->room_number ?? 'N/A' }}</p>
                        <p><strong>Room Type:</strong> {{ $booking->room_type_name ?? 'N/A' }}</p>
                        <p><strong>Guests:</strong> {{ $booking->guests ?? 1 }} Registered</p>
                        <p><strong>Extra Beds:</strong> {{ $booking->extra_beds ?? 0 }} Allocated</p>
                        <p><strong>Check-In Window:</strong> <span class="text-xs font-mono font-medium text-gray-700">{{ $booking->start_at ? $booking->start_at->format('M d, Y h:i A') : '—' }}</span></p>
                        <p><strong>Check-Out Window:</strong> <span class="text-xs font-mono font-medium text-gray-700">{{ $booking->end_at ? $booking->end_at->format('M d, Y h:i A') : '—' }}</span></p>
                    </div>

                    {{-- Guest & Ledger Right Box Block --}}
                    <div class="space-y-3 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                        <h4 class="font-bold text-gray-800 uppercase tracking-wider text-xs text-pink-500">Guest & Ledger Registry</h4>
                        <p><strong>Full Name:</strong> {{ trim(($booking->guest_fname ?? '').' '.($booking->guest_lname ?? '')) ?: 'Archived Guest' }}</p>
                        <p>
                            <strong>Email Address:</strong>
                            <x-private-email :email="$booking->user->email ?? null" fallback="no-linked-account@system.internal" />
                        </p>
                        <p><strong>Payment Method:</strong> <span class="uppercase font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $booking->payment_method ?? 'N/A' }}</span></p>
                        <p><strong>Base Price:</strong> ₱{{ number_format((float) ($booking->room_base_price ?? 0), 2) }}</p>
                        <p><strong>Total Gross Amount:</strong> <span class="text-teal-600 font-bold">₱{{ number_format((float) ($booking->total_amount ?? 0), 2) }}</span></p>
                        
                        <div class="pt-2 border-t border-gray-200/60 grid grid-cols-3 gap-1 text-[11px] text-center font-medium">
                            <span class="px-1 py-0.5 rounded {{ $booking->has_child ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-400' }}">Child</span>
                            <span class="px-1 py-0.5 rounded {{ $booking->has_pwd ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}">PWD</span>
                            <span class="px-1 py-0.5 rounded {{ $booking->has_senior ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-400' }}">Senior</span>
                        </div>
                    </div>
                </div>

                @if(!empty($booking->message))
                    <div class="mb-4 p-3 bg-blue-50/40 rounded-xl border border-blue-100/50 text-xs">
                        <strong class="text-blue-900 block mb-1">Guest Comment/Message:</strong>
                        <span class="italic text-gray-600">"{{ $booking->message }}"</span>
                    </div>
                @endif

                {{-- Accountability Clearance Tracking Map --}}
                <div class="mb-6 p-4 bg-gray-50/30 border border-gray-100 rounded-xl space-y-2.5 text-xs text-gray-500">
                    <h4 class="font-bold text-gray-400 uppercase tracking-wider text-[10px] border-b pb-1">System Operational Staff Clearances</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <p><strong>Approved Process Signature:</strong> <span class="text-gray-700 font-semibold">{{ $approvedByName ?? 'System Automation Pipeline' }}</span></p>
                        <p><strong>Checked-In Process Signature:</strong> <span class="text-gray-700 font-semibold">{{ $checkedInByName ?? '—' }}</span></p>
                        @if($booking->status === 'rejected' || $rejectedByName)
                            <p><strong>Rejection Process Signature:</strong> <span class="text-red-700 font-semibold">{{ $rejectedByName ?? '—' }}</span></p>
                        @endif
                        <p><strong>Checked-Out Process Signature:</strong> <span class="text-gray-700 font-semibold">{{ $checkedOutByName ?? '—' }}</span></p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1.5 border-t border-gray-100/70 text-[11px]">
                        <p><strong>Physical Entry Log:</strong> {{ $booking->checked_in_at ? $booking->checked_in_at->format('M d, Y h:i A') : '—' }}</p>
                        <p><strong>Physical Departure Log:</strong> {{ $booking->checked_out_at ? $booking->checked_out_at->format('M d, Y h:i A') : '—' }}</p>
                    </div>
                </div>

                {{-- Footer Layout Interaction Bar --}}
                <div class="flex justify-end space-x-3 border-t border-gray-100 pt-4 print-hide">
                    <button type="button" onclick="window.print()" class="px-4 py-2.5 bg-pink-600 text-white rounded-xl text-xs font-bold uppercase tracking-wide hover:bg-pink-700 shadow-sm transition active:scale-95 cursor-pointer">
                        Print Folio Invoice
                    </button>
                    <button type="button" @click="open = false; hasData = false; $wire.resetModalState()" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-wide hover:bg-gray-200 transition active:scale-95 cursor-pointer">
                        Dismiss
                    </button>
                </div>

            </div>
        @endif
    </div>
</div>
