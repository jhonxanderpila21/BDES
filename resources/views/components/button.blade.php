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
    'primary' => 'bg-[#2563EB] text-white hover:bg-blue-700 focus:ring-blue-500 shadow-sm',
    'secondary' => 'bg-white border border-[#E5E7EB] text-[#1F2937] hover:bg-[#FAFAF9] focus:ring-gray-300',
    'danger' => 'bg-[#DC2626] text-white hover:bg-red-700 focus:ring-red-500 shadow-sm',
    'success' => 'bg-[#2E7D32] text-white hover:bg-green-700 focus:ring-green-500 shadow-sm',
    'warning' => 'bg-[#F59E0B] text-white hover:bg-amber-600 focus:ring-yellow-500 shadow-sm',
    'outline' => 'border-2 border-[#2563EB] text-[#2563EB] hover:bg-blue-50 focus:ring-blue-500',
    'ghost' => 'text-[#6B7280] hover:bg-gray-100 focus:ring-gray-300',
    default => 'bg-[#2563EB] text-white hover:bg-blue-700 focus:ring-blue-500 shadow-sm'
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
