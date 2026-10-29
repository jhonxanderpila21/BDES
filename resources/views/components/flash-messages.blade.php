{{--
    Single source of truth for global floating toast notifications.
    Rendered ONCE by the admin layout — does NOT affect page flow or layout.
    Auto-dismisses within 4.5s with smooth exit animations, pause-on-hover, and manual close.
    Provides global JS helper: window.bdesToast(message, type, duration, title).
--}}
@php
    $statusMap = [
        'profile-updated'          => 'Your profile information has been updated.',
        'password-updated'         => 'Your password has been updated successfully.',
        'verification-link-sent'   => 'A new verification link has been sent to your email address.',
        'account-deleted'          => 'The account has been deactivated and archived.',
    ];
    $status = session('status');
    $statusText = $status ? ($statusMap[$status] ?? $status) : null;

    $messages = array_filter([
        'success' => session('success'),
        'error'   => session('error'),
        'warning' => session('warning'),
        'info'    => session('info') ?? $statusText,
    ]);
@endphp

<div id="bdes-toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2.5 max-w-sm sm:max-w-md w-[calc(100vw-2.5rem)] pointer-events-none" aria-live="polite">
    @if($errors->any())
        <div class="bdes-toast pointer-events-auto transform transition-all duration-300 ease-out translate-x-0 opacity-100 bg-white border border-rose-200 rounded-xl shadow-xl overflow-hidden" data-duration="6000" role="alert">
            <div class="p-3.5 sm:p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas fa-circle-xmark text-base"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <p class="text-xs font-bold text-rose-900 uppercase tracking-wide">Validation Error</p>
                    <ul class="text-xs text-gray-700 mt-1 space-y-0.5 list-disc list-inside">
                        @foreach(array_unique($errors->all()) as $error)
                            <li class="truncate">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="bdes-toast-close text-gray-400 hover:text-gray-600 p-1 -mr-1 -mt-1 transition-colors" aria-label="Close notification">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="bdes-toast-progress h-1 bg-rose-500 w-full transition-all linear"></div>
        </div>
    @endif

    @foreach($messages as $type => $message)
        @php
            $typeConfig = [
                'success' => [
                    'border'    => 'border-emerald-200',
                    'iconBg'    => 'bg-emerald-50 text-emerald-600',
                    'icon'      => 'fas fa-circle-check',
                    'title'     => 'Success',
                    'titleText' => 'text-emerald-900',
                    'bar'       => 'bg-emerald-500',
                ],
                'warning' => [
                    'border'    => 'border-amber-200',
                    'iconBg'    => 'bg-amber-50 text-amber-600',
                    'icon'      => 'fas fa-triangle-exclamation',
                    'title'     => 'Attention Required',
                    'titleText' => 'text-amber-900',
                    'bar'       => 'bg-amber-500',
                ],
                'error' => [
                    'border'    => 'border-rose-200',
                    'iconBg'    => 'bg-rose-50 text-rose-600',
                    'icon'      => 'fas fa-circle-xmark',
                    'title'     => 'Error Occurred',
                    'titleText' => 'text-rose-900',
                    'bar'       => 'bg-rose-500',
                ],
                'info' => [
                    'border'    => 'border-blue-200',
                    'iconBg'    => 'bg-blue-50 text-blue-600',
                    'icon'      => 'fas fa-circle-info',
                    'title'     => 'Information',
                    'titleText' => 'text-blue-900',
                    'bar'       => 'bg-blue-500',
                ],
            ][$type] ?? [
                'border'    => 'border-gray-200',
                'iconBg'    => 'bg-gray-50 text-gray-600',
                'icon'      => 'fas fa-bell',
                'title'     => 'Notice',
                'titleText' => 'text-gray-900',
                'bar'       => 'bg-gray-500',
            ];
        @endphp
        <div class="bdes-toast pointer-events-auto transform transition-all duration-300 ease-out translate-x-0 opacity-100 bg-white border {{ $typeConfig['border'] }} rounded-xl shadow-xl overflow-hidden" data-duration="4500" role="status">
            <div class="p-3.5 sm:p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg {{ $typeConfig['iconBg'] }} flex items-center justify-center shrink-0 mt-0.5">
                    <i class="{{ $typeConfig['icon'] }} text-base"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <p class="text-xs font-bold {{ $typeConfig['titleText'] }} uppercase tracking-wide">{{ $typeConfig['title'] }}</p>
                    <p class="text-xs text-gray-700 mt-0.5 break-words">{{ $message }}</p>
                </div>
                <button type="button" class="bdes-toast-close text-gray-400 hover:text-gray-600 p-1 -mr-1 -mt-1 transition-colors" aria-label="Close notification">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="bdes-toast-progress h-1 {{ $typeConfig['bar'] }} w-full transition-all linear"></div>
        </div>
    @endforeach
</div>

<script>
(function() {
    // Config mappings for dynamic JS toasts
    const TYPE_STYLES = {
        success: {
            border: 'border-emerald-200',
            iconBg: 'bg-emerald-50 text-emerald-600',
            icon: 'fas fa-circle-check',
            title: 'Success',
            titleText: 'text-emerald-900',
            bar: 'bg-emerald-500'
        },
        error: {
            border: 'border-rose-200',
            iconBg: 'bg-rose-50 text-rose-600',
            icon: 'fas fa-circle-xmark',
            title: 'Error Occurred',
            titleText: 'text-rose-900',
            bar: 'bg-rose-500'
        },
        warning: {
            border: 'border-amber-200',
            iconBg: 'bg-amber-50 text-amber-600',
            icon: 'fas fa-triangle-exclamation',
            title: 'Attention Required',
            titleText: 'text-amber-900',
            bar: 'bg-amber-500'
        },
        info: {
            border: 'border-blue-200',
            iconBg: 'bg-blue-50 text-blue-600',
            icon: 'fas fa-circle-info',
            title: 'Information',
            titleText: 'text-blue-900',
            bar: 'bg-blue-500'
        }
    };

    function initToastItem(toastEl, duration = 4500) {
        if (!toastEl || toastEl.dataset.initialized === 'true') return;
        toastEl.dataset.initialized = 'true';

        const progressBar = toastEl.querySelector('.bdes-toast-progress');
        const closeBtn = toastEl.querySelector('.bdes-toast-close');
        let remaining = duration;
        let startTime = Date.now();
        let timeoutId = null;

        function dismiss() {
            if (toastEl.dataset.dismissing === 'true') return;
            toastEl.dataset.dismissing = 'true';
            toastEl.classList.remove('translate-x-0', 'opacity-100');
            toastEl.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                toastEl.remove();
            }, 300);
        }

        function startTimer() {
            startTime = Date.now();
            if (progressBar) {
                progressBar.style.transition = `width ${remaining}ms linear`;
                progressBar.style.width = '0%';
            }
            timeoutId = setTimeout(dismiss, remaining);
        }

        function pauseTimer() {
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            const elapsed = Date.now() - startTime;
            remaining = Math.max(0, remaining - elapsed);
            if (progressBar) {
                const currentWidth = (remaining / duration) * 100;
                progressBar.style.transition = 'none';
                progressBar.style.width = `${currentWidth}%`;
            }
        }

        toastEl.addEventListener('mouseenter', pauseTimer);
        toastEl.addEventListener('mouseleave', () => {
            if (remaining > 0) startTimer();
            else dismiss();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dismiss();
            });
        }

        startTimer();
    }

    // Initialize any server-rendered toasts
    document.querySelectorAll('#bdes-toast-container .bdes-toast').forEach(toast => {
        const duration = parseInt(toast.dataset.duration, 10) || 4500;
        initToastItem(toast, duration);
    });

    /**
     * Global Toast Notification Function
     * @param {string} message - Notification text
     * @param {string} type - 'success', 'error', 'warning', 'info'
     * @param {number} duration - Milliseconds before auto-exit (default 4500)
     * @param {string|null} customTitle - Optional title override
     */
    window.bdesToast = function(message, type = 'info', duration = 4500, customTitle = null) {
        let container = document.getElementById('bdes-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'bdes-toast-container';
            container.className = 'fixed top-5 right-5 z-[9999] flex flex-col gap-2.5 max-w-sm sm:max-w-md w-[calc(100vw-2.5rem)] pointer-events-none';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }

        const normalizedType = TYPE_STYLES[type] ? type : (type === 'danger' ? 'error' : 'info');
        const style = TYPE_STYLES[normalizedType];
        const title = customTitle || style.title;

        const toast = document.createElement('div');
        toast.className = `bdes-toast pointer-events-auto transform transition-all duration-300 ease-out translate-x-full opacity-0 bg-white border ${style.border} rounded-xl shadow-xl overflow-hidden`;
        toast.setAttribute('role', normalizedType === 'error' ? 'alert' : 'status');
        toast.innerHTML = `
            <div class="p-3.5 sm:p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg ${style.iconBg} flex items-center justify-center shrink-0 mt-0.5">
                    <i class="${style.icon} text-base"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <p class="text-xs font-bold ${style.titleText} uppercase tracking-wide">${title}</p>
                    <p class="text-xs text-gray-700 mt-0.5 break-words">${message}</p>
                </div>
                <button type="button" class="bdes-toast-close text-gray-400 hover:text-gray-600 p-1 -mr-1 -mt-1 transition-colors" aria-label="Close notification">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="bdes-toast-progress h-1 ${style.bar} w-full transition-all linear"></div>
        `;

        container.appendChild(toast);

        // Trigger slide-in animation next tick
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
            initToastItem(toast, duration);
        });
    };

    // Aliases for developer convenience
    window.showToast = window.bdesToast;
    window.toast = window.bdesToast;
})();
</script>
