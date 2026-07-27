<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BDES - Barangay Disaster Asset Inventory')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <div class="bg-blue-600 text-white p-3 rounded-lg">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">BDES</h1>
                <p class="text-gray-600 mt-1">Barangay Disaster Asset Inventory</p>
            </div>

            {{-- Content --}}
            <div class="bg-white rounded-lg shadow-md p-8 border border-gray-200">
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
