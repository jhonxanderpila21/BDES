@extends('layouts.admin')

@section('title', 'Roles & Permissions - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Role & Access Control</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Administration'],
                ['label' => 'Roles & Permissions']
            ]" />
        </div>
        <a href="{{ route('users.index') }}" class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm">
            <i class="fas fa-users-cog"></i>
            Manage User Accounts
        </a>
    </div>

    {{-- Information Alert --}}
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-3">
        <i class="fas fa-shield-alt text-blue-600 text-lg mt-0.5"></i>
        <div class="text-xs text-blue-900 leading-relaxed">
            <strong class="font-semibold text-blue-950">BDES Role-Based Access Control (RBAC):</strong>
            The system employs 4 dedicated operational roles strictly governed by Laravel Policies, Form Requests, and Middleware: <strong>Admin</strong>, <strong>Inventory</strong>, <strong>Maintenance</strong>, and <strong>Staff for Viewing</strong>.
        </div>
    </div>

    @php
        $rolesData = [
            [
                'key'         => 'administrator',
                'name'        => 'Admin',
                'badge'       => 'danger',
                'description' => 'Full administrative control, user account provisioning, system settings, asset retirement, and executive audit.',
                'scope'       => 'All Modules',
                'permissions' => [
                    'User Account Management (Create, Edit, Disable, Reset Password)',
                    'Asset Registry & Equipment Dossiers (Full CRUD)',
                    'Asset Decommissioning & Retirement Authorization',
                    'Field Deployments & Return Inspections',
                    'Equipment Maintenance, Schedules & Inspections',
                    'Asset Accessories, Needs & Consumables Tracking',
                    'COA Depreciation Tracking & Salvage Value Records',
                    'Reports Center & Audit Trail Oversight',
                    'Asset Location Management',
                ],
            ],
            [
                'key'         => 'inventory_officer',
                'name'        => 'Inventory',
                'badge'       => 'warning',
                'description' => 'Field equipment operations, asset registry, deployments, return check-ins, accessories, and consumables replenishment.',
                'scope'       => 'Inventory & Field Operations',
                'permissions' => [
                    'Asset Registry (Create, Edit, Philippine Standard Auto-Computation)',
                    'Field Deployments & Operations Tracking',
                    'Return Records & Return Inspection Processing',
                    'Accessories & Attachment Stock Management',
                    'Operational Requirements & Consumables Refilling',
                    'Reported Incident Assets (Filing & Updating)',
                    'Asset History & Audit Trail Review',
                    'Asset Locations Inquiry',
                ],
            ],
            [
                'key'         => 'maintenance_officer',
                'name'        => 'Maintenance',
                'badge'       => 'success',
                'description' => 'Equipment servicing, repair work orders, preventive maintenance schedules, and physical quality inspections.',
                'scope'       => 'Equipment Upkeep & Service',
                'permissions' => [
                    'Maintenance Hub & Work Orders (Create, Update, Sign-off)',
                    'Preventive Maintenance Scheduling (Interval Tracking)',
                    'Physical Asset Inspections & Diagnostic Reports',
                    'Asset Registry & Technical Dossiers Inspection',
                    'Asset History Review',
                    'Asset Locations Inquiry',
                ],
            ],
            [
                'key'         => 'viewer',
                'name'        => 'Staff for Viewing',
                'badge'       => 'secondary',
                'description' => 'Read-only inquiry access to asset registries, location mappings, and incident monitoring logs.',
                'scope'       => 'Read-Only Catalog',
                'permissions' => [
                    'Read-only Asset Catalog & Specifications Inquiry',
                    'Asset Locations Inquiry',
                    'Incident Monitoring View',
                    'Personal Profile Settings',
                ],
            ],
        ];
    @endphp

    {{-- Roles Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role Name</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Functional Scope</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Assigned Users</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Enforcement</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($rolesData as $role)
                        @php
                            $userCount = \App\Models\User::where('role', $role['key'])->count();
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-left">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2.5 h-2.5 rounded-full {{ $role['badge'] === 'danger' ? 'bg-red-500' : ($role['badge'] === 'primary' ? 'bg-blue-600' : ($role['badge'] === 'warning' ? 'bg-amber-500' : ($role['badge'] === 'success' ? 'bg-emerald-500' : 'bg-gray-400'))) }}"></div>
                                    <p class="text-sm font-bold text-gray-900">{{ $role['name'] }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                <x-badge variant="{{ $role['badge'] }}" size="sm">{{ $role['scope'] }}</x-badge>
                            </td>
                            <td class="px-6 py-4 align-middle text-xs text-gray-600 max-w-xs text-left">{{ $role['description'] }}</td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    <i class="fas fa-users text-gray-500 text-[10px]"></i>
                                    {{ $userCount }} {{ Str::plural('account', $userCount) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1 text-xs text-emerald-700 font-medium">
                                    <i class="fas fa-check-circle text-emerald-500"></i> Active Policy
                                </span>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-right text-sm">
                                <button
                                    type="button"
                                    onclick="viewRoleDetails(@js($role['name']), @js($role['scope']), @js($role['description']), @js($role['permissions']), {{ $userCount }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors text-xs font-semibold"
                                >
                                    <i class="fas fa-list-check text-blue-600"></i>
                                    <span>Permissions</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- View Role Permissions Modal --}}
    <x-modal id="view-role-modal" title="Role Permissions & Authority Matrix">
        <div class="space-y-4">
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                <div class="flex items-center justify-between mb-1">
                    <h3 id="role_modal_title" class="text-base font-bold text-gray-900">Role Title</h3>
                    <span id="role_modal_scope" class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Scope</span>
                </div>
                <p id="role_modal_desc" class="text-xs text-gray-600 mt-1"></p>
                <div class="mt-2 text-xs text-gray-500">
                    Active assigned accounts: <strong id="role_modal_users" class="text-gray-800">0</strong>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Granted Authorities & Capabilities:</h4>
                <ul id="role_modal_perms" class="space-y-2 text-xs text-gray-700"></ul>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <x-button variant="secondary" type="button" onclick="window.closeModal('view-role-modal')" class="w-full">
                    Close Details
                </x-button>
            </div>
        </div>
    </x-modal>

    @push('scripts')
        <script>
            function viewRoleDetails(name, scope, desc, perms, userCount) {
                document.getElementById('role_modal_title').textContent = name;
                document.getElementById('role_modal_scope').textContent = scope;
                document.getElementById('role_modal_desc').textContent = desc;
                document.getElementById('role_modal_users').textContent = userCount + ' user account' + (userCount === 1 ? '' : 's');

                const listEl = document.getElementById('role_modal_perms');
                listEl.innerHTML = '';
                perms.forEach(perm => {
                    const li = document.createElement('li');
                    li.className = 'flex items-start gap-2 p-2 rounded-lg bg-gray-50 border border-gray-100';
                    li.innerHTML = `<i class="fas fa-check text-emerald-600 mt-0.5 text-xs flex-shrink-0"></i> <span>${perm}</span>`;
                    listEl.appendChild(li);
                });

                window.openModal('view-role-modal');
            }
        </script>
    @endpush
@endsection
