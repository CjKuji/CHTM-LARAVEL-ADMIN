<header class="fixed top-0 right-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white/80 px-4 backdrop-blur-md transition-all duration-300"
        :class="isMobile ? 'left-0' : (sidebarCollapsed ? 'left-20' : 'left-64')">
    <div class="flex flex-col pl-12 md:pl-0 min-w-0">
        <h1 class="text-base font-bold text-teal-950 truncate leading-tight">@yield('topbar_title', 'Admin Dashboard')</h1>
        <p class="hidden text-[11px] font-medium text-gray-400 sm:block tracking-wide mt-0.5">CHTM-RRS Hotel Management System</p>
    </div>

    <div class="flex items-center gap-3 flex-shrink-0">
        {{-- System Alerts Dropdown Controller --}}
        <div class="relative" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="relative rounded-xl p-2 text-gray-500 hover:bg-gray-100 transition-colors" aria-label="Notifications Panel">
                🔔
                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-pink-500 ring-2 ring-white"></span>
            </button>
            <div x-show="open" x-cloak @click.outside="open = false"
                 class="absolute right-0 top-12 w-80 rounded-xl border border-gray-200 bg-white p-3 shadow-xl">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider px-1">Notifications</p>
                <p class="mt-3 text-sm text-gray-500 italic text-center py-4">No new notifications</p>
            </div>
        </div>

        {{-- Isolated Shared Profile Card Inclusion --}}
        <div class="border-l border-gray-200 pl-3">
            @include('partials.profile-card')
        </div>
    </div>
</header>