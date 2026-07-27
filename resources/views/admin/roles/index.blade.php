@extends('layouts.admin')

@section('title', 'Roles - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Role Management</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'User Management'],
                ['label' => 'Roles']
            ]" />
        </div>
        <button onclick="openAddRoleModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Role
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <x-search-bar placeholder="Search roles..." />
        </div>
    </div>

    {{-- Roles Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $roles = [
                            ['name' => 'Administrator', 'description' => 'Full system access', 'permissions' => 15, 'users' => 2, 'status' => 'Active'],
                            ['name' => 'Manager', 'description' => 'Can manage assets and operations', 'permissions' => 12, 'users' => 5, 'status' => 'Active'],
                            ['name' => 'User', 'description' => 'Standard user access', 'permissions' => 8, 'users' => 12, 'status' => 'Active'],
                            ['name' => 'Viewer', 'description' => 'Read-only access', 'permissions' => 3, 'users' => 4, 'status' => 'Active'],
                        ];
                    @endphp
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $role['name'] }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $role['description'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="info" size="sm">{{ $role['permissions'] }} permissions</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $role['users'] }} users</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="success" size="sm">{{ $role['status'] }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button onclick="viewRole('{{ $role['name'] }}')" class="p-2 rounded hover:bg-gray-100 transition-colors text-blue-600 hover:text-blue-800" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editRole('{{ $role['name'] }}')" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteRole('{{ $role['name'] }}')" class="p-2 rounded hover:bg-gray-100 transition-colors text-red-600 hover:text-red-800" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8">
                                <x-empty-state 
                                    icon="fas fa-lock"
                                    title="No roles found"
                                    message="Start by adding your first role"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-pagination current="1" total="4" perPage="10" onPageChange="handlePageChange" />
    </x-card>

    {{-- Add/Edit Role Modal --}}
    <x-modal id="add-role-modal" title="Add New Role">
        <form class="space-y-4">
            <x-form-group label="Role Name" required>
                <input type="text" placeholder="Supervisor" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Description" required>
                <textarea placeholder="Brief description of the role" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <x-form-group label="Permissions" required>
                <div class="space-y-3 border border-gray-300 rounded-lg p-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-gray-700">View Assets</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-gray-700">Create Assets</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-gray-700">Edit Assets</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-gray-700">Delete Assets</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-gray-700">Deploy Assets</span>
                    </label>
                </div>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1">Create Role</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-role-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddRoleModal() {
                window.openModal('add-role-modal');
            }

            function viewRole(name) { alert(`View role: ${name}`); }
            function editRole(name) { alert(`Edit role: ${name}`); }
            function deleteRole(name) { if(confirm(`Delete ${name}?`)) alert(`Role ${name} deleted`); }
            function handlePageChange(page) { alert(`Go to page ${page}`); }
        </script>
    @endpush
@endsection
