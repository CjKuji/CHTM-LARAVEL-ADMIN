<div 
    x-data="{ open: false, hasData: false }" 
    @open-archive-modal.window="open = true; hasData = false;"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-xs"
>
    {{-- Background backdrop interceptor: closes instantly on tap --}}
    <div class="absolute inset-0" @click="open = false; hasData = false; $wire.resetModalState()"></div>

    <div 
        class="relative min-h-[350px] max-h-[85vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl border border-gray-100 flex flex-col z-10"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
    >
        {{-- Structural Loader Display: Visible instantly when clicked until state matches fully --}}
        <div x-show="!hasData" wire:loading.flex class="absolute inset-0 bg-white/95 flex items-center justify-center z-50 rounded-2xl">
            <div class="flex flex-col items-center space-y-2.5">
                <svg class="animate-spin h-7 w-7 text-pink-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-bold text-gray-500 tracking-wide">Syncing Ledger Status Matrix...</span>
            </div>
        </div>

        @if($booking)
            {{-- Flags Alpine to drop the loader block as soon as the model yields valid blade compilation output --}}
            <div x-init="hasData = true" class="contents" id="printable-folio-card">
                
                {{-- Modal Header Section --}}
                <div class="mb-4 flex items-start justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight md:text-lg">
                            Archived Record Verified #{{ $booking->original_booking_id ?? $booking->id }}
                        </h3>
                        <p class="text-[11px] font-bold text-gray-400 mt-0.5">Historical Read-Only Database Archive Ledger</p>
                    </div>
                    <button @click="open = false; hasData = false; $wire.resetModalState()" type="button" class="text-gray-400 hover:text-gray-600 rounded-lg p-1 hover:bg-gray-50 transition print-hide cursor-pointer" aria-label="Close modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Split Content Fields Info Information Layout Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs text-gray-600 mb-4">
                    
                    {{-- Left column block: Stay data items --}}
                    <div class="space-y-2.5 bg-gray-50/50 p-3.5 rounded-xl border border-gray-100">
                        <h4 class="font-black text-gray-800 uppercase tracking-wider text-[10px] border-b border-gray-200/50 pb-1">Stay Information</h4>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400 font-bold">Status:</span>
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider
                                @if(($booking->status ?? 'archived') === 'pending') bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20
                                @elseif(($booking->status ?? 'archived') === 'approved') bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/20
                                @elseif(($booking->status ?? 'archived') === 'checked_in') bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20
                                @elseif(($booking->status ?? 'archived') === 'checked_out' || ($booking->status ?? 'archived') === 'archived') bg-teal-50 text-teal-800 ring-1 ring-inset ring-teal-600/20
                                @elseif(($booking->status ?? 'archived') === 'cancelled') bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20
                                @else bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/10 @endif">
                                {{ str_replace('_', ' ', $booking->status ?? 'archived') }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-400 font-bold">Room Assigned:</span>
                            <span class="font-black text-teal-800 bg-teal-50 px-1.5 py-0.5 rounded text-[10px]">#{{ $booking->room_number ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-400 font-bold">Room Type:</span>
                            <span class="text-gray-900 font-bold text-right">{{ $booking->room_type_name ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-400 font-bold">Occupancy Capacity:</span>
                            <span class="text-gray-900 font-bold">{{ $booking->guests ?? 1 }} Registered</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-400 font-bold">Extra Rollaway Beds:</span>
                            <span class="text-gray-900 font-bold">{{ $booking->extra_beds ?? 0 }} Allocated</span>
                        </div>

                        <div class="border-t border-gray-100 pt-2 space-y-1">
                            <div class="flex flex-col">
                                <span class="text-gray-400 font-bold text-[10px]">CHECK-IN TIMEFRAME</span>
                                <span class="font-mono text-gray-800 font-bold text-[11px]">{{ $booking->start_at ? $booking->start_at->format('M d, Y h:i A') : '—' }}</span>
                            </div>
                            <div class="flex flex-col pt-1">
                                <span class="text-gray-400 font-bold text-[10px]">CHECK-OUT TIMEFRAME</span>
                                <span class="font-mono text-gray-500 font-medium text-[11px]">{{ $booking->end_at ? $booking->end_at->format('M d, Y h:i A') : '—' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right column block: Guest register stats summary items --}}
                    <div class="space-y-2.5 bg-gray-50/50 p-3.5 rounded-xl border border-gray-100 flex flex-col justify-between">
                        <div class="space-y-2.5">
                            <h4 class="font-black text-gray-800 uppercase tracking-wider text-[10px] border-b border-gray-200/50 pb-1">Guest & Ledger Registry</h4>
                            
                            <div class="flex flex-col">
                                <span class="text-gray-400 font-bold">Registered Guest Name:</span>
                                <span class="text-gray-900 font-black text-sm mt-0.5">{{ trim(($booking->guest_fname ?? '').' '.($booking->guest_lname ?? '')) ?: 'Archived Guest' }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-gray-400 font-bold">Email Address:</span>
                                <span class="text-gray-700 font-bold select-all font-mono text-[11px] break-all mt-0.5">
                                    @php
                                        // Restructured custom decryption engine workflow wrapper safely matching the primary modal logic context block
                                        $resolvedEmail = $booking->guest_email ?? $booking->user->email ?? null;

                                        if (filled($resolvedEmail) && !str_contains((string)$resolvedEmail, '@')) {
                                            try {
                                                $resolvedEmail = \App\Services\Encryption\Aes256GcmEncrypter::fromConfiguration()->decrypt((string)$resolvedEmail);
                                            } catch (\Exception $e) {
                                                try {
                                                    $resolvedEmail = \Illuminate\Support\Facades\Crypt::decryptString((string)$resolvedEmail);
                                                } catch (\Exception $ex) {
                                                    $resolvedEmail = 'Encrypted (Verification Failed)';
                                                }
                                            }
                                        }
                                    @endphp
                                    {{ $resolvedEmail ?? 'no-linked-account@system.internal' }}
                                </span>
                            </div>

                            <div class="flex justify-between border-t border-gray-100 pt-2">
                                <span class="text-gray-400 font-bold">Payment Method:</span>
                                <span class="uppercase font-mono text-[10px] font-black bg-white px-1.5 py-0.5 rounded border border-gray-200/60 text-gray-800 shadow-3xs">{{ $booking->payment_method ?? 'N/A' }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-400 font-bold">Base Nightly Price:</span>
                                <span class="text-gray-900 font-bold">₱{{ number_format((float) ($booking->room_base_price ?? 0), 2) }}</span>
                            </div>

                            <div class="flex justify-between items-baseline bg-white p-2 rounded-lg border border-gray-100 shadow-3xs">
                                <span class="text-gray-500 font-black text-[11px] uppercase tracking-wide">Gross Total Amount:</span>
                                <span class="text-teal-600 font-black text-base">₱{{ number_format((float) ($booking->total_amount ?? 0), 2) }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-200/60 grid grid-cols-3 gap-1 text-[10px] text-center font-bold">
                            <span class="px-1 py-0.5 rounded-md border {{ $booking->has_child ? 'bg-purple-50 border-purple-200 text-purple-700' : 'bg-gray-50/50 border-gray-200/40 text-gray-400' }}">Child</span>
                            <span class="px-1 py-0.5 rounded-md border {{ $booking->has_pwd ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-gray-50/50 border-gray-200/40 text-gray-400' }}">PWD</span>
                            <span class="px-1 py-0.5 rounded-md border {{ $booking->has_senior ? 'bg-orange-50 border-orange-200 text-orange-700' : 'bg-gray-50/50 border-gray-200/40 text-gray-400' }}">Senior</span>
                        </div>
                    </div>
                </div>

                {{-- Guest note block layout render window option --}}
                @if(!empty($booking->message))
                    <div class="mb-4 p-3 bg-blue-50/30 rounded-xl border border-blue-100/40 text-xs shadow-3xs">
                        <span class="text-blue-900 font-black uppercase text-[10px] tracking-wider block mb-0.5">Guest Notes & Comments:</span>
                        <span class="italic text-gray-600 font-medium">"{{ $booking->message }}"</span>
                    </div>
                @endif

                {{-- Accountability Clearance Tracking Map --}}
                <div class="mb-4 p-3.5 bg-gray-50/50 border border-gray-100 rounded-xl space-y-2.5 text-xs text-gray-500">
                    <h4 class="font-black text-gray-800 uppercase tracking-wider text-[10px] border-b border-gray-200/50 pb-1">System Operational Staff Clearances</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-400 font-bold">Approved By:</span>
                            <span class="text-gray-800 font-black">{{ $approvedByName ?? 'System Automation Pipeline' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 font-bold">Checked-In By:</span>
                            <span class="text-gray-800 font-black">{{ $checkedInByName ?? '—' }}</span>
                        </div>
                        @if(($booking->status ?? '') === 'rejected' || $rejectedByName)
                            <div class="flex justify-between sm:col-span-2 border-t border-gray-100 pt-1.5">
                                <span class="text-red-400 font-bold">Rejection Handle Signature:</span>
                                <span class="text-red-700 font-black">{{ $rejectedByName ?? '—' }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between @if(!($booking->status === 'rejected' || $rejectedByName)) sm:col-span-2 border-t border-gray-100 pt-1.5 @endif">
                            <span class="text-gray-400 font-bold">Checked-Out By:</span>
                            <span class="text-gray-800 font-black">{{ $checkedOutByName ?? '—' }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-gray-200/60 text-[11px]">
                        <div class="flex flex-col">
                            <span class="text-gray-400 font-bold text-[10px]">PHYSICAL ENTRY SYSTEM TIMESTAMP</span>
                            <span class="font-mono text-gray-700 font-bold">{{ $booking->checked_in_at ? $booking->checked_in_at->format('M d, Y h:i A') : '—' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-gray-400 font-bold text-[10px]">PHYSICAL DEPARTURE SYSTEM TIMESTAMP</span>
                            <span class="font-mono text-gray-700 font-bold">{{ $booking->checked_out_at ? $booking->checked_out_at->format('M d, Y h:i A') : '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Footer Action Bar Interaction Layout --}}
                <div class="flex justify-end border-t border-gray-100 pt-3.5 print-hide">
                    <button type="button" onclick="window.print()" class="w-full sm:w-auto px-5 py-2.5 bg-pink-600 text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-pink-700 shadow-xs transition active:scale-98 cursor-pointer text-center">
                        Print Folio Invoice
                    </button>
                </div>

            </div>
        @endif
    </div>
</div>