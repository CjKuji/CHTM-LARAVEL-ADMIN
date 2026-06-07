@props([
    'availability' => [],
    'rooms' => [],
    'roomTypes' => [],
])

@php
    $calendarRooms = $rooms->map(fn ($room) => [
        'id' => $room->id,
        'room_number' => $room->room_number,
        'room_type' => $room->roomType ? ['id' => $room->roomType->id, 'name' => $room->roomType->name] : null,
    ])->values();
@endphp

@once
    @push('head')
        <script src="{{ asset('js/room-calendar.js') }}" defer></script>
    @endpush
@once

<div
    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition"
    x-data="roomAvailabilityCalendar({
        availability: @js($availability),
        rooms: @js($calendarRooms),
        roomTypes: @js($roomTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values()),
    })"
>
    <div class="mb-6 flex items-center justify-between border-b border-gray-50 pb-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Room Type Availability Matrix</h2>
            <p class="text-xs text-gray-400 mt-0.5">Real-time room occupancy allocations.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-500 ring-1 ring-inset ring-gray-500/10">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live Data Sync</span>
        </span>
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative min-w-[160px]">
            <select
                x-model.number="selectedMonth"
                class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm font-medium text-gray-700 outline-none focus:ring-2 focus:ring-teal-500 transition cursor-pointer"
            >
                <template x-for="(label, index) in monthLabels" :key="index">
                    <option :value="index" x-text="label" class="font-medium text-gray-900"></option>
                </template>
            </select>
        </div>
        <div>
            <input
                type="number"
                x-model.number="selectedYear"
                class="w-32 rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-teal-500 transition"
            >
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-100 pb-5">
        <template x-for="type in roomTypes" :key="type.id">
            <button
                type="button"
                @click="selectedRoomTypeId = selectedRoomTypeId === type.id ? null : type.id"
                class="rounded-xl border px-4 py-2.5 text-xs font-bold uppercase tracking-wider shadow-sm transition active:scale-[0.98]"
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
                    <div
                        class="rounded-xl border p-3.5 text-center transition duration-150 flex flex-col items-center justify-center gap-1 min-h-[56px]"
                        :class="isBooked(day)
                            ? 'border-red-200 bg-red-50 text-red-700 font-semibold'
                            : 'border-emerald-100 bg-emerald-50/60 text-emerald-800 font-medium'"
                    >
                        <span class="text-xs tracking-tight" x-text="day"></span>
                        <span class="text-[9px] uppercase tracking-widest font-bold block opacity-75" x-text="isBooked(day) ? 'Full' : 'Open'"></span>
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
                    <span>Reserved / Full</span>
                </div>
            </div>
        </div>
    </template>
</div>