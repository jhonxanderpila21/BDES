@props([
    'name' => null,
    'id' => null,
    'show' => false,
    'title' => null,
])

@php
    // Backward compatible: support both `name` and `id` props
    $modalName = $name ?? $id;
@endphp

<div id="{{ $modalName }}"
    data-modal-name="{{ $modalName }}"
    class="fixed inset-0 z-[100]"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        class="fixed inset-0 transform transition-all"
        onclick="window.closeModal(@js($modalName))"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl transform transition-all sm:w-full sm:max-w-lg max-h-[90vh] flex flex-col overflow-hidden"
    >
        @if($title)
            <div class="flex-shrink-0 border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            </div>
        @endif

        <div class="flex-1 overflow-y-auto px-6 py-6">
            {{ $slot }}
        </div>
    </div>
</div>
