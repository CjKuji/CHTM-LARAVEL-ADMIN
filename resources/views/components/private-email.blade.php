@props([
    'email' => null,
    'fallback' => 'no-email',
    'class' => '',
])

@php
    $displayEmail = filled($email) ? (string) $email : $fallback;
@endphp

<button
    type="button"
    x-data="{ revealed: false }"
    @click="revealed = true"
    class="inline-flex max-w-full items-center rounded-md text-left transition focus:outline-none focus:ring-2 focus:ring-pink-500/30 {{ $class }}"
    :title="revealed ? 'Email visible' : 'Click to reveal email'"
>
    <span
        class="block max-w-full truncate transition duration-200"
        :class="revealed ? 'blur-0 select-text' : 'blur-sm select-none'"
    >
        {{ $displayEmail }}
    </span>
</button>
