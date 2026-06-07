@extends('layouts.app')

@section('title', 'Room Management')
@section('topbar_title', 'Room Management')

@section('content')
@php
    $stats = [
        'total' => $rooms->count(),
        'available' => $rooms->where('status', 'available')->count(),
        'occupied' => $rooms->where('status', 'occupied')->count(),
        'cleaning' => $rooms->whereIn('status', ['cleaning', 'needs_cleaning'])->count(),
    ];
    $editRoom = $editRoomId ? $rooms->firstWhere('id', $editRoomId) : null;
@endphp

<div class="space-y-6">
    {{-- Header Action Elements Block --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">Room Assets & Maintenance</h1>
            <p class="text-xs text-gray-500 font-medium">Control inventory status thresholds and housekeeping rosters. System timestamp outputs run in Philippine Standard Time (PHT).</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="document.getElementById('template-modal').classList.remove('hidden')" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-gray-600 shadow-sm hover:bg-gray-50 transition">🗒️ Manage Checklist Template</button>
            <button type="button" onclick="document.getElementById('room-modal').classList.remove('hidden')" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-md shadow-indigo-600/10 hover:bg-indigo-700 transition">+ Add New Asset Unit</button>
        </div>
    </div>

    {{-- Metrics Grid Display Row --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ([
            ['Total Rooms Listed', $stats['total'], 'text-slate-700', 'bg-white border-gray-200'], 
            ['Available For Booking', $stats['available'], 'text-emerald-700', 'bg-emerald-50/60 border-emerald-100'], 
            ['Occupied Living Space', $stats['occupied'], 'text-red-700', 'bg-red-50/60 border-red-100'], 
            ['Awaiting Housekeeping', $stats['cleaning'], 'text-amber-700', 'bg-amber-50/60 border-amber-100']
        ] as $metric)
            <div class="{{ $metric[3] }} rounded-2xl border p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $metric[0] }}</p>
                <p class="mt-1 text-2xl font-black {{ $metric[2] }} tracking-tight">{{ $metric[1] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Segmentation Selection Nav Tabs Track --}}
    <div class="flex w-fit gap-1 rounded-xl bg-gray-200/70 p-1">
        @foreach (['inventory' => '🏨 Room Asset Logs', 'housekeeping' => '🧹 Operational Housekeeping'] as $key => $label)
            <a href="{{ route('room', ['tab' => $key]) }}" 
               class="rounded-lg px-5 py-2 text-xs font-bold uppercase tracking-wider transition-all {{ $tab === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($tab === 'inventory')
        {{-- SECTION GRID LOGS INDEX AREA FRAME --}}
        @if ($rooms->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-16 text-center text-gray-400">
                <div class="mb-3 text-5xl">🏨</div>
                <p class="text-sm font-semibold">No operational physical assets registered in current schema.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($rooms as $room)
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition duration-200">
                        <div class="space-y-4 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 leading-tight">Room {{ $room->room_number }}</h3>
                                    <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $room->roomType?->name ?? 'Unlinked Type Blueprint' }}</p>
                                </div>
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-gray-600 border">
                                    {{ str_replace('_', ' ', $room->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-5 py-3">
                            <div class="flex gap-4">
                                <a href="{{ route('room', ['tab' => 'inventory', 'edit' => $room->id]) }}" class="text-xs font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-800">Edit Config</a>
                                <form method="POST" action="{{ route('room.destroy', $room) }}" onsubmit="return confirm('Confirm total removal deletion of this resource asset entry row data?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold uppercase tracking-wider text-red-500 hover:text-red-700">Erase Unit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        {{-- SECTION HOUSEKEEPING ROSTER INTERFACES FRAME --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($rooms as $room)
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="space-y-4 p-5">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 leading-tight">Room {{ $room->room_number }}</h3>
                            <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $room->roomType?->name }}</p>
                        </div>
                        <div class="space-y-2 pt-2">
                            @foreach ([
                                'do_not_disturb' => '🚫 Do Not Disturb Status', 
                                'make_up_room' => '🛏️ Request Make Up Room', 
                                'checkout_requested' => '🏃 Checkout Target Flag'
                            ] as $flag => $label)
                                <form method="POST" action="{{ route('room.flag', $room) }}">
                                    @csrf
                                    <input type="hidden" name="flag" value="{{ $flag }}">
                                    <button type="submit" class="w-full rounded-xl px-3 py-2 text-xs font-bold uppercase tracking-wider text-left border transition {{ ($flag === 'do_not_disturb' && $room->status === 'do_not_disturb') || ($flag !== 'do_not_disturb' && $room->{$flag}) ? 'bg-teal-600 text-white border-teal-600' : 'bg-gray-50 text-gray-700 hover:bg-gray-100' }}">
                                        {{ $label }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Dynamic Live Task Execution Tracking Rows Layout Segment --}}
        <div class="mt-8 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-400">Live Task Manifest by Room Blueprint Grid</h2>
            @foreach ($tasks->groupBy(fn ($t) => $t->room?->roomType?->name ?? 'Other Unmapped Configuration Blueprint Group') as $group => $groupTasks)
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/70 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-900 tracking-tight">{{ $group }}</h3>
                        <span class="rounded-full bg-white border px-2.5 py-0.5 text-xs font-bold text-gray-500 shadow-sm">{{ $groupTasks->count() }} active items</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($groupTasks as $task)
                            <div class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50/40 transition">
                                <div>
                                    <div class="text-sm font-bold text-gray-900">Room {{ $task->room?->room_number ?? 'Unknown' }}</div>
                                    <div class="text-xs text-gray-400 font-medium capitalize mt-0.5">Status: {{ str_replace('_', ' ', $task->status) }}</div>
                                </div>
                                <div class="flex gap-2">
                                    @if ($task->status === 'pending')
                                        <form method="POST" action="{{ route('housekeeping.start', $task) }}">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-blue-700">Start Cleaning</button>
                                        </form>
                                    @endif
                                    @if ($task->status === 'in_progress')
                                        <form method="POST" action="{{ route('housekeeping.complete', $task) }}">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-amber-600">Complete & Close</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- MODAL CONTAINER SLIP DRAWER A: ROOM MUTATION FACTORY --}}
<div id="room-modal" class="{{ $editRoom ? 'fixed flex' : 'hidden' }} inset-0 z-50 items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border">
        <h3 class="mb-4 text-base font-bold text-gray-900 tracking-tight border-b pb-2">{{ $editRoom ? 'Update Registered Asset Registry Configuration' : 'Append New Unit Frame Asset' }}</h3>
        <form method="POST" action="{{ $editRoom ? route('room.update', $editRoom) : route('room.store') }}" class="space-y-4">
            @csrf 
            @if($editRoom) 
                @method('PUT') 
            @endif
            
            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Class Blueprints Category</label>
                <select name="room_type_id" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:bg-white focus:outline-none" required>
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}" {{ ($editRoom && $editRoom->room_type_id == $type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Room Unit ID String Marker</label>
                <input name="room_number" placeholder="e.g. 301" value="{{ old('room_number', $editRoom ? $editRoom->room_number : '') }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:bg-white focus:outline-none" required>
            </div>

            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Floor Level Assignment Matrix Index</label>
                <input name="floor" type="number" placeholder="e.g. 3" value="{{ old('floor', $editRoom ? $editRoom->floor : '') }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Initial Asset Matrix Condition State</label>
                <select name="status" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:bg-white focus:outline-none">
                    @foreach(['available','occupied','needs_cleaning','cleaning','maintenance','do_not_disturb'] as $s)
                        <option value="{{ $s }}" {{ (($editRoom ? $editRoom->status : 'available') === $s) ? 'selected' : '' }}>{{ str_replace('_',' ', $s) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-6 flex justify-end gap-2 border-t pt-4">
                <button type="button" onclick="document.getElementById('room-modal').classList.add('hidden'); window.location.href=`{{ route('room', ['tab' => 'inventory']) }}`;" class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Dismiss Panel</button>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-600/10 hover:bg-indigo-700 transition">Commit Matrix Schema</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL CONTAINER SLIP DRAWER B: CHECKLIST ROUTINE BLUEPRINT ENGINE --}}
<div id="template-modal" class="hidden inset-0 z-50 items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border">
        <h3 class="mb-4 text-base font-bold text-gray-900 tracking-tight border-b pb-2">Generate Operational Cleanliness Routine Template</h3>
        <form method="POST" action="{{ route('housekeeping.templates.store') }}" class="space-y-4">
            @csrf
            
            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Checklist Roster Blueprint Identity Name</label>
                <input name="name" placeholder="e.g. Standard Turnover Protocol Matrix" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:bg-white focus:outline-none" required>
            </div>

            <div>
                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Assign Target Class Blueprint Association</label>
                <select name="room_type_id" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:bg-white focus:outline-none">
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Required Sequential Validation Action Operations Items</label>
                @for ($i = 0; $i < 4; $i++)
                    <input name="items[]" placeholder="Operational Action Checklist Target Item #{{ $i + 1 }}" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-xs focus:bg-white focus:outline-none">
                @endfor
            </div>

            <div class="mt-6 flex justify-end gap-2 border-t pt-4">
                <button type="button" onclick="document.getElementById('template-modal').classList.add('hidden')" class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Discard Creation</button>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-600/10 hover:bg-indigo-700 transition">Save Roster Setup</button>
            </div>
        </form>
    </div>
</div>
@endsection