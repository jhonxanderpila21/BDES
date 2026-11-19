<script>
document.addEventListener('DOMContentLoaded', () => {
    const DYNAMIC_SELECT_NAMES = new Set([
        'asset_id',
        'category_id',
        'type_id',
        'asset_type_id',
        'asset_type',
        'location_id',
        'assigned_to',
        'unit',
    ]);

    const enhancedSelects = new Map();

    function shouldEnhanceSelect(select) {
        if (!select || select.dataset.noSearch === 'true' || select.multiple) return false;
        if (!select.closest('[data-modal-name]')) return false;
        if (select.dataset.searchable === 'true') return true;
        if (DYNAMIC_SELECT_NAMES.has(select.name) || select.id === 'generic_deployment_select') return true;
        return select.options.length > 6;
    }

    function refreshSearchableSelect(select) {
        const api = enhancedSelects.get(select);
        if (api) api.refresh();
    }

    function enhanceSelect(select) {
        if (enhancedSelects.has(select)) return;

        // Wrapper container
        const wrapper = document.createElement('div');
        wrapper.className = 'relative w-full bdes-searchable-select';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        // Visually hide native select while keeping HTML5 validation anchored cleanly
        select.style.position = 'absolute';
        select.style.opacity = '0';
        select.style.pointerEvents = 'none';
        select.style.width = '100%';
        select.style.height = '38px';
        select.style.bottom = '0';
        select.style.left = '0';
        select.tabIndex = -1;

        // Trigger button
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm bg-white text-left flex items-center justify-between gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors hover:border-gray-400';

        const labelSpan = document.createElement('span');
        labelSpan.className = 'truncate block text-gray-800';

        const chevron = document.createElement('i');
        chevron.className = 'fas fa-chevron-down text-xs text-gray-400 shrink-0 transition-transform duration-150';

        button.appendChild(labelSpan);
        button.appendChild(chevron);
        wrapper.appendChild(button);

        // Dropdown panel
        const panel = document.createElement('div');
        panel.className = 'hidden absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-[120] overflow-hidden';

        const searchWrap = document.createElement('div');
        searchWrap.className = 'p-2 border-b border-gray-100 bg-gray-50/80';

        const searchBox = document.createElement('div');
        searchBox.className = 'relative';

        const searchIcon = document.createElement('i');
        searchIcon.className = 'fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search options...';
        searchInput.className = 'w-full pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500';

        searchBox.appendChild(searchIcon);
        searchBox.appendChild(searchInput);
        searchWrap.appendChild(searchBox);

        const optionsList = document.createElement('div');
        optionsList.className = 'max-h-52 overflow-y-auto py-1 divide-y divide-gray-50';

        panel.appendChild(searchWrap);
        panel.appendChild(optionsList);
        wrapper.appendChild(panel);

        let isOpen = false;

        function updateButtonLabel() {
            const selected = select.options[select.selectedIndex];
            if (selected && selected.value !== '') {
                labelSpan.textContent = selected.textContent.trim();
                labelSpan.className = 'truncate block text-gray-900 font-medium';
            } else {
                labelSpan.textContent = selected ? selected.textContent.trim() : 'Select an option...';
                labelSpan.className = 'truncate block text-gray-500';
            }
        }

        function renderOptions(query = '') {
            optionsList.innerHTML = '';
            const q = query.trim().toLowerCase();
            let visibleCount = 0;

            Array.from(select.options).forEach((opt) => {
                if (opt.hidden || opt.style.display === 'none') return;

                const text = opt.textContent.trim();
                if (q !== '' && opt.value !== '' && !text.toLowerCase().includes(q)) {
                    return;
                }

                visibleCount++;
                const isSelected = opt.value === select.value;

                const item = document.createElement('button');
                item.type = 'button';
                item.disabled = opt.disabled;
                item.className = `w-full text-left px-3.5 py-2 text-xs flex items-center justify-between gap-2 transition-colors ${
                    isSelected
                        ? 'bg-blue-50 text-blue-700 font-semibold'
                        : (opt.value === '' ? 'text-gray-500 hover:bg-gray-50 italic' : 'text-gray-700 hover:bg-blue-50/60 hover:text-blue-700')
                } ${opt.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`;

                const itemText = document.createElement('span');
                itemText.className = 'truncate';
                itemText.textContent = text;
                item.appendChild(itemText);

                if (isSelected && opt.value !== '') {
                    const check = document.createElement('i');
                    check.className = 'fas fa-check text-[10px] text-blue-600 shrink-0';
                    item.appendChild(check);
                }

                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    select.value = opt.value;
                    updateButtonLabel();
                    closePanel();
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                });

                optionsList.appendChild(item);
            });

            if (visibleCount === 0) {
                const empty = document.createElement('div');
                empty.className = 'px-3.5 py-3 text-xs text-gray-400 text-center italic';
                empty.textContent = 'No matching options found.';
                optionsList.appendChild(empty);
            }
        }

        function openPanel() {
            if (isOpen || select.disabled) return;
            // Close any other open searchable dropdowns
            document.querySelectorAll('.bdes-searchable-select').forEach((el) => {
                if (el !== wrapper && el._closePanel) el._closePanel();
            });
            isOpen = true;
            panel.classList.remove('hidden');
            chevron.classList.add('rotate-180');
            searchInput.value = '';
            renderOptions('');
            setTimeout(() => searchInput.focus(), 20);
        }

        function closePanel() {
            if (!isOpen) return;
            isOpen = false;
            panel.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }

        wrapper._closePanel = closePanel;

        button.addEventListener('click', (e) => {
            e.stopPropagation();
            if (isOpen) {
                closePanel();
            } else {
                openPanel();
            }
        });

        searchInput.addEventListener('input', () => {
            renderOptions(searchInput.value);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closePanel();
                button.focus();
            }
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                closePanel();
            }
        });

        select.addEventListener('change', () => {
            updateButtonLabel();
            if (isOpen) renderOptions(searchInput.value);
        });

        const observer = new MutationObserver(() => {
            updateButtonLabel();
            if (isOpen) renderOptions(searchInput.value);
        });
        observer.observe(select, { childList: true, attributes: true, subtree: true });

        updateButtonLabel();

        enhancedSelects.set(select, {
            refresh: () => {
                updateButtonLabel();
                if (isOpen) renderOptions(searchInput.value);
            },
        });
    }

    // Intercept programmatic select.value = ... assignments so custom trigger stays in sync
    const valueDescriptor = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
    if (valueDescriptor && valueDescriptor.set) {
        Object.defineProperty(HTMLSelectElement.prototype, 'value', {
            get() {
                return valueDescriptor.get.call(this);
            },
            set(val) {
                valueDescriptor.set.call(this, val);
                if (enhancedSelects.has(this)) {
                    enhancedSelects.get(this).refresh();
                }
            },
            configurable: true,
        });
    }

    // Responsive Cross-Filtering between related dropdowns inside Add / Edit forms
    function setupCrossFiltering() {
        document.querySelectorAll('[data-modal-name] form').forEach((form) => {
            // 1. Category <-> Asset Type (Add/Edit Asset)
            const categorySelect = form.querySelector('select[name="category_id"]');
            const typeSelect = form.querySelector('select[name="type_id"]');
            if (categorySelect && typeSelect) {
                const filterTypesByCategory = () => {
                    const catId = categorySelect.value;
                    Array.from(typeSelect.options).forEach((opt) => {
                        if (opt.value === '') {
                            opt.hidden = false;
                            return;
                        }
                        const optCat = opt.getAttribute('data-category-id');
                        opt.hidden = !!(catId && optCat && optCat !== catId);
                    });

                    const selectedTypeOpt = typeSelect.options[typeSelect.selectedIndex];
                    if (selectedTypeOpt && selectedTypeOpt.value !== '' && selectedTypeOpt.hidden) {
                        typeSelect.value = '';
                    }
                    refreshSearchableSelect(typeSelect);
                };

                categorySelect.addEventListener('change', filterTypesByCategory);
                typeSelect.addEventListener('change', () => {
                    const selectedTypeOpt = typeSelect.options[typeSelect.selectedIndex];
                    const optCat = selectedTypeOpt ? selectedTypeOpt.getAttribute('data-category-id') : null;
                    if (optCat && categorySelect.value !== optCat) {
                        categorySelect.value = optCat;
                        filterTypesByCategory();
                    }
                });
            }

            // 2. Asset Type <-> Linked Asset (Add/Edit Component / Attachment)
            const assetTypeSelect = form.querySelector('select[name="asset_type_id"]');
            const assetSelect = form.querySelector('select[name="asset_id"]');
            if (assetTypeSelect && assetSelect) {
                const filterAssetsByType = () => {
                    const tId = assetTypeSelect.value;
                    Array.from(assetSelect.options).forEach((opt) => {
                        if (opt.value === '') {
                            opt.hidden = false;
                            return;
                        }
                        const optType = opt.getAttribute('data-asset-type-id');
                        opt.hidden = !!(tId && optType !== tId);
                    });

                    const selectedAssetOpt = assetSelect.options[assetSelect.selectedIndex];
                    if (selectedAssetOpt && selectedAssetOpt.value !== '' && selectedAssetOpt.hidden) {
                        assetSelect.value = '';
                    }
                    refreshSearchableSelect(assetSelect);
                };

                assetTypeSelect.addEventListener('change', filterAssetsByType);
                assetSelect.addEventListener('change', () => {
                    const selectedAssetOpt = assetSelect.options[assetSelect.selectedIndex];
                    const optType = selectedAssetOpt ? selectedAssetOpt.getAttribute('data-asset-type-id') : null;
                    if (optType && assetTypeSelect.value !== optType) {
                        assetTypeSelect.value = optType;
                        filterAssetsByType();
                    }
                });
            }
        });
    }

    document.querySelectorAll('[data-modal-name] select').forEach((select) => {
        if (shouldEnhanceSelect(select)) {
            enhanceSelect(select);
        }
    });

    setupCrossFiltering();

    // Re-sync searchable selects whenever any modal opens
    if (typeof window.openModal === 'function') {
        const origOpenModal = window.openModal;
        window.openModal = function (name) {
            origOpenModal(name);
            const modal = document.getElementById(name);
            if (modal) {
                modal.querySelectorAll('select').forEach((sel) => refreshSearchableSelect(sel));
            }
        };
    }
});
</script>

