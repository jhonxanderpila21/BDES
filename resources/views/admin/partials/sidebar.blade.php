<!-- Sidebar Navigation -->
<aside 
    :style="sidebarOpen ? 'width: ' + sidebarWidth + 'px;' : 'width: 0px;'"
    :class="{'transition-all duration-300 ease-in-out': !isResizing}"
    class="flex-shrink-0 bg-white border-r border-gray-200 overflow-hidden relative shadow-sm select-none"
>
    <nav class="h-screen overflow-y-auto">
        {{-- Sidebar Content --}}
        <div class="p-4 space-y-2">
            @php
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
            @endphp

            {{-- 1. Executive Dashboard (Direct Link) --}}
            <a
                href="{{ route('dashboard') }}"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium hover:bg-gray-50 {{ $isActive('dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700' }}"
            >
                <i class="fas fa-th-large w-5 text-center {{ $isActive('dashboard') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                <span>Dashboard</span>
            </a>

            {{-- 2. Asset Management Dropdown --}}
            <div x-data="{ open: {{ $isAssetMgmtActive ? 'true' : 'false' }} }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $isAssetMgmtActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-boxes-stacked w-4 text-center {{ $isAssetMgmtActive ? 'text-blue-600' : 'text-gray-400' }}"></i>
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
                    {{-- Asset Registry --}}
                    <a
                        href="{{ route('assets.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ ($isActive('assets.*') || $isActive('asset-categories.*') || $isActive('asset-types.*')) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-box-archive w-4 text-center {{ ($isActive('assets.*') || $isActive('asset-categories.*') || $isActive('asset-types.*')) ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Asset Registry</span>
                    </a>

                    {{-- Attachments & Accessories --}}
                    <a
                        href="{{ route('attachments.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ ($isActive('attachments.*') || $isActive('asset-accessories.*') || $isActive('accessories.*')) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-paperclip w-4 text-center {{ ($isActive('attachments.*') || $isActive('asset-accessories.*') || $isActive('accessories.*')) ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Attachments & Accessories</span>
                    </a>

                    {{-- Operational Requirements --}}
                    <a
                        href="{{ route('operational-requirements.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('operational-requirements.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-list-check w-4 text-center {{ $isActive('operational-requirements.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Operational Requirements</span>
                    </a>

                    {{-- Asset Needs & Supplies --}}
                    <a
                        href="{{ route('asset-needs.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('asset-needs.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-gas-pump w-4 text-center {{ $isActive('asset-needs.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Asset Needs & Supplies</span>
                    </a>
                </div>
            </div>

            {{-- 3. Asset Operations Dropdown --}}
            <div x-data="{ open: {{ $isOperationsActive ? 'true' : 'false' }} }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $isOperationsActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-truck-fast w-4 text-center {{ $isOperationsActive ? 'text-blue-600' : 'text-gray-400' }}"></i>
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
                    {{-- Deployments & Returns --}}
                    <a
                        href="{{ route('deployment.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ ($isActive('deployment.*') || $isActive('returns.*')) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-truck-ramp-box w-4 text-center {{ ($isActive('deployment.*') || $isActive('returns.*')) ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Deployments & Returns</span>
                    </a>

                    {{-- Maintenance & Work Orders --}}
                    <a
                        href="{{ route('maintenance.index', ['tab' => 'logs']) }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ ($isActive('maintenance.*') && request('tab') !== 'inspections') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-screwdriver-wrench w-4 text-center {{ ($isActive('maintenance.*') && request('tab') !== 'inspections') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Maintenance & Work Orders</span>
                    </a>

                    {{-- Asset Inspection (Unified Schedules & Inspection Logs) --}}
                    <a
                        href="{{ route('maintenance.index', ['tab' => 'inspections']) }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ ($isActive('inspection.*') || $isActive('maintenance-schedules.*') || ($isActive('maintenance.*') && request('tab') === 'inspections')) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-clipboard-check w-4 text-center {{ ($isActive('inspection.*') || $isActive('maintenance-schedules.*') || ($isActive('maintenance.*') && request('tab') === 'inspections')) ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Asset Inspection</span>
                    </a>

                    {{-- Reported Assets --}}
                    <a
                        href="{{ route('reported-assets.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('reported-assets.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-flag w-4 text-center {{ $isActive('reported-assets.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Reported Assets</span>
                    </a>
                </div>
            </div>

            {{-- 4. Asset Locations (Separated in all roles) --}}
            <a
                href="{{ route('locations.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium hover:bg-gray-50 {{ $isActive('locations.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700' }}"
            >
                <i class="fas fa-location-dot w-5 text-center {{ $isActive('locations.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                <span>Asset Locations</span>
            </a>

            {{-- 5. Asset Financials Dropdown (Admin Only) --}}
            @if($isAdmin)
                <div x-data="{ open: {{ $isFinancialsActive ? 'true' : 'false' }} }" class="pt-1">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $isFinancialsActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-coins w-4 text-center {{ $isFinancialsActive ? 'text-blue-600' : 'text-gray-400' }}"></i>
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
                        {{-- Valuation & Budgeting --}}
                        <a
                            href="{{ route('asset-accessories.index', ['tab' => 'accessories']) }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ (request()->routeIs('asset-accessories.index') && request('tab') === 'accessories') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                        >
                            <i class="fas fa-wallet w-4 text-center text-gray-400"></i>
                            <span>Valuation & Budgeting</span>
                        </a>

                        {{-- Depreciation Tracking --}}
                        <a
                            href="{{ route('financials.depreciation') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('financials.depreciation*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                        >
                            <i class="fas fa-arrow-trend-down w-4 text-center {{ $isActive('financials.depreciation*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span>Depreciation Tracking</span>
                        </a>

                        {{-- Salvage Value Records --}}
                        <a
                            href="{{ route('financials.salvage') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('financials.salvage*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                        >
                            <i class="fas fa-recycle w-4 text-center {{ $isActive('financials.salvage*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span>Salvage Value Records</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- 6. Tracking & Reporting Dropdown --}}
            <div x-data="{ open: {{ $isTrackingActive ? 'true' : 'false' }} }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $isTrackingActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-chart-pie w-4 text-center {{ $isTrackingActive ? 'text-blue-600' : 'text-gray-400' }}"></i>
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
                    {{-- Asset History / Audit Trail (All Roles) --}}
                    <a
                        href="{{ route('asset-history.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('asset-history.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                    >
                        <i class="fas fa-clock-rotate-left w-4 text-center {{ $isActive('asset-history.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Asset History & Audit</span>
                    </a>

                    {{-- Reports Center (Admin Only) --}}
                    @if($isAdmin)
                        <a
                            href="{{ route('reports.index') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('reports.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                        >
                            <i class="fas fa-file-invoice w-4 text-center {{ $isActive('reports.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span>Reports Center</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- 7. Administration (Admin Only) OR Account Settings (Non-Admin) --}}
            @if($isAdmin)
                <div x-data="{ open: {{ $isAdminActive ? 'true' : 'false' }} }" class="pt-1">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $isAdminActive ? 'text-blue-700 bg-blue-50/70' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-sliders w-4 text-center {{ $isAdminActive ? 'text-blue-600' : 'text-gray-400' }}"></i>
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
                        {{-- User Management --}}
                        <a
                            href="{{ route('users.index') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('users.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                        >
                            <i class="fas fa-users-gear w-4 text-center {{ $isActive('users.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span>User Management</span>
                        </a>

                        {{-- Roles & Permissions --}}
                        <a
                            href="{{ route('roles.index') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('roles.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                        >
                            <i class="fas fa-user-shield w-4 text-center {{ $isActive('roles.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span>Roles & Permissions</span>
                        </a>

                        {{-- Account Settings --}}
                        <a
                            href="{{ route('profile.edit') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium hover:bg-gray-50 {{ $isActive('profile.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-600' }}"
                        >
                            <i class="fas fa-id-badge w-4 text-center {{ $isActive('profile.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span>Account Settings</span>
                        </a>
                    </div>
                </div>
            @else
                {{-- Account Settings (Direct link replacing Administration for non-admin users) --}}
                <div class="pt-1">
                    <a
                        href="{{ route('profile.edit') }}"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium hover:bg-gray-50 {{ $isActive('profile.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700' }}"
                    >
                        <i class="fas fa-id-badge w-5 text-center {{ $isActive('profile.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <span>Account Settings</span>
                    </a>
                </div>
            @endif
        </div>
    </nav>

    {{-- Draggable Width Resizer Handle on Right Edge --}}
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

{{-- Sidebar Overlay for mobile --}}
<div 
    x-show="sidebarOpen"
    x-cloak
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black bg-opacity-50 lg:hidden z-30"
    x-transition
></div>
