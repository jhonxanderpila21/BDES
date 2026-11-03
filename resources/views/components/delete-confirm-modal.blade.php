{{--
    High-Security Delete & Destructive Action Confirmation Modal.
    Prevents accidental deletions across the entire BDES system.
    Features:
    - Prominent visual warning with item identification
    - Default focus on Cancel (press Enter cancels instead of deletes)
    - Reusable triggers:
        confirmSecureDelete(event, this, itemName, customWarning)
        confirmSecureAction(event, this, { title, itemName, warning, confirmText, iconClass, isDestructive })
--}}
<div id="bdes-delete-confirm-modal"
     data-modal-name="bdes-delete-confirm-modal"
     class="fixed inset-0 z-[120]"
     style="display: none;"
     role="dialog"
     aria-modal="true"
     aria-labelledby="bdes-delete-modal-title">

    {{-- Dark Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
         onclick="window.closeModal('bdes-delete-confirm-modal')"></div>

    {{-- Modal Box --}}
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl border border-gray-100 sm:w-full sm:max-w-md max-h-[90vh] overflow-hidden p-6 z-10 transition-all">
        <div class="text-center">
            {{-- Warning Icon with Pulse Ring --}}
            <div id="bdes-action-icon-wrapper" class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-rose-100 text-rose-600 mb-4 ring-8 ring-rose-50 transition-colors">
                <i id="bdes-action-icon" class="fas fa-trash-can text-2xl"></i>
            </div>

            {{-- Title --}}
            <h3 id="bdes-delete-modal-title" class="text-lg font-bold text-gray-900">
                Confirm Permanent Deletion
            </h3>

            {{-- Description --}}
            <p id="bdes-delete-modal-desc" class="text-xs text-gray-500 mt-1.5">
                You are about to perform this action on the following item:
            </p>

            {{-- Item Identifier Card --}}
            <div id="bdes-action-item-card" class="mt-3.5 px-3 py-2.5 bg-rose-50/70 border border-rose-200/80 rounded-xl flex items-center justify-center gap-2 text-rose-900 font-semibold text-xs text-center break-words shadow-inner">
                <i id="bdes-action-card-icon" class="fas fa-triangle-exclamation text-rose-500 shrink-0"></i>
                <span id="bdes-delete-item-name" class="truncate max-w-[320px]">Selected Record</span>
            </div>

            {{-- Impact / Warning Note --}}
            <p id="bdes-delete-custom-warning" class="text-xs text-gray-500 mt-3 leading-relaxed">
                This record will be moved to the centralized Archived Records and hidden from active listings. Authorized administrators can view or restore it later.
            </p>

            {{-- Deletion Reason Input --}}
            <div id="bdes-delete-reason-wrapper" class="mt-3.5 text-left">
                <label for="bdes-delete-reason-input" class="block text-[11px] font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Reason for Deletion <span class="text-gray-400 font-normal lowercase">(optional)</span>
                </label>
                <textarea id="bdes-delete-reason-input"
                          rows="2"
                          placeholder="e.g. Broken beyond repair, obsolete, decommissioned..."
                          class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 resize-none transition-colors"></textarea>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
            <button type="button"
                    id="bdes-delete-cancel-btn"
                    onclick="window.closeModal('bdes-delete-confirm-modal')"
                    class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-gray-300">
                Cancel
            </button>
            <button type="button"
                    id="bdes-delete-confirm-btn"
                    onclick="window.executeConfirmedDelete()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500">
                <i id="bdes-confirm-btn-icon" class="fas fa-trash-can text-xs"></i>
                <span id="bdes-confirm-btn-text">Yes, Delete Record</span>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    let pendingActionForm = null;

    /**
     * Universal secure confirmation handler for destructive or critical actions.
     * @param {Event} event - Form submission event or button click event
     * @param {HTMLElement} formOrButton - The form or child button
     * @param {Object} options - Configuration options
     */
    window.confirmSecureAction = function(event, formOrButton, options = {}) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const form = formOrButton.tagName === 'FORM' ? formOrButton : formOrButton.closest('form');
        if (!form) return false;

        pendingActionForm = form;

        const isDestructive = options.isDestructive !== false;
        const title = options.title || (isDestructive ? 'Confirm Permanent Deletion' : 'Confirm Action');
        const itemName = options.itemName || 'Selected Record';
        const warning = options.warning || (isDestructive 
            ? 'This action is irreversible and will permanently remove this record from the active system.' 
            : 'Please verify that you wish to proceed with this system change.');
        const confirmText = options.confirmText || (isDestructive ? 'Yes, Delete Record' : 'Yes, Confirm');
        const iconClass = options.iconClass || (isDestructive ? 'fas fa-trash-can' : 'fas fa-shield-halved');

        const titleEl = document.getElementById('bdes-delete-modal-title');
        const nameEl = document.getElementById('bdes-delete-item-name');
        const warningEl = document.getElementById('bdes-delete-custom-warning');
        const iconEl = document.getElementById('bdes-action-icon');
        const iconWrapper = document.getElementById('bdes-action-icon-wrapper');
        const itemCard = document.getElementById('bdes-action-item-card');
        const cardIcon = document.getElementById('bdes-action-card-icon');
        const confirmBtn = document.getElementById('bdes-delete-confirm-btn');
        const confirmBtnText = document.getElementById('bdes-confirm-btn-text');
        const confirmBtnIcon = document.getElementById('bdes-confirm-btn-icon');

        if (titleEl) titleEl.textContent = title;
        if (nameEl) nameEl.textContent = itemName;
        if (warningEl) warningEl.textContent = warning;
        if (confirmBtnText) confirmBtnText.textContent = confirmText;

        if (iconEl) iconEl.className = `${iconClass} text-2xl`;
        if (confirmBtnIcon) confirmBtnIcon.className = `${iconClass} text-xs`;

        if (isDestructive) {
            if (iconWrapper) iconWrapper.className = 'mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-rose-100 text-rose-600 mb-4 ring-8 ring-rose-50 transition-colors';
            if (itemCard) itemCard.className = 'mt-3.5 px-3 py-2.5 bg-rose-50/70 border border-rose-200/80 rounded-xl flex items-center justify-center gap-2 text-rose-900 font-semibold text-xs text-center break-words shadow-inner';
            if (cardIcon) cardIcon.className = 'fas fa-triangle-exclamation text-rose-500 shrink-0';
            if (confirmBtn) confirmBtn.className = 'w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500';
        } else {
            if (iconWrapper) iconWrapper.className = 'mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 text-blue-600 mb-4 ring-8 ring-blue-50 transition-colors';
            if (itemCard) itemCard.className = 'mt-3.5 px-3 py-2.5 bg-blue-50/70 border border-blue-200/80 rounded-xl flex items-center justify-center gap-2 text-blue-900 font-semibold text-xs text-center break-words shadow-inner';
            if (cardIcon) cardIcon.className = 'fas fa-info-circle text-blue-500 shrink-0';
            if (confirmBtn) confirmBtn.className = 'w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500';
        }

        const reasonInput = document.getElementById('bdes-delete-reason-input');
        if (reasonInput) reasonInput.value = '';

        window.openModal('bdes-delete-confirm-modal');

        // Safety: autofocus the cancel button so accidental Enter presses do not submit
        setTimeout(() => {
            const cancelBtn = document.getElementById('bdes-delete-cancel-btn');
            if (cancelBtn) cancelBtn.focus();
        }, 60);

        return false;
    };

    /**
     * Standard shorthand for delete operations.
     */
    window.confirmSecureDelete = function(event, formOrButton, itemName, customWarning = null) {
        return window.confirmSecureAction(event, formOrButton, {
            title: 'Confirm Archive / Deletion',
            itemName: itemName,
            warning: customWarning || 'This record will be moved to the centralized Archived Records and hidden from active listings. Authorized administrators can restore it later.',
            confirmText: 'Yes, Archive Record',
            iconClass: 'fas fa-box-archive',
            isDestructive: true
        });
    };

    window.executeConfirmedDelete = function() {
        if (pendingActionForm) {
            const formToSubmit = pendingActionForm;
            pendingActionForm = null;

            const reasonInput = document.getElementById('bdes-delete-reason-input');
            if (reasonInput && reasonInput.value.trim()) {
                let hiddenReason = formToSubmit.querySelector('input[name="deletion_reason"]');
                if (!hiddenReason) {
                    hiddenReason = document.createElement('input');
                    hiddenReason.type = 'hidden';
                    hiddenReason.name = 'deletion_reason';
                    formToSubmit.appendChild(hiddenReason);
                }
                hiddenReason.value = reasonInput.value.trim();
            }

            window.closeModal('bdes-delete-confirm-modal');
            formToSubmit.submit();
        }
    };
})();
</script>
