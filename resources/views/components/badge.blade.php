@props([
    'variant' => 'primary',
    'size' => 'md',
    'class' => ''
])

@php
$baseClass = 'inline-flex items-center font-semibold rounded-full';

$variantClass = match($variant) {
    'primary' => 'bg-blue-100 text-blue-800',
    'secondary' => 'bg-gray-100 text-gray-800',
    'success' => 'bg-green-100 text-green-800',
    'danger' => 'bg-red-100 text-red-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'info' => 'bg-cyan-100 text-cyan-800',
    default => 'bg-blue-100 text-blue-800'
};

$sizeClass = match($size) {
    'sm' => 'px-2.5 py-0.5 text-xs',
    'md' => 'px-3 py-1 text-sm',
    'lg' => 'px-4 py-1.5 text-base',
    default => 'px-3 py-1 text-sm'
};
@endphp

<span class="{{ $baseClass }} {{ $variantClass }} {{ $sizeClass }} {{ $class }}">
    {{ $slot }}
</span>
