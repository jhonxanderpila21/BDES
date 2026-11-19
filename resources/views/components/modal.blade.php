@props([
    'name' => null,
    'id' => null,
    'show' => false,
    'title' => null,
    'maxWidth' => 'xl',
])

@php
    // Backward compatible: support both `name` and `id` props
    $modalName = $name ?? $id;
    $maxWidthClass = match($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        '6xl' => 'sm:max-w-6xl',
        default => 'sm:max-w-xl',
    };
    $maxWidthStyle = match($maxWidth) {
        'sm' => '24rem',
        'md' => '28rem',
        'lg' => '32rem',
        'xl' => '36rem',
        '2xl' => '42rem',
        '3xl' => '48rem',
        '4xl' => '56rem',
        '5xl' => '64rem',
        '6xl' => '72rem',
        default => '36rem',
    };
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
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-[1px]"></div>
    </div>

    <div
        style="width: min(95vw, {{ $maxWidthStyle }}); max-width: {{ $maxWidthStyle }};"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl border border-gray-200 transform transition-all w-[95vw] sm:w-full {{ $maxWidthClass }} max-h-[90vh] flex flex-col overflow-hidden"
    >
        @if($title)
            <div class="flex-shrink-0 border-b border-gray-200 bg-gray-50/80 px-6 py-4 flex items-center justify-between gap-4">
                <h2 class="text-lg font-bold text-gray-900 leading-tight">{{ $title }}</h2>
                <button
                    type="button"
                    onclick="window.closeModal(@js($modalName))"
                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-200/70 transition-colors shrink-0"
                    title="Close"
                    aria-label="Close modal"
                >
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        @endif

        <div class="flex-1 overflow-y-auto px-6 py-5">
            {{ $slot }}
        </div>
    </div>
</div>
