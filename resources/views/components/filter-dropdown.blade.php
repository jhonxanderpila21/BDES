@props([
    'label' => 'Filter',
    'options' => [],
    'class' => ''
])

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" {{ $attributes }}>
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="true"
        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors {{ $class }}"
    >
        <span>{{ $label }}</span>
        <i class="fas fa-chevron-down text-xs transition-transform" :class="{'rotate-180': open}"></i>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute top-full left-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 min-w-48"
    >
        <div class="p-4">
            @foreach($options as $optionValue => $optionLabel)
                <label class="flex items-center gap-2 py-2 cursor-pointer hover:bg-gray-50 rounded px-2">
                    <input type="checkbox" class="w-4 h-4 text-blue-600 rounded" value="{{ $optionValue }}">
                    <span class="text-sm text-gray-700">{{ $optionLabel }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
