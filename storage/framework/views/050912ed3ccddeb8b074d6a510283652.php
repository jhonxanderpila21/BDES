<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'fas fa-inbox',
    'title' => 'No data found',
    'message' => 'Start by adding your first item',
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
    'icon' => 'fas fa-inbox',
    'title' => 'No data found',
    'message' => 'Start by adding your first item',
    'class' => ''
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex flex-col items-center justify-center py-12 text-center <?php echo e($class); ?>">
    <div class="mb-4">
        <i class="<?php echo e($icon); ?> text-6xl text-gray-300"></i>
    </div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php echo e($title); ?></h3>
    <p class="text-gray-600 mb-6 max-w-md"><?php echo e($message); ?></p>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/empty-state.blade.php ENDPATH**/ ?>