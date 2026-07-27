<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'value' => '0',
    'icon' => 'fas fa-box',
    'color' => 'blue',
    'trend' => null,
    'trendDirection' => 'up',
    'href' => null,
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
    'title' => '',
    'value' => '0',
    'icon' => 'fas fa-box',
    'color' => 'blue',
    'trend' => null,
    'trendDirection' => 'up',
    'href' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$colorClass = match($color) {
    'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
    'green' => 'bg-green-50 text-green-600 border-green-200',
    'red' => 'bg-red-50 text-red-600 border-red-200',
    'yellow' => 'bg-yellow-50 text-yellow-600 border-yellow-200',
    'purple' => 'bg-purple-50 text-purple-600 border-purple-200',
    default => 'bg-blue-50 text-blue-600 border-blue-200'
};

$trendColorClass = $trendDirection === 'up' ? 'text-green-600' : 'text-red-600';
$trendIconClass = $trendDirection === 'up' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" class="group block rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 h-full">
<?php endif; ?>
<div class="h-full min-h-[160px] bg-white rounded-lg border border-gray-200 shadow-sm p-6 <?php echo e($href ? 'transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md' : ''); ?>">
    <div class="flex h-full items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <p class="text-gray-600 text-sm font-medium mb-1"><?php echo e($title); ?></p>
            <p class="text-3xl font-bold text-gray-900"><?php echo e($value); ?></p>
            <?php if($trend): ?>
                <p class="text-xs <?php echo e($trendColorClass); ?> mt-2 flex items-center gap-1">
                    <i class="<?php echo e($trendIconClass); ?> text-xs"></i>
                    <span><?php echo e($trend); ?></span>
                </p>
            <?php endif; ?>
        </div>
        <div class="flex-shrink-0 self-start">
            <div class="flex items-center justify-center h-12 w-12 rounded-lg <?php echo e($colorClass); ?>">
                <i class="<?php echo e($icon); ?> text-lg"></i>
            </div>
        </div>
    </div>
</div>
<?php if($href): ?>
    </a>
<?php endif; ?>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/components/statistic-card.blade.php ENDPATH**/ ?>