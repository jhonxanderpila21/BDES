@props([
    'label' => '',
    'href' => '#',
    'active' => false,
    'icon' => null,
])

<a 
    href="{{ $href }}"
    class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-gray-600 text-sm hover:text-gray-900 {{ $active ? 'bg-blue-50 text-blue-600 font-medium' : '' }}"
>
    @if($icon)
        <i class="{{ $icon }} w-4 text-center text-xs {{ $active ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
    @else
        <i class="fas fa-angle-right text-xs"></i>
    @endif
    <span>{{ $label }}</span>
</a>

