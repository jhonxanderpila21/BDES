<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'id' => null,
    'show' => false,
    'title' => null,
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
    'name' => null,
    'id' => null,
    'show' => false,
    'title' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Backward compatible: support both `name` and `id` props
    $modalName = $name ?? $id;
?>

<div id="<?php echo e($modalName); ?>"
    data-modal-name="<?php echo e($modalName); ?>"
    class="fixed inset-0 z-[100]"
    style="display: <?php echo e($show ? 'block' : 'none'); ?>;"
>
    <div
        class="fixed inset-0 transform transition-all"
        onclick="window.closeModal(<?php echo \Illuminate\Support\Js::from($modalName)->toHtml() ?>)"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl transform transition-all sm:w-full sm:max-w-lg max-h-[90vh] flex flex-col overflow-hidden"
    >
        <?php if($title): ?>
            <div class="flex-shrink-0 border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900"><?php echo e($title); ?></h2>
            </div>
        <?php endif; ?>

        <div class="flex-1 overflow-y-auto px-6 py-6">
            <?php echo e($slot); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/modal.blade.php ENDPATH**/ ?>