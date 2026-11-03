@props([
    'type' => 'info',
    'dismissible' => true,
    'class' => ''
])

@php
$typeClass = match($type) {
    'success' => 'bg-green-50 text-[#2E7D32] border-[#2E7D32]/30',
    'error' => 'bg-red-50 text-[#DC2626] border-[#DC2626]/30',
    'warning' => 'bg-amber-50 text-[#B45309] border-[#F59E0B]/30',
    'info' => 'bg-blue-50 text-blue-900 border-[#60A5FA]',
    default => 'bg-blue-50 text-blue-900 border-[#60A5FA]'
};

$iconClass = match($type) {
    'success' => 'fas fa-check-circle text-[#2E7D32]',
    'error' => 'fas fa-exclamation-circle text-[#DC2626]',
    'warning' => 'fas fa-exclamation-triangle text-[#F59E0B]',
    'info' => 'fas fa-info-circle text-[#2563EB]',
    default => 'fas fa-info-circle text-[#2563EB]'
};
@endphp

<div class="flex items-start gap-3 p-4 border rounded-lg {{ $typeClass }} {{ $class }}" role="alert">
    <i class="{{ $iconClass }} flex-shrink-0 mt-0.5"></i>
    <div class="flex-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="flex-shrink-0 ml-2 text-current opacity-50 hover:opacity-75" onclick="this.parentElement.style.display='none';">
            <i class="fas fa-times"></i>
        </button>
    @endif
</div>
