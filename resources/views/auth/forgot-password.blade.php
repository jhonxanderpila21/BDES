<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('demo_reset_link'))
        <div class="mb-4 p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 space-y-1.5">
            <div class="font-bold flex items-center gap-1.5 text-blue-800">
                <i class="fas fa-flask"></i>
                <span>Demo SMS Password Reset Link:</span>
            </div>
            <a href="{{ session('demo_reset_link') }}" class="block break-all text-blue-600 hover:text-blue-800 font-semibold underline">
                {{ session('demo_reset_link') }}
            </a>
        </div>
    @endif

    <div class="mb-6 text-center">
        <div class="w-12 h-12 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center mx-auto mb-3 text-blue-600">
            <i class="fas fa-mobile-screen-button text-lg"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Forgot your password?</h2>
        <p class="text-sm text-gray-600 mt-1">
            Enter your registered phone number and we will send a password reset link directly to your phone via SMS.
        </p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Phone Number -->
        <div>
            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1.5">
                Phone Number
            </label>
            <input
                id="phone_number"
                type="tel"
                name="phone_number"
                value="{{ old('phone_number', '+63') }}"
                required
                autofocus
                placeholder="+639171234567 or 09171234567"
                class="w-full px-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-1.5 text-xs text-red-600" />
        </div>

        <div class="pt-2">
            <button
                type="submit"
                class="w-full flex justify-center items-center gap-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <i class="fas fa-paper-plane text-sm"></i>
                <span>Send Password Reset Link via SMS</span>
            </button>
        </div>
    </form>

    <div class="mt-6 pt-4 border-t border-gray-100 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Back to Sign In</span>
        </a>
    </div>
</x-guest-layout>
