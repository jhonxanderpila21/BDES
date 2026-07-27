@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'class' => '',
    'icon' => null,
    'iconPosition' => 'left'
])

@php
$baseClass = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';

$variantClass = match($variant) {
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    'warning' => 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
    'outline' => 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 focus:ring-blue-500',
    'ghost' => 'text-gray-600 hover:bg-gray-100 focus:ring-gray-500',
    default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500'
};

$sizeClass = match($size) {
    'sm' => 'px-3 py-1.5 text-sm gap-2',
    'md' => 'px-4 py-2 text-base gap-2',
    'lg' => 'px-6 py-3 text-lg gap-3',
    default => 'px-4 py-2 text-base gap-2'
};
@endphp

<button 
    type="{{ $type }}" 
    class="{{ $baseClass }} {{ $variantClass }} {{ $sizeClass }} {{ $class }}"
    {{ $attributes }}
>
    @if($icon && $iconPosition === 'left')
        <i class="{{ $icon }}"></i>
    @endif
    <span>{{ $slot }}</span>
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }}"></i>
    @endif
</button>
