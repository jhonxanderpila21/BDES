@props([
    'type' => 'info',
    'dismissible' => true,
    'class' => ''
])

@php
$typeClass = match($type) {
    'success' => 'bg-green-50 text-green-800 border-green-200',
    'error' => 'bg-red-50 text-red-800 border-red-200',
    'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
    'info' => 'bg-blue-50 text-blue-800 border-blue-200',
    default => 'bg-blue-50 text-blue-800 border-blue-200'
};

$iconClass = match($type) {
    'success' => 'fas fa-check-circle text-green-600',
    'error' => 'fas fa-exclamation-circle text-red-600',
    'warning' => 'fas fa-exclamation-triangle text-yellow-600',
    'info' => 'fas fa-info-circle text-blue-600',
    default => 'fas fa-info-circle text-blue-600'
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
