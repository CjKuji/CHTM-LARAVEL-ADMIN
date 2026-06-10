@php
    $user = auth()->user();
    $role = $user?->role ?? 'user';
    $isAdmin = $user?->isAdmin() ?? false;

    $menuItems = [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => '⊞', 'roles' => ['super_admin', 'admin', 'reservation', 'frontoffice', 'housekeeper']],
        ['id' => 'frontoffice', 'label' => 'Front Office', 'icon' => '🏢', 'roles' => ['super_admin', 'admin', 'frontoffice']],
        ['id' => 'reservation', 'label' => 'Reservation', 'icon' => '📅', 'roles' => ['super_admin', 'admin', 'reservation']],
        ['id' => 'archived', 'label' => 'Archived', 'icon' => '🗄️', 'roles' => ['super_admin', 'admin', 'frontoffice']],
        ['id' => 'room', 'label' => 'Room', 'icon' => '🏠', 'roles' => ['super_admin', 'admin', 'frontoffice', 'housekeeper']],
        ['id' => 'audit', 'label' => 'Audit & Reports', 'icon' => '📊', 'roles' => ['super_admin', 'admin', 'frontoffice']],
        ['id' => 'settings', 'label' => 'System Settings', 'icon' => '⚙', 'roles' => ['super_admin', 'admin']],
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
@endphp

{{-- A. MOBILE DRAWER OVERLAY --}}
<div x-show="mobileOpen" x-cloak class="md:hidden relative z-50">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="mobileOpen = false"></div>
    <aside class="fixed bottom-0 left-0 top-0 w-72 bg-gradient-to-b from-teal-950 to-teal-900 text-white shadow-2xl flex flex-col">
        <div class="flex items-center justify-between border-b border-white/10 p-4 flex-shrink-0">
            <div>
                <h1 class="text-lg font-bold tracking-tight text-white">CHTM RRS</h1>
                <p class="text-xs text-teal-300/80 font-medium">Hotel Management</p>
            </div>
            <button type="button" @click="mobileOpen = false" class="text-white/60 hover:text-white p-1 rounded-lg transition" aria-label="Close menu">✕</button>
        </div>
        <nav class="flex-1 space-y-1 p-3 overflow-y-auto">
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
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ $active ? 'bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-md' : 'text-teal-100 hover:bg-teal-800/60 hover:text-white' }}">
                    <span class="text-lg flex-shrink-0 w-6 text-center">{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>
</div>

{{-- B. PERSISTENT DESKTOP SIDEBAR --}}
<aside class="desktop-sidebar hidden h-screen bg-gradient-to-b from-teal-950 to-teal-900 text-white shadow-xl md:flex flex-col flex-shrink-0"
       :class="sidebarCollapsed ? 'w-20' : 'w-64'">
    
    <div class="flex items-center justify-between border-b border-white/10 p-4 h-16 flex-shrink-0">
        <div x-show="!sidebarCollapsed" x-cloak class="min-w-0">
            <h1 class="text-lg font-bold tracking-tight truncate">CHTM RRS</h1>
            <p class="text-xs text-teal-300/80 font-medium truncate">Hotel Management</p>
        </div>
        <div x-show="sidebarCollapsed" x-cloak class="text-xl mx-auto">🏨</div>
        <button type="button" @click="toggleSidebar()" class="text-white/60 hover:text-white p-1 rounded-lg transition flex-shrink-0" aria-label="Toggle sidebar panel">
            <span class="font-bold text-sm" x-text="sidebarCollapsed ? '→' : '←'"></span>
        </button>
    </div>

    <nav class="flex-1 space-y-1 p-3 overflow-y-auto">
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
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200 {{ $active ? 'bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-md' : 'text-teal-100 hover:bg-teal-800/60 hover:text-white' }}"
               :class="sidebarCollapsed ? 'justify-center' : ''"
               title="{{ $item['label'] }}">
                <span class="text-lg flex-shrink-0 w-6 text-center">{{ $item['icon'] }}</span>
                <span class="text-sm font-medium truncate" x-show="!sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-3 text-center text-xs text-teal-400 font-medium flex-shrink-0">
        <span x-show="!sidebarCollapsed" x-cloak>Version 2.0.0</span>
        <span x-show="sidebarCollapsed" x-cloak>v2.0</span>
    </div>
</aside>