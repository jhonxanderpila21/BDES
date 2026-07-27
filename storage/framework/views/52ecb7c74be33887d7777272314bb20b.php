<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => '',
    'label' => '',
    'active' => false,
    'expanded' => false,
    'toggleKey' => null,
    'setExpanded' => null,
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
    'icon' => '',
    'label' => '',
    'active' => false,
    'expanded' => false,
    'toggleKey' => null,
    'setExpanded' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="relative" x-data="{ expanded: <?php echo e(json_encode($expanded ?? false)); ?> }">
    <button
        @click="expanded = !expanded"
        class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-gray-700 text-sm font-medium group <?php echo e($active ? 'bg-blue-50 text-blue-600' : ''); ?>"
    >
        <div class="flex items-center gap-3">
            <i class="<?php echo e($icon); ?> <?php echo e($active ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
            <span><?php echo e($label); ?></span>
        </div>
        <i class="fas fa-chevron-right text-xs transition-transform" :class="{'rotate-90': expanded}"></i>
    </button>

    
    <div x-show="expanded" x-cloak x-transition class="mt-1">
        <?php echo e($slot); ?>

    </div>
</div>

<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/sidebar-menu-item.blade.php ENDPATH**/ ?>