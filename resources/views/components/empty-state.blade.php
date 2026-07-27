@props([
    'icon' => 'fas fa-inbox',
    'title' => 'No data found',
    'message' => 'Start by adding your first item',
    'class' => ''
])

<div class="flex flex-col items-center justify-center py-12 text-center {{ $class }}">
    <div class="mb-4">
        <i class="{{ $icon }} text-6xl text-gray-300"></i>
    </div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $title }}</h3>
    <p class="text-gray-600 mb-6 max-w-md">{{ $message }}</p>
    {{ $slot }}
</div>
