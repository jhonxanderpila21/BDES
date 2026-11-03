@props([
    'status' => 'available',
    'label' => null,
    'size' => 'sm',
])

@php
$normalized = strtolower(trim((string)$status));

$badgeConfig = match($normalized) {
    'available', 'good', 'excellent', 'active', 'passed', 'completed', 'verified', 'in_stock' => [
        'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'icon' => 'fas fa-check-circle',
        'defaultLabel' => ucfirst(str_replace('_', ' ', $normalized)),
    ],
    'deployed', 'in_progress', 'scheduled' => [
        'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
        'icon' => match($normalized) {
            'deployed' => 'fas fa-truck-fast',
            'in_progress' => 'fas fa-spinner fa-spin',
            default => 'fas fa-calendar-check',
        },
        'defaultLabel' => ucfirst(str_replace('_', ' ', $normalized)),
    ],
    'under_maintenance', 'maintenance', 'fair', 'pending', 'needs_repair', 'due', 'monitoring only' => [
        'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
        'icon' => 'fas fa-wrench',
        'defaultLabel' => match($normalized) {
            'under_maintenance' => 'Under Maintenance',
            default => ucfirst(str_replace('_', ' ', $normalized)),
        },
    ],
    'out_of_service', 'poor', 'overdue', 'failed', 'not available', 'lost', 'danger' => [
        'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
        'icon' => 'fas fa-circle-exclamation',
        'defaultLabel' => match($normalized) {
            'out_of_service' => 'Out of Service',
            'not available' => 'Not Available',
            default => ucfirst(str_replace('_', ' ', $normalized)),
        },
    ],
    'retired', 'inactive', 'returned', 'archived' => [
        'bg' => 'bg-gray-100 text-gray-700 border-gray-300',
        'icon' => 'fas fa-archive',
        'defaultLabel' => ucfirst(str_replace('_', ' ', $normalized)),
    ],
    default => [
        'bg' => 'bg-gray-100 text-gray-700 border-gray-200',
        'icon' => 'fas fa-info-circle',
        'defaultLabel' => ucfirst(str_replace('_', ' ', $normalized)),
    ],
};

$sizeClass = $size === 'xs' ? 'px-2 py-0.5 text-[10px]' : ($size === 'lg' ? 'px-3.5 py-1 text-sm' : 'px-2.5 py-1 text-xs');
$displayLabel = $label ?? $badgeConfig['defaultLabel'];
@endphp

<span class="inline-flex items-center gap-1.5 font-semibold rounded-full border {{ $badgeConfig['bg'] }} {{ $sizeClass }}">
    @if($badgeConfig['icon'])
        <i class="{{ $badgeConfig['icon'] }} text-[10px]"></i>
    @endif
    <span>{{ $displayLabel }}</span>
</span>

