@php
    $classes = match ($status) {
        'pending'     => 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-600/10',
        'approved'    => 'bg-teal-50 text-teal-700 ring-1 ring-inset ring-teal-600/10', // ALIGNED: Matched with core dashboard design palette
        'checked_in'  => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/10',
        'checked_out' => 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-600/10',
        default       => 'bg-gray-100 text-gray-500 ring-1 ring-inset ring-gray-500/10',
    };
    $label = $status === 'approved' ? 'confirmed' : str_replace('_', ' ', $status);
@endphp

<span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold capitalize tracking-wide transition duration-150 {{ $classes }}">
    {{ $label }}
</span>