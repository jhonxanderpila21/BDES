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

    @stack('scripts')
</body>
</html>
