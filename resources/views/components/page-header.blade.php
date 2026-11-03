@props([
    'title' => '',
    'subtitle' => null,
    'icon' => null,
    'breadcrumbs' => [],
    'isReadOnlyStaff' => null,
    'readOnlyBadge' => true,
])

@php
    if (is_null($isReadOnlyStaff)) {
        $isReadOnlyStaff = auth()->user()?->isViewer() || auth()->user()?->hasRole('viewer', 'staff');
    }
@endphp

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
            @if($icon)
                <i class="{{ $icon }} text-blue-600"></i>
            @endif
            <span>{{ $title }}</span>
        </h1>
        @if($subtitle)
            <p class="text-xs text-gray-500 mb-2">{{ $subtitle }}</p>
        @endif
        @if(!empty($breadcrumbs))
            <x-breadcrumb :items="$breadcrumbs" />
        @endif
    </div>

    {{-- Contextual Actions or Read-Only Staff Indicator --}}
    @if($isReadOnlyStaff && $readOnlyBadge)
        <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-xs font-semibold shadow-xs">
            <i class="fas fa-shield-alt text-blue-600"></i>
            <span>Read-Only Staff Access</span>
        </div>
    @elseif(!$isReadOnlyStaff && (isset($actions) || $slot->isNotEmpty()))
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>

