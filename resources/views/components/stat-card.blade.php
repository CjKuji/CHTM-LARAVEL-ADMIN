@props(['title' => '', 'value' => '', 'icon' => 'ti-trending-up', 'color' => 'pink'])

@php
    // Map standard color variations dynamically for CHTM branding
    $colorMap = [
        'pink' => ['bg' => 'bg-pink-50', 'text' => 'text-pink-600'],
        'teal' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700'],
        'slate' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
    ];
    $theme = $colorMap[$color] ?? $colorMap['pink'];
@endphp

<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm flex items-center justify-between">
    <div class="space-y-2">
        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ $title }}</p>
        <h3 class="text-3xl font-bold tracking-tight text-gray-900">{{ $value }}</h3>
    </div>
    <div class="p-3 rounded-xl {{ $theme['bg'] }} {{ $theme['text'] }}">
        <i class="ti {{ $icon }} text-2xl"></i>
    </div>
</div>