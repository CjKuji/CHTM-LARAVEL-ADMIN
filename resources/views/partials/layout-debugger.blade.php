{{-- ============================================================
     LAYOUT DEBUGGER — drop this anywhere, remove when done
     Shows live Alpine state + computed CSS values in real time
============================================================ --}}
<div x-data="{
        open: true,
        get info() {
            const aside = document.querySelector('aside');
            const canvas = document.querySelector('.main-canvas');
            const header = document.querySelector('header');
            return {
                // Alpine state (from body x-data)
                sidebarCollapsed: $root.closest('[x-data]') ? 'use $store' : '?',
                ls: localStorage.getItem('chtm_sidebar_collapsed'),

                // Aside (sidebar)
                asidePosition: aside ? getComputedStyle(aside).position : 'NOT FOUND',
                asideWidth: aside ? getComputedStyle(aside).width : 'NOT FOUND',
                asideClass: aside ? aside.className.trim() : 'NOT FOUND',

                // Main canvas
                canvasClass: canvas ? canvas.className.trim() : 'NOT FOUND',
                canvasMarginLeft: canvas ? getComputedStyle(canvas).marginLeft : 'NOT FOUND',

                // Header / topbar
                headerPosition: header ? getComputedStyle(header).position : 'NOT FOUND',
                headerLeft: header ? getComputedStyle(header).left : 'NOT FOUND',
                headerWidth: header ? getComputedStyle(header).width : 'NOT FOUND',

                // Viewport
                viewportWidth: window.innerWidth + 'px',
            };
        }
     }"
     style="position:fixed;bottom:16px;right:16px;z-index:99999;width:420px;font-family:monospace;font-size:11px;">

    {{-- Toggle button --}}
    <button @click="open = !open"
            style="width:100%;background:#1e293b;color:#f8fafc;padding:6px 12px;border-radius:8px 8px 0 0;text-align:left;border:none;cursor:pointer;font-family:monospace;font-size:11px;">
        🛠 Layout Debugger <span x-text="open ? '▼ hide' : '▲ show'"></span>
    </button>

    <div x-show="open"
         style="background:#0f172a;color:#e2e8f0;padding:12px;border-radius:0 0 8px 8px;border:1px solid #334155;border-top:none;max-height:500px;overflow-y:auto;">

        {{-- Refresh button --}}
        <button @click="$nextTick(() => {})"
                style="background:#334155;color:#94a3b8;border:none;border-radius:4px;padding:2px 8px;cursor:pointer;margin-bottom:8px;font-size:10px;">
            ↻ refresh
        </button>

        {{-- LocalStorage --}}
        <div style="margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #1e293b;">
            <div style="color:#94a3b8;margin-bottom:4px;">📦 localStorage</div>
            <div>chtm_sidebar_collapsed: <span style="color:#34d399;" x-text="ls ?? 'null'"></span></div>
        </div>

        {{-- Sidebar --}}
        <div style="margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #1e293b;">
            <div style="color:#94a3b8;margin-bottom:4px;">📌 aside (sidebar)</div>
            <div>position: <span style="color:#f472b6;" x-text="asidePosition"></span></div>
            <div>width: <span style="color:#f472b6;" x-text="asideWidth"></span></div>
            <div style="word-break:break-all;">class: <span style="color:#fbbf24;" x-text="asideClass"></span></div>
        </div>

        {{-- Main canvas --}}
        <div style="margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #1e293b;">
            <div style="color:#94a3b8;margin-bottom:4px;">🖼 .main-canvas</div>
            <div>margin-left: <span style="color:#f472b6;" x-text="canvasMarginLeft"></span></div>
            <div style="word-break:break-all;">class: <span style="color:#fbbf24;" x-text="canvasClass"></span></div>
        </div>

        {{-- Topbar --}}
        <div style="margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #1e293b;">
            <div style="color:#94a3b8;margin-bottom:4px;">🔝 header (topbar)</div>
            <div>position: <span style="color:#f472b6;" x-text="headerPosition"></span></div>
            <div>left: <span style="color:#f472b6;" x-text="headerLeft"></span></div>
            <div>width: <span style="color:#f472b6;" x-text="headerWidth"></span></div>
        </div>

        {{-- Viewport --}}
        <div>
            <div style="color:#94a3b8;margin-bottom:4px;">📐 viewport</div>
            <div>width: <span style="color:#f472b6;" x-text="viewportWidth"></span></div>
        </div>

    </div>
</div>