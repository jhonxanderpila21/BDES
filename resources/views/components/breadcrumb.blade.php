@props([
    'items' => []
])

<nav aria-label="Breadcrumb" class="flex items-center gap-2">
    <ol class="flex items-center gap-2 text-[14px]">
        @foreach($items as $index => $item)
            <li>
                @if(isset($item['url']))
                    <a href="{{ $item['url'] }}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-600 font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
            @if($index < count($items) - 1)
                <li class="text-gray-400">
                    <i class="fas fa-chevron-right text-xs"></i>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
