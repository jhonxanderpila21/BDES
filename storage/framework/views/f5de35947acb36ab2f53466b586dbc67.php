<!-- Sidebar Navigation -->
<aside 
    :class="{'w-64': sidebarOpen, 'w-0': !sidebarOpen}"
    class="w-64 flex-shrink-0 bg-white border-r border-gray-200 overflow-hidden transition-all duration-300 ease-in-out shadow-sm"
>
    <nav class="h-screen overflow-y-auto" x-data="{ openDropdown: null }">
        
        <div class="p-6 space-y-2">
            <?php
                $isActive = function (string $routePattern) {
                    return request()->routeIs($routePattern);
                };

                $assetManagementOpen = (
                    request()->routeIs('assets.*') ||
                    request()->routeIs('accessories.*') ||
                    request()->routeIs('attachments.*') ||
                    request()->routeIs('operational-requirements.*') ||
                    request()->routeIs('asset-needs.*')
                );
            ?>

            
            <a
                href="<?php echo e(route('dashboard')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('dashboard') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-th-large <?php echo e($isActive('dashboard') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Dashboard</span>
            </a>

            
            <a
                href="<?php echo e(route('users.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('users.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-users <?php echo e($isActive('users.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>User Management</span>
            </a>

            
            <div>
                <?php if (isset($component)) { $__componentOriginalb4dc1e680a94e5cffd4f77271bb15fae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4dc1e680a94e5cffd4f77271bb15fae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-menu-item','data' => ['icon' => 'fas fa-boxes-stacked','label' => 'Asset Management','active' => request()->routeIs('assets.*','accessories.*','attachments.*','operational-requirements.*','asset-needs.*'),'expanded' => $assetManagementOpen,'setExpanded' => 'openDropdown = openDropdown === \'asset-management\' ? null : \'asset-management\'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-menu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fas fa-boxes-stacked','label' => 'Asset Management','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('assets.*','accessories.*','attachments.*','operational-requirements.*','asset-needs.*')),'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($assetManagementOpen),'setExpanded' => 'openDropdown = openDropdown === \'asset-management\' ? null : \'asset-management\'']); ?>
                    <ul class="mt-1 space-y-1">
                        <li>
                            <?php if (isset($component)) { $__componentOriginal997557fad3caf2daf17902cc73dc0423 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal997557fad3caf2daf17902cc73dc0423 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-submenu-item','data' => ['label' => 'Asset Registry','href' => ''.e(route('assets.index')).'','active' => request()->routeIs('assets.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-submenu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Registry','href' => ''.e(route('assets.index')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('assets.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $attributes = $__attributesOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__attributesOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $component = $__componentOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__componentOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
                        </li>
                        <li>
                            <?php if (isset($component)) { $__componentOriginal997557fad3caf2daf17902cc73dc0423 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal997557fad3caf2daf17902cc73dc0423 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-submenu-item','data' => ['label' => 'Asset Accessories','href' => ''.e(route('accessories.index')).'','active' => request()->routeIs('accessories.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-submenu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Accessories','href' => ''.e(route('accessories.index')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('accessories.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $attributes = $__attributesOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__attributesOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $component = $__componentOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__componentOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
                        </li>
                        <li>
                            <?php if (isset($component)) { $__componentOriginal997557fad3caf2daf17902cc73dc0423 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal997557fad3caf2daf17902cc73dc0423 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-submenu-item','data' => ['label' => 'Asset Attachments','href' => ''.e(route('attachments.index')).'','active' => request()->routeIs('attachments.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-submenu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Attachments','href' => ''.e(route('attachments.index')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('attachments.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $attributes = $__attributesOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__attributesOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $component = $__componentOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__componentOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
                        </li>
                        <li>
                            <?php if (isset($component)) { $__componentOriginal997557fad3caf2daf17902cc73dc0423 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal997557fad3caf2daf17902cc73dc0423 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-submenu-item','data' => ['label' => 'Operational Requirements','href' => ''.e(route('operational-requirements.index')).'','active' => request()->routeIs('operational-requirements.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-submenu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Operational Requirements','href' => ''.e(route('operational-requirements.index')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('operational-requirements.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $attributes = $__attributesOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__attributesOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $component = $__componentOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__componentOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
                        </li>
                        <li>
                            <?php if (isset($component)) { $__componentOriginal997557fad3caf2daf17902cc73dc0423 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal997557fad3caf2daf17902cc73dc0423 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-submenu-item','data' => ['label' => 'Asset Needs','href' => ''.e(route('asset-needs.index')).'','active' => request()->routeIs('asset-needs.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-submenu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Asset Needs','href' => ''.e(route('asset-needs.index')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('asset-needs.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $attributes = $__attributesOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__attributesOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $component = $__componentOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__componentOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
                        </li>
                        <li>
                            <?php if (isset($component)) { $__componentOriginal997557fad3caf2daf17902cc73dc0423 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal997557fad3caf2daf17902cc73dc0423 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-submenu-item','data' => ['label' => 'Depreciation Tracking','href' => '#','active' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-submenu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Depreciation Tracking','href' => '#','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $attributes = $__attributesOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__attributesOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $component = $__componentOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__componentOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
                        </li>
                        <li>
                            <?php if (isset($component)) { $__componentOriginal997557fad3caf2daf17902cc73dc0423 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal997557fad3caf2daf17902cc73dc0423 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-submenu-item','data' => ['label' => 'Salvage Value Records','href' => '#','active' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-submenu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Salvage Value Records','href' => '#','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $attributes = $__attributesOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__attributesOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal997557fad3caf2daf17902cc73dc0423)): ?>
<?php $component = $__componentOriginal997557fad3caf2daf17902cc73dc0423; ?>
<?php unset($__componentOriginal997557fad3caf2daf17902cc73dc0423); ?>
<?php endif; ?>
                        </li>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4dc1e680a94e5cffd4f77271bb15fae)): ?>
<?php $attributes = $__attributesOriginalb4dc1e680a94e5cffd4f77271bb15fae; ?>
<?php unset($__attributesOriginalb4dc1e680a94e5cffd4f77271bb15fae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4dc1e680a94e5cffd4f77271bb15fae)): ?>
<?php $component = $__componentOriginalb4dc1e680a94e5cffd4f77271bb15fae; ?>
<?php unset($__componentOriginalb4dc1e680a94e5cffd4f77271bb15fae); ?>
<?php endif; ?>
            </div>

            
            <a
                href="<?php echo e(route('asset-categories.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('asset-categories.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-folder-open <?php echo e($isActive('asset-categories.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Asset Categories</span>
            </a>

            
            <a
                href="<?php echo e(route('asset-types.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('asset-types.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-tags <?php echo e($isActive('asset-types.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Asset Types</span>
            </a>

            
            <a
                href="<?php echo e(route('deployment.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('deployment.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-truck <?php echo e($isActive('deployment.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Deployment</span>
            </a>

            
            <a
                href="<?php echo e(route('returns.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('returns.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-undo-alt <?php echo e($isActive('returns.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Return</span>
            </a>

            
            <a href="<?php echo e(route('asset-needs.index')); ?>" 
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg <?php echo e(request()->routeIs('asset-needs.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?>">
                <i class="fas fa-gas-pump w-5 text-center <?php echo e(request()->routeIs('asset-needs.*') ? 'text-blue-700' : 'text-gray-400'); ?>"></i>
                Asset Needs
            </a>

            
            <a href="<?php echo e(route('asset-accessories.index')); ?>" 
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg <?php echo e(request()->routeIs('asset-accessories.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?>">
                <i class="fas fa-tools w-5 text-center <?php echo e(request()->routeIs('asset-accessories.*') ? 'text-blue-700' : 'text-gray-400'); ?>"></i>
                Asset Accessories
            </a>

            
            <a href="<?php echo e(route('maintenance-schedules.index')); ?>" 
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg <?php echo e(request()->routeIs('maintenance-schedules.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?>">
                <i class="fas fa-calendar-check w-5 text-center <?php echo e(request()->routeIs('maintenance-schedules.*') ? 'text-blue-700' : 'text-gray-400'); ?>"></i>
                Preventive Maintenance
            </a>
            <a
                href="<?php echo e(route('maintenance.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('maintenance.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-wrench <?php echo e($isActive('maintenance.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Maintenance</span>
            </a>

            
            <a
                href="<?php echo e(route('inspection.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('inspection.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-clipboard-check <?php echo e($isActive('inspection.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Inspection</span>
            </a>

            
            <a
                href="<?php echo e(route('incident-reports.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('incident-reports.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-exclamation-triangle <?php echo e($isActive('incident-reports.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Incident Reports</span>
            </a>

            
            <a
                href="<?php echo e(route('reported-assets.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('reported-assets.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-flag <?php echo e($isActive('reported-assets.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Reported Asset</span>
            </a>

            
            <a
                href="<?php echo e(route('asset-history.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('asset-history.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-clock <?php echo e($isActive('asset-history.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Asset History</span>
            </a>

            
            <a
                href="<?php echo e(route('reports.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('reports.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-chart-bar <?php echo e($isActive('reports.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Reports</span>
            </a>

            
            <a
                href="<?php echo e(route('profile.edit')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('profile.*') ? 'bg-blue-50 text-blue-600' : ''); ?>"
            >
                <i class="fas fa-user-circle <?php echo e($isActive('profile.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900'); ?>"></i>
                <span>Account Settings</span>
            </a>
        </div>
    </nav>
</aside>


<div 
    x-show="sidebarOpen"
    x-cloak
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black bg-opacity-50 lg:hidden z-30"
    x-transition
></div>

<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/admin/partials/sidebar.blade.php ENDPATH**/ ?>