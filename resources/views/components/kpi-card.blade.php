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
$colorStyles = match($color) {
    'indigo' => [
        'card' => 'border-indigo-200 bg-indigo-50/20 hover:border-indigo-400 hover:shadow-md',
        'title' => 'text-indigo-700',
        'value' => 'text-indigo-700',
        'sub' => 'text-indigo-500',
        'iconBox' => 'bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white',
    ],
    'purple' => [
        'card' => 'border-purple-200 bg-purple-50/20 hover:border-purple-400 hover:shadow-md',
        'title' => 'text-purple-700',
        'value' => 'text-purple-700',
        'sub' => 'text-purple-500',
        'iconBox' => 'bg-purple-100 text-purple-600 group-hover:bg-purple-600 group-hover:text-white',
    ],
    'emerald', 'green' => [
        'card' => 'border-emerald-200 bg-emerald-50/20 hover:border-emerald-400 hover:shadow-md',
        'title' => 'text-emerald-700',
        'value' => 'text-emerald-700',
        'sub' => 'text-emerald-600',
        'iconBox' => 'bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white',
    ],
    'amber', 'yellow' => [
        'card' => 'border-amber-200 bg-amber-50/20 hover:border-amber-400 hover:shadow-md',
        'title' => 'text-amber-700',
        'value' => 'text-amber-700',
        'sub' => 'text-amber-600',
        'iconBox' => 'bg-amber-100 text-amber-600 group-hover:bg-amber-600 group-hover:text-white',
    ],
    'rose', 'red' => [
        'card' => 'border-rose-200 bg-rose-50/20 hover:border-rose-400 hover:shadow-md',
        'title' => 'text-rose-700',
        'value' => 'text-rose-700',
        'sub' => 'text-rose-600',
        'iconBox' => 'bg-rose-100 text-rose-600 group-hover:bg-rose-600 group-hover:text-white',
    ],
    default => [ // blue / default
        'card' => 'border-gray-200 hover:border-blue-400 hover:shadow-md',
        'title' => 'text-gray-500 group-hover:text-blue-600',
        'value' => 'text-gray-900',
        'sub' => 'text-gray-400',
        'iconBox' => 'bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white',
    ],
};

$activeClass = $active ? 'ring-2 ring-blue-500 border-blue-400 shadow-md' : '';
@endphp

@if($href)
    <a href="{{ $href }}" @if($tooltip) title="{{ $tooltip }}" @endif class="bg-white rounded-xl p-5 border shadow-sm transition-all flex items-center justify-between group block {{ $colorStyles['card'] }} {{ $activeClass }}">
        <div class="min-w-0 pr-2">
            <p class="text-xs font-semibold uppercase tracking-wider mb-1 transition-colors {{ $colorStyles['title'] }}">{{ $title }}</p>
            <p class="text-2xl font-bold truncate {{ $colorStyles['value'] }}">{{ $value }}</p>
            @if($subtitle)
                <p class="text-xs font-medium mt-0.5 truncate {{ $colorStyles['sub'] }}">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl flex-shrink-0 transition-colors {{ $colorStyles['iconBox'] }}">
            <i class="{{ $icon }}"></i>
        </div>
    </a>
@else
    <div class="bg-white rounded-xl p-5 border shadow-sm flex items-center justify-between group {{ $colorStyles['card'] }}">
        <div class="min-w-0 pr-2">
            <p class="text-xs font-semibold uppercase tracking-wider mb-1 {{ $colorStyles['title'] }}">{{ $title }}</p>
            <p class="text-2xl font-bold truncate {{ $colorStyles['value'] }}">{{ $value }}</p>
            @if($subtitle)
                <p class="text-xs font-medium mt-0.5 truncate {{ $colorStyles['sub'] }}">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl flex-shrink-0 transition-colors {{ $colorStyles['iconBox'] }}">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
@endif

