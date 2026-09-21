@extends('layouts.admin')

@section('title', 'Account Settings - BDES')

@section('content')
<div class="space-y-6" x-data="{ deleteModal: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-blue-100 text-blue-700 rounded-lg"><i class="fas fa-id-badge"></i></span>
                Account Settings
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Administration'],
                ['label' => 'Account Settings']
            ]" />
        </div>
    </div>

    {{-- Session Feedback Alerts --}}
    @if(session('status') === 'profile-updated')
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i class="fas fa-circle-check text-emerald-600 text-lg"></i>
            <p class="text-sm font-medium">Your profile information has been successfully updated.</p>
        </div>
    @elseif(session('status') === 'password-updated')
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i class="fas fa-circle-check text-emerald-600 text-lg"></i>
            <p class="text-sm font-medium">Your account password has been updated securely.</p>
        </div>
    @endif

    {{-- Executive Profile Banner --}}
    <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 opacity-10 text-9xl">
            <i class="fas fa-shield-halved"></i>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-3xl font-bold text-white shadow-inner flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-sm">
                        {{ ucfirst(str_replace('_', ' ', $user->role ?? 'Staff')) }}
                    </span>
                    @if($user->is_active ?? true)
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Active Account
                        </span>
                    @endif
                </div>
                <p class="text-sm text-blue-100 flex items-center gap-2">
                    <i class="fas fa-envelope text-xs opacity-75"></i>
                    <span>{{ $user->email }}</span>
                    @if($user->username)
                        <span class="opacity-50">•</span>
                        <span class="font-mono text-xs">@<span>{{ $user->username }}</span></span>
                    @endif
                </p>
                <p class="text-xs text-blue-200/80 pt-1">
                    Member since {{ $user->created_at?->format('F d, Y') ?? 'N/A' }} • System User #{{ $user->id }}
                </p>
            </div>
        </div>
    </div>

    {{-- Main Settings Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Security Overview & Role Meta --}}
        <div class="space-y-6">
            {{-- Account Credentials & Access Card --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                    <i class="fas fa-shield-check text-blue-600"></i>
                    Access & Credentials
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="text-gray-500 font-medium">System Role</span>
                            <p class="text-gray-900 font-bold mt-0.5">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}</p>
                        </div>
                        <span class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i class="fas fa-user-gear"></i></span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="text-gray-500 font-medium">Role Authority</span>
                            <p class="text-gray-900 font-bold mt-0.5">
                                @if($user->role === 'administrator')
                                    Full Administrative Control
                                @elseif($user->role === 'barangay_captain')
                                    Executive Sign-off & Reports
                                @elseif($user->role === 'logistics_officer')
                                    Operations & Deployment
                                @else
                                    Staff Asset Monitoring
                                @endif
                            </p>
                        </div>
                        <span class="p-2 bg-purple-50 text-purple-600 rounded-lg"><i class="fas fa-key"></i></span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="text-gray-500 font-medium">Account Status</span>
                            <p class="text-emerald-700 font-bold mt-0.5 flex items-center gap-1.5">
                                <i class="fas fa-check-circle text-emerald-500"></i> Active & Verified
                            </p>
                        </div>
                        <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg"><i class="fas fa-id-card"></i></span>
                    </div>
                </div>
            </div>

            {{-- Security Tips & Best Practices --}}
            <div class="bg-blue-50/70 border border-blue-100 rounded-2xl p-5 text-xs text-blue-900 space-y-2">
                <h4 class="font-bold flex items-center gap-2 text-blue-950">
                    <i class="fas fa-lock text-blue-600"></i>
                    Security Recommendation
                </h4>
                <p class="text-blue-800/90 leading-relaxed">
                    Ensure your account uses a unique, complex passphrase of at least 8 characters. Do not share your disaster system credentials with unauthorized personnel.
                </p>
            </div>
        </div>

        {{-- Right Column (2 cols): Profile Forms --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal Profile Information --}}
            <x-card>
                <div class="p-5 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-user-pen text-blue-600"></i> Personal Information
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Update your display name, username, and official email address.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="w-full px-3.5 py-2 text-sm border @error('name') border-red-300 ring-1 ring-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('name')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Username --}}
                        <div>
                            <label for="username" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Username
                            </label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                value="{{ old('username', $user->username) }}"
                                placeholder="e.g., admin_bdes"
                                class="w-full px-3.5 py-2 text-sm border @error('username') border-red-300 ring-1 ring-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono"
                            >
                            @error('username')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                            Official Email Address <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="w-full px-3.5 py-2 text-sm border @error('email') border-red-300 ring-1 ring-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-gray-100">
                        <button
                            type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold shadow-sm transition flex items-center gap-2"
                        >
                            <i class="fas fa-floppy-disk"></i>
                            <span>Save Profile Changes</span>
                        </button>
                    </div>
                </form>
            </x-card>

            {{-- Update Password Form --}}
            <x-card>
                <div class="p-5 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-key text-blue-600"></i> Password & Security
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Ensure your account uses a strong, random password to maintain security.</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Current Password --}}
                    <div>
                        <label for="update_password_current_password" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="update_password_current_password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full px-3.5 py-2 text-sm border @if($errors->updatePassword->has('current_password')) border-red-300 ring-1 ring-red-300 @else border-gray-300 @endif rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        @if($errors->updatePassword->has('current_password'))
                            <p class="text-xs text-red-600 mt-1">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- New Password --}}
                        <div>
                            <label for="update_password_password" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                New Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="update_password_password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                class="w-full px-3.5 py-2 text-sm border @if($errors->updatePassword->has('password')) border-red-300 ring-1 ring-red-300 @else border-gray-300 @endif rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @if($errors->updatePassword->has('password'))
                                <p class="text-xs text-red-600 mt-1">{{ $errors->updatePassword->first('password') }}</p>
                            @endif
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="update_password_password_confirmation" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Confirm New Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="update_password_password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                class="w-full px-3.5 py-2 text-sm border @if($errors->updatePassword->has('password_confirmation')) border-red-300 ring-1 ring-red-300 @else border-gray-300 @endif rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @if($errors->updatePassword->has('password_confirmation'))
                                <p class="text-xs text-red-600 mt-1">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-gray-100">
                        <button
                            type="submit"
                            class="px-5 py-2.5 bg-gray-900 text-white rounded-lg hover:bg-black text-sm font-semibold shadow-sm transition flex items-center gap-2"
                        >
                            <i class="fas fa-lock"></i>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </x-card>

            {{-- Danger Zone / Account Deletion --}}
            <div class="bg-rose-50/50 border border-rose-200 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-rose-900 flex items-center gap-2">
                            <i class="fas fa-triangle-exclamation text-rose-600"></i>
                            Delete Account
                        </h3>
                        <p class="text-xs text-rose-700 mt-1">
                            Permanently delete your user profile and remove your credentials from the system.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="deleteModal = true"
                        class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-semibold shadow-sm transition whitespace-nowrap"
                    >
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Account Modal --}}
    <div
        x-show="deleteModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4"
        x-transition
    >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="deleteModal = false">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2 text-rose-600">
                    <i class="fas fa-triangle-exclamation"></i>
                    Confirm Account Deletion
                </h3>
                <button @click="deleteModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            <p class="text-xs text-gray-600 leading-relaxed">
                Are you sure you want to delete your account? Once deleted, all resources and data linked to your account will be permanently removed. Please enter your current password to confirm.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
                @csrf
                @method('DELETE')

                <div>
                    <label for="deletion_password" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Confirm With Password
                    </label>
                    <input
                        id="deletion_password"
                        name="password"
                        type="password"
                        placeholder="Enter your current password"
                        required
                        class="w-full px-3.5 py-2 text-sm border @if($errors->userDeletion->has('password')) border-red-300 ring-1 ring-red-300 @else border-gray-300 @endif rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                    @if($errors->userDeletion->has('password'))
                        <p class="text-xs text-red-600 mt-1">{{ $errors->userDeletion->first('password') }}</p>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200">
                    <button
                        type="button"
                        @click="deleteModal = false"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-semibold shadow-sm transition"
                    >
                        Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
