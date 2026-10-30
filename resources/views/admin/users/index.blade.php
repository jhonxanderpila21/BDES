@extends('layouts.admin')

@section('title', 'Users - BDES')

@php
    $roleLabels = [
        'administrator'       => 'Admin',
        'inventory_officer'   => 'Inventory',
        'maintenance_officer' => 'Maintenance',
        'viewer'              => 'Staff (Viewing)',
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
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-left">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 text-sm font-semibold">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 text-left">{{ $user->username }}</td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 text-left">{{ $user->email }}</td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                <x-badge variant="primary" size="sm">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                @if($user->trashed())
                                    <x-badge variant="danger" size="sm">Archived</x-badge>
                                @else
                                    <x-badge :variant="$user->is_active ? 'success' : 'secondary'" size="sm">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($user->trashed())
                                        <form action="{{ route('users.restore', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirmSecureAction(event, this, { title: 'Restore User Account', itemName: '{{ addslashes($user->name) }} ({{ $user->username }})', warning: 'This account will be reactivated and restored to active user listings.', confirmText: 'Yes, Restore Account', iconClass: 'fas fa-trash-arrow-up', isDestructive: false });">
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
                                            <form action="{{ route('users.toggle-active', $user) }}" method="POST" class="inline-block" onsubmit="return confirmSecureAction(event, this, { title: '{{ $user->is_active ? 'Disable' : 'Enable' }} User Account', itemName: '{{ addslashes($user->name) }} ({{ $user->username }})', warning: '{{ $user->is_active ? 'This user will be temporarily blocked from signing in.' : 'This user account will regain active sign-in access.' }}', confirmText: '{{ $user->is_active ? 'Yes, Disable Account' : 'Yes, Enable Account' }}', iconClass: 'fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}', isDestructive: {{ $user->is_active ? 'true' : 'false' }} });">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors {{ $user->is_active ? 'text-orange-600 hover:text-orange-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $user->is_active ? 'Disable account' : 'Enable account' }}">
                                                    <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('users.reset-password', $user) }}" method="POST" class="inline-block" onsubmit="return confirmSecureAction(event, this, { title: 'Reset User Password', itemName: '{{ addslashes($user->name) }} ({{ $user->username }})', warning: 'The account password will be reset to temporary default (TempPass_123).', confirmText: 'Yes, Reset Password', iconClass: 'fas fa-key', isDestructive: false });">
                                            @csrf
                                            <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-blue-600 hover:text-blue-800" title="Reset to TempPass_123">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </form>

                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'User Account: {{ addslashes($user->name) }} ({{ $user->username }})', 'This user account will be archived and will lose system access.');">
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
                    <input type="text" name="first_name" id="add-user-first-name" placeholder="e.g., Juan" value="{{ old('first_name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Middle Name (Optional)">
                    <input type="text" name="middle_name" id="add-user-middle-name" placeholder="e.g., Santos" value="{{ old('middle_name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Last Name" required>
                    <input type="text" name="last_name" id="add-user-last-name" placeholder="e.g., Dela Cruz" value="{{ old('last_name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Suffix (Optional)">
                    <select name="suffix" id="add-user-suffix" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">None</option>
                        @foreach(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $sfx)
                            <option value="{{ $sfx }}" {{ old('suffix') === $sfx ? 'selected' : '' }}>{{ $sfx }}</option>
                        @endforeach
                    </select>
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Username" required>
                    <div class="relative">
                        <input type="text" name="username" id="add-user-username" placeholder="e.g., jdelacruz" value="{{ old('username') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <span id="add-user-username-hint" class="text-[11px] text-gray-500 mt-1 block">Auto-generated from First and Last Name.</span>
                </x-form-group>

                <x-form-group label="Email Address" required>
                    <div class="relative">
                        <input type="email" name="email" id="add-user-email" placeholder="e.g., jdelacruz@bdes.gov" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <span id="add-user-email-hint" class="text-[11px] text-gray-500 mt-1 block">Auto-generated based on First and Last Name.</span>
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
            // Existing usernames and emails to prevent collisions instantly in real-time
            const existingUsernames = new Set(@json(\App\Models\User::withTrashed()->pluck('username')->map(fn($u) => strtolower($u))));
            const existingEmails = new Set(@json(\App\Models\User::withTrashed()->pluck('email')->map(fn($e) => strtolower($e))));

            const addFirstNameInput = document.getElementById('add-user-first-name');
            const addLastNameInput = document.getElementById('add-user-last-name');
            const addUsernameInput = document.getElementById('add-user-username');
            const addEmailInput = document.getElementById('add-user-email');
            const addUsernameHint = document.getElementById('add-user-username-hint');
            const addEmailHint = document.getElementById('add-user-email-hint');

            let usernameManuallyChanged = {{ old('username') ? 'true' : 'false' }};
            let emailManuallyChanged = {{ old('email') ? 'true' : 'false' }};

            function openAddUserModal() {
                if (!{!! json_encode(old('_form') === 'add_user') !!}) {
                    usernameManuallyChanged = false;
                    emailManuallyChanged = false;
                }
                window.openModal('add-user-modal');
            }

            addUsernameInput?.addEventListener('input', function() {
                usernameManuallyChanged = this.value.trim().length > 0;
                if (!usernameManuallyChanged) {
                    autoGenerateCredentials();
                } else if (addUsernameHint) {
                    addUsernameHint.innerHTML = '<span class="text-gray-500 text-[11px]"><i class="fas fa-pen text-gray-400 mr-1"></i> Custom username entered.</span>';
                }
            });

            addEmailInput?.addEventListener('input', function() {
                emailManuallyChanged = this.value.trim().length > 0;
                if (!emailManuallyChanged) {
                    autoGenerateCredentials();
                } else if (addEmailHint) {
                    addEmailHint.innerHTML = '<span class="text-gray-500 text-[11px]"><i class="fas fa-pen text-gray-400 mr-1"></i> Custom email address entered.</span>';
                }
            });

            function autoGenerateCredentials() {
                const firstName = (addFirstNameInput?.value || '').trim();
                const lastName = (addLastNameInput?.value || '').trim();

                if (!firstName || !lastName) {
                    if (!usernameManuallyChanged && addUsernameInput) {
                        addUsernameInput.value = '';
                        if (addUsernameHint) addUsernameHint.innerHTML = '<span class="text-[11px] text-gray-500">Auto-generated from First and Last Name.</span>';
                    }
                    if (!emailManuallyChanged && addEmailInput) {
                        addEmailInput.value = '';
                        if (addEmailHint) addEmailHint.innerHTML = '<span class="text-[11px] text-gray-500">Auto-generated based on First and Last Name.</span>';
                    }
                    return;
                }

                // Sanitize to alphanumeric characters
                const cleanFirst = firstName.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, '');
                const cleanLast = lastName.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, '');

                const initial = cleanFirst.length > 0 ? cleanFirst.charAt(0) : '';
                const base = (initial + cleanLast) || 'user';
                const domain = 'bdes.gov';

                let candidateUser = base;
                let candidateEmail = candidateUser + '@' + domain;
                let counter = 1;

                while (existingUsernames.has(candidateUser) || existingEmails.has(candidateEmail)) {
                    counter++;
                    candidateUser = base + counter;
                    candidateEmail = candidateUser + '@' + domain;
                }

                if (!usernameManuallyChanged && addUsernameInput) {
                    addUsernameInput.value = candidateUser;
                    if (addUsernameHint) {
                        if (counter > 1) {
                            addUsernameHint.innerHTML = '<span class="text-amber-600 font-medium text-[11px]"><i class="fas fa-shield-alt mr-1"></i> Suffix <strong>' + counter + '</strong> auto-applied to avoid conflict with existing ' + base + '.</span>';
                        } else {
                            addUsernameHint.innerHTML = '<span class="text-emerald-600 font-medium text-[11px]"><i class="fas fa-check-circle mr-1"></i> Unique username auto-generated.</span>';
                        }
                    }
                }

                if (!emailManuallyChanged && addEmailInput) {
                    addEmailInput.value = candidateEmail;
                    if (addEmailHint) {
                        if (counter > 1) {
                            addEmailHint.innerHTML = '<span class="text-amber-600 font-medium text-[11px]"><i class="fas fa-shield-alt mr-1"></i> Suffix <strong>' + counter + '</strong> auto-applied to avoid conflict with existing ' + base + '@' + domain + '.</span>';
                        } else {
                            addEmailHint.innerHTML = '<span class="text-emerald-600 font-medium text-[11px]"><i class="fas fa-check-circle mr-1"></i> Unique email address auto-generated.</span>';
                        }
                    }
                }
            }

            addFirstNameInput?.addEventListener('input', autoGenerateCredentials);
            addLastNameInput?.addEventListener('input', autoGenerateCredentials);

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
