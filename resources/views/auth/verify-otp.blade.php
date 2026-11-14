<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <div class="w-12 h-12 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center mx-auto mb-3 text-blue-600">
            <i class="fas fa-mobile-screen-button text-lg"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Phone Number Verification</h2>
        <p class="text-sm text-gray-500 mt-1">
            We sent a 6-digit verification code to
            <span class="font-semibold text-gray-800">{{ $maskedPhone }}</span>
        </p>
        <p class="text-xs text-gray-400 mt-1">
            For your security, the code expires in 5 minutes.
        </p>
    </div>

    <!-- Verify OTP Form -->
    <form method="POST" action="{{ route('otp.verify') }}" class="space-y-4">
        @csrf

        <div>
            <label for="otp" class="block text-sm font-medium text-gray-700 mb-1.5 text-center">
                Enter 6-Digit OTP Code
            </label>
            <div class="relative">
                <input
                    id="otp"
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    value="{{ old('otp') }}"
                    required
                    autofocus
                    autocomplete="one-time-code"
                    placeholder="000000"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)"
                    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-center text-xl font-bold tracking-[0.45em] text-gray-900 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                />
            </div>
            <x-input-error :messages="$errors->get('otp')" class="mt-2 text-xs text-red-600 text-center" />
        </div>

        @if ($expiresInSeconds > 0)
            <div class="text-center text-xs text-gray-500" id="otp-expiry-container">
                <i class="far fa-clock mr-1 text-blue-500"></i>
                Code expires in <span id="otp-expiry-timer" class="font-semibold text-gray-700"></span>
            </div>
        @else
            <div class="text-center text-xs text-amber-600 font-medium">
                <i class="fas fa-triangle-exclamation mr-1"></i>
                Your previous code has expired. Please click Resend OTP below.
            </div>
        @endif

        <div class="pt-1">
            <button
                type="submit"
                class="w-full flex justify-center items-center gap-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <i class="fas fa-shield-check text-sm"></i>
                <span>Verify OTP</span>
            </button>
        </div>
    </form>

    <!-- Resend OTP & Logout Actions -->
    <div class="mt-6 pt-5 border-t border-gray-100 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('otp.resend') }}" id="resend-otp-form">
            @csrf
            <button
                type="submit"
                id="resend-otp-btn"
                @if ($cooldownSeconds > 0) disabled @endif
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors"
            >
                <i class="fas fa-rotate-right text-xs"></i>
                <span id="resend-otp-label">
                    @if ($cooldownSeconds > 0)
                        Resend OTP ({{ $cooldownSeconds }}s)
                    @else
                        Resend OTP
                    @endif
                </span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-gray-800 transition-colors"
            >
                <i class="fas fa-arrow-right-from-bracket text-xs"></i>
                <span>Log Out</span>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let cooldown = {{ (int) $cooldownSeconds }};
            const resendBtn = document.getElementById('resend-otp-btn');
            const resendLabel = document.getElementById('resend-otp-label');

            if (cooldown > 0 && resendBtn && resendLabel) {
                const cooldownInterval = setInterval(function () {
                    cooldown--;
                    if (cooldown <= 0) {
                        clearInterval(cooldownInterval);
                        resendBtn.disabled = false;
                        resendLabel.textContent = 'Resend OTP';
                    } else {
                        resendLabel.textContent = 'Resend OTP (' + cooldown + 's)';
                    }
                }, 1000);
            }

            let expiresIn = {{ (int) $expiresInSeconds }};
            const expiryTimer = document.getElementById('otp-expiry-timer');
            const expiryContainer = document.getElementById('otp-expiry-container');

            function formatTime(sec) {
                const mins = Math.floor(sec / 60);
                const rem = sec % 60;
                return String(mins).padStart(2, '0') + ':' + String(rem).padStart(2, '0');
            }

            if (expiresIn > 0 && expiryTimer) {
                expiryTimer.textContent = formatTime(expiresIn);
                const expiryInterval = setInterval(function () {
                    expiresIn--;
                    if (expiresIn <= 0) {
                        clearInterval(expiryInterval);
                        if (expiryContainer) {
                            expiryContainer.innerHTML = '<span class="text-amber-600 font-medium"><i class="fas fa-triangle-exclamation mr-1"></i>Your OTP has expired. Please click Resend OTP.</span>';
                        }
                    } else {
                        expiryTimer.textContent = formatTime(expiresIn);
                    }
                }, 1000);
            }
        });
    </script>
</x-guest-layout>

