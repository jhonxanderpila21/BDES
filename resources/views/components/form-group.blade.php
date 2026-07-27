@props([
    'label' => '',
    'required' => false,
    'error' => null,
    'helper' => null,
    'class' => ''
])

<div class="mb-4 {{ $class }}">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($error)
        <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
            <i class="fas fa-exclamation-circle"></i>
            {{ $error }}
        </p>
    @endif

    @if($helper)
        <p class="mt-1 text-sm text-gray-500">{{ $helper }}</p>
    @endif
</div>
