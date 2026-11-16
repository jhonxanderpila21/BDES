<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <div class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center mx-auto mb-3 text-indigo-600">
            <i class="fas fa-key text-lg"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Change Temporary Password</h2>
        <p class="text-sm text-gray-500 mt-1">
            For your security, please set a new personal password before accessing your dashboard.
        </p>
    </div>

    <form method="POST" action="{{ route('password.first-time.update') }}" class="space-y-4">
        @csrf

        <!-- New Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                New Password
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
                placeholder="Enter new strong password"
                class="w-full px-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            />
            <p class="mt-1 text-[11px] text-gray-500">
                Must be at least 8 characters and include uppercase, lowercase, and numbers.
            </p>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-600" />
        </div>

        <!-- Confirm New Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                Confirm Password
            </label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Re-enter new password"
                class="w-full px-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-red-600" />
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button
                type="submit"
                class="w-full flex justify-center items-center gap-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <i class="fas fa-floppy-disk text-sm"></i>
                <span>Save New Password &amp; Continue</span>
            </button>
        </div>
    </form>

    <div class="mt-6 pt-4 border-t border-gray-100 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-gray-800 transition-colors"
            >
                <i class="fas fa-arrow-right-from-bracket text-xs"></i>
                <span>Sign out</span>
            </button>
        </form>
    </div>
</x-guest-layout>
