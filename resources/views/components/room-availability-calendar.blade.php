@props([
    'availability' => [],
    'rooms' => [],
    'roomTypes' => [],
])

@php
    $calendarRooms = collect($rooms)->map(function ($room) {
        $roomData = is_array($room) ? $room : (method_exists($room, 'toArray') ? $room->toArray() : (array) $room);
        
        $typeId = null;
        $typeName = '';

        if (!empty($roomData['room_type'])) {
            $typeId = $roomData['room_type']['id'] ?? null;
            $typeName = $roomData['room_type']['name'] ?? '';
        } elseif (!empty($roomData['roomType'])) {
            $typeId = $roomData['roomType']['id'] ?? null;
            $typeName = $roomData['roomType']['name'] ?? '';
        } elseif (!empty($roomData['room_type_id'])) {
            $typeId = $roomData['room_type_id'];
        }

        return [
            'id' => $roomData['id'] ?? null,
            'room_number' => $roomData['room_number'] ?? null,
            'room_type' => $typeId ? ['id' => $typeId, 'name' => $typeName] : null,
        ];
    })->values()->all();

    $cleanRoomTypes = collect($roomTypes)->map(function ($type) {
        $typeData = is_array($type) ? $type : (method_exists($type, 'toArray') ? $type->toArray() : (array) $type);
        return [
            'id' => $typeData['id'] ?? null,
            'name' => $typeData['name'] ?? $typeData['type_name'] ?? 'Standard Unit',
        ];
    })->values()->all();
@endphp

@once
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('roomAvailabilityCalendar', (config) => ({
            availability: config.availability || [],
            rooms: config.rooms || [],
            roomTypes: config.roomTypes || [],
            
            selectedRoomTypeId: null,
            selectedMonth: null,
            selectedYear: null,
            
            monthLabels: [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ],

            init() {
                // FIXED: Direct structural assignment using raw primitive config fallbacks on boot
                this.selectedMonth = parseInt(config.initialMonth);
                this.selectedYear = parseInt(config.initialYear);

                if (config.isLivewire) {
                    this.$watch('$wire.availability', (value) => {
                        this.availability = value;
                    });
                    this.$watch('$wire.selectedMonth', (value) => {
                        if (value !== undefined) this.selectedMonth = parseInt(value);
                    });
                    this.$watch('$wire.selectedYear', (value) => {
                        if (value !== undefined) this.selectedYear = parseInt(value);
                    });
                }
            },

            get daysInMonth() {
                if (this.selectedMonth === null || this.selectedYear === null) return [];
                const count = new Date(this.selectedYear, this.selectedMonth + 1, 0).getDate();
                return Array.from({ length: count }, (_, i) => i + 1);
            },

            getFormattedTargetDate(day) {
                const monthStr = String(this.selectedMonth + 1).padStart(2, '0');
                const dayStr = String(day).padStart(2, '0');
                return `${this.selectedYear}-${monthStr}-${dayStr}`;
            },

            toHumanReadableDate(dateString) {
                if (!dateString) return '—';
                const parts = dateString.split('-');
                if (parts.length !== 3) return dateString;

                const year = parts[0];
                const monthIndex = parseInt(parts[1], 10) - 1;
                const day = parseInt(parts[2], 10);

                return `${this.monthLabels[monthIndex] || ''} ${String(day).padStart(2, '0')}, ${year}`;
            },

            getBookingDetails(day) {
                if (!this.selectedRoomTypeId || this.selectedMonth === null || this.selectedYear === null) return null;

                const targetRoomIds = this.rooms
                    .filter(r => r.room_type && r.room_type.id === this.selectedRoomTypeId)
                    .map(r => r.id);

                if (targetRoomIds.length === 0) return null;

                const targetDateString = this.getFormattedTargetDate(day);

                return this.availability.find(booking => {
                    if (!targetRoomIds.includes(booking.room_id)) return false;
                    const startStr = booking.start ? booking.start.substring(0, 10) : '';
                    const endStr = booking.end ? booking.end.substring(0, 10) : '';
                    return targetDateString >= startStr && targetDateString <= endStr;
                }) || null;
            },

            isBooked(day) {
                return this.getBookingDetails(day) !== null;
            },

            onDayClick(day) {
                console.log(`Clicked Day: ${this.getFormattedTargetDate(day)}`);
            }
        }));
    });
</script>
@endonce

<div
    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition"
    data-reservation-url="{{ route('reservation') }}"
    data-tab="{{ $tab ?? request('tab','pending') }}"
    x-data="roomAvailabilityCalendar({
        availability: $wire.entangle('availability'),
        rooms: @js($calendarRooms),
        roomTypes: @js($cleanRoomTypes),
        initialMonth: @js($this->selectedMonth),
        initialYear: @js($this->selectedYear),
        isLivewire: true
    })"
>
    <div class="mb-6 flex items-center justify-between border-b border-gray-50 pb-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Room Type Availability Matrix</h2>
            <p class="text-xs text-gray-400 mt-0.5">Real-time occupancy status for confirmed reservations.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-500 ring-1 ring-inset ring-gray-500/10">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live Data Sync</span>
        </span>
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative min-w-[160px]">
            {{-- FIXED: Added explicit x-model binding connection directly synchronized alongside Livewire mutation listeners --}}
            <select
                x-model.number="selectedMonth"
                @change="$wire.set('selectedMonth', selectedMonth)"
                class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-teal-500 transition cursor-pointer"
            >
                <template x-for="(label, index) in monthLabels" :key="index">
                    <option :value="index" x-text="label" :selected="selectedMonth === index" class="font-medium text-gray-900"></option>
                </template>
            </select>
        </div>
        <div>
            {{-- FIXED: Two-way pipeline updates for manual numbers adjustment inputs --}}
            <input
                type="number"
                x-model.number="selectedYear"
                @input.debounce.500ms="$wire.set('selectedYear', selectedYear)"
                class="w-32 rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm font-bold text-gray-700 outline-none focus:ring-2 focus:ring-teal-500 transition"
            >
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-100 pb-5">
        <template x-for="type in roomTypes" :key="type.id">
            <button
                type="button"
                @click="selectedRoomTypeId = selectedRoomTypeId === type.id ? null : type.id"
                class="rounded-xl border px-4 py-2.5 text-xs font-bold uppercase tracking-wider shadow-sm transition active:scale-[0.98] cursor-pointer"
                :class="selectedRoomTypeId === type.id
                    ? 'border-teal-600 bg-teal-600 text-white' 
                    : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                x-text="type.name"
            ></button>
        </template>
    </div>

    <template x-if="!selectedRoomTypeId">
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-10 text-center text-gray-400">
            <i class="ti ti-click text-2xl text-gray-300 mb-1"></i>
            <p class="text-sm font-medium italic">Select a lodging variant above to populate the availability matrix grids.</p>
        </div>
    </template>

    <template x-if="selectedRoomTypeId">
        <div>
            <div class="grid grid-cols-7 gap-2 select-none text-sm">
                <template x-for="day in daysInMonth" :key="day">
                    <div class="relative group">
                        <div
                            class="rounded-xl border p-3.5 text-center transition duration-150 flex flex-col items-center justify-center gap-1 min-h-[56px] cursor-pointer"
                            :class="isBooked(day)
                                ? 'border-red-200 bg-red-50 text-red-700 font-semibold'
                                : 'border-emerald-100 bg-emerald-50/60 text-emerald-800 font-medium'"
                            @click="onDayClick(day)"
                        >
                            <span class="text-xs tracking-tight" x-text="day"></span>
                            <span class="text-[9px] uppercase tracking-widest font-bold block opacity-75" 
                                  x-text="isBooked(day) ? 'Full' : 'Open'"></span>
                        </div>

                        <template x-if="getBookingDetails(day)">
                            <div class="absolute bottom-full left-1/2 z-50 mb-2 w-52 -translate-x-1/2 scale-95 opacity-0 transition-all duration-200 pointer-events-none group-hover:scale-100 group-hover:opacity-100">
                                <div class="rounded-lg bg-gray-900 p-2.5 text-xs text-white shadow-xl ring-1 ring-black/10">
                                    <div class="font-bold tracking-wide uppercase border-b border-gray-700 pb-1 mb-1 text-[9px] flex justify-between items-center">
                                        <span>Allocation Info</span>
                                        <span class="px-1 rounded font-black text-[8px] bg-red-500 text-white"
                                              x-text="getBookingDetails(day).status.replace('_', ' ')"></span>
                                    </div>
                                    <div class="font-medium text-gray-200" x-text="getBookingDetails(day).user_name"></div>
                                    
                                    <div class="text-[10px] text-gray-400 font-medium mt-1 space-y-0.5">
                                        <div>
                                            <span class="text-gray-500 font-mono text-[9px]">IN:</span> 
                                            <span x-text="toHumanReadableDate(getBookingDetails(day).start)"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 font-mono text-[9px]">OUT:</span> 
                                            <span x-text="toHumanReadableDate(getBookingDetails(day).end)"></span>
                                        </div>
                                    </div>
                                    <div class="absolute top-full left-1/2 h-2 w-2 -translate-x-1/2 -translate-y-1 rotate-45 bg-gray-900"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            
            <div class="mt-4 flex items-center justify-end gap-4 text-xs font-medium text-gray-500 px-1">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-emerald-100 border border-emerald-200 inline-block"></span>
                    <span>Vacant / Available</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-red-50 border border-red-200 inline-block"></span>
                    <span>Reserved / Occupied</span>
                </div>
            </div>
        </div>
    </template>
</div>