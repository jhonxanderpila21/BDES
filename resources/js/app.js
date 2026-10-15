// Shared modal state, keyed by the modal's `name`/`id` prop.
window.ModalStore = window.ModalStore || {};

function setModalVisibility(name, visible) {
	document.querySelectorAll('[data-modal-name]').forEach((modal) => {
		if (modal.dataset.modalName === name) {
			modal.style.display = visible ? 'block' : 'none';
		}
	});
}

window.openModal = function(name) {
	window.ModalStore[name] = true;
	setModalVisibility(name, true);
	window.dispatchEvent(new CustomEvent('modal:opened', { detail: name }));
}

window.closeModal = function(name) {
	window.ModalStore[name] = false;
	setModalVisibility(name, false);
	window.dispatchEvent(new CustomEvent('modal:closed', { detail: name }));
}

window.toggleDropdown = function(triggerEl) {
	const container = triggerEl.closest('[data-dropdown]');
	if (!container) return;
	const menu = container.querySelector('[data-dropdown-menu]');
	if (!menu) return;
	menu.classList.toggle('hidden');
}

window.closeAllDropdowns = function() {
	document.querySelectorAll('[data-dropdown-menu]').forEach(el => el.classList.add('hidden'));
}

// Close dropdowns on outside click
document.addEventListener('click', (e) => {
	const dropdown = e.target.closest('[data-dropdown]');
	if (!dropdown) {
		window.closeAllDropdowns();
	}
});

// Escape closes any open dropdown, then the topmost open modal.
document.addEventListener('keydown', (e) => {
	if (e.key !== 'Escape') return;

	window.closeAllDropdowns();

	const openModals = Array.from(document.querySelectorAll('[data-modal-name]'))
		.filter((modal) => modal.style.display === 'block');

	const topmost = openModals[openModals.length - 1];
	if (topmost) {
		window.closeModal(topmost.dataset.modalName);
	}
});

// ─── Password Eye Toggle ──────────────────────────────────────────────────────
// Automatically attaches a show/hide eye button to every password input on the
// page, including those injected into modals after the initial DOM load.

function attachEyeToggle(input) {
	// Skip if already processed
	if (input.dataset.eyeAttached) return;
	input.dataset.eyeAttached = 'true';

	// Ensure the parent is position:relative so the button can be positioned
	const parent = input.parentElement;
	if (getComputedStyle(parent).position === 'static') {
		parent.style.position = 'relative';
	}

	// Add right padding to the input so text doesn't overlap the button
	input.style.paddingRight = '2.5rem';

	// Create the toggle button
	const btn = document.createElement('button');
	btn.type = 'button';
	btn.tabIndex = -1;
	btn.setAttribute('aria-label', 'Toggle password visibility');
	btn.className = 'password-eye-btn absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors';
	btn.innerHTML = '<i class="fas fa-eye text-sm"></i>';

	btn.addEventListener('click', () => {
		const isPassword = input.type === 'password';
		input.type = isPassword ? 'text' : 'password';
		btn.querySelector('i').className = isPassword ? 'fas fa-eye-slash text-sm' : 'fas fa-eye text-sm';
		btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
	});

	parent.appendChild(btn);
}

function initPasswordEyeToggles(root) {
	(root || document).querySelectorAll('input[type="password"]').forEach(attachEyeToggle);
}

// Run on initial page load
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => initPasswordEyeToggles());
} else {
	initPasswordEyeToggles();
}

// Re-run whenever a modal opens (new inputs may have been added to the DOM)
window.addEventListener('modal:opened', () => {
	// Small delay to let Alpine.js / x-show render the modal content
	setTimeout(() => initPasswordEyeToggles(), 50);
});

// MutationObserver catches dynamically injected password inputs (e.g. Alpine modals)
const _eyeObserver = new MutationObserver((mutations) => {
	for (const mutation of mutations) {
		for (const node of mutation.addedNodes) {
			if (node.nodeType !== 1) continue;
			if (node.matches('input[type="password"]')) {
				attachEyeToggle(node);
			} else {
				node.querySelectorAll && node.querySelectorAll('input[type="password"]').forEach(attachEyeToggle);
			}
		}
	}
});
_eyeObserver.observe(document.body, { childList: true, subtree: true });
