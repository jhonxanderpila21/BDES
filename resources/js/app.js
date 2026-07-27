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
