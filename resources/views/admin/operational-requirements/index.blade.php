@extends('layouts.admin')

@section('title', 'Operational Requirements - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <i class="fas fa-list-check text-blue-600"></i>
                Operational Requirements
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Operational Requirements']
            ]" />
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="openAddRequirementModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                <i class="fas fa-plus"></i>
                Add Requirement
            </button>
        </div>
    </div>

    {{-- High-Level KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total Requirements --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Requirements</p>
                <p class="text-2xl font-bold text-gray-900">{{ $metrics['total_requirements'] ?? $requirements->total() }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fas fa-gas-pump"></i>
            </div>
        </div>

        {{-- Adequate Reserves --}}
        <div class="bg-white rounded-xl p-5 border border-emerald-200 bg-emerald-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Adequate Stock</p>
                <p class="text-2xl font-bold text-emerald-700">{{ $metrics['adequate_stock'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>

        {{-- Low Stock Alert --}}
        <div class="bg-white rounded-xl p-5 border border-rose-200 bg-rose-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-700 mb-1">Low Stock Alert</p>
                <p class="text-2xl font-bold text-rose-700">{{ $metrics['low_stock'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-xl">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
        </div>

        {{-- Covered Asset Types --}}
        <div class="bg-white rounded-xl p-5 border border-indigo-200 bg-indigo-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 mb-1">Equipment Types Covered</p>
                <p class="text-2xl font-bold text-indigo-700">{{ $metrics['monitored_types'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <form action="{{ route('operational-requirements.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
        <div class="flex-1">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Search requirement, supply, or equipment type..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>

        <select name="asset_type_id" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">All Equipment Types</option>
            @foreach($assetTypes as $t)
                <option value="{{ $t->id }}" {{ request('asset_type_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">All Stock Statuses</option>
            <option value="adequate" {{ request('status') === 'adequate' ? 'selected' : '' }}>In Stock / Adequate</option>
            <option value="low" {{ request('status') === 'low' || request('low') ? 'selected' : '' }}>Low Stock / Below Min</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            Filter
        </button>

        @if(request()->hasAny(['search', 'asset_type_id', 'status', 'low']))
            <a href="{{ route('operational-requirements.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                Reset
            </a>
        @endif
    </form>

    {{-- Requirements Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Requirement / Supply</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">For Asset Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Available Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Assets Using</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($requirements as $requirement)
                        @php
                            $current = (float)$requirement->current_level;
                            $max = (float)$requirement->maximum_capacity;
                            $min = (float)$requirement->minimum_level;
                            $pct = $max > 0 ? min(100, round(($current / $max) * 100)) : 0;
                            $isLow = $requirement->isLowStock();
                            $usingCount = $requirement->using_assets->count();
                        @endphp
                        <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer group" onclick="openRequirementDetailsModal(@js($requirement))">
                            {{-- Requirement Name & Description --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-gas-pump text-blue-500 text-sm group-hover:scale-110 transition-transform"></i>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $requirement->requirement_type }}</p>
                                        @if($requirement->description)
                                            <p class="text-xs text-gray-400 truncate max-w-xs mt-0.5">{{ $requirement->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Asset Type --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                {{ $requirement->assetType->name ?? 'General / Cross-Equipment' }}
                            </td>

                            {{-- Available Stock with Mini Progress Bar --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="space-y-1 max-w-[200px]">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold font-mono text-gray-900">{{ $current }} {{ $requirement->unit }}</span>
                                        <span class="text-gray-400 text-[11px] font-mono">Max: {{ $max }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $isLow ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 block">Min Threshold: {{ $min }} {{ $requirement->unit }}</span>
                                </div>
                            </td>

                            {{-- Assets Using Counter --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $usingCount > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-500' }}">
                                    <i class="fas fa-boxes-stacked text-[10px]"></i>
                                    {{ $usingCount }} {{ $usingCount === 1 ? 'asset' : 'assets' }}
                                </span>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($isLow)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                        <i class="fas fa-triangle-exclamation text-[10px] text-rose-600"></i> Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i class="fas fa-check-circle text-[10px] text-emerald-600"></i> In Stock
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                    <button 
                                        type="button"
                                        onclick="event.stopPropagation(); openRequirementDetailsModal(@js($requirement))" 
                                        class="p-1.5 rounded hover:bg-blue-100 text-blue-600 hover:text-blue-800 transition-colors" 
                                        title="View Requirement & Assets"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button 
                                        type="button"
                                        onclick='event.stopPropagation(); openAddQuantityModal(@json($requirement))' 
                                        class="p-1.5 rounded hover:bg-emerald-50 text-emerald-600 hover:text-emerald-800 transition-colors" 
                                        title="Add Quantity / Restock"
                                    >
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                    <button 
                                        type="button"
                                        onclick='event.stopPropagation(); editRequirement(@json($requirement))' 
                                        class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                        title="Edit Requirement"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('operational-requirements.destroy', $requirement) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this requirement?');" onclick="event.stopPropagation()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Requirement" onclick="event.stopPropagation()">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-empty-state icon="fas fa-list-check" title="No operational requirements found" description="Click Add Requirement above to register consumable fuels, oils, and emergency supplies." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $requirements->links() }}
        </div>
    </x-card>

    {{-- ========================================================================= --}}
    {{-- MODAL: REQUIREMENT DETAILS & ASSETS USING IT (MERGED VIEW)                --}}
    {{-- ========================================================================= --}}
    <x-modal id="requirement-details-modal" title="Operational Requirement & Supply Allocation" maxWidth="4xl">
        <div class="space-y-6">
            {{-- Header Card --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-bold text-sm">
                                <i class="fas fa-gas-pump"></i>
                            </span>
                            <div>
                                <h3 id="dtl_req_name" class="text-base font-bold text-gray-900">Requirement Name</h3>
                                <p id="dtl_req_type_badge" class="text-xs text-gray-500">Applicable Equipment Type</p>
                            </div>
                        </div>
                        <p id="dtl_req_desc" class="text-xs text-gray-600 mt-2 pl-10 italic">No notes provided</p>
                    </div>
                    <div id="dtl_req_status_container" class="flex items-center gap-2">
                        <!-- Dynamic status badge injected -->
                    </div>
                </div>
            </div>

            {{-- Stock Level Gauge Deck --}}
            <div class="bg-white p-4 rounded-xl border border-gray-200 space-y-3">
                <div class="flex items-center justify-between text-xs font-semibold text-gray-700">
                    <span>Current Reserve Stock Level</span>
                    <span id="dtl_req_stock_pct" class="font-mono text-blue-600 font-bold">0%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden border border-gray-200">
                    <div id="dtl_req_progress_bar" class="h-3 rounded-full bg-emerald-500 transition-all duration-300" style="width: 0%"></div>
                </div>
                <div class="grid grid-cols-3 gap-3 pt-2 text-center">
                    <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Current Stock</span>
                        <span id="dtl_req_current_level" class="text-sm font-bold text-gray-900">0</span>
                    </div>
                    <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Min Threshold</span>
                        <span id="dtl_req_min_level" class="text-sm font-bold text-rose-600">0</span>
                    </div>
                    <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Max Capacity</span>
                        <span id="dtl_req_max_capacity" class="text-sm font-bold text-blue-600">0</span>
                    </div>
                </div>
            </div>

            {{-- Assets Using This Requirement Table --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                        <i class="fas fa-boxes-stacked text-blue-600"></i>
                        <span>Assets Utilizing This Operational Requirement</span>
                    </h4>
                    <span id="dtl_req_assets_count" class="text-xs text-gray-400 font-medium">0 assets</span>
                </div>

                <div class="border border-gray-200 rounded-xl overflow-hidden max-h-80 overflow-y-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-gray-100/75 border-b border-gray-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Asset Code & Name</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Facility / Location</th>
                                <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Condition</th>
                                <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="dtl_req_assets_tbody" class="divide-y divide-gray-100 bg-white">
                            {{-- Dynamically populated --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" id="dtl_req_restock_btn" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Stock / Restock</span>
                    </button>
                    <a id="dtl_req_registry_link" href="#" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl text-xs font-semibold border border-blue-200 transition-colors">
                        <i class="fas fa-external-link-alt"></i>
                        <span>View Matching Assets in Registry</span>
                    </a>
                </div>
                <button type="button" onclick="window.closeModal('requirement-details-modal')" class="w-full sm:w-auto px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Add Requirement Modal --}}
    <x-modal id="add-requirement-modal" title="Add Operational Requirement">
        <form class="space-y-4" action="{{ route('operational-requirements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <x-form-group label="Requirement Name" required>
                <input type="text" name="requirement_type" placeholder="e.g., Diesel Fuel, Battery Pack, First Aid Kits" value="{{ old('requirement_type') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <x-form-group label="Applicable Equipment Type">
                <select name="asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                    <option value="">General (Applies to all or multiple equipment)</option>
                    @foreach($assetTypes as $type)
                        <option value="{{ $type->id }}" {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Unit of Measurement" required>
                <input type="text" name="unit" placeholder="e.g., liters, units, boxes, pairs" value="{{ old('unit') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Initial Stock Level" required>
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

    @push('scripts')
        <script>
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
                document.getElementById('edit_unit').value = req.unit;
                document.getElementById('edit_current_level').value = req.current_level;
                document.getElementById('edit_max_capacity').value = req.maximum_capacity;
                document.getElementById('edit_min_level').value = req.minimum_level;
                document.getElementById('edit_description').value = req.description || '';
                window.openModal('edit-requirement-modal');
            }

            // Interactive Dossier Modal Showing Assets Using Requirement
            function openRequirementDetailsModal(req) {
                document.getElementById('dtl_req_name').innerText = req.requirement_type;
                document.getElementById('dtl_req_type_badge').innerText = req.asset_type && req.asset_type.name 
                    ? `Equipment Type: ${req.asset_type.name}` 
                    : (req.assetType ? `Equipment Type: ${req.assetType.name}` : 'General / Cross-Equipment Consumable');
                document.getElementById('dtl_req_desc').innerText = req.description || 'No special handling instructions or operational notes specified.';

                const current = parseFloat(req.current_level || 0);
                const max = parseFloat(req.maximum_capacity || 0);
                const min = parseFloat(req.minimum_level || 0);
                const unit = req.unit || 'units';

                document.getElementById('dtl_req_current_level').innerText = `${current} ${unit}`;
                document.getElementById('dtl_req_min_level').innerText = `${min} ${unit}`;
                document.getElementById('dtl_req_max_capacity').innerText = `${max} ${unit}`;

                const pct = max > 0 ? Math.min(100, Math.round((current / max) * 100)) : 0;
                document.getElementById('dtl_req_stock_pct').innerText = `${pct}%`;
                
                const progressBar = document.getElementById('dtl_req_progress_bar');
                progressBar.style.width = `${pct}%`;

                const isLow = current <= min;
                if (isLow) {
                    progressBar.className = 'h-3 rounded-full bg-rose-500 transition-all duration-300';
                    document.getElementById('dtl_req_status_container').innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                            <i class="fas fa-triangle-exclamation text-[10px] text-rose-600"></i> Low Stock Alert
                        </span>
                    `;
                } else {
                    progressBar.className = 'h-3 rounded-full bg-emerald-500 transition-all duration-300';
                    document.getElementById('dtl_req_status_container').innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i class="fas fa-check-circle text-[10px] text-emerald-600"></i> In Stock & Adequate
                        </span>
                    `;
                }

                // Populate Assets Using this Supply
                const assets = req.using_assets || [];
                const countBadge = document.getElementById('dtl_req_assets_count');
                countBadge.innerText = `${assets.length} equipment unit${assets.length === 1 ? '' : 's'}`;

                const tbody = document.getElementById('dtl_req_assets_tbody');
                if (assets.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 italic text-xs">
                                <i class="fas fa-boxes-stacked text-2xl text-gray-300 mb-2 block"></i>
                                No equipment is currently registered as consuming or using this operational requirement.
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = assets.map(asset => {
                        let statusBadge = '';
                        if (asset.status === 'available') {
                            statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-check-circle text-[9px]"></i> Available</span>';
                        } else if (asset.status === 'deployed') {
                            statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Deployed</span>';
                        } else if (asset.status === 'under_maintenance') {
                            statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Maintenance</span>';
                        } else {
                            statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">${asset.status.replace('_', ' ')}</span>`;
                        }

                        const condBadge = asset.condition === 'excellent' || asset.condition === 'good'
                            ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">' + (asset.condition.toUpperCase()) + '</span>'
                            : (asset.condition === 'fair'
                                ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">FAIR</span>'
                                : '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">POOR</span>');

                        const locationName = asset.location ? asset.location.name : 'Unassigned';

                        return `
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded">${asset.code}</span>
                                        <div>
                                            <a href="/assets/${asset.id}" class="font-semibold text-gray-900 hover:text-blue-600 hover:underline block">${asset.name}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-middle text-xs text-gray-600">
                                    <i class="fas fa-location-dot text-gray-400 mr-1 text-[10px]"></i> ${locationName}
                                </td>
                                <td class="px-4 py-3 align-middle text-center">
                                    ${condBadge}
                                </td>
                                <td class="px-4 py-3 align-middle text-center">
                                    ${statusBadge}
                                </td>
                                <td class="px-4 py-3 align-middle text-right">
                                    <a href="/assets/${asset.id}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold border border-blue-200 transition-colors">
                                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                        <span>Dossier</span>
                                    </a>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }

                // Connect restock action button
                const restockBtn = document.getElementById('dtl_req_restock_btn');
                restockBtn.onclick = function() {
                    window.closeModal('requirement-details-modal');
                    openAddQuantityModal(req);
                };

                // Connect registry shortcut button
                const registryLink = document.getElementById('dtl_req_registry_link');
                if (req.asset_type_id) {
                    registryLink.href = `/assets?type_id=${req.asset_type_id}&tab=assets`;
                    registryLink.classList.remove('hidden');
                } else {
                    registryLink.classList.add('hidden');
                }

                window.openModal('requirement-details-modal');
            }
        </script>
    @endpush
@endsection
