@extends('layouts.app')

@section('title', 'Audit & Reports')

@section('content')
@php use App\Support\DashboardPresenter; @endphp

{{-- Safe CSS Print Utilities --}}
<style type="text/css">
@media print {
    /* 1. Target and hide the master layout components safely without generic element naming conflicts */
    body > nav,
    body > header,
    body > aside,
    #sidebar,
    .sidebar,
    .no-print,
    .tab-navigation-wrapper,
    button,
    .flex.items-center.gap-2 {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        visibility: hidden !important;
    }

    /* 2. Style anchor rule targeting the unique report wrapper element header */
    .report-print-header {
        display: flex !important;
        border-bottom: 2px solid #e5e7eb !important;
        padding-bottom: 1rem !important;
        margin-bottom: 1.5rem !important;
    }

    /* 3. Re-align full grid and table structural frames */
    body, 
    main,
    .mx-auto,
    .main-dashboard-layout,
    div[class*="main"],
    div[class*="content"] {
        background: #fff !important;
        color: #000 !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        position: relative !important;
        transform: none !important;
        top: 0 !important;
        left: 0 !important;
    }

    /* 4. Formatting tweaks for report page grids */
    .print-card {
        border: 1px solid #e5e7eb !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
    
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
}
</style>

<div class="main-dashboard-layout">
    {{-- Printable Custom Header block for title and brand identity logo --}}
    <div class="report-print-header border-b border-gray-200 bg-white px-6 py-5 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
        <div class="mx-auto flex max-w-screen-2xl w-full flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                {{-- Brand Logo Element Integration (Directly located at public/logo.png) --}}
                <div class="shrink-0">
                    <img src="{{ asset('logo.png') }}" alt="CHTM RRS Logo" class="h-12 w-auto object-contain">
                </div>
                
                <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>

                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-600 shadow-sm shadow-teal-600/20">
                        <i class="ti ti-chart-pie text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-900">Audit & Reports</h1>
                        <p class="text-xs font-medium text-gray-500 mt-0.5">Financial ledger audit · Occupancy tracking · Guest analytics · Immutable system trail</p>
                    </div>
                </div>
            </div>
            
            {{-- Action Matrix: Print Action Trigger --}}
            <div class="flex items-center gap-2 no-print">
                <button onclick="window.print();" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 active:bg-gray-100">
                    <i class="ti ti-printer text-base text-gray-500"></i>
                    <span>Print Report</span>
                </button>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-screen-2xl space-y-6 p-4 sm:p-6 lg:p-8">
        
        {{-- Search Filter Form Parameters Wrapper (Hidden automatically on PDF print layout) --}}
        <form method="GET" class="no-print flex flex-col sm:flex-row sm:items-end gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <div class="w-full sm:w-auto min-w-[160px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Reporting Period</label>
                <div class="relative">
                    <select name="period" class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium text-gray-800 transition focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                        @foreach(['daily','monthly','quarterly','annual'] as $p)
                            <option value="{{ $p }}" @selected($period===$p)>{{ ucfirst($p) }} Summary</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="w-full sm:w-auto min-w-[120px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Target Year</label>
                <input type="number" name="year" value="{{ $year }}" min="2020" max="2035" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium text-gray-800 transition focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20">
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button class="w-full sm:w-auto rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-teal-600/10 transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500/50 active:bg-teal-800">
                    Apply Filters
                </button>
            </div>
            
            <div class="sm:ml-auto flex items-center gap-2 self-start sm:self-center bg-gray-50 border border-gray-100 rounded-xl px-4 py-2">
                <span class="h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
                <span class="text-xs font-semibold text-gray-600 tracking-wide uppercase">{{ $dateLabel }}</span>
            </div>
        </form>

        {{-- Performance High-Level KPI Summary Grid --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Total Revenue', '₱'.number_format($summary['total_revenue'], 2), 'ti-currency-peso', 'text-emerald-600 bg-emerald-50'], 
                ['Gross Bookings', $summary['total_bookings'], 'ti-book', 'text-blue-600 bg-blue-50'], 
                ['Checked Out', $summary['checked_out'], 'ti-logout', 'text-orange-600 bg-orange-50'], 
                ['Occupancy Rate', $summary['occupancy_rate'].'%', 'ti-door-enter', 'text-purple-600 bg-purple-50']
            ] as [$label, $value, $icon, $colorScheme])
                <div class="print-card rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md/5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ $label }}</p>
                            <p class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $value }}</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $colorScheme }}">
                            <i class="ti {{ $icon }} text-lg"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Main Ledger Reporting Segment Area Container --}}
        <div class="print-card overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            
            {{-- Internal Module Tab Filter Toggles --}}
            <div class="tab-navigation-wrapper flex overflow-x-auto border-b border-gray-200 bg-gray-50/70 px-2 pt-2">
                @foreach ([
                    'sales' => ['label' => 'Sales & Revenue', 'icon' => 'ti-report-money'], 
                    'occupancy' => ['label' => 'Room Occupancy', 'icon' => 'ti-bed'], 
                    'guests' => ['label' => 'Guest Statistics', 'icon' => 'ti-users'], 
                    'logs' => ['label' => 'Audit Trail Logs', 'icon' => 'ti-shield-lock']
                ] as $id => $meta)
                    <a href="{{ route('audit', array_merge(request()->except('tab'), ['tab' => $id])) }}" 
                       class="inline-flex items-center gap-2 whitespace-nowrap rounded-t-xl px-5 py-3.5 text-sm font-semibold transition-all duration-150 {{ $tab === $id ? 'bg-white text-teal-700 border-t border-x border-gray-200 shadow-[0_-2px_6px_rgba(0,0,0,0.02)]' : 'text-gray-500 hover:bg-gray-100/70 hover:text-gray-900' }}">
                        <i class="ti {{ $meta['icon'] }} text-base"></i>
                        <span>{{ $meta['label'] }}</span>
                    </a>
                @endforeach
            </div>
            
            <div class="p-6">
                {{-- Tab 1: Sales Ledger Grid Representation --}}
                @if ($tab === 'sales')
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50/70 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                    <th class="px-5 py-3.5 rounded-l-xl">Guest Account Profile</th>
                                    <th class="px-5 py-3.5">Assigned Room Type</th>
                                    <th class="px-5 py-3.5">Gross Amount Charged</th>
                                    <th class="px-5 py-3.5 rounded-r-xl">Settlement Method</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($archivedRows as $row)
                                    <tr class="hover:bg-gray-50/40 transition">
                                        <td class="px-5 py-4 font-semibold text-gray-900">
                                            {{ trim(($row->guest_fname ?? '').' '.($row->guest_lname ?? '')) ?: 'Archived Corporate Guest' }}
                                        </td>
                                        <td class="px-5 py-4 font-medium text-gray-600">{{ $row->room_type_name }}</td>
                                        <td class="px-5 py-4 font-bold text-gray-900">
                                            ₱{{ number_format((float)$row->total_amount, 2) }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-gray-700">
                                                {{ $row->payment_method }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center">
                                            <div class="flex flex-col items-center justify-center gap-2 text-gray-400">
                                                <i class="ti ti-receipt-off text-3xl text-gray-300"></i>
                                                <p class="font-medium text-sm">No transaction records match the specified search parameters.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                {{-- Tab 2: Occupancy Analytic Matrix Block --}}
                @elseif ($tab === 'occupancy')
                    <div class="rounded-2xl border border-teal-100 bg-teal-50/20 p-6">
                        <div class="flex items-center gap-2.5 font-bold text-teal-900">
                            <i class="ti ti-info-circle text-lg text-teal-700"></i>
                            <span class="text-sm uppercase tracking-wider">Occupancy Scope Matrix Review</span>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            The current property occupancy target rate for this reporting frame is computed at <strong class="font-bold text-teal-950">{{ $summary['occupancy_rate'] }}%</strong> capacity. High-yield production segments indicate the highest resource load parameters are locked under: <strong class="font-bold text-teal-950">{{ $summary['top_room_type'] ?? 'Standard Premium Base' }}</strong>.
                        </p>
                    </div>

                {{-- Tab 3: Guest Analytics Grid Configuration --}}
                @elseif ($tab === 'guests')
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                        @foreach($guestStats as $k => $v)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ str_replace('_',' ', $k) }}</p>
                                <p class="mt-1.5 text-2xl font-bold tracking-tight text-gray-900">{{ $v }}</p>
                            </div>
                        @endforeach
                    </div>

                {{-- Tab 4: Immutable System Audit Logs Trail Pipeline --}}
                @else
                    <div class="divide-y divide-gray-100 border-t border-gray-100">
                        @forelse ($auditLogs as $log)
                            @php $style = DashboardPresenter::actionStyle($log->action); @endphp
                            <div class="flex items-center gap-4 py-3.5 hover:bg-gray-50/30 px-2 rounded-xl transition">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $style['bg'] ?? 'bg-gray-100' }} shadow-sm">
                                    <i class="ti {{ $style['icon'] ?? 'ti-activity' }} {{ $style['text'] ?? 'text-gray-700' }} text-base"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-700 truncate">
                                        {{ DashboardPresenter::actionLabel($log->action, $log->entity_type) }}
                                        <span class="font-bold text-gray-900 tracking-tight">#{{ $log->entity_id }}</span>
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-flex items-center rounded-lg bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-400 border border-gray-100">
                                        {{ $log->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-14 gap-2 text-center text-gray-400">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400 border border-gray-100 mb-1">
                                    <i class="ti ti-activity text-2xl"></i>
                                </div>
                                <p class="font-medium text-sm">No operation logs discovered in this targeted timeline partition.</p>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection