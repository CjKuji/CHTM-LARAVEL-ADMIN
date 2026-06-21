@extends('layouts.app')

@section('title', 'Audit & Reports')

@section('content')
@php use App\Support\DashboardPresenter; @endphp
<header class="border-b border-gray-100 bg-white px-6 py-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
    <div class="mx-auto flex max-w-screen-2xl items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-600">
            <i class="ti ti-chart-pie text-xl text-white"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Audit & Reports</h1>
            <p class="text-xs text-gray-400">Financial audit · Occupancy · Guest analytics · System trail</p>
        </div>
    </div>
</header>

<div class="mx-auto max-w-screen-2xl space-y-5 p-4 sm:p-6">
    <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Period</label>
            <select name="period" class="rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500">
                @foreach(['daily','monthly','quarterly','annual'] as $p)
                    <option value="{{ $p }}" @selected($period===$p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Year</label>
            <input type="number" name="year" value="{{ $year }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm w-24 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>
        <button class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-teal-700">Apply</button>
        <span class="ml-auto text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1.5 rounded-lg">{{ $dateLabel }}</span>
    </form>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([['Total Revenue', '₱'.number_format($summary['total_revenue'],2)], ['Bookings', $summary['total_bookings']], ['Checked Out', $summary['checked_out']], ['Occupancy', $summary['occupancy_rate'].'%']] as [$label, $value])
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 tracking-tight">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex overflow-x-auto border-b border-gray-100 bg-gray-50/60">
            @foreach (['sales' => 'Sales & Revenue', 'occupancy' => 'Room Occupancy', 'guests' => 'Guest Statistics', 'logs' => 'Audit Logs'] as $id => $label)
                <a href="{{ route('audit', array_merge(request()->except('tab'), ['tab' => $id])) }}" 
                   class="whitespace-nowrap px-6 py-3.5 text-sm font-medium transition duration-150 {{ $tab === $id ? 'border-b-2 border-teal-600 text-teal-700 bg-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100/50' }}">
                   {{ $label }}
                </a>
            @endforeach
        </div>
        
        <div class="p-6">
            @if ($tab === 'sales')
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Guest</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Room Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Total Charged</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Payment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($archivedRows as $row)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-4 py-3 text-gray-900 font-medium">{{ trim(($row->guest_fname ?? '').' '.($row->guest_lname ?? '')) ?: 'Archived Guest' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->room_type_name }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-semibold">₱{{ number_format((float)$row->total_amount, 2) }}</td>
                                    <td class="px-4 py-3"><span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 uppercase tracking-wider">{{ $row->payment_method }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-400">No transactions recorded in this scope.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif ($tab === 'occupancy')
                <div class="rounded-xl border border-teal-100 bg-teal-50/30 p-5 text-sm text-teal-900">
                    <div class="flex items-center gap-2 font-medium">
                        <i class="ti ti-info-circle text-base text-teal-700"></i>
                        <span>Occupancy Window Scope Analysis</span>
                    </div>
                    <p class="mt-2 text-gray-600">Current occupancy rate within this target block is evaluated at <strong class="text-teal-900">{{ $summary['occupancy_rate'] }}%</strong>. The highest generating quarters context is currently flagged under: <strong class="text-teal-900">{{ $summary['top_room_type'] }}</strong>.</p>
                </div>
            @elseif ($tab === 'guests')
                <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                    @foreach($guestStats as $k => $v)
                        <div class="rounded-xl bg-gray-50 p-4 border border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ str_replace('_',' ', $k) }}</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1 tracking-tight">{{ $v }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="space-y-1">
                    @forelse ($auditLogs as $log)
                        @php $style = DashboardPresenter::actionStyle($log->action); @endphp
                        <div class="flex items-center gap-3 border-b border-gray-100 py-3 last:border-0 hover:bg-gray-50/40 px-2 rounded-lg transition">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $style['bg'] }}">
                                <i class="ti {{ $style['icon'] }} {{ $style['text'] }} text-sm"></i>
                            </div>
                            <p class="flex-1 text-sm text-gray-700">{{ DashboardPresenter::actionLabel($log->action, $log->entity_type) }} <span class="font-semibold text-gray-900">#{{ $log->entity_id }}</span></p>
                            <span class="text-xs text-gray-400 font-medium">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 gap-2 text-center text-sm text-gray-400">
                            <i class="ti ti-activity text-2xl text-gray-300"></i>
                            <span>No audit trail operations logged in this period.</span>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>
@endsection