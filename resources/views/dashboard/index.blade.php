@extends('layouts.app', ['activeMenu' => 'dashboard'])

@php
    use App\Support\DashboardPresenter;

    $today = now()->timezone('Asia/Manila')->format('l, F j, Y');
    $barColor = DashboardPresenter::occupancyBarColor($roomStatus['occupancyPct']);
@endphp

@section('title', 'Dashboard')

@section('content')
    <header class="border-b border-gray-100 bg-white px-6 py-5 shadow-[0_1px_4px_rgba(0,0,0,0.04)]">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-600">
                    <i class="ti ti-layout-dashboard text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold leading-tight text-gray-900">Dashboard</h1>
                    <p class="mt-0.5 text-xs text-gray-400">
                        Welcome back,
                        <span class="font-medium text-gray-600">{{ $user->fname }}</span>
                        · {{ $today }}
                    </p>
                </div>
            </div>

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors hover:bg-gray-100">
                <i class="ti ti-refresh text-sm"></i>
                <span>Refresh</span>
            </a>
        </div>
    </header>

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <x-stat-card label="Total Rooms" :value="$roomStatus['total']" sub="All inventory"
                         icon="ti-building-skyscraper" icon-bg="bg-blue-50" icon-text="text-blue-600" />
                         
            <x-stat-card label="Occupied" :value="$roomStatus['occupied']" :sub="$roomStatus['occupancyPct'].'% occupancy'"
                         icon="ti-users" icon-bg="bg-rose-50" icon-text="text-rose-500" />
                         
            <x-stat-card label="Available" :value="$roomStatus['available']" sub="Ready for check-in"
                         icon="ti-door-enter" icon-bg="bg-teal-50" icon-text="text-teal-600" />
                         
            <x-stat-card label="Needs Cleaning" :value="$roomStatus['needsCleaning']" sub="Awaiting housekeeping"
                         icon="ti-spray" icon-bg="bg-amber-50" icon-text="text-amber-600" />
                         
            <x-stat-card label="Pending" :value="$pendingCount"
                         :sub="$checkoutsToday.' checkout'.($checkoutsToday !== 1 ? 's' : '').' today'"
                         icon="ti-calendar-time" icon-bg="bg-violet-50" icon-text="text-violet-600" />
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white px-5 py-4 shadow-[0_1px_4px_rgba(0,0,0,0.05)]">
            <span class="w-20 whitespace-nowrap text-xs font-bold uppercase tracking-wider text-gray-400">Occupancy</span>
            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full transition-all duration-700 {{ $barColor }}"
                     @style(['width' => $roomStatus['occupancyPct'] . '%'])></div>
            </div>
            <span class="text-sm font-bold tabular-nums text-gray-800">{{ $roomStatus['occupancyPct'] }}%</span>
            <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md">{{ $roomStatus['occupied'] }} / {{ $roomStatus['total'] }}</span>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-[0_1px_4px_rgba(0,0,0,0.05)]">
                <div class="mb-4 flex items-center gap-2 border-b border-gray-50 pb-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg border border-gray-100 bg-gray-50">
                        <i class="ti ti-users text-sm text-gray-500"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Currently Occupied Rooms</h2>
                </div>

                <div class="max-h-72 space-y-1 overflow-y-auto divide-y divide-gray-50/60">
                    @forelse ($occupiedRooms as $row)
                        <div class="flex items-center gap-3 rounded-xl px-2 py-3 transition hover:bg-gray-50/80">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-rose-50 border border-rose-100">
                                <span class="text-xs font-bold text-rose-500">{{ mb_strtoupper(mb_substr($row['guest_name'], 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-800">{{ $row['guest_name'] }}</p>
                                <p class="truncate text-xs text-gray-400 font-medium">{{ $row['room_type'] }} · <span class="text-gray-600 font-semibold">Room {{ $row['room_number'] }}</span></p>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <p class="text-xs font-semibold text-gray-700">
                                    {{ DashboardPresenter::fmtDate($row['start_at']) }} → {{ DashboardPresenter::fmtDate($row['end_at']) }}
                                </p>
                                <div class="mt-0.5 flex items-center justify-end gap-1">
                                    <i class="ti ti-clock text-[11px] text-gray-300"></i>
                                    <span class="text-[11px] font-medium text-gray-400">{{ $row['nights_so_far'] }} night{{ $row['nights_so_far'] !== 1 ? 's' : '' }} so far</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400 gap-2">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                                <i class="ti ti-bed text-2xl"></i>
                            </div>
                            <p class="text-sm font-medium italic">No rooms are currently occupied.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-[0_1px_4px_rgba(0,0,0,0.05)]">
                <div class="mb-4 flex items-center gap-2 border-b border-gray-50 pb-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg border border-gray-100 bg-gray-50">
                        <i class="ti ti-calendar-time text-sm text-gray-500"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Upcoming Reservations</h2>
                </div>

                <div class="max-h-72 space-y-1 overflow-y-auto divide-y divide-gray-50/60">
                    @forelse ($upcomingBookings as $row)
                        <div class="flex items-center gap-3 rounded-xl px-2 py-3 transition hover:bg-gray-50/80">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-teal-50 border border-teal-100">
                                <span class="text-xs font-bold text-teal-600">{{ mb_strtoupper(mb_substr($row['guest_name'], 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-800">{{ $row['guest_name'] }}</p>
                                <p class="truncate text-xs text-gray-400 font-medium">
                                    {{ $row['room_type'] }} · <span class="text-gray-600 font-semibold">Room {{ $row['room_number'] }}</span> · {{ $row['nights'] }} night{{ $row['nights'] !== 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <p class="text-xs font-semibold text-gray-700">{{ DashboardPresenter::fmtDate($row['start_at']) }}</p>
                                <span class="mt-1 inline-block rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider shadow-sm {{ DashboardPresenter::daysUntilColor($row['start_at']) }}">
                                    {{ DashboardPresenter::daysUntil($row['start_at']) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400 gap-2">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                                <i class="ti ti-calendar text-2xl"></i>
                            </div>
                            <p class="text-sm font-medium italic">No upcoming arrivals scheduled.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-[0_1px_4px_rgba(0,0,0,0.05)]">
            <div class="mb-4 flex items-center gap-2 border-b border-gray-50 pb-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg border border-gray-100 bg-gray-50">
                    <i class="ti ti-history text-sm text-gray-500"></i>
                </div>
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Recent System Activity</h2>
            </div>

            <div class="space-y-1">
                @forelse ($recentActivity as $log)
                    @php $style = DashboardPresenter::actionStyle($log->action); @endphp
                    <div class="flex items-center gap-3 rounded-xl px-2 py-2.5 transition hover:bg-gray-50/50">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ $style['bg'] }}">
                            <i class="ti {{ $style['icon'] }} text-sm {{ $style['text'] }}"></i>
                        </div>
                        <p class="flex-1 truncate text-sm text-gray-700">
                            {{ DashboardPresenter::actionLabel($log->action, $log->entity_type) }}
                            @if ($log->entity_id)
                                <span class="font-bold text-gray-900"> #{{ $log->entity_id }}</span>
                            @endif
                        </p>
                        <span class="flex-shrink-0 text-[11px] font-semibold tabular-nums text-gray-400 bg-gray-100 px-2 py-1 rounded-md">
                            {{ DashboardPresenter::fmtDate($log->created_at) }} · {{ DashboardPresenter::fmtTime($log->created_at) }}
                        </span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-gray-400 gap-1.5">
                        <i class="ti ti-activity text-xl text-gray-300"></i>
                        <p class="text-sm italic">No recent modifications logged.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection