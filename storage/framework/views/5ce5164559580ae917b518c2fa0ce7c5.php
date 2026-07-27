<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => '',
    'required' => false,
    'error' => null,
    'helper' => null,
    'class' => ''
]));

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

foreach (array_filter(([
    'label' => '',
    'required' => false,
    'error' => null,
    'helper' => null,
    'class' => ''
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mb-4 <?php echo e($class); ?>">
    <?php if($label): ?>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            <?php echo e($label); ?>

            <?php if($required): ?>
                <span class="text-red-500">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php echo e($slot); ?>


    <?php if($error): ?>
        <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo e($error); ?>

        </p>
    <?php endif; ?>

    <?php if($helper): ?>
        <p class="mt-1 text-sm text-gray-500"><?php echo e($helper); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/form-group.blade.php ENDPATH**/ ?>