<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'class' => '',
    'icon' => null,
    'iconPosition' => 'left'
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
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'class' => '',
    'icon' => null,
    'iconPosition' => 'left'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$baseClass = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';

$variantClass = match($variant) {
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    'warning' => 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
    'outline' => 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 focus:ring-blue-500',
    'ghost' => 'text-gray-600 hover:bg-gray-100 focus:ring-gray-500',
    default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500'
};

$sizeClass = match($size) {
    'sm' => 'px-3 py-1.5 text-sm gap-2',
    'md' => 'px-4 py-2 text-base gap-2',
    'lg' => 'px-6 py-3 text-lg gap-3',
    default => 'px-4 py-2 text-base gap-2'
};
?>

<button 
    type="<?php echo e($type); ?>" 
    class="<?php echo e($baseClass); ?> <?php echo e($variantClass); ?> <?php echo e($sizeClass); ?> <?php echo e($class); ?>"
    <?php echo e($attributes); ?>

>
    <?php if($icon && $iconPosition === 'left'): ?>
        <i class="<?php echo e($icon); ?>"></i>
    <?php endif; ?>
    <span><?php echo e($slot); ?></span>
    <?php if($icon && $iconPosition === 'right'): ?>
        <i class="<?php echo e($icon); ?>"></i>
    <?php endif; ?>
</button>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/button.blade.php ENDPATH**/ ?>