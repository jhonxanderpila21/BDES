@props([
    'title' => '',
    'value' => '0',
    'icon' => 'fas fa-chart-bar',
    'color' => 'blue',
    'subtitle' => null,
    'href' => null,
    'active' => false,
    'tooltip' => null,
])

@php
$iconBoxStyles = match($color) {
    'indigo' => 'bg-indigo-50 text-indigo-600',
    'purple' => 'bg-purple-50 text-purple-600',
    'emerald', 'green' => 'bg-emerald-50 text-emerald-600',
    'amber', 'yellow' => 'bg-amber-50 text-amber-600',
    'rose', 'red' => 'bg-rose-50 text-rose-600',
    default => 'bg-blue-50 text-blue-600',
};

$activeClass = $active
    ? 'ring-2 ring-blue-500 border-blue-400 shadow-sm'
    : 'border-gray-200 hover:border-blue-300';
@endphp

@if($href)
    <a href="{{ $href }}" @if($tooltip) title="{{ $tooltip }}" @endif class="bg-white p-4 rounded-xl border shadow-xs transition-all flex items-center gap-3 group {{ $activeClass }}">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl shrink-0 transition-colors {{ $iconBoxStyles }}">
            <i class="{{ $icon }}"></i>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate group-hover:text-blue-600 transition-colors">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900 truncate">{{ $value }}</p>
            @if($subtitle)
                <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
    </a>
@else
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl shrink-0 {{ $iconBoxStyles }}">
            <i class="{{ $icon }}"></i>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900 truncate">{{ $value }}</p>
            @if($subtitle)
                <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
@endif
