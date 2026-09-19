<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
?>

<div class="relative" data-dropdown>
    <div onclick="window.toggleDropdown(this)">
        <?php echo e($trigger); ?>

    </div>

    <div data-dropdown-menu class="absolute z-50 mt-2 <?php echo e($width); ?> rounded-md shadow-lg <?php echo e($alignmentClasses); ?> hidden" onclick="window.closeAllDropdowns()">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 <?php echo e($contentClasses); ?>">
            <?php echo e($content); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/dropdown.blade.php ENDPATH**/ ?>