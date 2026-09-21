<!-- Sidebar Navigation -->
<aside 
    :style="sidebarOpen ? 'width: ' + sidebarWidth + 'px;' : 'width: 0px;'"
    :class="{'transition-all duration-300 ease-in-out': !isResizing}"
    class="flex-shrink-0 bg-white border-r border-gray-200 overflow-hidden relative shadow-sm select-none"
>
    <nav class="h-screen overflow-y-auto">
        
        <div class="p-4 space-y-2">
            <?php
                $isActive = function (string $routePattern) {
                    return request()->routeIs($routePattern);
                };

                $isAdmin = auth()->check() && auth()->user()->isAdmin();

                $isAssetMgmtActive = $isActive('assets.*') 
                    || $isActive('asset-categories.*') 
                    || $isActive('asset-types.*') 
                    || $isActive('attachments.*') 
                    || $isActive('asset-accessories.*') 
                    || $isActive('accessories.*') 
                    || $isActive('operational-requirements.*') 
                    || $isActive('asset-needs.*');

                $isOperationsActive = $isActive('deployment.*') 
                    || $isActive('returns.*') 
                    || $isActive('maintenance.*') 
                    || $isActive('maintenance-schedules.*') 
                    || $isActive('inspection.*') 
                    || $isActive('reported-assets.*');

                $isFinancialsActive = (request()->routeIs('asset-accessories.index') && request('tab') === 'accessories')
                    || $isActive('financials.*');

                $isTrackingActive = $isActive('asset-history.*') || $isActive('reports.*');

                $isAdminActive = $isActive('users.*') 
                    || $isActive('roles.*') 
                    || $isActive('profile.*');
            ?>

            
            <a
                href="<?php echo e(route('dashboard')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700'); ?>"
            >
                <i class="fas fa-th-large w-5 text-center <?php echo e($isActive('dashboard') ? 'text-blue-600' : 'text-gray-500'); ?>"></i>
                <span>Dashboard</span>
            </a>

            
            <div x-data="{ open: <?php echo e($isAssetMgmtActive ? 'true' : 'false'); ?> }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left <?php echo e($isAssetMgmtActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'); ?>"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-boxes-stacked w-4 text-center <?php echo e($isAssetMgmtActive ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Asset Management</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="pl-3 pr-1 space-y-1 border-l-2 border-gray-100 ml-3.5 my-1"
                >
                    
                    <a
                        href="<?php echo e(route('assets.index')); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e(($isActive('assets.*') || $isActive('asset-categories.*') || $isActive('asset-types.*')) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-box-archive w-4 text-center <?php echo e(($isActive('assets.*') || $isActive('asset-categories.*') || $isActive('asset-types.*')) ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Asset Registry</span>
                    </a>

                    
                    <a
                        href="<?php echo e(route('attachments.index')); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e(($isActive('attachments.*') || $isActive('asset-accessories.*') || $isActive('accessories.*')) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-paperclip w-4 text-center <?php echo e(($isActive('attachments.*') || $isActive('asset-accessories.*') || $isActive('accessories.*')) ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Attachments & Accessories</span>
                    </a>

                    
                    <a
                        href="<?php echo e(route('operational-requirements.index')); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('operational-requirements.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-list-check w-4 text-center <?php echo e($isActive('operational-requirements.*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Operational Requirements</span>
                    </a>

                    
                    <a
                        href="<?php echo e(route('asset-needs.index')); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('asset-needs.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-gas-pump w-4 text-center <?php echo e($isActive('asset-needs.*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Asset Needs & Supplies</span>
                    </a>
                </div>
            </div>

            
            <div x-data="{ open: <?php echo e($isOperationsActive ? 'true' : 'false'); ?> }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left <?php echo e($isOperationsActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'); ?>"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-truck-fast w-4 text-center <?php echo e($isOperationsActive ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Asset Operations</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="pl-3 pr-1 space-y-1 border-l-2 border-gray-100 ml-3.5 my-1"
                >
                    
                    <a
                        href="<?php echo e(route('deployment.index')); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e(($isActive('deployment.*') || $isActive('returns.*')) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-truck-ramp-box w-4 text-center <?php echo e(($isActive('deployment.*') || $isActive('returns.*')) ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Deployments & Returns</span>
                    </a>

                    
                    <a
                        href="<?php echo e(route('maintenance.index', ['tab' => 'logs'])); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e(($isActive('maintenance.*') && request('tab') !== 'inspections') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-screwdriver-wrench w-4 text-center <?php echo e(($isActive('maintenance.*') && request('tab') !== 'inspections') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Maintenance & Work Orders</span>
                    </a>

                    
                    <a
                        href="<?php echo e(route('maintenance.index', ['tab' => 'inspections'])); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e(($isActive('inspection.*') || $isActive('maintenance-schedules.*') || ($isActive('maintenance.*') && request('tab') === 'inspections')) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-clipboard-check w-4 text-center <?php echo e(($isActive('inspection.*') || $isActive('maintenance-schedules.*') || ($isActive('maintenance.*') && request('tab') === 'inspections')) ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Asset Inspection</span>
                    </a>

                    
                    <a
                        href="<?php echo e(route('reported-assets.index')); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('reported-assets.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-flag w-4 text-center <?php echo e($isActive('reported-assets.*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Reported Assets</span>
                    </a>
                </div>
            </div>

            
            <a
                href="<?php echo e(route('locations.index')); ?>"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('locations.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700'); ?>"
            >
                <i class="fas fa-location-dot w-5 text-center <?php echo e($isActive('locations.*') ? 'text-blue-600' : 'text-gray-500'); ?>"></i>
                <span>Asset Locations</span>
            </a>

            
            <?php if($isAdmin): ?>
                <div x-data="{ open: <?php echo e($isFinancialsActive ? 'true' : 'false'); ?> }" class="pt-1">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left <?php echo e($isFinancialsActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'); ?>"
                    >
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-coins w-4 text-center <?php echo e($isFinancialsActive ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                            <span>Asset Financials</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="pl-3 pr-1 space-y-1 border-l-2 border-gray-100 ml-3.5 my-1"
                    >
                        
                        <a
                            href="<?php echo e(route('asset-accessories.index', ['tab' => 'accessories'])); ?>"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e((request()->routeIs('asset-accessories.index') && request('tab') === 'accessories') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                        >
                            <i class="fas fa-wallet w-4 text-center text-gray-400"></i>
                            <span>Valuation & Budgeting</span>
                        </a>

                        
                        <a
                            href="<?php echo e(route('financials.depreciation')); ?>"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('financials.depreciation*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                        >
                            <i class="fas fa-arrow-trend-down w-4 text-center <?php echo e($isActive('financials.depreciation*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                            <span>Depreciation Tracking</span>
                        </a>

                        
                        <a
                            href="<?php echo e(route('financials.salvage')); ?>"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('financials.salvage*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                        >
                            <i class="fas fa-recycle w-4 text-center <?php echo e($isActive('financials.salvage*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                            <span>Salvage Value Records</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            
            <div x-data="{ open: <?php echo e($isTrackingActive ? 'true' : 'false'); ?> }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left <?php echo e($isTrackingActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'); ?>"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-chart-pie w-4 text-center <?php echo e($isTrackingActive ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Tracking & Reporting</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="pl-3 pr-1 space-y-1 border-l-2 border-gray-100 ml-3.5 my-1"
                >
                    
                    <a
                        href="<?php echo e(route('asset-history.index')); ?>"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('asset-history.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                    >
                        <i class="fas fa-clock-rotate-left w-4 text-center <?php echo e($isActive('asset-history.*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                        <span>Asset History & Audit</span>
                    </a>

                    
                    <?php if($isAdmin): ?>
                        <a
                            href="<?php echo e(route('reports.index')); ?>"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('reports.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                        >
                            <i class="fas fa-file-invoice w-4 text-center <?php echo e($isActive('reports.*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                            <span>Reports Center</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php if($isAdmin): ?>
                <div x-data="{ open: <?php echo e($isAdminActive ? 'true' : 'false'); ?> }" class="pt-1">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left <?php echo e($isAdminActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'); ?>"
                    >
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-sliders w-4 text-center <?php echo e($isAdminActive ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                            <span>Administration</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="pl-3 pr-1 space-y-1 border-l-2 border-gray-100 ml-3.5 my-1"
                    >
                        
                        <a
                            href="<?php echo e(route('users.index')); ?>"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('users.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                        >
                            <i class="fas fa-users-gear w-4 text-center <?php echo e($isActive('users.*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                            <span>User Management</span>
                        </a>

                        
                        <a
                            href="<?php echo e(route('roles.index')); ?>"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('roles.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                        >
                            <i class="fas fa-user-shield w-4 text-center <?php echo e($isActive('roles.*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                            <span>Roles & Permissions</span>
                        </a>

                        
                        <a
                            href="<?php echo e(route('profile.edit')); ?>"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 <?php echo e($isActive('profile.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600'); ?>"
                        >
                            <i class="fas fa-id-badge w-4 text-center <?php echo e($isActive('profile.*') ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                            <span>Account Settings</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                
                <div class="pt-1">
                    <a
                        href="<?php echo e(route('profile.edit')); ?>"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium hover:bg-gray-50 <?php echo e($isActive('profile.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700'); ?>"
                    >
                        <i class="fas fa-id-badge w-5 text-center <?php echo e($isActive('profile.*') ? 'text-blue-600' : 'text-gray-500'); ?>"></i>
                        <span>Account Settings</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    
    <div 
        @mousedown="startResize($event)"
        @dblclick="resetSidebarWidth()"
        title="Click and drag to adjust sidebar width (double-click to reset to default)"
        class="absolute right-0 top-0 bottom-0 w-1.5 cursor-col-resize hover:bg-blue-500 transition-colors z-20 group"
        :class="isResizing ? 'bg-blue-600 w-2' : 'bg-transparent hover:bg-blue-400'"
    >
        <div class="h-full w-full opacity-0 group-hover:opacity-100 bg-blue-500 transition-opacity"></div>
    </div>
</aside>


<div 
    x-show="sidebarOpen"
    x-cloak
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black bg-opacity-50 lg:hidden z-30"
    x-transition
></div>
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/admin/partials/sidebar.blade.php ENDPATH**/ ?>