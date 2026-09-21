<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Restricted | BDES</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        {{-- Shield Icon --}}
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-50 text-amber-600 mb-6 shadow-sm border border-amber-100">
            <i class="fas fa-shield-halved text-3xl"></i>
        </div>

        {{-- Error Details --}}
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Access Restricted</h1>
        <p class="mt-2 text-xs font-semibold uppercase tracking-wider text-amber-600 bg-amber-50 px-3 py-1 rounded-full inline-block border border-amber-200">
            HTTP 403 Forbidden &bull; Administrator Privileges Required
        </p>

        <p class="mt-4 text-sm text-gray-600 leading-relaxed">
            Your current account role does not have authorization to view or manage this administrative resource. If you believe this is an error, please contact your Barangay Administrator or IT Head.
        </p>

        {{-- Navigation Actions --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a
                href="{{ route('dashboard') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm"
            >
                <i class="fas fa-th-large text-xs"></i>
                <span>Return to Dashboard</span>
            </a>
            <button
                type="button"
                onclick="window.history.back()"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition shadow-sm"
            >
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Go Back</span>
            </button>
        </div>

        {{-- Footer reference --}}
        <div class="mt-10 pt-6 border-t border-gray-200 text-xs text-gray-400">
            <span>Barangay Disaster Assets Management & Monitoring System (BDES)</span>
        </div>
    </div>
</body>
</html>

