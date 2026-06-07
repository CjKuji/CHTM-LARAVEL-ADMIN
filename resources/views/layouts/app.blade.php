<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CHTM RRS')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        serif: ['Montserrat', 'ui-serif', 'Georgia', 'serif'],
                    },
                },
            },
        };
    </script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('head')
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased"
      x-data="{
          sidebarCollapsed: localStorage.getItem('chtm_sidebar_collapsed') === '1',
          mobileOpen: false,
          isMobile: window.innerWidth < 768,
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('chtm_sidebar_collapsed', this.sidebarCollapsed ? '1' : '0');
          }
      }"
      @resize.window="isMobile = window.innerWidth < 768">

    <div class="flex min-h-screen overflow-x-hidden">
        {{-- App Navigation Sidebar Partial Injection --}}
        @include('partials.sidebar', ['activeMenu' => $activeMenu ?? 'dashboard'])

        {{-- Main Frame Workspace Block Element --}}
        <main class="min-h-screen flex-1 transition-all duration-300 flex flex-col min-w-0"
              :class="isMobile ? 'ml-0' : (sidebarCollapsed ? 'ml-20' : 'ml-64')">
            
            {{-- Unified App Global Header Topbar --}}
            @include('partials.topbar')

            {{-- Master Application View Viewport Box Frame --}}
            <div class="pt-16 flex-1 flex flex-col">
                {{-- Session Notification Handling Alerts System --}}
                @if (session('status'))
                    <div class="mx-6 mt-4 flex items-center justify-between rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                        <div class="flex items-center gap-2">
                            <i class="ti ti-circle-check text-base flex-shrink-0"></i>
                            <span class="font-medium">{{ session('status') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-green-600 hover:text-green-900 p-1 rounded-lg transition" aria-label="Dismiss alert">
                            <i class="ti ti-x text-xs"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mx-6 mt-4 flex items-center justify-between rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                        <div class="flex items-center gap-2">
                            <i class="ti ti-alert-circle text-base flex-shrink-0"></i>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-red-600 hover:text-red-900 p-1 rounded-lg transition" aria-label="Dismiss alert">
                            <i class="ti ti-x text-xs"></i>
                        </button>
                    </div>
                @endif

                {{-- Direct Target Dashboard Module Frame Area Template Injection --}}
                <div class="p-6 flex-1">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>