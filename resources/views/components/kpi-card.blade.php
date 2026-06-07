@props([
    'label',
    'value',
    'sub' => null,
    'icon',
    'iconBg',
    'iconText',
])

<div {{ $attributes->merge(['class' => 'flex items-start gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-[0_1px_4px_rgba(0,0,0,0.04)] hover:shadow-sm transition duration-200']) }}>
    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl {{ $iconBg }} shadow-sm transition duration-150">
        <i class="ti {{ $icon }} text-xl {{ $iconText }}"></i>
    </div>
    
    <div class="min-w-0 flex-1">
        <p class="mb-1 text-[11px] font-bold uppercase tracking-widest text-gray-400">{{ $label }}</p>
        <p class="text-2xl font-bold leading-tight tabular-nums text-gray-900 tracking-tight">{{ $value }}</p>
        @if ($sub)
            <p class="mt-1 truncate text-xs font-medium text-gray-400 flex items-center gap-1">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-200 shrink-0"></span>
                <span>{{ $sub }}</span>
            </p>
        @endif
    </div>
</div>