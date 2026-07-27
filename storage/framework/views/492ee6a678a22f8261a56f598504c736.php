<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'items' => []
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
    'items' => []
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<nav aria-label="Breadcrumb" class="flex items-center gap-2 mb-6">
    <ol class="flex items-center gap-2 text-sm">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li>
                <?php if(isset($item['url'])): ?>
                    <a href="<?php echo e($item['url']); ?>" class="text-blue-600 hover:text-blue-800 transition-colors">
                        <?php echo e($item['label']); ?>

                    </a>
                <?php else: ?>
                    <span class="text-gray-600"><?php echo e($item['label']); ?></span>
                <?php endif; ?>
            </li>
            <?php if($index < count($items) - 1): ?>
                <li class="text-gray-400">
                    <i class="fas fa-chevron-right"></i>
                </li>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>