@props([
    'current' => 1,
    'total' => 10,
    'perPage' => 10,
    'onPageChange' => 'handlePageChange'
])

@php
$totalPages = ceil($total / $perPage);
$showPrevious = $current > 1;
$showNext = $current < $totalPages;
$startPage = max(1, $current - 2);
$endPage = min($totalPages, $current + 2);
@endphp

<div class="flex items-center justify-between mt-6 px-4 py-3">
    <div class="text-sm text-gray-600">
        Showing <span class="font-medium">{{ ($current - 1) * $perPage + 1 }}</span> to 
        <span class="font-medium">{{ min($current * $perPage, $total) }}</span> of 
        <span class="font-medium">{{ $total }}</span> results
    </div>

    <div class="flex items-center gap-1">
        {{-- Previous Button --}}
        @if($showPrevious)
            <button onclick="{{ $onPageChange }}({{ $current - 1 }})" class="px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <button disabled class="px-3 py-2 rounded-lg text-gray-300 cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </button>
        @endif

        {{-- Page Numbers --}}
        @if($startPage > 1)
            <button onclick="{{ $onPageChange }}(1)" class="px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">1</button>
            @if($startPage > 2)
                <span class="px-3 py-2 text-gray-500">...</span>
            @endif
        @endif

        @for($page = $startPage; $page <= $endPage; $page++)
            @if($page == $current)
                <button class="px-3 py-2 rounded-lg bg-blue-600 text-white font-medium">{{ $page }}</button>
            @else
                <button onclick="{{ $onPageChange }}({{ $page }})" class="px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">{{ $page }}</button>
            @endif
        @endfor

        @if($endPage < $totalPages)
            @if($endPage < $totalPages - 1)
                <span class="px-3 py-2 text-gray-500">...</span>
            @endif
            <button onclick="{{ $onPageChange }}({{ $totalPages }})" class="px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">{{ $totalPages }}</button>
        @endif

        {{-- Next Button --}}
        @if($showNext)
            <button onclick="{{ $onPageChange }}({{ $current + 1 }})" class="px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-chevron-right"></i>
            </button>
        @else
            <button disabled class="px-3 py-2 rounded-lg text-gray-300 cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif
    </div>
</div>
