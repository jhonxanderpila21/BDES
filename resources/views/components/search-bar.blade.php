@props([
    'placeholder' => 'Search...',
    'class' => ''
])

<div class="relative {{ $class }}">
    <input 
        type="text" 
        placeholder="{{ $placeholder }}"
        class="w-full px-4 py-2 pl-10 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
        {{ $attributes }}
    >
    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
</div>
