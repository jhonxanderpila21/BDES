<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => '',
    'href' => '#',
    'active' => false,
    'icon' => null,
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
    'href' => '#',
    'active' => false,
    'icon' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a 
    href="<?php echo e($href); ?>"
    class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-gray-600 text-sm hover:text-gray-900 <?php echo e($active ? 'bg-blue-50 text-blue-600 font-medium' : ''); ?>"
>
    <?php if($icon): ?>
        <i class="<?php echo e($icon); ?> w-4 text-center text-xs <?php echo e($active ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'); ?>"></i>
    <?php else: ?>
        <i class="fas fa-angle-right text-xs"></i>
    <?php endif; ?>
    <span><?php echo e($label); ?></span>
</a>

<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/sidebar-submenu-item.blade.php ENDPATH**/ ?>