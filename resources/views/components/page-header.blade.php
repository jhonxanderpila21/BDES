@props([
    'title' => '',
    'subtitle' => null,
    'icon' => null,
    'breadcrumbs' => [],
    'isReadOnlyStaff' => null,
    'readOnlyBadge' => true,
    'backUrl' => null,
])

@php
    if (is_null($isReadOnlyStaff)) {
        $isReadOnlyStaff = auth()->user()?->isViewer() || auth()->user()?->hasRole('viewer', 'staff');
    }
    $hasActions = ($isReadOnlyStaff && $readOnlyBadge) || (!$isReadOnlyStaff && (isset($actions) || $slot->isNotEmpty()));

    $previousUrl = url()->previous();
    $currentUrl = url()->current();
    $fallbackUrl = $backUrl ?: (($previousUrl && $previousUrl !== $currentUrl && !str_contains($previousUrl, '/login')) ? $previousUrl : route('dashboard'));
@endphp

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2.5">
            <a
                href="{{ $fallbackUrl }}"
                onclick="if (window.history.length > 1 && document.referrer && document.referrer !== window.location.href && !document.referrer.includes('/login')) { event.preventDefault(); window.history.back(); }"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 transition-colors shadow-2xs shrink-0 print:hidden"
                title="Go Back"
            >
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            @if($icon)
                <span class="inline-flex items-center justify-center p-2 bg-blue-100 text-blue-700 rounded-lg shrink-0">
                    <i class="{{ $icon }} text-lg"></i>
                </span>
            @endif
            <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        </div>
        @if($subtitle)
            <p class="text-xs text-gray-500 mt-1">{{ $subtitle }}</p>
        @endif
        @if(!empty($breadcrumbs))
            <div class="mt-1.5">
                <x-breadcrumb :items="$breadcrumbs" />
            </div>
        @endif
    </div>

    @if($hasActions)
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($isReadOnlyStaff && $readOnlyBadge)
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-xs font-semibold shadow-xs">
                    <i class="fas fa-shield-alt text-blue-600"></i>
                    <span>Read-Only Staff Access</span>
                </div>
            @elseif(!$isReadOnlyStaff && (isset($actions) || $slot->isNotEmpty()))
                {{ $actions ?? $slot }}
            @endif
        </div>
    @endif
</div>
