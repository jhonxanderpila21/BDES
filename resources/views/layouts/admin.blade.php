<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - BDES')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/bdes-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
{{-- Sidebar starts collapsed below the `lg` breakpoint so the mobile overlay is not painted on load. --}}
<body 
    class="bg-[#FAFAF9] text-[#1F2937] antialiased" 
    x-data="{ 
        sidebarOpen: window.innerWidth >= 1024,
        sidebarWidth: parseInt(localStorage.getItem('bdes_sidebar_width')) || 256,
        isResizing: false,
        startResize(e) {
            this.isResizing = true;
            const startX = e.clientX;
            const startWidth = this.sidebarWidth;
            const doResize = (moveEvent) => {
                if (!this.isResizing) return;
                const newWidth = Math.min(Math.max(startWidth + (moveEvent.clientX - startX), 200), 420);
                this.sidebarWidth = newWidth;
                localStorage.setItem('bdes_sidebar_width', newWidth);
            };
            const stopResize = () => {
                this.isResizing = false;
                window.removeEventListener('mousemove', doResize);
                window.removeEventListener('mouseup', stopResize);
            };
            window.addEventListener('mousemove', doResize);
            window.addEventListener('mouseup', stopResize);
        },
        resetSidebarWidth() {
            this.sidebarWidth = 256;
            localStorage.setItem('bdes_sidebar_width', 256);
        },
        init() {
            window.addEventListener('resize', () => {
                if (window.innerWidth < 1024 && this.sidebarOpen && !this.isResizing) {
                    this.sidebarOpen = false;
                }
            });
        }
    }"
    :class="{'cursor-col-resize select-none': isResizing}"
>
    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        @include('admin.partials.sidebar')

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            {{-- Top Navigation Bar --}}
            @include('admin.partials.navbar')


            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto">
                <div class="px-3 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 w-full max-w-full">
                    <x-flash-messages />

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
