<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BDES') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/bdes-logo.png') }}">

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 min-h-screen">
        <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
            {{-- Ambient background decorations based on dashboard blue theme --}}
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-36 -right-36 w-96 h-96 bg-blue-100/60 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-36 -left-36 w-96 h-96 bg-indigo-100/50 rounded-full blur-3xl"></div>
            </div>

            <div class="relative z-10 w-full max-w-md">
                {{-- BDES Brand Header --}}
                <div class="text-center mb-8">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3.5 group">
                        <img src="{{ asset('images/bdes-logo.png') }}" alt="BDES Logo" class="w-14 h-14 object-contain rounded-full shadow-sm transition-transform group-hover:scale-105">
                        <div class="text-left">
                            <h1 class="text-2xl font-bold tracking-tight text-gray-900 leading-tight">BDES</h1>
                            <p class="text-xs font-medium text-gray-500">Disaster Asset Management</p>
                        </div>
                    </a>
                </div>

                {{-- Card Container --}}
                <div class="bg-white border border-gray-200/80 shadow-lg shadow-blue-500/5 rounded-2xl p-8 sm:p-10">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                <p class="text-center text-xs text-gray-500 mt-6">
                    &copy; {{ date('Y') }} Barangay Disaster Emergency Services. All rights reserved.
                </p>
            </div>
        </div>
    </body>
</html>

