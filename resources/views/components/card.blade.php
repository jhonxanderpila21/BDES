@props([
    'class' => ''
])

<div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] {{ $class }}">
    {{ $slot }}
</div>
