<!-- Sidebar Navigation -->
<aside 
    :class="{'w-64': sidebarOpen, 'w-0': !sidebarOpen}"
    class="w-64 flex-shrink-0 bg-white border-r border-gray-200 overflow-hidden transition-all duration-300 ease-in-out shadow-sm"
>
    <nav class="h-screen overflow-y-auto">
        {{-- Sidebar Content --}}
        <div class="p-6 space-y-2">
            @php
                $isActive = function (string $routePattern) {
                    return request()->routeIs($routePattern);
                };
            @endphp

            {{-- 1. Dashboard --}}
            <a
                href="{{ route('dashboard') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('dashboard') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-th-large {{ $isActive('dashboard') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Dashboard</span>
            </a>

            {{-- 2. User Management --}}
            <a
                href="{{ route('users.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('users.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-users {{ $isActive('users.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>User Management</span>
            </a>

            {{-- 3. Asset Management --}}
            <div class="space-y-1">
                <a
                    href="{{ route('assets.index') }}"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ request()->routeIs('assets.*') && !request()->routeIs('accessories.*', 'asset-accessories.*', 'attachments.*', 'operational-requirements.*', 'asset-needs.*') ? 'bg-blue-50 text-blue-600' : '' }}"
                >
                    <i class="fas fa-boxes-stacked {{ request()->routeIs('assets.*') && !request()->routeIs('accessories.*', 'asset-accessories.*', 'attachments.*', 'operational-requirements.*', 'asset-needs.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                    <span>Asset Management</span>
                </a>

                <ul class="pl-2 space-y-1">
                    <li>
                        <x-sidebar-submenu-item
                            label="Asset Registry"
                            href="{{ route('assets.index') }}"
                            icon="fas fa-clipboard-list"
                            :active="request()->routeIs('assets.*') && !request()->routeIs('accessories.*', 'asset-accessories.*', 'attachments.*', 'operational-requirements.*', 'asset-needs.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar-submenu-item
                            label="Asset Accessories"
                            href="{{ route('asset-accessories.index') }}"
                            icon="fas fa-tools"
                            :active="request()->routeIs('asset-accessories.*') || request()->routeIs('accessories.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar-submenu-item
                            label="Asset Attachments"
                            href="{{ route('attachments.index') }}"
                            icon="fas fa-paperclip"
                            :active="request()->routeIs('attachments.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar-submenu-item
                            label="Operational Requirements"
                            href="{{ route('operational-requirements.index') }}"
                            icon="fas fa-list-check"
                            :active="request()->routeIs('operational-requirements.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar-submenu-item
                            label="Asset Needs"
                            href="{{ route('asset-needs.index') }}"
                            icon="fas fa-gas-pump"
                            :active="request()->routeIs('asset-needs.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar-submenu-item
                            label="Depreciation Tracking"
                            href="#"
                            icon="fas fa-arrow-trend-down"
                            :active="false"
                        />
                    </li>
                    <li>
                        <x-sidebar-submenu-item
                            label="Salvage Value Records"
                            href="#"
                            icon="fas fa-recycle"
                            :active="false"
                        />
                    </li>
                </ul>
            </div>

            {{-- 4. Asset Categories --}}
            <a
                href="{{ route('asset-categories.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('asset-categories.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-folder-open {{ $isActive('asset-categories.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Asset Categories</span>
            </a>

            {{-- 5. Asset Types --}}
            <a
                href="{{ route('asset-types.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('asset-types.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-tags {{ $isActive('asset-types.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Asset Types</span>
            </a>

            {{-- 6. Deployment --}}
            <a
                href="{{ route('deployment.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('deployment.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-truck {{ $isActive('deployment.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Deployment</span>
            </a>

            {{-- 7. Return --}}
            <a
                href="{{ route('returns.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('returns.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-undo-alt {{ $isActive('returns.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Return</span>
            </a>

            {{-- 8. Maintenance --}}
            <a href="{{ route('maintenance-schedules.index') }}" 
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('maintenance-schedules.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                <i class="fas fa-calendar-check w-5 text-center {{ request()->routeIs('maintenance-schedules.*') ? 'text-blue-700' : 'text-gray-400' }}"></i>
                Preventive Maintenance
            </a>
            <a
                href="{{ route('maintenance.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('maintenance.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-wrench {{ $isActive('maintenance.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Maintenance</span>
            </a>

            {{-- 9. Inspection --}}
            <a
                href="{{ route('inspection.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('inspection.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-clipboard-check {{ $isActive('inspection.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Inspection</span>
            </a>

            {{-- 10. Incident Reports --}}
            <a
                href="{{ route('incident-reports.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('incident-reports.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-exclamation-triangle {{ $isActive('incident-reports.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Incident Reports</span>
            </a>

            {{-- 10.5. Reported Asset --}}
            <a
                href="{{ route('reported-assets.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('reported-assets.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-flag {{ $isActive('reported-assets.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Reported Asset</span>
            </a>

            {{-- 11. Asset History --}}
            <a
                href="{{ route('asset-history.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('asset-history.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-clock {{ $isActive('asset-history.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Asset History</span>
            </a>

            {{-- 12. Reports --}}
            <a
                href="{{ route('reports.index') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('reports.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-chart-bar {{ $isActive('reports.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Reports</span>
            </a>

            {{-- 13. Account Settings --}}
            <a
                href="{{ route('profile.edit') }}"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-700 text-sm font-medium hover:bg-gray-50 {{ $isActive('profile.*') ? 'bg-blue-50 text-blue-600' : '' }}"
            >
                <i class="fas fa-user-circle {{ $isActive('profile.*') ? 'text-blue-600' : 'text-gray-600 group-hover:text-gray-900' }}"></i>
                <span>Account Settings</span>
            </a>
        </div>
    </nav>
</aside>

{{-- Sidebar Overlay for mobile --}}
<div 
    x-show="sidebarOpen"
    x-cloak
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black bg-opacity-50 lg:hidden z-30"
    x-transition
></div>

