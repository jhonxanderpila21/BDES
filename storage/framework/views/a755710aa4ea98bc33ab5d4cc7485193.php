<?php $__env->startSection('title', 'Assets - BDES'); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <i class="fas fa-boxes-stacked text-blue-600"></i>
                Assets
            </h1>
            <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Assets']
            ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Assets']
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

        
        <div class="flex flex-wrap items-center gap-2">
            <?php if($activeTab === 'categories'): ?>
                <button onclick="openAddCategoryModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Category
                </button>
            <?php elseif($activeTab === 'types'): ?>
                <button onclick="openAddTypeModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Asset Type
                </button>
            <?php else: ?>
                <button onclick="openAddAssetModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Asset
                </button>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['type' => 'success','class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','class' => 'mb-6']); ?><?php echo e(session('success')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['type' => 'danger','class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','class' => 'mb-6']); ?><?php echo e($errors->first()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Assets</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo e($metrics['total_assets']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fas fa-boxes-stacked"></i>
            </div>
        </div>

        
        <div class="bg-white rounded-xl p-5 border border-emerald-200 bg-emerald-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Available</p>
                <p class="text-2xl font-bold text-emerald-700"><?php echo e($metrics['available_assets']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>

        
        <div class="bg-white rounded-xl p-5 border border-indigo-200 bg-indigo-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 mb-1">Categories</p>
                <p class="text-2xl font-bold text-indigo-700"><?php echo e($metrics['total_categories']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>

        
        <div class="bg-white rounded-xl p-5 border border-purple-200 bg-purple-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-700 mb-1">Asset Types</p>
                <p class="text-2xl font-bold text-purple-700"><?php echo e($metrics['total_types']); ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>

    
    <div class="flex border-b border-gray-200 mb-6 space-x-1">
        <a 
            href="<?php echo e(route('assets.index', ['tab' => 'assets'])); ?>"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors <?php echo e($activeTab === 'assets' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?>"
        >
            <i class="fas fa-boxes-stacked"></i>
            <span>Asset Inventory</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full <?php echo e($activeTab === 'assets' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'); ?>">
                <?php echo e($assets->total()); ?>

            </span>
        </a>

        <a 
            href="<?php echo e(route('assets.index', ['tab' => 'categories'])); ?>"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors <?php echo e($activeTab === 'categories' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?>"
        >
            <i class="fas fa-folder-open"></i>
            <span>Categories</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full <?php echo e($activeTab === 'categories' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'); ?>">
                <?php echo e($categoriesList->total()); ?>

            </span>
        </a>

        <a 
            href="<?php echo e(route('assets.index', ['tab' => 'types'])); ?>"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors <?php echo e($activeTab === 'types' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?>"
        >
            <i class="fas fa-tags"></i>
            <span>Asset Types</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full <?php echo e($activeTab === 'types' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'); ?>">
                <?php echo e($typesList->total()); ?>

            </span>
        </a>
    </div>

    
    
    
    <?php if($activeTab === 'assets'): ?>
        
        <form action="<?php echo e(route('assets.index')); ?>" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="assets">

            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="<?php echo e(request('search')); ?>" 
                    placeholder="Search by asset code, name, serial number, manufacturer..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="category_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Categories</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php echo e(request('category_id') == $category->id ? 'selected' : ''); ?>>
                        <?php echo e($category->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select name="condition" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Conditions</option>
                <?php $__currentLoopData = ['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php echo e(request('condition') === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Statuses</option>
                <?php $__currentLoopData = [
                    'available' => 'Available',
                    'deployed' => 'Deployed',
                    'under_maintenance' => 'Under Maintenance',
                    'out_of_service' => 'Out of Service'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php echo e(request('status') === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>

            <?php if(request()->hasAny(['search', 'category_id', 'type_id', 'condition', 'status'])): ?>
                <a href="<?php echo e(route('assets.index', ['tab' => 'assets'])); ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        
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
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset Code</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category / Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Serial Number</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Condition</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded">
                                        <?php echo e($asset->code); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm font-medium text-gray-900"><?php echo e($asset->name); ?></p>
                                    <?php if($asset->manufacturer): ?>
                                        <p class="text-xs text-gray-500"><?php echo e($asset->manufacturer); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <p class="font-medium text-gray-800"><?php echo e($asset->category->name ?? 'Uncategorized'); ?></p>
                                    <?php if($asset->type): ?>
                                        <p class="text-xs text-gray-500"><?php echo e($asset->type->name); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                                    <?php echo e($asset->serial_number ?: 'N/A'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $conditionColor = match($asset->condition) {
                                            'excellent', 'good' => 'success',
                                            'fair' => 'warning',
                                            'poor' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => ''.e($conditionColor).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($conditionColor).'','size' => 'sm']); ?><?php echo e(ucfirst($asset->condition)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $statusColor = match($asset->status) {
                                            'available' => 'success',
                                            'deployed' => 'info',
                                            'under_maintenance' => 'warning',
                                            'out_of_service' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => ''.e($statusColor).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($statusColor).'','size' => 'sm']); ?><?php echo e(ucfirst(str_replace('_', ' ', $asset->status))); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e($asset->location->name ?? 'N/A'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            onclick="openViewAssetModal(<?php echo e(json_encode($asset)); ?>, '<?php echo e($asset->category->name ?? 'N/A'); ?>', '<?php echo e($asset->type->name ?? 'N/A'); ?>', '<?php echo e($asset->location->name ?? 'N/A'); ?>')" 
                                            class="p-1.5 rounded hover:bg-gray-100 text-blue-600 hover:text-blue-800 transition-colors" 
                                            title="View Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button 
                                            onclick="openEditAssetModal(<?php echo e(json_encode($asset)); ?>)" 
                                            class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                            title="Edit Asset"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="<?php echo e(route('assets.destroy', $asset)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this asset? This action cannot be undone.');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Asset">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'fas fa-boxes-stacked','title' => 'No assets found','description' => 'Start by clicking Add Asset above to register new equipment.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fas fa-boxes-stacked','title' => 'No assets found','description' => 'Start by clicking Add Asset above to register new equipment.']); ?>
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
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($assets->links()); ?>

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

    
    
    
    <?php if($activeTab === 'categories'): ?>
        
        <form action="<?php echo e(route('assets.index')); ?>" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="categories">

            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="<?php echo e(request('search')); ?>" 
                    placeholder="Search category name or description..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Statuses</option>
                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>

            <?php if(request()->hasAny(['search', 'status'])): ?>
                <a href="<?php echo e(route('assets.index', ['tab' => 'categories'])); ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        
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
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Assets Registered</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $categoriesList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm font-semibold text-gray-900"><?php echo e($category->name); ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                    <?php echo e($category->description ?: 'No description provided'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo e($category->assets_count); ?> assets
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => ''.e($category->status === 'active' ? 'success' : 'secondary').'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($category->status === 'active' ? 'success' : 'secondary').'','size' => 'sm']); ?>
                                        <?php echo e(ucfirst($category->status ?? 'active')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            onclick="openEditCategoryModal(<?php echo e($category->id); ?>, <?php echo \Illuminate\Support\Js::from($category->name)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($category->description)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($category->status)->toHtml() ?>)"
                                            class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                            title="Edit Category"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="<?php echo e(route('asset-categories.destroy', $category)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Archive/Delete this category?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Category">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'fas fa-folder-open','title' => 'No categories found','description' => 'Create your first asset category using the Add Category button above.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fas fa-folder-open','title' => 'No categories found','description' => 'Create your first asset category using the Add Category button above.']); ?>
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
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($categoriesList->links()); ?>

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

    
    
    
    <?php if($activeTab === 'types'): ?>
        
        <form action="<?php echo e(route('assets.index')); ?>" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="types">

            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="<?php echo e(request('search')); ?>" 
                    placeholder="Search asset type or parent category..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="category_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Categories</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php echo e(request('category_id') == $category->id ? 'selected' : ''); ?>>
                        <?php echo e($category->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>

            <?php if(request()->hasAny(['search', 'category_id'])): ?>
                <a href="<?php echo e(route('assets.index', ['tab' => 'types'])); ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Serialized</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Borrowable</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Maintenance</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Inspection</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Accessories</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Attachments</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Op. Reqs</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $typesList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="font-semibold text-gray-900"><?php echo e($type->name); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($type->assets_count); ?> assets registered</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                                    <?php echo e($type->category->name ?? 'N/A'); ?>

                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <?php if($type->serialized): ?>
                                        <span class="text-emerald-600 font-bold"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="text-gray-300"><i class="fas fa-minus"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <?php if($type->borrowable): ?>
                                        <span class="text-emerald-600 font-bold"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="text-gray-300"><i class="fas fa-minus"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <?php if($type->requires_maintenance): ?>
                                        <span class="text-emerald-600 font-bold"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="text-gray-300"><i class="fas fa-minus"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <?php if($type->requires_inspection): ?>
                                        <span class="text-emerald-600 font-bold"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="text-gray-300"><i class="fas fa-minus"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <?php if($type->has_accessories): ?>
                                        <span class="text-emerald-600 font-bold"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="text-gray-300"><i class="fas fa-minus"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <?php if($type->has_attachments): ?>
                                        <span class="text-emerald-600 font-bold"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="text-gray-300"><i class="fas fa-minus"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <?php if($type->requires_operational_requirements): ?>
                                        <span class="text-emerald-600 font-bold"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="text-gray-300"><i class="fas fa-minus"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            onclick="openEditTypeModal(<?php echo e($type->id); ?>, <?php echo \Illuminate\Support\Js::from($type->name)->toHtml() ?>, <?php echo e($type->category_id ?? 'null'); ?>, <?php echo e($type->serialized ? 'true' : 'false'); ?>, <?php echo e($type->borrowable ? 'true' : 'false'); ?>, <?php echo e($type->requires_maintenance ? 'true' : 'false'); ?>, <?php echo e($type->requires_inspection ? 'true' : 'false'); ?>, <?php echo e($type->has_accessories ? 'true' : 'false'); ?>, <?php echo e($type->has_attachments ? 'true' : 'false'); ?>, <?php echo e($type->requires_operational_requirements ? 'true' : 'false'); ?>)"
                                            class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                            title="Edit Type"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="<?php echo e(route('asset-types.destroy', $type)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Delete this asset type?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Type">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center">
                                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'fas fa-tags','title' => 'No asset types found','description' => 'Create your first asset type using the Add Asset Type button above.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fas fa-tags','title' => 'No asset types found','description' => 'Create your first asset type using the Add Asset Type button above.']); ?>
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
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($typesList->links()); ?>

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

    
    
    
    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'add-asset-modal','title' => 'Add New Asset']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'add-asset-modal','title' => 'Add New Asset']); ?>
        <form action="<?php echo e(route('assets.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Asset Name','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Name','required' => true]); ?>
                    <input type="text" name="name" placeholder="e.g., Rescue Boat A" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Manufacturer']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Manufacturer']); ?>
                    <input type="text" name="manufacturer" placeholder="e.g., Yamaha, Honda" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Category','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Category','required' => true]); ?>
                    <select name="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Choose Category --</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Asset Type']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Type']); ?>
                    <select name="type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Optional Asset Type --</option>
                        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($tp->id); ?>"><?php echo e($tp->name); ?> (<?php echo e($tp->category->name ?? 'General'); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Serial Number']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Serial Number']); ?>
                    <input type="text" name="serial_number" placeholder="e.g., SN-98234-X" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Assigned Location','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Assigned Location','required' => true]); ?>
                    <select name="location_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Choose Location --</option>
                        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($loc->id); ?>"><?php echo e($loc->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Condition','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Condition','required' => true]); ?>
                    <select name="condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="excellent">Excellent</option>
                        <option value="good" selected>Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Operational Status','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Operational Status','required' => true]); ?>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="available" selected>Available</option>
                        <option value="deployed">Deployed</option>
                        <option value="under_maintenance">Under Maintenance</option>
                        <option value="out_of_service">Out of Service</option>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Purchase Date']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Purchase Date']); ?>
                    <input type="date" name="purchase_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Warranty Expiry']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Warranty Expiry']); ?>
                    <input type="date" name="warranty_expiry" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Notes / Description']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Notes / Description']); ?>
                <textarea name="notes" rows="2" placeholder="Optional notes or specs..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'primary','type' => 'submit','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit','class' => 'flex-1']); ?>Create Asset <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'add-asset-modal\')','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'add-asset-modal\')','class' => 'flex-1']); ?>Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'edit-asset-modal','title' => 'Edit Asset']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'edit-asset-modal','title' => 'Edit Asset']); ?>
        <form id="edit-asset-form" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Asset Code','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Code','required' => true]); ?>
                    <input type="text" name="code" id="edit_asset_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Asset Name','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Name','required' => true]); ?>
                    <input type="text" name="name" id="edit_asset_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Category','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Category','required' => true]); ?>
                    <select name="category_id" id="edit_asset_category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Asset Type']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Type']); ?>
                    <select name="type_id" id="edit_asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- None --</option>
                        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($tp->id); ?>"><?php echo e($tp->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Serial Number']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Serial Number']); ?>
                    <input type="text" name="serial_number" id="edit_asset_serial_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Manufacturer']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Manufacturer']); ?>
                    <input type="text" name="manufacturer" id="edit_asset_manufacturer" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Condition','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Condition','required' => true]); ?>
                    <select name="condition" id="edit_asset_condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="excellent">Excellent</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Operational Status','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Operational Status','required' => true]); ?>
                    <select name="status" id="edit_asset_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="available">Available</option>
                        <option value="deployed">Deployed</option>
                        <option value="under_maintenance">Under Maintenance</option>
                        <option value="out_of_service">Out of Service</option>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Location','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Location','required' => true]); ?>
                    <select name="location_id" id="edit_asset_location_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($loc->id); ?>"><?php echo e($loc->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Purchase Date']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Purchase Date']); ?>
                    <input type="date" name="purchase_date" id="edit_asset_purchase_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Warranty Expiry']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Warranty Expiry']); ?>
                    <input type="date" name="warranty_expiry" id="edit_asset_warranty_expiry" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Notes']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Notes']); ?>
                <textarea name="notes" id="edit_asset_notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'primary','type' => 'submit','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit','class' => 'flex-1']); ?>Save Changes <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'edit-asset-modal\')','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'edit-asset-modal\')','class' => 'flex-1']); ?>Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'view-asset-modal','title' => 'Asset Specifications & Details']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'view-asset-modal','title' => 'Asset Specifications & Details']); ?>
        <div class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <div>
                    <h3 id="view_asset_name" class="text-lg font-bold text-gray-900"></h3>
                    <p id="view_asset_code" class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded inline-block mt-1"></p>
                </div>
                <div id="view_asset_status_badge"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 text-xs font-medium uppercase block">Category</span>
                    <span id="view_asset_category" class="font-medium text-gray-800"></span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-medium uppercase block">Asset Type</span>
                    <span id="view_asset_type" class="font-medium text-gray-800"></span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-medium uppercase block">Serial Number</span>
                    <span id="view_asset_serial" class="font-mono text-gray-800"></span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-medium uppercase block">Manufacturer</span>
                    <span id="view_asset_manufacturer" class="text-gray-800"></span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-medium uppercase block">Location</span>
                    <span id="view_asset_location" class="text-gray-800"></span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-medium uppercase block">Condition</span>
                    <span id="view_asset_condition" class="capitalize text-gray-800"></span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-medium uppercase block">Purchase Date</span>
                    <span id="view_asset_purchase_date" class="text-gray-800"></span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-medium uppercase block">Warranty Expiry</span>
                    <span id="view_asset_warranty" class="text-gray-800"></span>
                </div>
            </div>

            <div id="view_asset_notes_container" class="pt-2 border-t border-gray-100">
                <span class="text-gray-500 text-xs font-medium uppercase block">Notes & Specifications</span>
                <p id="view_asset_notes" class="text-sm text-gray-700 mt-1 bg-gray-50 p-3 rounded-lg"></p>
            </div>

            <div class="pt-4 border-t border-gray-200 flex justify-end">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'view-asset-modal\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'view-asset-modal\')']); ?>Close <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    
    
    
    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'add-category-modal','title' => 'Add Category']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'add-category-modal','title' => 'Add Category']); ?>
        <form class="space-y-4" action="<?php echo e(route('asset-categories.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Category Name','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Category Name','required' => true]); ?>
                <input type="text" name="name" placeholder="e.g., Emergency Response Equipment" value="<?php echo e(old('name')); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Description']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Description']); ?>
                <textarea name="description" placeholder="Brief description..." rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('description')); ?></textarea>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Status','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Status','required' => true]); ?>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="active" <?php echo e(old('status', 'active') === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo e(old('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                </select>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'primary','type' => 'submit','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit','class' => 'flex-1']); ?>Create Category <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'add-category-modal\')','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'add-category-modal\')','class' => 'flex-1']); ?>Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'edit-category-modal','title' => 'Edit Category']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'edit-category-modal','title' => 'Edit Category']); ?>
        <form class="space-y-4" id="edit-category-form" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Category Name','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Category Name','required' => true]); ?>
                <input type="text" name="name" id="edit-category-name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Description']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Description']); ?>
                <textarea name="description" id="edit-category-description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Status','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Status','required' => true]); ?>
                <select name="status" id="edit-category-status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'primary','type' => 'submit','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit','class' => 'flex-1']); ?>Save Changes <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'edit-category-modal\')','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'edit-category-modal\')','class' => 'flex-1']); ?>Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    
    
    
    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'add-type-modal','title' => 'Add Asset Type']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'add-type-modal','title' => 'Add Asset Type']); ?>
        <form class="space-y-4" action="<?php echo e(route('asset-types.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Type Name','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Type Name','required' => true]); ?>
                <input
                    type="text"
                    name="name"
                    placeholder="e.g., Rescue Vehicle"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value="<?php echo e(old('name')); ?>"
                >
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Category','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Category','required' => true]); ?>
                <select
                    name="category_id"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                >
                    <option value="">Select Category</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                            <?php echo e($category->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <div class="space-y-3 border border-gray-300 rounded-lg p-4 bg-gray-50">
                <p class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Type Configuration Rules</p>

                <input type="hidden" name="serialized" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="serialized" value="1" class="w-4 h-4 text-blue-600 rounded" <?php echo e(old('serialized') ? 'checked' : ''); ?>>
                    <span class="text-sm text-gray-700">Serialized (Individually Tracked by Serial Number)</span>
                </label>

                <input type="hidden" name="borrowable" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="borrowable" value="1" class="w-4 h-4 text-blue-600 rounded" <?php echo e(old('borrowable') ? 'checked' : ''); ?>>
                    <span class="text-sm text-gray-700">Borrowable / Deployable</span>
                </label>

                <input type="hidden" name="requires_maintenance" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_maintenance" value="1" class="w-4 h-4 text-blue-600 rounded" <?php echo e(old('requires_maintenance') ? 'checked' : ''); ?>>
                    <span class="text-sm text-gray-700">Requires Maintenance</span>
                </label>

                <input type="hidden" name="requires_inspection" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_inspection" value="1" class="w-4 h-4 text-blue-600 rounded" <?php echo e(old('requires_inspection') ? 'checked' : ''); ?>>
                    <span class="text-sm text-gray-700">Requires Routine Inspection</span>
                </label>

                <input type="hidden" name="has_accessories" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_accessories" value="1" class="w-4 h-4 text-blue-600 rounded" <?php echo e(old('has_accessories') ? 'checked' : ''); ?>>
                    <span class="text-sm text-gray-700">Has Accessories</span>
                </label>

                <input type="hidden" name="has_attachments" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_attachments" value="1" class="w-4 h-4 text-blue-600 rounded" <?php echo e(old('has_attachments') ? 'checked' : ''); ?>>
                    <span class="text-sm text-gray-700">Has Attachments</span>
                </label>

                <input type="hidden" name="requires_operational_requirements" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_operational_requirements" value="1" class="w-4 h-4 text-blue-600 rounded" <?php echo e(old('requires_operational_requirements') ? 'checked' : ''); ?>>
                    <span class="text-sm text-gray-700">Requires Operational Consumables / Requirements</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'primary','class' => 'flex-1','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','class' => 'flex-1','type' => 'submit']); ?>Create Type <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'add-type-modal\')','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'add-type-modal\')','class' => 'flex-1']); ?>Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'edit-type-modal','title' => 'Edit Asset Type']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'edit-type-modal','title' => 'Edit Asset Type']); ?>
        <form class="space-y-4" id="edit-type-form" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Type Name','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Type Name','required' => true]); ?>
                <input
                    type="text"
                    name="name"
                    id="edit-type-name"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal9855f61cf324bb44a86bed9db080852c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9855f61cf324bb44a86bed9db080852c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-group','data' => ['label' => 'Category','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Category','required' => true]); ?>
                <select
                    name="category_id"
                    id="edit-type-category"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                >
                    <option value="">Select Category</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $attributes = $__attributesOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__attributesOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9855f61cf324bb44a86bed9db080852c)): ?>
<?php $component = $__componentOriginal9855f61cf324bb44a86bed9db080852c; ?>
<?php unset($__componentOriginal9855f61cf324bb44a86bed9db080852c); ?>
<?php endif; ?>

            <div class="space-y-3 border border-gray-300 rounded-lg p-4 bg-gray-50">
                <input type="hidden" name="serialized" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="serialized" id="edit-type-serialized" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Serialized</span>
                </label>

                <input type="hidden" name="borrowable" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="borrowable" id="edit-type-borrowable" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Borrowable</span>
                </label>

                <input type="hidden" name="requires_maintenance" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_maintenance" id="edit-type-requires_maintenance" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Maintenance</span>
                </label>

                <input type="hidden" name="requires_inspection" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_inspection" id="edit-type-requires_inspection" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Inspection</span>
                </label>

                <input type="hidden" name="has_accessories" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_accessories" id="edit-type-has_accessories" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Has Accessories</span>
                </label>

                <input type="hidden" name="has_attachments" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_attachments" id="edit-type-has_attachments" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Has Attachments</span>
                </label>

                <input type="hidden" name="requires_operational_requirements" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_operational_requirements" id="edit-type-requires_operational_requirements" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Operational Requirements</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'primary','class' => 'flex-1','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','class' => 'flex-1','type' => 'submit']); ?>Save Changes <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'edit-type-modal\')','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','onclick' => 'window.closeModal(\'edit-type-modal\')','class' => 'flex-1']); ?>Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // Asset Modals
            function openAddAssetModal() {
                window.openModal('add-asset-modal');
            }

            function openEditAssetModal(asset) {
                const form = document.getElementById('edit-asset-form');
                form.action = `/assets/${asset.id}`;
                document.getElementById('edit_asset_code').value = asset.code || '';
                document.getElementById('edit_asset_name').value = asset.name || '';
                document.getElementById('edit_asset_category_id').value = asset.category_id || '';
                document.getElementById('edit_asset_type_id').value = asset.type_id || '';
                document.getElementById('edit_asset_serial_number').value = asset.serial_number || '';
                document.getElementById('edit_asset_manufacturer').value = asset.manufacturer || '';
                document.getElementById('edit_asset_condition').value = asset.condition || 'good';
                document.getElementById('edit_asset_status').value = asset.status || 'available';
                document.getElementById('edit_asset_location_id').value = asset.location_id || '';
                document.getElementById('edit_asset_purchase_date').value = asset.purchase_date ? asset.purchase_date.substring(0, 10) : '';
                document.getElementById('edit_asset_warranty_expiry').value = asset.warranty_expiry ? asset.warranty_expiry.substring(0, 10) : '';
                document.getElementById('edit_asset_notes').value = asset.notes || '';
                window.openModal('edit-asset-modal');
            }

            function openViewAssetModal(asset, categoryName, typeName, locationName) {
                document.getElementById('view_asset_name').textContent = asset.name || 'Unnamed Asset';
                document.getElementById('view_asset_code').textContent = asset.code || '';
                document.getElementById('view_asset_category').textContent = categoryName || 'N/A';
                document.getElementById('view_asset_type').textContent = typeName || 'N/A';
                document.getElementById('view_asset_serial').textContent = asset.serial_number || 'N/A';
                document.getElementById('view_asset_manufacturer').textContent = asset.manufacturer || 'N/A';
                document.getElementById('view_asset_location').textContent = locationName || 'N/A';
                document.getElementById('view_asset_condition').textContent = asset.condition || 'N/A';
                document.getElementById('view_asset_purchase_date').textContent = asset.purchase_date ? asset.purchase_date.substring(0, 10) : 'N/A';
                document.getElementById('view_asset_warranty').textContent = asset.warranty_expiry ? asset.warranty_expiry.substring(0, 10) : 'N/A';
                document.getElementById('view_asset_notes').textContent = asset.notes || 'None';
                
                const statusBadge = document.getElementById('view_asset_status_badge');
                const statusText = (asset.status || '').replace('_', ' ').toUpperCase();
                let statusClasses = 'bg-gray-100 text-gray-700';
                if (asset.status === 'available') statusClasses = 'bg-emerald-100 text-emerald-800';
                else if (asset.status === 'deployed') statusClasses = 'bg-blue-100 text-blue-800';
                else if (asset.status === 'under_maintenance') statusClasses = 'bg-amber-100 text-amber-800';
                else if (asset.status === 'out_of_service') statusClasses = 'bg-red-100 text-red-800';

                statusBadge.innerHTML = `<span class="px-2.5 py-1 text-xs font-semibold rounded-full ${statusClasses}">${statusText}</span>`;
                window.openModal('view-asset-modal');
            }

            // Category Modals
            function openAddCategoryModal() {
                window.openModal('add-category-modal');
            }

            function openEditCategoryModal(id, name, description, status) {
                const form = document.getElementById('edit-category-form');
                form.action = `/asset-categories/${id}`;
                document.getElementById('edit-category-name').value = name;
                document.getElementById('edit-category-description').value = description ?? '';
                document.getElementById('edit-category-status').value = status;
                window.openModal('edit-category-modal');
            }

            // Type Modals
            function openAddTypeModal() {
                window.openModal('add-type-modal');
            }

            function openEditTypeModal(id, name, categoryId, serialized, borrowable, requiresMaintenance, requiresInspection, hasAccessories, hasAttachments, requiresOperationalRequirements) {
                const form = document.getElementById('edit-type-form');
                form.action = `/asset-types/${id}`;
                document.getElementById('edit-type-name').value = name;
                document.getElementById('edit-type-category').value = categoryId ?? '';
                document.getElementById('edit-type-serialized').checked = Boolean(serialized);
                document.getElementById('edit-type-borrowable').checked = Boolean(borrowable);
                document.getElementById('edit-type-requires_maintenance').checked = Boolean(requiresMaintenance);
                document.getElementById('edit-type-requires_inspection').checked = Boolean(requiresInspection);
                document.getElementById('edit-type-has_accessories').checked = Boolean(hasAccessories);
                document.getElementById('edit-type-has_attachments').checked = Boolean(hasAttachments);
                document.getElementById('edit-type-requires_operational_requirements').checked = Boolean(requiresOperationalRequirements);
                window.openModal('edit-type-modal');
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/admin/assets/index.blade.php ENDPATH**/ ?>