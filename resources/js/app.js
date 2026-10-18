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

	// Wrap the input in its own tight, position:relative box. Anchoring to the
	// original parent breaks centering when that parent also holds labels or
	// error text, so the button would drift off the field.
	const wrapper = document.createElement('div');
	wrapper.className = 'password-eye-wrapper';
	wrapper.style.position = 'relative';
	wrapper.style.display = 'block';
	wrapper.style.width = '100%';

	// Move vertical margins from the input to the wrapper so the wrapper's box
	// equals the input's border box (keeps the icon exactly centered).
	const cs = getComputedStyle(input);
	wrapper.style.marginTop = cs.marginTop;
	wrapper.style.marginBottom = cs.marginBottom;
	input.style.marginTop = '0';
	input.style.marginBottom = '0';

	input.parentNode.insertBefore(wrapper, input);
	wrapper.appendChild(input);

	// Leave room on the right so typed text never sits under the icon
	input.style.paddingRight = '2.75rem';

	const btn = document.createElement('button');
	btn.type = 'button';
	btn.tabIndex = -1;
	btn.setAttribute('aria-label', 'Show password');
	btn.title = 'Show password';
	btn.className = 'password-eye-btn text-gray-400 hover:text-gray-600 focus:outline-none transition-colors';
	Object.assign(btn.style, {
		position: 'absolute',
		top: '50%',
		right: '0.75rem',
		transform: 'translateY(-50%)',
		display: 'flex',
		alignItems: 'center',
		justifyContent: 'center',
		width: '1.5rem',
		height: '1.5rem',
		padding: '0',
		background: 'transparent',
		border: '0',
		lineHeight: '1',
		cursor: 'pointer',
		zIndex: '2',
	});
	btn.innerHTML = '<i class="fas fa-eye text-sm" aria-hidden="true"></i>';

	btn.addEventListener('click', (e) => {
		e.preventDefault();
		const reveal = input.type === 'password';
		input.type = reveal ? 'text' : 'password';
		btn.querySelector('i').className = (reveal ? 'fas fa-eye-slash' : 'fas fa-eye') + ' text-sm';
		const label = reveal ? 'Hide password' : 'Show password';
		btn.setAttribute('aria-label', label);
		btn.title = label;
		input.focus();
	});

	// Always re-mask on submit so the browser never stores it as plain text
	if (input.form) {
		input.form.addEventListener('submit', () => { input.type = 'password'; });
	}

	wrapper.appendChild(btn);
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
