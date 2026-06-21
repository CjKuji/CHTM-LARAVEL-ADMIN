<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full select-none">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CHTM RRS' }}</title>

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

    <style>
        [x-cloak] { display: none !important; }

        /* Smooth flex column state tracking transitions */
        .main-canvas {
            transition: max-width 300ms cubic-bezier(0.4, 0, 0.2, 1), flex-basis 300ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        .desktop-sidebar {
            transition: w-64 300ms cubic-bezier(0.4, 0, 0.2, 1), width 300ms cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>

    @livewireStyles
    @stack('head')
</head>

<body class="bg-gray-50 font-sans text-gray-900 antialiased h-screen w-screen overflow-hidden flex"
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

    {{-- SIDEBAR: Rendered cleanly side-by-side inside the body flex container --}}
    @include('partials.sidebar', ['activeMenu' => $activeMenu ?? 'dashboard'])

    {{-- MAIN CANVAS WRAPPER --}}
    <div class="main-canvas flex flex-col h-full min-w-0 flex-1 overflow-hidden">
        
        {{-- TOPBAR Component --}}
        <header class="sticky top-0 z-20 flex h-16 w-full items-center justify-between
                       border-b border-gray-200 bg-white/90 px-6 backdrop-blur-md flex-shrink-0 min-w-0">

            {{-- Left Section: Navigation Toggle Trigger & Screen Brand Headings --}}
            <div class="flex items-center gap-3 min-w-0 flex-1">
                {{-- Mobile Drawer Toggle Trigger Control Button --}}
                <button type="button"
                        @click="mobileOpen = true"
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-teal-900 text-white shadow-md md:hidden border border-teal-800 hover:bg-teal-800 transition active:scale-95"
                        aria-label="Open navigation menu">
                    ☰
                </button>

                <div class="flex flex-col min-w-0">
                    <h1 class="text-base font-bold text-teal-950 truncate leading-tight">
                        @yield('topbar_title', 'Admin Dashboard')
                    </h1>
                    <p class="hidden text-[11px] font-medium text-gray-400 sm:block tracking-wide mt-0.5 truncate">
                        CHTM-RRS Hotel Management System
                    </p>
                </div>
            </div>

            {{-- Right Section: Utilities panel controls component --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                
                {{-- Notifications Component Toggle Box --}}
                <div class="relative" x-data="{ open: false }">
                    <button type="button"
                            @click="open = !open"
                            class="relative rounded-xl p-2 text-gray-500 hover:bg-gray-100 transition-colors"
                            aria-label="Notifications">
                        🔔
                        <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-pink-500 ring-2 ring-white"></span>
                    </button>
                    <div x-show="open" x-cloak
                         @click.outside="open = false"
                         class="absolute right-0 top-12 z-50 w-80 rounded-xl border border-gray-200 bg-white p-3 shadow-xl">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider px-1">Notifications</p>
                        <p class="mt-3 text-sm text-gray-500 italic text-center py-4">No new notifications</p>
                    </div>
                </div>

                {{-- User Profile Card --}}
                <div class="border-l border-gray-200 pl-3 flex-shrink-0">
                    @include('partials.profile-card')
                </div>

            </div>
        </header>

        {{-- SCROLLABLE CONTAINER CANVAS BODY --}}
        <div class="flex-1 overflow-x-hidden overflow-y-auto w-full min-w-0 bg-gray-50/50">
            <main class="p-4 sm:p-6 w-full max-w-full min-w-0">
                @if(isset($slot) && $slot->isNotEmpty())
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>

    </div>

    @stack('scripts')
    @livewireScripts
</body>
</html>
