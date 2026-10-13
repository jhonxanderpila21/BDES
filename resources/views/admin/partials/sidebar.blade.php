<!-- Sidebar Navigation -->
@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();

    $isActive = function (string $routePattern) {
        return request()->routeIs($routePattern);
    };

    $linkClass = function (bool $active) {
        return $active 
            ? 'bg-[#2563EB] text-white font-semibold shadow-sm' 
            : 'text-blue-100/90 hover:text-white hover:bg-white/10';
    };

    $iconClass = function (bool $active) {
        return $active ? 'text-white' : 'text-[#60A5FA]';
    };

    $headerClass = function (bool $active) {
        return $active 
            ? 'text-white bg-white/15 font-bold' 
            : 'text-blue-200/90 hover:text-white hover:bg-white/10';
    };

    $headerIconClass = function (bool $active) {
        return $active ? 'text-white' : 'text-[#60A5FA]';
    };

    $directLinkClass = function (bool $active) {
        return $active 
            ? 'bg-[#2563EB] text-white font-semibold shadow-sm' 
            : 'text-blue-100 hover:bg-white/10 hover:text-white';
    };

    $directIconClass = function (bool $active) {
        return $active ? 'text-white' : 'text-[#60A5FA]';
    };

    $chevronClass = 'text-blue-300';
    $treeBorderClass = 'border-blue-400/30';

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

    $isTrackingActive = $isActive('asset-history.*') 
        || $isActive('locations.*') 
        || $isActive('reports.*');

    $isAdminActive = $isActive('users.*') 
        || $isActive('roles.*') 
        || $isActive('profile.*');
@endphp
<aside 
    :style="sidebarOpen ? 'width: ' + sidebarWidth + 'px;' : 'width: 0px;'"
    :class="{'transition-all duration-300 ease-in-out': !isResizing}"
    class="flex-shrink-0 bg-[#003366] text-white border-r border-[#002244] overflow-hidden relative shadow-md select-none"
>
    <nav class="h-screen overflow-y-auto">
        {{-- Sidebar Content --}}
        <div class="p-4 space-y-2">

            {{-- 1. Executive Dashboard (Direct Link) --}}
            <a
                href="{{ route('dashboard') }}"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium {{ $directLinkClass($isActive('dashboard')) }}"
            >
                <i class="fas fa-th-large w-5 text-center {{ $directIconClass($isActive('dashboard')) }}"></i>
                <span>Dashboard</span>
            </a>

            {{-- 2. Asset Management Dropdown --}}
            <div x-data="{ open: {{ $isAssetMgmtActive ? 'true' : 'false' }} }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isAssetMgmtActive) }}"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-boxes-stacked w-4 text-center {{ $headerIconClass($isAssetMgmtActive) }}"></i>
                        <span>Asset Management</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
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
                    class="pl-3 pr-1 space-y-1 border-l-2 {{ $treeBorderClass }} ml-3.5 my-1"
                >
                    {{-- Asset Registry --}}
                    <a
                        href="{{ route('assets.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('assets.*') || $isActive('asset-categories.*') || $isActive('asset-types.*')) }}"
                    >
                        <i class="fas fa-box-archive w-4 text-center {{ $iconClass($isActive('assets.*') || $isActive('asset-categories.*') || $isActive('asset-types.*')) }}"></i>
                        <span>Asset Registry</span>
                    </a>

                    {{-- Attachments & Accessories --}}
                    <a
                        href="{{ route('attachments.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('attachments.*') || $isActive('asset-accessories.*') || $isActive('accessories.*')) }}"
                    >
                        <i class="fas fa-paperclip w-4 text-center {{ $iconClass($isActive('attachments.*') || $isActive('asset-accessories.*') || $isActive('accessories.*')) }}"></i>
                        <span>Attachments & Accessories</span>
                    </a>

                    {{-- Operational Requirements --}}
                    <a
                        href="{{ route('operational-requirements.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('operational-requirements.*')) }}"
                    >
                        <i class="fas fa-list-check w-4 text-center {{ $iconClass($isActive('operational-requirements.*')) }}"></i>
                        <span>Operational Requirements</span>
                    </a>

                    {{-- Asset Needs & Supplies --}}
                    <a
                        href="{{ route('asset-needs.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('asset-needs.*')) }}"
                    >
                        <i class="fas fa-gas-pump w-4 text-center {{ $iconClass($isActive('asset-needs.*')) }}"></i>
                        <span>Asset Needs & Supplies</span>
                    </a>
                </div>
            </div>

            {{-- 3. Asset Operations Dropdown --}}
            <div x-data="{ open: {{ $isOperationsActive ? 'true' : 'false' }} }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isOperationsActive) }}"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-truck-fast w-4 text-center {{ $headerIconClass($isOperationsActive) }}"></i>
                        <span>Asset Operations</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
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
                    class="pl-3 pr-1 space-y-1 border-l-2 {{ $treeBorderClass }} ml-3.5 my-1"
                >
                    {{-- Deployments & Returns --}}
                    <a
                        href="{{ route('deployment.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('deployment.*') || $isActive('returns.*')) }}"
                    >
                        <i class="fas fa-truck-ramp-box w-4 text-center {{ $iconClass($isActive('deployment.*') || $isActive('returns.*')) }}"></i>
                        <span>Deployments & Returns</span>
                    </a>

                    {{-- Maintenance & Work Orders --}}
                    <a
                        href="{{ route('maintenance.index', ['tab' => 'logs']) }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('maintenance.*') || $isActive('maintenance-schedules.*') || $isActive('inspection.*')) }}"
                    >
                        <i class="fas fa-screwdriver-wrench w-4 text-center {{ $iconClass($isActive('maintenance.*') || $isActive('maintenance-schedules.*') || $isActive('inspection.*')) }}"></i>
                        <span>Maintenance & Work Orders</span>
                    </a>

                    {{-- Reported Assets --}}
                    <a
                        href="{{ route('reported-assets.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('reported-assets.*')) }}"
                    >
                        <i class="fas fa-flag w-4 text-center {{ $iconClass($isActive('reported-assets.*')) }}"></i>
                        <span>Reported Assets</span>
                    </a>
                </div>
            </div>

            {{-- 4. Asset Financials Dropdown (Admin Only) --}}
            @if($isAdmin)
                <div x-data="{ open: {{ $isFinancialsActive ? 'true' : 'false' }} }" class="pt-1">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isFinancialsActive) }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-coins w-4 text-center {{ $headerIconClass($isFinancialsActive) }}"></i>
                            <span>Asset Financials</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
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
                        class="pl-3 pr-1 space-y-1 border-l-2 {{ $treeBorderClass }} ml-3.5 my-1"
                    >
                        {{-- Valuation & Budgeting --}}
                        <a
                            href="{{ route('asset-accessories.index', ['tab' => 'accessories']) }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass(request()->routeIs('asset-accessories.index') && request('tab') === 'accessories') }}"
                        >
                            <i class="fas fa-wallet w-4 text-center {{ $iconClass(request()->routeIs('asset-accessories.index') && request('tab') === 'accessories') }}"></i>
                            <span>Valuation & Budgeting</span>
                        </a>

                        {{-- Depreciation Tracking --}}
                        <a
                            href="{{ route('financials.depreciation') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('financials.depreciation*')) }}"
                        >
                            <i class="fas fa-arrow-trend-down w-4 text-center {{ $iconClass($isActive('financials.depreciation*')) }}"></i>
                            <span>Depreciation Tracking</span>
                        </a>

                        {{-- Salvage Value Records --}}
                        <a
                            href="{{ route('financials.salvage') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('financials.salvage*')) }}"
                        >
                            <i class="fas fa-recycle w-4 text-center {{ $iconClass($isActive('financials.salvage*')) }}"></i>
                            <span>Salvage Value Records</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- 5. Tracking & Reporting Dropdown --}}
            <div x-data="{ open: {{ $isTrackingActive ? 'true' : 'false' }} }" class="pt-1">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isTrackingActive) }}"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-chart-pie w-4 text-center {{ $headerIconClass($isTrackingActive) }}"></i>
                        <span>Tracking & Reporting</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
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
                    class="pl-3 pr-1 space-y-1 border-l-2 {{ $treeBorderClass }} ml-3.5 my-1"
                >
                    {{-- Asset History / Audit Trail (All Roles) --}}
                    <a
                        href="{{ route('asset-history.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('asset-history.*')) }}"
                    >
                        <i class="fas fa-clock-rotate-left w-4 text-center {{ $iconClass($isActive('asset-history.*')) }}"></i>
                        <span>Asset History & Audit</span>
                    </a>

                    {{-- Asset Locations (All Roles) --}}
                    <a
                        href="{{ route('locations.index') }}"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('locations.*')) }}"
                    >
                        <i class="fas fa-location-dot w-4 text-center {{ $iconClass($isActive('locations.*')) }}"></i>
                        <span>Asset Locations</span>
                    </a>

                    {{-- Reports Center (Admin Only) --}}
                    @if($isAdmin)
                        <a
                            href="{{ route('reports.index') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('reports.*')) }}"
                        >
                            <i class="fas fa-file-invoice w-4 text-center {{ $iconClass($isActive('reports.*')) }}"></i>
                            <span>Reports Center</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- 6. Administration (Admin Only) OR Account Settings (Non-Admin) --}}
            @if($isAdmin)
                <div x-data="{ open: {{ $isAdminActive ? 'true' : 'false' }} }" class="pt-1">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isAdminActive) }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-sliders w-4 text-center {{ $headerIconClass($isAdminActive) }}"></i>
                            <span>Administration</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
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
                        class="pl-3 pr-1 space-y-1 border-l-2 {{ $treeBorderClass }} ml-3.5 my-1"
                    >
                        {{-- User Management --}}
                        <a
                            href="{{ route('users.index') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('users.*')) }}"
                        >
                            <i class="fas fa-users-gear w-4 text-center {{ $iconClass($isActive('users.*')) }}"></i>
                            <span>User Management</span>
                        </a>

                        {{-- Roles & Permissions --}}
                        <a
                            href="{{ route('roles.index') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('roles.*')) }}"
                        >
                            <i class="fas fa-user-shield w-4 text-center {{ $iconClass($isActive('roles.*')) }}"></i>
                            <span>Roles & Permissions</span>
                        </a>

                        {{-- Account Settings --}}
                        <a
                            href="{{ route('profile.edit') }}"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('profile.*')) }}"
                        >
                            <i class="fas fa-id-badge w-4 text-center {{ $iconClass($isActive('profile.*')) }}"></i>
                            <span>Account Settings</span>
                        </a>
                    </div>
                </div>
            @else
                {{-- Account Settings (Direct link replacing Administration for non-admin users) --}}
                <div class="pt-1">
                    <a
                        href="{{ route('profile.edit') }}"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium {{ $directLinkClass($isActive('profile.*')) }}"
                    >
                        <i class="fas fa-id-badge w-5 text-center {{ $directIconClass($isActive('profile.*')) }}"></i>
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
        class="absolute right-0 top-0 bottom-0 w-1.5 cursor-col-resize hover:bg-[#2563EB] transition-colors z-20 group"
        :class="isResizing ? 'bg-[#2563EB] w-2' : 'bg-transparent hover:bg-blue-400'"
    >
        <div class="h-full w-full opacity-0 group-hover:opacity-100 bg-[#2563EB] transition-opacity"></div>
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
