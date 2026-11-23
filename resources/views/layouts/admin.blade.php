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
    @include('partials.layout-scripts')
    <style>
        body {
            font-size: 14px !important;
            line-height: 1.5;
        }
        main,
        main p,
        main span,
        main td,
        main th,
        main label,
        main input,
        main select,
        main textarea,
        main button,
        main a,
        aside nav a,
        aside nav button,
        aside nav span,
        .text-xs,
        .text-sm,
        .text-\[10px\],
        .text-\[11px\],
        .text-\[12px\],
        .text-\[13px\],
        .text-\[14px\],
        .text-\[15px\] {
            font-size: 14px !important;
            line-height: 1.45 !important;
        }
        main h1, .text-3xl, .text-2xl {
            font-size: 1.5rem !important;
            line-height: 2rem !important;
        }
        main h2, .text-xl {
            font-size: 1.25rem !important;
            line-height: 1.75rem !important;
        }
        main h3, .text-lg, .text-base {
            font-size: 1.05rem !important;
            line-height: 1.5rem !important;
        }
        main table th,
        main table td {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
    </style>
</head>
{{-- Sidebar starts collapsed below the `lg` breakpoint so the mobile overlay is not painted on load. --}}
<body 
    class="bg-[#FAFAF9] text-[#1F2937] antialiased" 
    x-data="adminLayout"
    :class="{'cursor-col-resize select-none': isResizing}"
>
    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            {{-- Top Navigation Bar --}}
            @include('partials.navbar')


            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto overflow-x-hidden">
                <div class="px-3 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 w-full max-w-full min-w-0">
                    <x-flash-messages />

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Global Delete Confirmation Security Modal --}}
    <x-delete-confirm-modal />

    @include('partials.searchable-select-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[method="GET"], form[method="get"]').forEach(function (form) {
                let debounceTimer = null;
                form.querySelectorAll('select, input[type="date"], input[type="checkbox"], input[type="radio"]').forEach(function (el) {
                    if (!el.getAttribute('onchange')) {
                        el.addEventListener('change', function () {
                            form.submit();
                        });
                    }
                });
                form.querySelectorAll('input[type="text"], input[type="search"], input[type="number"]').forEach(function (el) {
                    el.addEventListener('input', function () {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(function () {
                            form.submit();
                        }, 500);
                    });
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
