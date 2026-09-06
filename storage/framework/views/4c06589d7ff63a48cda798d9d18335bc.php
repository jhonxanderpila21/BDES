

<?php $__env->startSection('title', 'Asset History - BDES'); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset History</h1>
            <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Monitoring'],
                ['label' => 'Asset History']
            ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Monitoring'],
                ['label' => 'Asset History']
            ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $attributes = $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $component = $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <?php if (isset($component)) { $__componentOriginal61542037d001e2034791c9aff5866543 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal61542037d001e2034791c9aff5866543 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.search-bar','data' => ['placeholder' => 'Search by asset code or name...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('search-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['placeholder' => 'Search by asset code or name...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal61542037d001e2034791c9aff5866543)): ?>
<?php $attributes = $__attributesOriginal61542037d001e2034791c9aff5866543; ?>
<?php unset($__attributesOriginal61542037d001e2034791c9aff5866543); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal61542037d001e2034791c9aff5866543)): ?>
<?php $component = $__componentOriginal61542037d001e2034791c9aff5866543; ?>
<?php unset($__componentOriginal61542037d001e2034791c9aff5866543); ?>
<?php endif; ?>
        </div>
        <?php if (isset($component)) { $__componentOriginal998aae73fc463ff48ed6f2c9a271e491 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal998aae73fc463ff48ed6f2c9a271e491 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-dropdown','data' => ['label' => 'Filter by Event Type','options' => ['creation' => 'Asset Created', 'deployment' => 'Deployment', 'return' => 'Return', 'maintenance' => 'Maintenance', 'inspection' => 'Inspection', 'incident' => 'Incident']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Filter by Event Type','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['creation' => 'Asset Created', 'deployment' => 'Deployment', 'return' => 'Return', 'maintenance' => 'Maintenance', 'inspection' => 'Inspection', 'incident' => 'Incident'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal998aae73fc463ff48ed6f2c9a271e491)): ?>
<?php $attributes = $__attributesOriginal998aae73fc463ff48ed6f2c9a271e491; ?>
<?php unset($__attributesOriginal998aae73fc463ff48ed6f2c9a271e491); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal998aae73fc463ff48ed6f2c9a271e491)): ?>
<?php $component = $__componentOriginal998aae73fc463ff48ed6f2c9a271e491; ?>
<?php unset($__componentOriginal998aae73fc463ff48ed6f2c9a271e491); ?>
<?php endif; ?>
    </div>

    
    <div class="space-y-6">
        
        <?php
            $historyEvents = [
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Asset Created', 'date' => '2024-01-15', 'time' => '10:30 AM', 'details' => 'Initial asset entry into system', 'user' => 'Admin User', 'status' => 'Completed'],
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Deployed', 'date' => '2024-06-20', 'time' => '14:15 PM', 'details' => 'Deployed to Purok A Fire Station', 'user' => 'Carlos Rodriguez', 'status' => 'Completed'],
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Maintenance', 'date' => '2024-07-01', 'time' => '09:00 AM', 'details' => 'Refill and pressure check performed', 'user' => 'Maintenance Team', 'status' => 'Completed'],
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Returned', 'date' => '2024-07-12', 'time' => '16:45 PM', 'details' => 'Returned from deployment - Good condition', 'user' => 'Juan Santos', 'status' => 'Completed'],
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Inspection', 'date' => '2024-07-15', 'time' => '11:20 AM', 'details' => 'Quarterly inspection passed', 'user' => 'Ana Luna', 'status' => 'Completed'],
            ];
        ?>

        <?php $__empty_1 = true; $__currentLoopData = $historyEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        
                        <div class="flex-shrink-0 relative">
                            <?php
                                $eventColor = match($event['event']) {
                                    'Asset Created' => 'bg-blue-100 text-blue-600',
                                    'Deployed' => 'bg-purple-100 text-purple-600',
                                    'Returned' => 'bg-green-100 text-green-600',
                                    'Maintenance' => 'bg-yellow-100 text-yellow-600',
                                    'Inspection' => 'bg-cyan-100 text-cyan-600',
                                    'Incident' => 'bg-red-100 text-red-600',
                                    default => 'bg-gray-100 text-gray-600'
                                };

                                $eventIcon = match($event['event']) {
                                    'Asset Created' => 'fas fa-plus',
                                    'Deployed' => 'fas fa-truck',
                                    'Returned' => 'fas fa-undo',
                                    'Maintenance' => 'fas fa-wrench',
                                    'Inspection' => 'fas fa-clipboard-check',
                                    'Incident' => 'fas fa-exclamation-triangle',
                                    default => 'fas fa-circle'
                                };
                            ?>
                            <div class="flex items-center justify-center h-12 w-12 rounded-full <?php echo e($eventColor); ?>">
                                <i class="<?php echo e($eventIcon); ?>"></i>
                            </div>
                        </div>

                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900"><?php echo e($event['event']); ?></h3>
                                    <p class="text-sm text-gray-600 mt-1">Asset: <span class="font-medium"><?php echo e($event['asset']); ?></span></p>
                                </div>
                                <div class="flex-shrink-0">
                                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => 'success','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','size' => 'sm']); ?><?php echo e($event['status']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">
                                        <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                        <?php echo e($event['date']); ?> at <?php echo e($event['time']); ?>

                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-600">
                                        <i class="fas fa-user mr-2 text-gray-400"></i>
                                        <?php echo e($event['user']); ?>

                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-700"><?php echo e($event['details']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <div class="px-6 py-8">
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'fas fa-history','title' => 'No history found','message' => 'This asset has no historical records yet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fas fa-history','title' => 'No history found','message' => 'This asset has no historical records yet']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/admin/asset-history/index.blade.php ENDPATH**/ ?>