<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BDES - Barangay Disaster Asset Inventory')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/bdes-logo.png') }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            {{-- Content --}}
            <div class="bg-white rounded-lg shadow-md p-8 border border-gray-200">
                {{-- Header --}}
                <div class="text-center mb-6 pb-6 border-b border-gray-100">
                    <div class="inline-flex items-center justify-center gap-3.5">
                        <img src="{{ asset('images/bdes-logo.png') }}" alt="BDES Logo" class="w-14 h-14 object-cover aspect-square rounded-full shadow-sm">
                        <div class="text-left">
                            <h1 class="text-2xl font-bold tracking-tight text-gray-900 leading-tight">BDES</h1>
                            <p class="text-xs font-medium text-gray-500">Barangay Disaster Asset Inventory</p>
                        </div>
                    </div>
                </div>
                @if($errors->any())
                    <div class="mb-6">
                        <x-alert type="error">
                            <strong>Error!</strong>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </x-alert>
                    </div>
                @endif

                @if(session('status'))
                    <div class="mb-6">
                        <x-alert type="success">
                            {{ session('status') }}
                        </x-alert>
                    </div>
                @endif

                @yield('content')
            </div>

            {{-- Footer --}}
            <p class="text-center text-gray-600 text-sm mt-8">
                © 2024 Barangay Disaster Asset Inventory System. All rights reserved.
            </p>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
