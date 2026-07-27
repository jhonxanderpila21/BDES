@props([
    'label' => '',
    'href' => '#',
    'active' => false
])

<a 
    href="{{ $href }}"
    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-gray-600 text-sm hover:text-gray-900 {{ $active ? 'bg-blue-50 text-blue-600' : '' }}"
>
    <i class="fas fa-angle-right text-xs"></i>
    {{ $label }}
</a>
