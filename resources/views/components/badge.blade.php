@props([
    'variant' => 'primary',
    'size' => 'md',
    'class' => ''
])

@php
$baseClass = 'inline-flex items-center font-semibold rounded-full';

$variantClass = match($variant) {
    'primary' => 'bg-blue-100 text-[#2563EB]',
    'secondary' => 'bg-gray-100 text-[#6B7280]',
    'success' => 'bg-green-100 text-[#2E7D32]',
    'danger' => 'bg-red-100 text-[#DC2626]',
    'warning' => 'bg-amber-100 text-[#B45309]',
    'info' => 'bg-blue-50 text-[#2563EB] border border-[#60A5FA]/40',
    default => 'bg-blue-100 text-[#2563EB]'
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
