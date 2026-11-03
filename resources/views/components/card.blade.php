@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'actions' => null,
    'footer' => null,
    'class' => '',
    'headerClass' => '',
    'bodyClass' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-[#E5E7EB] overflow-hidden ' . $class]) }}>
    @if($title || $actions || isset($header))
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $headerClass }}">
            @if(isset($header))
                {{ $header }}
            @else
                <div class="flex items-center gap-2.5">
                    @if($icon)
                        <i class="{{ $icon }} text-blue-600 text-base"></i>
                    @endif
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $title }}</h3>
                        @if($subtitle)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                @if($actions)
                    <div class="flex items-center gap-2">
                        {{ $actions }}
                    </div>
                @endif
            @endif
        </div>
    @endif

    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>

    @if($footer || isset($footerSlot))
        <div class="px-5 py-3 bg-gray-50/70 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            {{ $footer ?? $footerSlot }}
        </div>
    @endif
</div>
