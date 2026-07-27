@props([
    'icon' => '',
    'label' => '',
    'active' => false,
    'expanded' => false,
    'toggleKey' => null,
    'setExpanded' => null,
])

<div class="relative" x-data="{ expanded: {{ json_encode($expanded ?? false) }} }">
    <button
        @click="expanded = !expanded"
        class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-gray-700 text-sm font-medium group {{ $active ? 'bg-blue-50 text-blue-600' : '' }}"
    >
        <div class="flex items-center gap-3">
            <i class="{{ $icon }} {{ $active ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
            <span>{{ $label }}</span>
        </div>
        <i class="fas fa-chevron-right text-xs transition-transform" :class="{'rotate-90': expanded}"></i>
    </button>

    {{-- Submenu --}}
    <div x-show="expanded" x-cloak x-transition class="mt-1">
        {{ $slot }}
    </div>
</div>

