<!-- Sidebar Navigation -->
<aside 
    :class="{'w-64': sidebarOpen, 'w-0': !sidebarOpen}"
    class="w-64 flex-shrink-0 bg-white border-r border-gray-200 overflow-hidden transition-all duration-300 ease-in-out shadow-sm"
>
    <nav class="h-screen overflow-y-auto" x-data="{ openDropdown: null }">
        {{-- Sidebar Content --}}
        <div class="p-6 space-y-2">
            @php
                $isActive = function (string $routePattern) {
                    return request()->routeIs($routePattern);
                };

                $assetManagementOpen = (
                    request()->routeIs('assets.*') ||
                    request()->routeIs('accessories.*') ||
                    request()->routeIs('attachments.*') ||
                    request()->routeIs('operational-requirements.*')
                );
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

            {{-- 3. Asset Management (dropdown) --}}
            <div>
                <x-sidebar-menu-item
                    icon="fas fa-boxes-stacked"
                    label="Asset Management"
                    :active="request()->routeIs('assets.*','accessories.*','attachments.*','operational-requirements.*')"
                    :expanded="$assetManagementOpen"
                    setExpanded="openDropdown = openDropdown === 'asset-management' ? null : 'asset-management'"
                >
                    <ul class="mt-1 space-y-1">
                        <li>
                            <x-sidebar-submenu-item
                                label="Asset Registry"
                                href="{{ route('assets.index') }}"
                                :active="request()->routeIs('assets.*')"
                            />
                        </li>
                        <li>
                            <x-sidebar-submenu-item
                                label="Asset Accessories"
                                href="{{ route('accessories.index') }}"
                                :active="request()->routeIs('accessories.*')"
                            />
                        </li>
                        <li>
                            <x-sidebar-submenu-item
                                label="Asset Attachments"
                                href="{{ route('attachments.index') }}"
                                :active="request()->routeIs('attachments.*')"
                            />
                        </li>
                        <li>
                            <x-sidebar-submenu-item
                                label="Operational Requirements"
                                href="{{ route('operational-requirements.index') }}"
                                :active="request()->routeIs('operational-requirements.*')"
                            />
                        </li>
                        <li>
                            <x-sidebar-submenu-item
                                label="Depreciation Tracking"
                                href="#"
                                :active="false"
                            />
                        </li>
                        <li>
                            <x-sidebar-submenu-item
                                label="Salvage Value Records"
                                href="#"
                                :active="false"
                            />
                        </li>
                    </ul>
                </x-sidebar-menu-item>
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

