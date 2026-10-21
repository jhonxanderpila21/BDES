@extends('layouts.admin')

@section('title', 'Users - BDES')

@php
    $roleLabels = [
        'administrator' => 'Administrator',
        'barangay_captain' => 'Barangay Captain',
        'inventory_officer' => 'Inventory Officer',
        'maintenance_officer' => 'Maintenance Officer',
        'viewer' => 'Viewer',
    ];
@endphp

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">User Management</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'User Management'],
                ['label' => 'Users']
            ]" />
        </div>
        <button onclick="openAddUserModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add User
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('users.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search users by name, username or email..." />
            </form>
        </div>
        <form action="{{ route('users.index') }}" method="GET">
            <select name="role" onchange="this.form.submit()" class="flex items-center gap-2 pl-4 pr-10 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Roles</option>
                @foreach($roleLabels as $value => $label)
                    <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
        <form action="{{ route('users.index') }}" method="GET">
            <select name="status" onchange="this.form.submit()" class="flex items-center gap-2 pl-4 pr-10 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived (Deleted)</option>
            </select>
        </form>
    </div>

    {{-- Users Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 text-sm font-semibold">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->username }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="primary" size="sm">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->trashed())
                                    <x-badge variant="danger" size="sm">Archived</x-badge>
                                @else
                                    <x-badge :variant="$user->is_active ? 'success' : 'secondary'" size="sm">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    @if($user->trashed())
                                        <form action="{{ route('users.restore', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Restore this user account?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-2 rounded hover:bg-emerald-50 transition-colors text-emerald-600 hover:text-emerald-800" title="Restore Account">
                                                <i class="fas fa-trash-arrow-up"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button
                                            onclick="openEditUserModal({{ $user->id }}, @js($user->first_name), @js($user->middle_name), @js($user->last_name), @js($user->suffix), @js($user->username), @js($user->email), @js($user->role))"
                                            class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600 hover:text-yellow-800" title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('users.toggle-active', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ $user->is_active ? 'Disable' : 'Enable' }} this account?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors {{ $user->is_active ? 'text-orange-600 hover:text-orange-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $user->is_active ? 'Disable account' : 'Enable account' }}">
                                                    <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('users.reset-password', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Reset password for {{ $user->name }} to standard temporary password (TempPass_123)?');">
                                            @csrf
                                            <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-blue-600 hover:text-blue-800" title="Reset to TempPass_123">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </form>

                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Archive (soft-delete) this user account?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-red-600 hover:text-red-800" title="Archive / Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8">
                                <x-empty-state
                                    icon="fas fa-users"
                                    title="No users found"
                                    message="Start by adding your first user to the system"
                                >
                                    <button onclick="openAddUserModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                        <i class="fas fa-plus"></i>
                                        Add User
                                    </button>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $users->links() }}
        </div>
    </x-card>

    {{-- Add User Modal --}}
    <x-modal id="add-user-modal" title="Add New User" :show="$errors->any() && old('_form') === 'add_user'">
        <form class="space-y-4" action="{{ route('users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_form" value="add_user">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="First Name" required>
                    <input type="text" name="first_name" placeholder="e.g., Juan" value="{{ old('first_name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Middle Name (Optional)">
                    <input type="text" name="middle_name" placeholder="e.g., Santos" value="{{ old('middle_name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Last Name" required>
                    <input type="text" name="last_name" placeholder="e.g., Dela Cruz" value="{{ old('last_name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Suffix (Optional)">
                    <select name="suffix" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">None</option>
                        @foreach(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $sfx)
                            <option value="{{ $sfx }}" {{ old('suffix') === $sfx ? 'selected' : '' }}>{{ $sfx }}</option>
                        @endforeach
                    </select>
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Username" required>
                    <input type="text" name="username" placeholder="e.g., jdelacruz" value="{{ old('username') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Email Address" required>
                    <input type="email" name="email" placeholder="e.g., jdelacruz@bdes.gov.ph" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <x-form-group label="Role" required>
                <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Role</option>
                    @foreach($roleLabels as $value => $label)
                        <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </x-form-group>

            {{-- Auto-set password info --}}
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 flex items-center gap-2">
                <i class="fas fa-shield-halved text-blue-600 text-sm"></i>
                <span>Password will be automatically set to default temporary password: <strong>TempPass_123</strong>.</span>
            </div>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Create User</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-user-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit User Modal --}}
    <x-modal id="edit-user-modal" title="Edit User" :show="$errors->any() && old('_form') === 'edit_user'">
        <form class="space-y-4" id="edit-user-form" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="edit_user">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="First Name" required>
                    <input type="text" name="first_name" id="edit-user-first-name" value="{{ old('_form') === 'edit_user' ? old('first_name') : '' }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Middle Name (Optional)">
                    <input type="text" name="middle_name" id="edit-user-middle-name" value="{{ old('_form') === 'edit_user' ? old('middle_name') : '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Last Name" required>
                    <input type="text" name="last_name" id="edit-user-last-name" value="{{ old('_form') === 'edit_user' ? old('last_name') : '' }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Suffix (Optional)">
                    <select name="suffix" id="edit-user-suffix" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">None</option>
                        @foreach(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $sfx)
                            <option value="{{ $sfx }}">{{ $sfx }}</option>
                        @endforeach
                    </select>
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Username" required>
                    <input type="text" name="username" id="edit-user-username" value="{{ old('_form') === 'edit_user' ? old('username') : '' }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Email Address" required>
                    <input type="email" name="email" id="edit-user-email" value="{{ old('_form') === 'edit_user' ? old('email') : '' }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <x-form-group label="Role" required>
                <select name="role" id="edit-user-role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach($roleLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-user-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddUserModal() {
                window.openModal('add-user-modal');
            }

            function openEditUserModal(id, firstName, middleName, lastName, suffix, username, email, role) {
                const form = document.getElementById('edit-user-form');
                form.action = `/users/${id}`;
                document.getElementById('edit-user-first-name').value = firstName || '';
                document.getElementById('edit-user-middle-name').value = middleName || '';
                document.getElementById('edit-user-last-name').value = lastName || '';
                document.getElementById('edit-user-suffix').value = suffix || '';
                document.getElementById('edit-user-username').value = username || '';
                document.getElementById('edit-user-email').value = email || '';
                document.getElementById('edit-user-role').value = role || '';
                window.openModal('edit-user-modal');
            }
        </script>
    @endpush
@endsection
