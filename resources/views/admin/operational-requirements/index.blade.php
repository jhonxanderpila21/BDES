@extends('layouts.admin')

@section('title', 'Operational Requirements - BDES')

@section('content')
@php
    $isReadOnlyStaff = auth()->user()?->isViewer() || auth()->user()?->hasRole('viewer', 'staff');
@endphp

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Operational Requirements</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Operational Requirements']
            ]" />
        </div>
        @if(!$isReadOnlyStaff)
            <button onclick="openAddRequirementModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-sm">
                <i class="fas fa-plus"></i>
                Add Requirement
            </button>
        @else
            <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-xs font-semibold shadow-xs">
                <i class="fas fa-shield-alt text-blue-600"></i>
                Read-Only Staff Access
            </div>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('operational-requirements.index') }}" method="GET">
                @if(request('low'))
                    <input type="hidden" name="low" value="1">
                @endif
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search requirements..." />
            </form>
        </div>
        <a href="{{ route('operational-requirements.index', array_filter(['low' => request('low') ? null : 1, 'search' => request('search')])) }}"
           class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('low') ? 'bg-red-50 border-red-300 text-red-700 font-semibold' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-filter text-xs"></i>
            Low Stock Only
        </a>
    </div>

    {{-- Requirements Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Requirement / Supply</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Available Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($requirements as $requirement)
                        <tr 
                            class="hover:bg-gray-50 transition-colors cursor-pointer"
                            onclick="if(!event.target.closest('button, a, form, input')) openViewRequirementModal({{ json_encode($requirement) }}, @js($requirement->assetType->name ?? 'General / Any'));"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-bold text-gray-900">{{ $requirement->requirement_type }}</p>
                                @if($requirement->description)
                                    <p class="text-xs text-gray-500 truncate max-w-xs mt-0.5">{{ $requirement->description }}</p>
                                @endif
                                @if($requirement->assetType)
                                    <p class="text-[11px] text-blue-600 mt-0.5"><i class="fas fa-tag mr-1 text-[9px]"></i>{{ $requirement->assetType->name }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="font-bold font-mono text-gray-900">{{ (float)$requirement->current_level }}</span>
                                <span class="text-xs text-gray-500 font-medium ml-0.5">{{ $requirement->unit }}</span>
                                <span class="text-xs text-gray-400 block mt-0.5">Capacity: {{ (float)$requirement->maximum_capacity }} {{ $requirement->unit }} (Min: {{ (float)$requirement->minimum_level }})</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($requirement->isLowStock())
                                    <x-badge variant="danger" size="sm">
                                        <i class="fas fa-triangle-exclamation mr-1"></i> Low Stock
                                    </x-badge>
                                @else
                                    <x-badge variant="success" size="sm">
                                        <i class="fas fa-check-circle mr-1"></i> In Stock
                                    </x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Quick View Details Modal --}}
                                    <button 
                                        type="button"
                                        onclick="openViewRequirementModal({{ json_encode($requirement) }}, @js($requirement->assetType->name ?? 'General / Any'))" 
                                        class="p-2 rounded hover:bg-gray-100 transition-colors text-gray-600 hover:text-gray-900" 
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if(!$isReadOnlyStaff)
                                        <button 
                                            type="button"
                                            onclick='openAddQuantityModal(@json($requirement))' 
                                            class="p-2 rounded hover:bg-emerald-50 transition-colors text-emerald-600 hover:text-emerald-800" 
                                            title="Add Quantity / Restock"
                                        >
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <button 
                                            type="button"
                                            onclick='editRequirement(@json($requirement))' 
                                            class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600" 
                                            title="Edit Requirement"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('operational-requirements.destroy', $requirement) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this requirement?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-red-600" title="Delete Requirement">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8">
                                <x-empty-state 
                                    icon="fas fa-list-check" 
                                    title="No requirements found" 
                                    :description="$isReadOnlyStaff ? 'No operational requirements matching the criteria were found.' : 'Click Add Requirement above to register consumable supplies.'" 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $requirements->links() }}
        </div>
    </x-card>

    {{-- ========================================================================= --}}
    {{-- VIEW REQUIREMENT DETAILS MODAL (READ-ONLY)                                --}}
    {{-- ========================================================================= --}}
    <x-modal id="view-requirement-modal" title="Operational Requirement Details">
        <div class="space-y-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900" id="view_req_name"></h3>
                        <p class="text-xs text-gray-500 mt-0.5" id="view_req_asset_type"></p>
                    </div>
                    <div id="view_req_status_badge"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-2">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Available Stock</p>
                    <p class="text-base font-bold text-gray-900 mt-1" id="view_req_current_level">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Maximum Capacity</p>
                    <p class="text-base font-semibold text-gray-800 mt-1" id="view_req_max_capacity">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Minimum Level</p>
                    <p class="text-base font-semibold text-gray-800 mt-1" id="view_req_min_level">-</p>
                </div>
            </div>

            <div class="bg-white p-2 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Description / Notes</p>
                <p class="text-sm text-gray-700 mt-1 leading-relaxed" id="view_req_description">No additional notes provided.</p>
            </div>

            <div class="flex pt-4 border-t border-gray-200">
                <x-button variant="secondary" type="button" onclick="window.closeModal('view-requirement-modal')" class="w-full">
                    Close
                </x-button>
            </div>
        </div>
    </x-modal>

    @if(!$isReadOnlyStaff)
        {{-- Add Requirement Modal --}}
        <x-modal id="add-requirement-modal" title="Add Operational Requirement">
            <form class="space-y-4" action="{{ route('operational-requirements.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <x-form-group label="Requirement Name" required>
                    <input type="text" name="requirement_type" placeholder="e.g., Diesel Fuel, Battery Pack, First Aid Kits" value="{{ old('requirement_type') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="For Asset Type (Optional)">
                    <select name="asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm bg-white">
                        <option value="">-- General / Any Asset --</option>
                        @foreach($assetTypes as $at)
                            <option value="{{ $at->id }}" {{ old('asset_type_id') == $at->id ? 'selected' : '' }}>{{ $at->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Unit of Measurement" required>
                    <input type="text" name="unit" placeholder="e.g., liters, units, boxes, pairs" value="{{ old('unit') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-form-group label="Count (Initial Stock)" required>
                        <input type="number" step="0.01" min="0" id="req_current_level" name="current_level" value="{{ old('current_level', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </x-form-group>
                    <x-form-group label="Maximum Capacity" required>
                        <input type="number" step="0.01" min="0.01" id="req_max_capacity" oninput="document.getElementById('req_current_level').max = this.value; document.getElementById('req_min_level').max = this.value;" name="maximum_capacity" value="{{ old('maximum_capacity') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </x-form-group>
                    <x-form-group label="Minimum Level" required>
                        <input type="number" step="0.01" min="0" id="req_min_level" name="minimum_level" value="{{ old('minimum_level', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </x-form-group>
                </div>

                <x-form-group label="Notes">
                    <textarea name="description" placeholder="Optional notes..." rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">{{ old('description') }}</textarea>
                </x-form-group>

                {{-- Specification / Document / Initial Receipt Input --}}
                <x-receipt-input label="Specification Sheet, MSDS, or Initial Receipt" help="Upload safety data sheets, manuals, or purchase receipts (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

                <div class="flex gap-3 mt-6">
                    <x-button variant="primary" type="submit" class="flex-1">Create Requirement</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('add-requirement-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>

        {{-- Edit Requirement Modal --}}
        <x-modal id="edit-requirement-modal" title="Edit Operational Requirement">
            <form class="space-y-4" id="edit-requirement-form" method="POST">
                @csrf
                @method('PUT')

                <x-form-group label="Requirement Name" required>
                    <input type="text" name="requirement_type" id="edit_requirement_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="For Asset Type (Optional)">
                    <select name="asset_type_id" id="edit_asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm bg-white">
                        <option value="">-- General / Any Asset --</option>
                        @foreach($assetTypes as $at)
                            <option value="{{ $at->id }}">{{ $at->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Unit of Measurement" required>
                    <input type="text" name="unit" id="edit_unit" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-form-group label="Count (Current Level)" required>
                        <input type="number" step="0.01" min="0" id="edit_current_level" name="current_level" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </x-form-group>
                    <x-form-group label="Maximum Capacity" required>
                        <input type="number" step="0.01" min="0.01" id="edit_max_capacity" name="maximum_capacity" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </x-form-group>
                    <x-form-group label="Minimum Level" required>
                        <input type="number" step="0.01" min="0" id="edit_min_level" name="minimum_level" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </x-form-group>
                </div>

                <x-form-group label="Notes">
                    <textarea name="description" id="edit_description" placeholder="Optional notes..." rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
                </x-form-group>

                <div class="flex gap-3 mt-6">
                    <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('edit-requirement-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>

        {{-- Add Quantity / Restock Modal --}}
        <x-modal id="add-quantity-modal" title="Add Quantity / Restock Requirement">
            <form class="space-y-4" id="add-quantity-form" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-900">
                    <span class="font-semibold" id="restock_req_name">Operational Item</span>
                    <span class="text-xs text-blue-700 block mt-0.5">Current Stock: <strong id="restock_current_level">0</strong> <span id="restock_unit_label">units</span> (Max Capacity: <span id="restock_max_cap">0</span>)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form-group label="Quantity to Add" required>
                        <input type="number" step="0.01" min="0.01" id="restock_amount" name="amount" required placeholder="e.g. 50" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </x-form-group>

                    <x-form-group label="Unit Cost (₱)">
                        <input type="number" step="0.01" min="0" id="restock_unit_cost" name="unit_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </x-form-group>
                </div>

                <x-form-group label="Supplier / Vendor Source (Optional)">
                    <input type="text" name="supplier" placeholder="e.g. Caltex / Shell Barangay Depot" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                {{-- Proof of Cost / Receipt Upload --}}
                <x-receipt-input label="Proof of Cost / Official Receipt (OR) / Delivery Invoice (Optional)" help="Upload supplier official receipt, invoice, or delivery receipt as proof of cost (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

                <x-form-group label="Restock Notes">
                    <textarea name="notes" placeholder="Delivery details, batch number, etc..." rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
                </x-form-group>

                <div class="flex gap-3 mt-6">
                    <x-button variant="primary" type="submit" class="flex-1">Add Stock & Record</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('add-quantity-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>
    @endif

    @push('scripts')
        <script>
            function openViewRequirementModal(req, assetTypeName) {
                document.getElementById('view_req_name').innerText = req.requirement_type || 'Unnamed Requirement';
                document.getElementById('view_req_asset_type').innerText = (assetTypeName && assetTypeName !== 'General / Any') ? ('For: ' + assetTypeName) : 'General / Unassigned to Asset Type';

                const unit = req.unit || 'units';
                const current = parseFloat(req.current_level || 0);
                const max = parseFloat(req.maximum_capacity || 0);
                const min = parseFloat(req.minimum_level || 0);

                document.getElementById('view_req_current_level').innerText = `${current} ${unit}`;
                document.getElementById('view_req_max_capacity').innerText = `${max} ${unit}`;
                document.getElementById('view_req_min_level').innerText = `${min} ${unit}`;

                const isLow = current <= min;
                const badgeHtml = isLow
                    ? '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 border border-red-200"><i class="fas fa-triangle-exclamation text-[10px]"></i> Low Stock</span>'
                    : '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fas fa-check-circle text-[10px]"></i> In Stock</span>';
                document.getElementById('view_req_status_badge').innerHTML = badgeHtml;

                document.getElementById('view_req_description').innerText = req.description && req.description.trim() !== '' ? req.description : 'No additional notes provided.';

                window.openModal('view-requirement-modal');
            }

            @if(!$isReadOnlyStaff)
                function openAddRequirementModal() {
                    window.openModal('add-requirement-modal');
                }
                function openAddQuantityModal(req) {
                    const form = document.getElementById('add-quantity-form');
                    form.action = '/operational-requirements/' + req.id + '/refill';
                    document.getElementById('restock_req_name').innerText = req.requirement_type;
                    document.getElementById('restock_current_level').innerText = req.current_level;
                    document.getElementById('restock_unit_label').innerText = req.unit;
                    document.getElementById('restock_max_cap').innerText = req.maximum_capacity + ' ' + req.unit;
                    document.getElementById('restock_amount').value = '';
                    document.getElementById('restock_unit_cost').value = '';
                    window.openModal('add-quantity-modal');
                }
                function editRequirement(req) {
                    const form = document.getElementById('edit-requirement-form');
                    form.action = '/operational-requirements/' + req.id;
                    document.getElementById('edit_requirement_type').value = req.requirement_type;
                    if (document.getElementById('edit_asset_type_id')) {
                        document.getElementById('edit_asset_type_id').value = req.asset_type_id || '';
                    }
                    document.getElementById('edit_unit').value = req.unit;
                    document.getElementById('edit_current_level').value = req.current_level;
                    document.getElementById('edit_max_capacity').value = req.maximum_capacity;
                    document.getElementById('edit_min_level').value = req.minimum_level;
                    document.getElementById('edit_description').value = req.description || '';
                    window.openModal('edit-requirement-modal');
                }
            @endif
        </script>
    @endpush
@endsection
