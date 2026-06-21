@php
    use App\Support\DashboardPresenter;

    $authUser = auth()->user();
    // FIXED: Swapped the undefined method fullName() for the matching full_name attribute property
    $userName = $authUser?->full_name ?? 'Guest';
    $userEmail = $authUser?->email ?? '';
    $initials = mb_strtoupper(mb_substr(collect(explode(' ', $userName))->filter()->map(fn ($p) => mb_substr($p, 0, 1))->join(''), 0, 2));
    $roleLabel = DashboardPresenter::roleLabel($authUser?->role ?? 'user');
@endphp

<div class="relative" x-data="{ open: false, minimized: false }">
    {{-- Minimized Avatar Pill Trigger --}}
    <button type="button" x-show="minimized" x-cloak @click="minimized = false" title="{{ $userName }}"
            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-pink-400 to-pink-500 text-sm font-bold text-white shadow-sm hover:opacity-95 transition-all transform active:scale-95">
        {{ $initials }}
    </button>

    {{-- Main Topbar Action Trigger Header --}}
    <div x-show="!minimized" x-cloak>
        <button type="button" @click="open = !open"
                class="flex items-center gap-3 rounded-xl px-2 py-1.5 transition hover:bg-pink-50/60 focus:outline-none">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-semibold text-gray-800 leading-tight">{{ $userName }}</p>
                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $roleLabel }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-pink-400 to-pink-500 text-sm font-bold text-white shadow-sm ring-2 ring-white">
                {{ $initials }}
            </div>
        </button>

        {{-- Backdrop Layer Dismissal Target --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-40" @click="open = false"></div>

        {{-- Menu Option Dropdown Panel Card --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute right-0 top-full z-50 mt-2 w-72 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl shadow-pink-200/40">
            
            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-4 py-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Account Options</h3>
                <button type="button" @click="minimized = true; open = false" class="text-gray-400 hover:text-pink-600 transition-colors" title="Minimize Control">
                    <span class="text-lg font-bold leading-none">-</span>
                </button>
            </div>

            <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-4">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-pink-400 to-pink-500 text-lg font-bold text-white shadow-sm">
                    {{ $initials }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-gray-900">{{ $userName }}</p>
                    <p class="truncate text-xs text-gray-500 font-medium">
                        <x-private-email :email="$userEmail" fallback="no-email" />
                    </p>
                    <div class="mt-1 flex items-center gap-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                        <span class="text-[10px] font-bold text-green-600 uppercase tracking-wide">Verified Desk Profile</span>
                    </div>
                </div>
            </div>

            <div class="py-1 bg-white">
                <a href="{{ route('profile') }}"
                   class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-gray-700 transition hover:bg-pink-50/50 hover:text-pink-700">
                    My Profile
                </a>
                <div class="border-t border-gray-100 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50/50">
                        Logout
                    </button>
                </form>
            </div>

            <div class="border-t border-gray-100 bg-gray-50 px-4 py-2.5 text-[11px] text-gray-400 font-medium truncate">
                <span>Signed in as </span><x-private-email :email="$userEmail" fallback="no-email" />
            </div>
        </div>
    </div>
</div>