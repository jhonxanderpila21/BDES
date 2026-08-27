<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'info',
    'dismissible' => true,
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
    'type' => 'info',
    'dismissible' => true,
    'class' => ''
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$typeClass = match($type) {
    'success' => 'bg-green-50 text-green-800 border-green-200',
    'error' => 'bg-red-50 text-red-800 border-red-200',
    'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
    'info' => 'bg-blue-50 text-blue-800 border-blue-200',
    default => 'bg-blue-50 text-blue-800 border-blue-200'
};

$iconClass = match($type) {
    'success' => 'fas fa-check-circle text-green-600',
    'error' => 'fas fa-exclamation-circle text-red-600',
    'warning' => 'fas fa-exclamation-triangle text-yellow-600',
    'info' => 'fas fa-info-circle text-blue-600',
    default => 'fas fa-info-circle text-blue-600'
};
?>

<div class="flex items-start gap-3 p-4 border rounded-lg <?php echo e($typeClass); ?> <?php echo e($class); ?>" role="alert">
    <i class="<?php echo e($iconClass); ?> flex-shrink-0 mt-0.5"></i>
    <div class="flex-1">
        <?php echo e($slot); ?>

    </div>
    <?php if($dismissible): ?>
        <button type="button" class="flex-shrink-0 ml-2 text-current opacity-50 hover:opacity-75" onclick="this.parentElement.style.display='none';">
            <i class="fas fa-times"></i>
        </button>
    <?php endif; ?>
</div>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/alert.blade.php ENDPATH**/ ?>