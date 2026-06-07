@extends('layouts.app')

@section('title', 'System Settings')
@section('topbar_title', 'System Settings')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-gray-900">Core Engine Configuration</h1>
        <p class="text-xs text-gray-500 font-medium">Calibrate environment telemetry thresholds, global flag signals, layout appearance scopes, and system access models.</p>
    </div>

    {{-- Pill Dynamic Selection Track --}}
    <div class="flex flex-wrap gap-1.5 border-b border-gray-200 pb-2">
        @foreach ([
            'notifications' => '🔔 Notification Webhooks', 
            'appearance' => '🎨 Theme Context Settings', 
            'admin' => '⚙️ Root Security Domain Parameters'
        ] as $id => $label)
            <a href="{{ route('settings', ['tab' => $id]) }}" 
               class="rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition-all {{ $activeTab === $id ? 'bg-teal-700 text-white shadow-sm' : 'bg-gray-200/60 text-gray-600 hover:bg-gray-200 hover:text-gray-900' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- System Configuration Pipeline Submission Form --}}
    <form method="POST" action="{{ route('settings.update') }}" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        <input type="hidden" name="tab" value="{{ $activeTab }}">

        @if ($activeTab === 'notifications')
            <div class="space-y-1 divide-y divide-gray-100">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Dispatched Matrix Activity Hook Rules</p>
                @foreach (['checkIns' => 'Live Front Desk Guest Check-In Actions Trigger', 'checkOuts' => 'Housekeeping Notification Room Check-out Activity Signal', 'reservations' => 'New Matrix Booking Reservation Slip Submission Hooks', 'ratings' => 'Public Feedback Evaluation Metrics Core Storage Event Updates'] as $key => $label)
                    <label class="flex items-center justify-between py-3.5 cursor-pointer group">
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">{{ $label }}</span>
                        <input type="checkbox" name="{{ $key }}" value="1" @checked($notifications[$key] ?? false) 
                               class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500/30 transition">
                    </label>
                @endforeach
            </div>

        @elseif ($activeTab === 'appearance')
            <div class="space-y-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Display Layout Layer Manifest</p>
                <label class="flex items-center justify-between py-2 border-b border-gray-50 cursor-pointer group">
                    <div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors block">Dark Theme Interface Layer</span>
                        <span class="text-[11px] text-gray-400 font-medium block mt-0.5">Render a low-light theme. Alpha preview state mode visualization logic extension framework wrapper.</span>
                    </div>
                    <input type="checkbox" name="darkMode" value="1" @checked($appearance['darkMode'] ?? false) 
                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500/30 transition">
                </label>
            </div>

        @else
            <div class="space-y-1 divide-y divide-gray-100">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Identity Safeguard Token Configuration</p>
                
                <label class="flex items-center justify-between py-3.5 cursor-pointer group">
                    <div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors block">Mandatory Multi-Factor Validation Security (2FA)</span>
                        <span class="text-[11px] text-gray-400 font-medium block mt-0.5">Enforces explicit device code verification challenge loops upon access pipeline authentication.</span>
                    </div>
                    <input type="checkbox" name="twoFactorEnabled" value="1" @checked($twoFactorEnabled) 
                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500/30 transition">
                </label>

                <label class="flex items-center justify-between py-3.5 cursor-pointer group">
                    <div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors block">Anomaly Authentication Alert Dispatch</span>
                        <span class="text-[11px] text-gray-400 font-medium block mt-0.5">Dispatches notification flags immediately if login events trace to unmapped terminal clients.</span>
                    </div>
                    <input type="checkbox" name="loginAlertEnabled" value="1" @checked($loginAlertEnabled) 
                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500/30 transition">
                </label>
            </div>
        @endif

        <div class="mt-6 border-t border-gray-100 pt-4 flex justify-end">
            <button type="submit" class="rounded-xl bg-teal-700 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-md shadow-teal-700/10 hover:bg-teal-800 active:scale-95 transition-all">
                Save Environment Configuration Update
            </button>
        </div>
    </form>
</div>
@endsection