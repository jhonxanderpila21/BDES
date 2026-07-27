@props([
    'title' => '',
    'value' => '0',
    'icon' => 'fas fa-box',
    'color' => 'blue',
    'trend' => null,
    'trendDirection' => 'up',
    'href' => null,
])

@php
$colorClass = match($color) {
    'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
    'green' => 'bg-green-50 text-green-600 border-green-200',
    'red' => 'bg-red-50 text-red-600 border-red-200',
    'yellow' => 'bg-yellow-50 text-yellow-600 border-yellow-200',
    'purple' => 'bg-purple-50 text-purple-600 border-purple-200',
    default => 'bg-blue-50 text-blue-600 border-blue-200'
};

$trendColorClass = $trendDirection === 'up' ? 'text-green-600' : 'text-red-600';
$trendIconClass = $trendDirection === 'up' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
@endphp

@if($href)
    <a href="{{ $href }}" class="group block rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 h-full">
@endif
<div class="h-full min-h-[160px] bg-white rounded-lg border border-gray-200 shadow-sm p-6 {{ $href ? 'transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md' : '' }}">
    <div class="flex h-full items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <p class="text-gray-600 text-sm font-medium mb-1">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
            @if($trend)
                <p class="text-xs {{ $trendColorClass }} mt-2 flex items-center gap-1">
                    <i class="{{ $trendIconClass }} text-xs"></i>
                    <span>{{ $trend }}</span>
                </p>
            @endif
        </div>
        <div class="flex-shrink-0 self-start">
            <div class="flex items-center justify-center h-12 w-12 rounded-lg {{ $colorClass }}">
                <i class="{{ $icon }} text-lg"></i>
            </div>
        </div>
    </div>
</div>
@if($href)
    </a>
@endif
