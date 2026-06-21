@php
    $user = auth()->user();
    $role = $user?->role ?? 'user';
    $isAdmin = $user?->isAdmin() ?? false;

    $menuItems = [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['super_admin', 'admin', 'reservation', 'frontoffice', 'housekeeper']],
        ['id' => 'frontoffice', 'label' => 'Front Office', 'roles' => ['super_admin', 'admin', 'frontoffice']],
        ['id' => 'reservation', 'label' => 'Reservation', 'roles' => ['super_admin', 'admin', 'reservation']],
        ['id' => 'archived', 'label' => 'Archived', 'roles' => ['super_admin', 'admin', 'frontoffice']],
        ['id' => 'room', 'label' => 'Room', 'roles' => ['super_admin', 'admin', 'frontoffice', 'housekeeper']],
        ['id' => 'audit', 'label' => 'Audit & Reports', 'roles' => ['super_admin', 'admin', 'frontoffice']],
        ['id' => 'settings', 'label' => 'System Settings', 'roles' => ['super_admin', 'admin']],
    ];

    $hrefMap = [
        'dashboard' => route('dashboard'),
        'frontoffice' => route('frontoffice'),
        'reservation' => route('reservation'),
        'archived' => route('archived'),
        'room' => route('room'),
        'audit' => route('audit'),
        'settings' => route('settings'),
    ];

    $visibleItems = collect($menuItems)->filter(function ($item) use ($isAdmin, $role) {
        return $isAdmin || in_array($role, $item['roles'], true);
    });

    // NATIVE PHP CLOSURE VARIABLE TO SAFELY RENDER ICONS
    $renderSidebarIcon = function($id) {
        switch($id) {
            case 'dashboard':
                return '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V16zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V16z"/></svg>';
            case 'frontoffice':
                return '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>';
            case 'reservation':
                return '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
            case 'archived':
                return '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>';
            case 'room':
                return '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
            case 'audit':
                return '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            case 'settings':
                return '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
            default:
                return '';
        }
    };
@endphp

{{-- A. MOBILE DRAWER OVERLAY --}}
<div x-show="mobileOpen" x-cloak class="md:hidden relative z-50">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="mobileOpen = false"></div>
    <aside class="fixed bottom-0 left-0 top-0 w-64 bg-gradient-to-b from-teal-950 to-teal-900 text-white shadow-2xl flex flex-col">
        <div class="flex items-center justify-between border-b border-white/5 p-3.5 flex-shrink-0">
            <div>
                <h1 class="text-base font-black tracking-tight text-white">CHTM RRS</h1>
                <p class="text-[10px] text-teal-300/80 font-bold uppercase tracking-wider">Hotel Management</p>
            </div>
            <button type="button" @click="mobileOpen = false" class="text-white/40 hover:text-white p-1 rounded-lg transition" aria-label="Close menu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="flex-1 space-y-0.5 p-2 overflow-y-auto">
            @foreach ($visibleItems as $item)
                @php
                    $routeName = request()->route()?->getName();
                    $activeByRoute = match ($item['id']) {
                        'dashboard' => $routeName === 'dashboard',
                        'frontoffice' => $routeName === 'frontoffice' || $routeName === 'frontoffice.update' || $routeName === 'frontoffice.receipts.store' || $routeName === 'frontoffice.receipt',
                        'reservation' => $routeName === 'reservation' || str_starts_with((string) $routeName, 'reservation.'),
                        'archived' => $routeName === 'archived',
                        'room' => $routeName === 'room' || str_starts_with((string) $routeName, 'room.'),
                        'audit' => $routeName === 'audit',
                        'settings' => $routeName === 'settings' || $routeName === 'settings.update',
                        default => false,
                    };
                    $active = ($activeMenu ?? '') === $item['id'] || $activeByRoute;
                @endphp

                <a href="{{ $hrefMap[$item['id']] }}"
                   @click="mobileOpen = false"
                   class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-xs font-bold transition-all duration-150 {{ $active ? 'bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-xs' : 'text-teal-100 hover:bg-teal-800/40 hover:text-white' }}">
                    <span class="flex-shrink-0 text-teal-300/90">{!! $renderSidebarIcon($item['id']) !!}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>
</div>

{{-- B. PERSISTENT DESKTOP SIDEBAR --}}
<aside class="desktop-sidebar hidden h-screen bg-gradient-to-b from-teal-950 to-teal-900 text-white shadow-xs md:flex flex-col flex-shrink-0 transition-all duration-200 border-r border-white/5"
       :class="sidebarCollapsed ? 'w-14' : 'w-56'">
    
    <div class="flex items-center justify-between border-b border-white/5 p-3 h-14 flex-shrink-0">
        <div x-show="!sidebarCollapsed" x-cloak class="min-w-0 pl-1">
            <h1 class="text-sm font-black tracking-tight truncate">CHTM RRS</h1>
            <p class="text-[10px] text-teal-300/80 font-bold uppercase tracking-wider truncate">Hotel Management</p>
        </div>
        <div x-show="sidebarCollapsed" x-cloak class="mx-auto text-teal-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>
        </div>
        <button type="button" @click="toggleSidebar()" class="text-white/40 hover:text-white p-1 rounded-md transition flex-shrink-0" aria-label="Toggle sidebar panel">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" x-show="!sidebarCollapsed"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" x-show="sidebarCollapsed"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>

    <nav class="flex-1 space-y-0.5 p-2 overflow-y-auto">
        @foreach ($visibleItems as $item)
            @php
                $routeName = request()->route()?->getName();
                $activeByRoute = match ($item['id']) {
                    'dashboard' => $routeName === 'dashboard',
                    'frontoffice' => $routeName === 'frontoffice' || $routeName === 'frontoffice.update' || $routeName === 'frontoffice.receipts.store' || $routeName === 'frontoffice.receipt',
                    'reservation' => $routeName === 'reservation' || str_starts_with((string) $routeName, 'reservation.'),
                    'archived' => $routeName === 'archived',
                    'room' => $routeName === 'room' || str_starts_with((string) $routeName, 'room.'),
                    'audit' => $routeName === 'audit',
                    'settings' => $routeName === 'settings' || $routeName === 'settings.update',
                    default => false,
                };
                $active = ($activeMenu ?? '') === $item['id'] || $activeByRoute;
            @endphp

            <a href="{{ $hrefMap[$item['id']] }}"
               class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 transition-all duration-150 {{ $active ? 'bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-xs' : 'text-teal-100 hover:bg-teal-800/40 hover:text-white' }}"
               :class="sidebarCollapsed ? 'justify-center' : ''"
               title="{{ $item['label'] }}">
                <span class="flex-shrink-0 w-4 h-4 text-center {{ $active ? 'text-white' : 'text-teal-300/90' }}">{!! $renderSidebarIcon($item['id']) !!}</span>
                <span class="text-xs font-bold truncate" x-show="!sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="border-t border-white/5 p-2 text-center text-[10px] text-teal-400/80 font-bold tracking-wide flex-shrink-0">
        <span x-show="!sidebarCollapsed" x-cloak>VERSION 2.0.0</span>
        <span x-show="sidebarCollapsed" x-cloak>V2.0</span>
    </div>
</aside>