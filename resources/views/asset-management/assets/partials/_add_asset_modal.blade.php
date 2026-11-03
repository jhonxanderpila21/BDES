{{-- Add Asset Modal --}}
<x-modal id="add-asset-modal" title="Add New Asset" :show="$errors->any() || request('reopen_add_asset') || session('reopen_add_asset')">
    <form 
        id="addAssetForm"
        class="space-y-4" 
        action="{{ route('assets.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
        x-data="{
            cost: {{ old('acquisition_cost', 0) ?: 0 }},
            qty: {{ old('quantity', 1) ?: 1 }},
            get totalCost() {
                const c = parseFloat(this.cost) || 0;
                const q = parseInt(this.qty) || 1;
                return (c * q).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }"
    >
        @csrf

        {{-- Automated SKU Notice --}}
        <div class="p-3 bg-blue-50/80 border border-blue-200 rounded-lg text-xs text-blue-800 flex items-start gap-2.5">
            <i class="fas fa-magic text-blue-600 mt-0.5 text-sm"></i>
            <div>
                <p class="font-semibold text-blue-900">Automated SKU & Asset Code System</p>
                <p class="text-blue-700 mt-0.5">
                    Asset codes/SKUs are automatically generated in sequence (e.g., <code class="bg-blue-100 px-1 py-0.5 rounded font-bold">FE-003</code>, <code class="bg-blue-100 px-1 py-0.5 rounded font-bold">GEN-002</code>, <code class="bg-blue-100 px-1 py-0.5 rounded font-bold">FAK-003</code>) based on type or category. Any existing SKU collisions are automatically skipped.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-group label="Asset Name" required>
                <input
                    type="text"
                    id="asset_form_name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="e.g., Heavy-Duty Portable Generator 5kVA"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
            </x-form-group>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                    <button type="button" onclick="saveDraftAndOpenCategoryModal()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Add New
                    </button>
                </div>
                <select
                    id="asset_form_category_id"
                    name="category_id"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                    <option value="">Select Category</option>
                    @php
                        $selectedCatId = old('category_id', request('selected_category_id'));
                    @endphp
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ (string)$selectedCatId === (string)$category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Asset Type</label>
                    <button type="button" onclick="saveDraftAndOpenTypeModal()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Add New
                    </button>
                </div>
                <select
                    id="asset_form_type_id"
                    name="type_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                    <option value="">-- Optional Asset Type --</option>
                    @php
                        $selectedTypeId = old('type_id', request('selected_type_id'));
                    @endphp
                    @foreach($types ?? [] as $tp)
                        <option value="{{ $tp->id }}" {{ (string)$selectedTypeId === (string)$tp->id ? 'selected' : '' }}>
                            {{ $tp->name }} ({{ $tp->category->name ?? 'General' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Assigned Location <span class="text-red-500">*</span></label>
                    <a href="{{ route('locations.index', ['return_to' => 'assets']) }}" onclick="saveDraftBeforeRedirect()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Add New
                    </a>
                </div>
                <select
                    id="asset_form_location_id"
                    name="location_id"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                    <option value="">Select Location</option>
                    @php
                        $selectedLocId = old('location_id', request('selected_location_id'));
                    @endphp
                    @foreach($locations ?? [] as $location)
                        <option value="{{ $location->id }}" {{ (string)$selectedLocId === (string)$location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-form-group label="Serial Number (Base)">
                <input
                    type="text"
                    id="asset_form_serial_number"
                    name="serial_number"
                    value="{{ old('serial_number') }}"
                    placeholder="e.g. SN-2024-001"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
            </x-form-group>

            <x-form-group label="Condition" required>
                <select
                    name="condition"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                    @foreach(['good' => 'Good (Serviceable)', 'excellent' => 'Excellent (Brand New)', 'fair' => 'Fair (Minor Wear)', 'poor' => 'Poor (Needs Repair)'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('condition', 'good') === $val ? 'selected' : '' }}>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Status" required>
                <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Available (In Stock)</option>
                    <option value="deployed" {{ old('status') === 'deployed' ? 'selected' : '' }}>Deployed (In Field)</option>
                    <option value="under_maintenance" {{ old('status') === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                    <option value="out_of_service" {{ old('status') === 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                </select>
            </x-form-group>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-group label="Manufacturer / Brand">
                <input
                    type="text"
                    id="asset_form_manufacturer"
                    name="manufacturer"
                    value="{{ old('manufacturer') }}"
                    placeholder="e.g., Yamaha, Honda, Stihl"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
            </x-form-group>

            <x-form-group label="Purchase Date">
                <input
                    type="date"
                    id="asset_form_purchase_date"
                    name="purchase_date"
                    value="{{ old('purchase_date', date('Y-m-d')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
            </x-form-group>
        </div>

        {{-- Cost & Quantity Group --}}
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Cost per Asset (PHP)">
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500 text-sm font-semibold">₱</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            inputmode="decimal"
                            name="acquisition_cost"
                            x-model="cost"
                            value="{{ old('acquisition_cost') }}"
                            placeholder="0.00"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        >
                    </div>
                </x-form-group>

                <x-form-group label="Quantity (Units to Register)">
                    <input
                        type="number"
                        min="1"
                        max="100"
                        step="1"
                        name="quantity"
                        x-model="qty"
                        value="{{ old('quantity', 1) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-semibold"
                    >
                </x-form-group>

                <x-form-group label="Warranty Expiry">
                    <input
                        type="date"
                        name="warranty_expiry"
                        value="{{ old('warranty_expiry') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    >
                </x-form-group>
            </div>

            {{-- Live Total Cost Calculation --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-200 text-sm">
                <span class="text-gray-600 font-medium">
                    Total Investment (<span x-text="qty || 1"></span> <span x-text="(qty > 1) ? 'units' : 'unit'"></span>):
                </span>
                <span class="text-base font-bold text-gray-900">
                    ₱<span x-text="totalCost"></span>
                </span>
            </div>
        </div>

        {{-- Proof of Cost / Receipt Upload --}}
        <div class="pt-1">
            <x-receipt-input label="Proof of Cost / Official Receipt (OR) / Invoice (Optional)" help="Upload official receipt, invoice, or purchase order as proof of cost (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />
        </div>

        <x-form-group label="Notes / Technical Specifications">
            <textarea
                id="asset_form_notes"
                name="notes"
                rows="2"
                placeholder="Optional notes, engine specifications, serial numbering patterns, etc..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            >{{ old('notes') }}</textarea>
        </x-form-group>

        <div class="flex gap-3 mt-6">
            <x-button variant="primary" type="submit" class="flex-1">
                <i class="fas fa-plus-circle mr-1.5"></i> Register Asset(s)
            </x-button>
            <x-button variant="secondary" type="button" onclick="window.closeModal('add-asset-modal')" class="flex-1">
                Cancel
            </x-button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    const DRAFT_KEY = 'bdes_add_asset_draft';

    function saveDraftBeforeRedirect() {
        const draft = {
            name: document.getElementById('asset_form_name')?.value || '',
            category_id: document.getElementById('asset_form_category_id')?.value || '',
            type_id: document.getElementById('asset_form_type_id')?.value || '',
            serial_number: document.getElementById('asset_form_serial_number')?.value || '',
            manufacturer: document.getElementById('asset_form_manufacturer')?.value || '',
            purchase_date: document.getElementById('asset_form_purchase_date')?.value || '',
            notes: document.getElementById('asset_form_notes')?.value || '',
        };
        sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
    }

    function restoreDraftIfPresent() {
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            if (!raw) return;
            const draft = JSON.parse(raw);
            if (draft.name && !document.getElementById('asset_form_name').value) {
                document.getElementById('asset_form_name').value = draft.name;
            }
            if (draft.serial_number && !document.getElementById('asset_form_serial_number').value) {
                document.getElementById('asset_form_serial_number').value = draft.serial_number;
            }
            if (draft.manufacturer && !document.getElementById('asset_form_manufacturer').value) {
                document.getElementById('asset_form_manufacturer').value = draft.manufacturer;
            }
            if (draft.notes && !document.getElementById('asset_form_notes').value) {
                document.getElementById('asset_form_notes').value = draft.notes;
            }
        } catch(e) {}
    }

    function saveDraftAndOpenCategoryModal() {
        saveDraftBeforeRedirect();
        window.closeModal('add-asset-modal');
        if (typeof openAddCategoryModal === 'function') {
            openAddCategoryModal();
        }
    }

    function saveDraftAndOpenTypeModal() {
        saveDraftBeforeRedirect();
        window.closeModal('add-asset-modal');
        if (typeof openAddTypeModal === 'function') {
            openAddTypeModal();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        restoreDraftIfPresent();

        // Clear draft when form successfully submitted
        const form = document.getElementById('addAssetForm');
        if (form) {
            form.addEventListener('submit', function() {
                sessionStorage.removeItem(DRAFT_KEY);
            });
        }
    });
</script>
@endpush
