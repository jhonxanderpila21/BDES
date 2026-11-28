<!-- Sidebar Navigation -->
@php
    $currentUser = auth()->user();
    $isAdmin = $currentUser && $currentUser->isAdmin();
    $isInventory = $currentUser && ($currentUser->hasRole('inventory_officer', 'bdrrmc_officer') || $currentUser->isAdmin());
    $isMaintenance = $currentUser && ($currentUser->hasRole('maintenance_officer') || $currentUser->isAdmin());
    $isViewer = $currentUser && ($currentUser->isViewer() || $currentUser->hasRole('viewer', 'staff'));

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

    $isFinancialsActive = $isActive('financials.*');

    $isTrackingActive = $isActive('asset-history.*') 
        || $isActive('locations.*') 
        || $isActive('reports.*')
        || $isActive('archived-records.*');

    $isAdminActive = $isActive('users.*') 
        || $isActive('roles.*') 
        || $isActive('profile.*');
@endphp
<aside 
    :style="sidebarCollapsed ? 'width: 72px;' : 'width: ' + sidebarWidth + 'px;'"
    :class="{'transition-all duration-300 ease-in-out': !isResizing}"
    class="relative flex-shrink-0 h-screen bg-[#003366] text-white border-r border-[#002244] overflow-hidden shadow-md select-none"
>
    <nav class="h-screen flex flex-col justify-between overflow-y-auto overflow-x-hidden">
        <div>
        {{-- Top-Left Brand Logo Header (aligned with top navbar height) --}}
        <div class="px-3.5 h-[68px] border-b border-white/10 flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0" title="BDES - Disaster Assets">
                <img src="{{ asset('images/bdes-logo.png') }}" alt="BDES Logo" class="w-10 h-10 object-cover aspect-square rounded-full ring-2 ring-white/20 shadow-sm shrink-0">
                <div class="min-w-0" x-show="!sidebarCollapsed" x-cloak>
                    <h2 class="text-base font-extrabold text-white tracking-wide leading-tight truncate">BDES</h2>
                    <p class="text-xs font-medium text-[#60A5FA] leading-tight truncate">Disaster Assets</p>
                </div>
            </a>
        </div>

        {{-- Sidebar Content --}}
        <div class="p-3 space-y-2">

            {{-- 1. Executive Dashboard (Direct Link) --}}
            <a
                href="{{ route('dashboard') }}"
                title="Dashboard"
                :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-2.5 px-3 py-2'"
                class="w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $directLinkClass($isActive('dashboard')) }}"
            >
                <i class="fas fa-th-large w-5 text-center {{ $directIconClass($isActive('dashboard')) }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>

            {{-- 1b. Notifications & Equipment Updates (Direct Link with Unread Badge) --}}
            @php
                $sidebarUnreadCount = (int) ($navbarNotifications['unread_count'] ?? 0);
            @endphp
            <a
                href="{{ route('notifications.index') }}"
                title="Notifications"
                :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'justify-between px-3 py-2'"
                class="relative w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $directLinkClass($isActive('notifications.*')) }}"
            >
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-2.5'">
                    <i class="fas fa-bell w-5 text-center {{ $directIconClass($isActive('notifications.*')) }}"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Notifications</span>
                </div>
                @if($sidebarUnreadCount > 0)
                    <span
                        x-show="!sidebarCollapsed"
                        class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full shadow-xs"
                    >
                        {{ $sidebarUnreadCount > 99 ? '99+' : $sidebarUnreadCount }}
                    </span>
                    <span
                        x-show="sidebarCollapsed"
                        x-cloak
                        class="absolute top-1.5 right-2.5 w-2.5 h-2.5 bg-red-600 rounded-full border-2 border-[#003366]"
                    ></span>
                @endif
            </a>

            {{-- 2. Asset Management Dropdown --}}
            <div x-data="{ open: {{ $isAssetMgmtActive ? 'true' : 'false' }} }" class="pt-1">
                <button
                    type="button"
                    title="Asset Management"
                    @click="if (sidebarCollapsed) { window.location.href = '{{ route('assets.index') }}'; } else { open = !open; }"
                    :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'justify-between px-3 py-2'"
                    class="w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isAssetMgmtActive) }}"
                >
                    <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-2.5'">
                        <i class="fas fa-boxes-stacked w-5 text-center {{ $headerIconClass($isAssetMgmtActive) }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Asset Management</span>
                    </div>
                    <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <div
                    x-show="open && !sidebarCollapsed"
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
                        title="Asset Registry"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('assets.*') || $isActive('asset-categories.*') || $isActive('asset-types.*')) }}"
                    >
                        <i class="fas fa-box-archive w-4 text-center {{ $iconClass($isActive('assets.*') || $isActive('asset-categories.*') || $isActive('asset-types.*')) }}"></i>
                        <span>Asset Registry</span>
                    </a>

                    @if($isAdmin || $isInventory || $isViewer)
                        {{-- Asset Components & Parts (Attachments & Accessories) --}}
                        <a
                            href="{{ route('attachments.index') }}"
                            title="Components & Parts"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('attachments.*') || $isActive('asset-accessories.*') || $isActive('accessories.*')) }}"
                        >
                            <i class="fas fa-puzzle-piece w-4 text-center {{ $iconClass($isActive('attachments.*') || $isActive('asset-accessories.*') || $isActive('accessories.*')) }}"></i>
                            <span>Components & Parts</span>
                        </a>

                        {{-- Operational Requirements & Supplies --}}
                        <a
                            href="{{ route('operational-requirements.index') }}"
                            title="Operational Requirements"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('operational-requirements.*') || $isActive('asset-needs.*')) }}"
                        >
                            <i class="fas fa-list-check w-4 text-center {{ $iconClass($isActive('operational-requirements.*') || $isActive('asset-needs.*')) }}"></i>
                            <span>Operational Requirements</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- 3. Asset Operations Dropdown --}}
            <div x-data="{ open: {{ $isOperationsActive ? 'true' : 'false' }} }" class="pt-1">
                <button
                    type="button"
                    title="Asset Operations"
                    @click="if (sidebarCollapsed) { window.location.href = '{{ ($isAdmin || $isInventory || $isViewer) ? route('deployment.index') : route('maintenance.index', ['tab' => 'logs']) }}'; } else { open = !open; }"
                    :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'justify-between px-3 py-2'"
                    class="w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isOperationsActive) }}"
                >
                    <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-2.5'">
                        <i class="fas fa-truck-fast w-5 text-center {{ $headerIconClass($isOperationsActive) }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Asset Operations</span>
                    </div>
                    <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <div
                    x-show="open && !sidebarCollapsed"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="pl-3 pr-1 space-y-1 border-l-2 {{ $treeBorderClass }} ml-3.5 my-1"
                >
                    @if($isAdmin || $isInventory || $isViewer)
                        {{-- Deployments & Returns (Equipment Officer workflow) --}}
                        <a
                            href="{{ route('deployment.index') }}"
                            title="Deployments & Returns"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('deployment.*') || $isActive('returns.*')) }}"
                        >
                            <i class="fas fa-truck-ramp-box w-4 text-center {{ $iconClass($isActive('deployment.*') || $isActive('returns.*')) }}"></i>
                            <span>Deployments & Returns</span>
                        </a>
                    @endif

                    @if($isAdmin || $isMaintenance || $isViewer)
                        {{-- Maintenance (Maintenance Officer workflow) --}}
                        <a
                            href="{{ route('maintenance.index', ['tab' => 'logs']) }}"
                            title="Maintenance"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('maintenance.*') || $isActive('maintenance-schedules.*') || $isActive('inspection.*')) }}"
                        >
                            <i class="fas fa-screwdriver-wrench w-4 text-center {{ $iconClass($isActive('maintenance.*') || $isActive('maintenance-schedules.*') || $isActive('inspection.*')) }}"></i>
                            <span>Maintenance</span>
                        </a>
                    @endif

                    {{-- Reported Assets --}}
                    <a
                        href="{{ route('reported-assets.index') }}"
                        title="Reported Assets"
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
                        title="Asset Financials"
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('financials.expenses') }}'; } else { open = !open; }"
                        :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'justify-between px-3 py-2'"
                        class="w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isFinancialsActive) }}"
                    >
                        <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-2.5'">
                            <i class="fas fa-coins w-5 text-center {{ $headerIconClass($isFinancialsActive) }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Asset Financials</span>
                        </div>
                        <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>

                    <div
                        x-show="open && !sidebarCollapsed"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="pl-3 pr-1 space-y-1 border-l-2 {{ $treeBorderClass }} ml-3.5 my-1"
                    >
                        {{-- Asset Expenses --}}
                        <a
                            href="{{ route('financials.expenses') }}"
                            title="Asset Expenses"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('financials.expenses*')) }}"
                        >
                            <i class="fas fa-file-invoice-dollar w-4 text-center {{ $iconClass($isActive('financials.expenses*')) }}"></i>
                            <span>Asset Expenses</span>
                        </a>

                        {{-- Depreciation Value --}}
                        <a
                            href="{{ route('financials.depreciation') }}"
                            title="Depreciation Value"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('financials.depreciation*')) }}"
                        >
                            <i class="fas fa-arrow-trend-down w-4 text-center {{ $iconClass($isActive('financials.depreciation*')) }}"></i>
                            <span>Depreciation Value</span>
                        </a>

                        {{-- Salvage Value Records --}}
                        <a
                            href="{{ route('financials.salvage') }}"
                            title="Salvage Value Records"
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
                    title="Tracking & Reporting"
                    @click="if (sidebarCollapsed) { window.location.href = '{{ route('asset-history.index') }}'; } else { open = !open; }"
                    :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'justify-between gap-3 px-3 py-2'"
                    class="w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-normal transition-colors select-none text-left {{ $headerClass($isTrackingActive) }}"
                >
                    <div class="flex items-center min-w-0" :class="sidebarCollapsed ? 'justify-center' : 'gap-2.5'">
                        <i class="fas fa-chart-pie w-5 text-center shrink-0 {{ $headerIconClass($isTrackingActive) }}"></i>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Tracking & Reporting</span>
                    </div>
                    <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] ml-2 shrink-0 {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <div
                    x-show="open && !sidebarCollapsed"
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
                        title="Asset History & Audit"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('asset-history.*')) }}"
                    >
                        <i class="fas fa-clock-rotate-left w-4 text-center {{ $iconClass($isActive('asset-history.*')) }}"></i>
                        <span>Asset History & Audit</span>
                    </a>

                    {{-- Asset Locations (All Roles) --}}
                    <a
                        href="{{ route('locations.index') }}"
                        title="Asset Locations"
                        class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('locations.*')) }}"
                    >
                        <i class="fas fa-location-dot w-4 text-center {{ $iconClass($isActive('locations.*')) }}"></i>
                        <span>Asset Locations</span>
                    </a>

                    {{-- Reports Center (Admin Only) --}}
                    @if($isAdmin)
                        <a
                            href="{{ route('reports.index') }}"
                            title="Reports Center"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('reports.*')) }}"
                        >
                            <i class="fas fa-file-invoice w-4 text-center {{ $iconClass($isActive('reports.*')) }}"></i>
                            <span>Reports Center</span>
                        </a>

                        {{-- Archived Records (Admin Only) --}}
                        <a
                            href="{{ route('archived-records.index') }}"
                            title="Archived Records"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('archived-records.*')) }}"
                        >
                            <i class="fas fa-box-archive w-4 text-center {{ $iconClass($isActive('archived-records.*')) }}"></i>
                            <span>Archived Records</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- 6. Administration (Admin Only) OR Account Settings (Non-Admin) --}}
            @if($isAdmin)
                <div x-data="{ open: {{ $isAdminActive ? 'true' : 'false' }} }" class="pt-1">
                    <button
                        type="button"
                        title="Administration"
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('users.index') }}'; } else { open = !open; }"
                        :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'justify-between px-3 py-2'"
                        class="w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $headerClass($isAdminActive) }}"
                    >
                        <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-2.5'">
                            <i class="fas fa-sliders w-5 text-center {{ $headerIconClass($isAdminActive) }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Administration</span>
                        </div>
                        <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] {{ $chevronClass }} transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>

                    <div
                        x-show="open && !sidebarCollapsed"
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
                            title="User Management"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('users.*')) }}"
                        >
                            <i class="fas fa-users-gear w-4 text-center {{ $iconClass($isActive('users.*')) }}"></i>
                            <span>User Management</span>
                        </a>

                        {{-- Roles & Permissions --}}
                        <a
                            href="{{ route('roles.index') }}"
                            title="Roles & Permissions"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors text-xs font-medium {{ $linkClass($isActive('roles.*')) }}"
                        >
                            <i class="fas fa-user-shield w-4 text-center {{ $iconClass($isActive('roles.*')) }}"></i>
                            <span>Roles & Permissions</span>
                        </a>

                        {{-- Account Settings --}}
                        <a
                            href="{{ route('profile.edit') }}"
                            title="Account Settings"
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
                        title="Account Settings"
                        :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-2.5 px-3 py-2'"
                        class="w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-wider transition-colors select-none text-left {{ $directLinkClass($isActive('profile.*')) }}"
                    >
                        <i class="fas fa-id-badge w-5 text-center {{ $directIconClass($isActive('profile.*')) }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Account Settings</span>
                    </a>
                </div>
            @endif
        </div>
        </div>

        {{-- Bottom Sidebar Logout Button --}}
        <div class="p-3 border-t border-white/10 mt-auto">
            <button
                type="button"
                title="Log Out"
                @click="window.dispatchEvent(new CustomEvent('bdes-open-logout'))"
                :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'gap-2.5 px-3 py-2.5'"
                class="w-full flex items-center rounded-lg text-xs font-bold uppercase tracking-wider text-rose-200 hover:text-white hover:bg-red-600/80 transition-colors select-none text-left cursor-pointer"
            >
                <i class="fas fa-right-from-bracket w-5 text-center text-rose-300"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Log Out</span>
            </button>
        </div>
    </nav>
</aside>
