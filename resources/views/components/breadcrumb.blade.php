@props([
    'items' => []
])

<nav aria-label="Breadcrumb" class="flex items-center gap-2 mb-6">
    <ol class="flex items-center gap-2 text-sm">
        @foreach($items as $index => $item)
            <li>
                @if(isset($item['url']))
                    <a href="{{ $item['url'] }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-600">{{ $item['label'] }}</span>
                @endif
            </li>
            @if($index < count($items) - 1)
                <li class="text-gray-400">
                    <i class="fas fa-chevron-right"></i>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
