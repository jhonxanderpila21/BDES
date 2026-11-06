@extends('layouts.admin')

@section('title', 'Asset Components and Parts - BDES')

@section('content')
@php
    $isReadOnlyStaff = auth()->user()?->isViewer() || auth()->user()?->hasRole('viewer', 'staff');
@endphp

    {{-- Page Header --}}
    <x-page-header 
        title="Asset Components and Parts"
        icon="fas fa-puzzle-piece"
        subtitle="Track and manage equipment components, replacement parts, and accessories"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asset Management'],
            ['label' => 'Asset Components & Parts']
        ]"
    >
        <x-slot:actions>
            @if($activeTab === 'accessories')
                <button onclick="openAddAccessoryModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Accessory
                </button>
            @elseif($activeTab === 'attachments')
                <button onclick="openAddAttachmentModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Attachment
                </button>
            @else
                <div x-data="{ open: false }" class="relative inline-block text-left">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                        <i class="fas fa-plus"></i>
                        <span>Add Component / Part</span>
                        <i class="fas fa-chevron-down text-xs ml-1"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="origin-top-right absolute right-0 mt-2 w-52 rounded-xl shadow-lg bg-white ring-1 ring-black/5 z-20 py-1 divide-y divide-gray-100">
                        <button type="button" onclick="openAddAttachmentModal(); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2">
                            <i class="fas fa-puzzle-piece text-blue-500"></i> New Component
                        </button>
                        <button type="button" onclick="openAddAccessoryModal(); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-purple-50 hover:text-purple-700 flex items-center gap-2">
                            <i class="fas fa-wrench text-purple-500"></i> New Part
                        </button>
                    </div>
                </div>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- High-Level KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-kpi-card 
            title="Total Components & Parts" 
            :value="$metrics['total_components']" 
            icon="fas fa-puzzle-piece" 
            color="blue" 
            subtitle="Total registered items" 
            :href="route('attachments.index', ['tab' => 'all'])" 
        />
        <x-kpi-card 
            title="Units in Stock" 
            :value="$metrics['total_available_units']" 
            icon="fas fa-boxes-stacked" 
            color="indigo" 
            subtitle="Items currently in stock" 
        />
        <x-kpi-card 
            title="Available In-Service" 
            :value="$metrics['available_components'] ?? 0" 
            icon="fas fa-check-circle" 
            color="emerald" 
            subtitle="Ready to use" 
        />
        <x-kpi-card 
            title="Total Valuation" 
            :value="'₱' . number_format($metrics['total_inventory_value'], 2)" 
            icon="fas fa-coins" 
            color="purple" 
            subtitle="Total recorded value" 
        />
    </div>

    {{-- Filters & Search --}}
    <form action="{{ route('attachments.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
        <input type="hidden" name="tab" value="{{ $activeTab }}">

        <div class="flex-1">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Search component or part name, asset type, or linked asset..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>

        @if($activeTab === 'accessories')
            <select name="asset_type" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Asset Types</option>
                @foreach($accessoryAssetTypes as $t)
                    <option value="{{ $t }}" {{ request('asset_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Statuses</option>
                @foreach($accessoryStatuses as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        @else
            <select name="condition" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Conditions</option>
                @foreach(['good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor'] as $key => $label)
                    <option value="{{ $key }}" {{ request('condition') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        @endif

        <select name="asset_id" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">All Linked Assets</option>
            @foreach($assets as $asset)
                <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                    {{ $asset->code }} — {{ $asset->name }}
                </option>
            @endforeach
        </select>

        <select name="sort_by" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="id" {{ request('sort_by') === 'id' || !request('sort_by') ? 'selected' : '' }}>Sort: Default</option>
            @if($activeTab === 'accessories')
                <option value="accessory_name" {{ request('sort_by') === 'accessory_name' ? 'selected' : '' }}>Sort: Accessory Name</option>
                <option value="asset_type" {{ request('sort_by') === 'asset_type' ? 'selected' : '' }}>Sort: Asset Type</option>
                <option value="quantity" {{ request('sort_by') === 'quantity' ? 'selected' : '' }}>Sort: Quantity</option>
                <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Sort: Status</option>
            @elseif($activeTab === 'attachments')
                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Sort: Attachment Name</option>
                <option value="quantity" {{ request('sort_by') === 'quantity' ? 'selected' : '' }}>Sort: Quantity</option>
                <option value="condition" {{ request('sort_by') === 'condition' ? 'selected' : '' }}>Sort: Condition</option>
            @else
                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Sort: Component Name</option>
                <option value="quantity" {{ request('sort_by') === 'quantity' ? 'selected' : '' }}>Sort: Quantity</option>
            @endif
        </select>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            Filter
        </button>

        @if(request()->hasAny(['search', 'type', 'condition', 'asset_type', 'status', 'asset_id', 'sort_by']))
            <a href="{{ route('attachments.index', ['tab' => $activeTab]) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                Reset
            </a>
        @endif
    </form>

    {{-- Unified Components and Parts Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            @if($activeTab === 'accessories')
                                Accessory Name
                            @elseif($activeTab === 'attachments')
                                Attachment Name
                            @else
                                Component / Part Name
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">For Asset Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Linked Asset</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Condition</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($items as $item)
                        @php
                            $isAccessory = isset($item->is_accessory) ? $item->is_accessory : ($item instanceof \App\Models\AssetAccessory);
                            $displayName = $isAccessory ? $item->accessory_name : $item->name;
                            $classification = $isAccessory ? 'Accessory' : ($item->type ?: 'General');
                            $forAssetType = $isAccessory ? ($item->asset_type ?: 'Any') : ($item->assetType->name ?? 'Any');
                        @endphp
                        <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer group {{ $isAccessory && ($item->status === 'Not Available' || $item->quantity <= 0) ? 'opacity-75' : '' }}" onclick="{{ $isAccessory ? 'openViewAccessoryModal(' . json_encode($item) . ', \'' . addslashes($item->asset->name ?? '') . '\', \'' . addslashes($item->asset->code ?? '') . '\');' : 'openViewAttachmentModal(' . json_encode($item) . ', \'' . addslashes($item->asset->name ?? '') . '\', \'' . addslashes($item->asset->code ?? '') . '\', \'' . addslashes($item->assetType->name ?? '') . '\');' }}" title="Quick View">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <i class="fas fa-puzzle-piece text-blue-500 text-sm group-hover:scale-110 transition-transform"></i>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $displayName }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $forAssetType }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->asset)
                                    @if($isReadOnlyStaff)
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $item->asset->name }}</p>
                                            <p class="text-xs font-mono text-gray-500">{{ $item->asset->code }}</p>
                                        </div>
                                    @else
                                        <a href="{{ route('assets.show', $item->asset) }}" class="group block hover:text-blue-600">
                                            <p class="text-sm font-medium text-gray-800 group-hover:text-blue-600">{{ $item->asset->name }}</p>
                                            <p class="text-xs font-mono text-gray-500 group-hover:text-blue-600">{{ $item->asset->code }}</p>
                                        </a>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-sm">
                                @if($item->quantity > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-check-circle text-[10px]"></i> {{ $item->quantity }} Available
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        <i class="fas fa-circle-xmark text-[10px]"></i> No Available
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $rawCond = strtolower($item->condition ?? 'good');
                                    $cond = ($rawCond === 'excellent') ? 'good' : $rawCond;
                                    $condColor = match($cond) {
                                        'good' => 'success',
                                        'fair' => 'warning',
                                        'poor' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <x-badge variant="{{ $condColor }}" size="sm">{{ ucfirst($cond) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                    @if(!$isAccessory)
                                        @if(!$isReadOnlyStaff)
                                            <button 
                                                type="button"
                                                onclick="event.stopPropagation(); openEditAttachmentModal({{ json_encode($item) }})" 
                                                class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                                title="Edit Attachment"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('attachments.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Attachment: {{ addslashes($item->name) }}', 'This attachment record will be removed from the system.');" onclick="event.stopPropagation()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Attachment">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        @if(!$isReadOnlyStaff)
                                            <button 
                                                type="button"
                                                onclick="event.stopPropagation(); openAdjustModal({{ $item->id }}, @js($item->accessory_name), {{ $item->quantity }})"
                                                class="p-1.5 rounded hover:bg-gray-100 text-blue-600 hover:text-blue-800 transition-colors" 
                                                title="Adjust Quantity"
                                            >
                                                <i class="fas fa-sliders-h"></i>
                                            </button>
                                            <button 
                                                type="button"
                                                onclick="event.stopPropagation(); openEditAccessoryModal({{ json_encode($item) }})"
                                                class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                                title="Edit Accessory"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('asset-accessories.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Accessory: {{ addslashes($item->accessory_name) }}', 'This accessory record will be removed from the system.');" onclick="event.stopPropagation()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Accessory">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-empty-state 
                                    icon="fas fa-puzzle-piece" 
                                    title="{{ $activeTab === 'accessories' ? 'No accessories found' : ($activeTab === 'attachments' ? 'No attachments found' : 'No components or parts found') }}" 
                                    :description="$isReadOnlyStaff ? 'No items matching the criteria were found.' : 'Click Add Attachment or Add Accessory above to register equipment components and parts.'" 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $items->links() }}
        </div>
    </x-card>

    {{-- ========================================================================= --}}
    {{-- DETAILS MODALS: ATTACHMENT & ACCESSORY                                     --}}
    {{-- ========================================================================= --}}
    
    {{-- Hidden compatibility elements --}}
    <div id="attachment-details-modal" class="hidden"></div>
    <div id="accessory-details-modal" class="hidden"></div>

    {{-- Component / Part Details Modal (Attachments) --}}
    <x-modal id="view-attachment-modal" title="Component / Part Details" maxWidth="2xl">
        <div class="space-y-5">
            {{-- Header info --}}
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span id="dtl_attach_type" class="hidden"></span>
                        <h3 id="dtl_attach_name" class="text-lg font-bold text-gray-900">Component / Part Name</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="dtl_attach_cond_badge"></span>
                        <span id="dtl_attach_status_badge"></span>
                    </div>
                </div>
            </div>

            {{-- Linked Asset Card --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600 flex items-center gap-2">
                    <i class="fas fa-link text-blue-600"></i>
                    <span>Assigned Asset</span>
                </h4>
                <div id="dtl_attach_asset_box" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                            <i class="fas fa-boxes-stacked"></i>
                        </span>
                        <div>
                            <span id="dtl_attach_asset_code" class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">CODE</span>
                            <span id="dtl_attach_asset_name" class="font-semibold text-gray-900 text-sm ml-1">Asset Name</span>
                        </div>
                    </div>
                    <a id="dtl_attach_asset_link" href="#" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold border border-blue-200 transition-colors">
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                        <span>View Asset</span>
                    </a>
                </div>
            </div>

            {{-- Grid specs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Quantity in Stock</span>
                    <span id="dtl_attach_quantity" class="text-base font-bold text-gray-900 block mt-0.5">1</span>
                </div>
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">For Asset Type</span>
                    <span id="dtl_attach_asset_type" class="text-sm font-semibold text-gray-800 block mt-0.5">General</span>
                </div>
                <span id="dtl_attach_unit_cost" class="hidden"></span>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                @if(!$isReadOnlyStaff)
                <button type="button" id="dtl_attach_edit_btn" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl text-xs font-bold border border-amber-200 transition-colors">
                    <i class="fas fa-edit"></i>
                    <span>Edit Component / Part</span>
                </button>
                @endif
                <button type="button" onclick="window.closeModal('view-attachment-modal'); window.closeModal('attachment-details-modal');" class="w-full sm:w-auto px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Component / Part Details Modal (Accessories) --}}
    <x-modal id="view-accessory-modal" title="Component / Part Details" maxWidth="2xl">
        <div class="space-y-5">
            {{-- Header info --}}
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span id="dtl_acc_type" class="hidden"></span>
                        <h3 id="dtl_acc_name" class="text-lg font-bold text-gray-900">Component / Part Name</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="dtl_acc_cond_badge"></span>
                        <span id="dtl_acc_status_badge"></span>
                    </div>
                </div>
            </div>

            {{-- Linked Asset Card --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600 flex items-center gap-2">
                    <i class="fas fa-link text-blue-600"></i>
                    <span>Assigned Asset</span>
                </h4>
                <div id="dtl_acc_asset_box" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                            <i class="fas fa-boxes-stacked"></i>
                        </span>
                        <div>
                            <span id="dtl_acc_asset_code" class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">CODE</span>
                            <span id="dtl_acc_asset_name" class="font-semibold text-gray-900 text-sm ml-1">Asset Name</span>
                        </div>
                    </div>
                    <a id="dtl_acc_asset_link" href="#" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold border border-blue-200 transition-colors">
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                        <span>View Asset</span>
                    </a>
                </div>
            </div>

            {{-- Grid specs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Quantity in Stock</span>
                    <span id="dtl_acc_quantity" class="text-base font-bold text-gray-900 block mt-0.5">0 units</span>
                </div>
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">For Asset Type</span>
                    <span id="dtl_acc_asset_type" class="text-sm font-semibold text-gray-800 block mt-0.5">General</span>
                </div>
                <span id="dtl_acc_unit_cost" class="hidden"></span>
                <span id="dtl_acc_total_value" class="hidden"></span>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                @if(!$isReadOnlyStaff)
                <button type="button" id="dtl_acc_edit_btn" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl text-xs font-bold border border-amber-200 transition-colors">
                    <i class="fas fa-edit"></i>
                    <span>Edit Component / Part</span>
                </button>
                @endif
                <button type="button" onclick="window.closeModal('view-accessory-modal'); window.closeModal('accessory-details-modal');" class="w-full sm:w-auto px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    @if(!$isReadOnlyStaff)
        {{-- ========================================================================= --}}
        {{-- MODALS: ATTACHMENTS (ADMIN / INVENTORY OFFICER ONLY)                      --}}
        {{-- ========================================================================= --}}
        {{-- Add Attachment Modal --}}
        <x-modal id="add-attachment-modal" title="Add Asset Attachment">
            <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <x-form-group label="Attachment Name" required>
                    <input type="text" name="name" placeholder="e.g., Heavy Duty Fire Hose (30m)" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <input type="hidden" name="type" value="Component">

                <x-form-group label="For Asset Type">
                    <select name="asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Optional Asset Type --</option>
                        @foreach($assetTypes as $at)
                            <option value="{{ $at->id }}">{{ $at->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-form-group label="Linked Asset">
                            <select name="asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">-- Unassigned / General --</option>
                                @foreach($assets as $ast)
                                    <option value="{{ $ast->id }}">{{ $ast->code }} — {{ $ast->name }}</option>
                                @endforeach
                            </select>
                        </x-form-group>
                    </div>

                    <div>
                        <x-form-group label="Quantity" required>
                            <input type="number" min="0" name="quantity" value="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </x-form-group>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form-group label="Condition" required>
                        <select name="condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="good" selected>Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </x-form-group>

                    <x-form-group label="Unit Cost (₱)">
                        <input type="number" step="0.01" min="0" name="unit_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </x-form-group>
                </div>

                {{-- File / Photo / Receipt Input --}}
                <x-receipt-input label="Proof of Cost / Official Receipt (OR) / Invoice (Optional)" help="Upload official receipt, invoice, or purchase order as proof of cost (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <x-button variant="primary" type="submit" class="flex-1">Create Attachment</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('add-attachment-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>

        {{-- Edit Attachment Modal --}}
        <x-modal id="edit-attachment-modal" title="Edit Asset Attachment">
            <form id="edit-attachment-form" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <x-form-group label="Attachment Name" required>
                    <input type="text" name="name" id="edit_attach_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <input type="hidden" name="type" id="edit_attach_type" value="Component">

                <x-form-group label="For Asset Type">
                    <select name="asset_type_id" id="edit_attach_asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Optional Asset Type --</option>
                        @foreach($assetTypes as $at)
                            <option value="{{ $at->id }}">{{ $at->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-form-group label="Linked Asset">
                            <select name="asset_id" id="edit_attach_asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">-- Unassigned / General --</option>
                                @foreach($assets as $ast)
                                    <option value="{{ $ast->id }}">{{ $ast->code }} — {{ $ast->name }}</option>
                                @endforeach
                            </select>
                        </x-form-group>
                    </div>

                    <div>
                        <x-form-group label="Quantity" required>
                            <input type="number" min="0" name="quantity" id="edit_attach_quantity" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </x-form-group>
                    </div>
                </div>

                <x-form-group label="Condition" required>
                    <select name="condition" id="edit_attach_condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                </x-form-group>

                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('edit-attachment-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>

        {{-- ========================================================================= --}}
        {{-- MODALS: ACCESSORIES (ADMIN / INVENTORY OFFICER ONLY)                      --}}
        {{-- ========================================================================= --}}
        {{-- Add Accessory Modal --}}
        <x-modal id="add-accessory-modal" title="Add Accessory">
            <form class="space-y-4" action="{{ route('asset-accessories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <x-form-group label="Accessory Name" required>
                    <input type="text" name="accessory_name" placeholder="e.g., Mounting Bracket, Spare Battery" value="{{ old('accessory_name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <x-form-group label="Linked Asset" required>
                    <select name="asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Select Asset</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                {{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </x-form-group>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form-group label="Initial Quantity" required>
                        <input type="number" min="0" name="quantity" value="{{ old('quantity', 0) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </x-form-group>

                    <x-form-group label="Unit Cost (₱)" required>
                        <input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost', '0.00') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </x-form-group>
                </div>

                {{-- File / Photo / Receipt Input --}}
                <x-receipt-input label="Proof of Cost / Official Receipt (OR) / Invoice (Optional)" help="Upload official receipt, invoice, or purchase voucher as proof of cost (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <x-button variant="primary" type="submit" class="flex-1">Create Accessory</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('add-accessory-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>

        {{-- Edit Accessory Modal --}}
        <x-modal id="edit-accessory-modal" title="Edit Accessory">
            <form class="space-y-4" id="edit-accessory-form" method="POST">
                @csrf
                @method('PUT')

                <x-form-group label="Accessory Name" required>
                    <input type="text" name="accessory_name" id="edit_accessory_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <x-form-group label="Linked Asset" required>
                    <select name="asset_id" id="edit_accessory_asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Select Asset</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </x-form-group>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form-group label="Quantity" required>
                        <input type="number" min="0" name="quantity" id="edit_accessory_quantity" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </x-form-group>

                    <x-form-group label="Unit Cost (₱)" required>
                        <input type="number" step="0.01" min="0" name="unit_cost" id="edit_accessory_unit_cost" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </x-form-group>
                </div>

                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('edit-accessory-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>

        {{-- Adjust Quantity Modal --}}
        <x-modal id="adjust-quantity-modal" title="Adjust Quantity">
            <form class="space-y-4" id="adjust-quantity-form" method="POST">
                @csrf

                <div class="bg-blue-50 rounded-lg p-4 mb-2">
                    <p class="text-sm text-blue-800">
                        <strong>Accessory:</strong> <span id="adjust_accessory_name"></span>
                    </p>
                    <p class="text-sm text-blue-800 mt-1">
                        <strong>Current Quantity:</strong> <span id="adjust_current_qty"></span>
                    </p>
                </div>

                <x-form-group label="Action" required>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="action" value="add" checked class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <span class="text-sm font-medium text-gray-700">Restock (Add)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="action" value="subtract" class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <span class="text-sm font-medium text-gray-700">Consume (Subtract)</span>
                        </label>
                    </div>
                </x-form-group>

                <x-form-group label="Amount" required>
                    <input type="number" min="1" name="amount" id="adjust_amount_input" placeholder="Quantity to add/subtract..." required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <x-button variant="primary" type="submit" class="flex-1">Update Quantity</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('adjust-quantity-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>
    @endif

    @push('scripts')
        <script>
            // Unified View Attachment Modal (Available for all roles)
            function openViewAttachmentModal(attachment, assetName, assetCode, typeName) {
                document.getElementById('dtl_attach_name').textContent = attachment.name || 'Unnamed Attachment';
                document.getElementById('dtl_attach_type').textContent = attachment.type || 'General Attachment';
                
                const qty = parseInt(attachment.quantity || 0, 10);
                document.getElementById('dtl_attach_quantity').textContent = qty > 0 ? `${qty} unit(s)` : 'No Available';
                
                const compatibleType = typeName || (attachment.asset_type && attachment.asset_type.name ? attachment.asset_type.name : (attachment.assetType ? attachment.assetType.name : 'General'));
                document.getElementById('dtl_attach_asset_type').textContent = compatibleType;

                // Condition Badge
                let cond = (attachment.condition || 'good').toLowerCase();
                if (cond === 'excellent') cond = 'good';
                const condBadge = cond === 'good'
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">GOOD</span>'
                    : (cond === 'fair'
                        ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">FAIR</span>'
                        : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">POOR</span>');
                document.getElementById('dtl_attach_cond_badge').innerHTML = condBadge;

                // Status Badge
                if (qty > 0) {
                    document.getElementById('dtl_attach_status_badge').innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-check-circle text-[9px]"></i> Available</span>';
                } else {
                    document.getElementById('dtl_attach_status_badge').innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200"><i class="fas fa-circle-xmark text-[9px]"></i> No Available</span>';
                }

                // Linked Asset
                const aName = assetName || (attachment.asset ? attachment.asset.name : '');
                const aCode = assetCode || (attachment.asset ? attachment.asset.code : '');
                const aId = attachment.asset_id || (attachment.asset ? attachment.asset.id : null);

                if (aName && aName.trim() !== '') {
                    document.getElementById('dtl_attach_asset_code').textContent = aCode || 'CODE';
                    document.getElementById('dtl_attach_asset_code').classList.remove('hidden');
                    document.getElementById('dtl_attach_asset_name').textContent = aName;
                    if (aId) {
                        document.getElementById('dtl_attach_asset_link').href = `/assets/${aId}`;
                        document.getElementById('dtl_attach_asset_link').classList.remove('hidden');
                    } else {
                        document.getElementById('dtl_attach_asset_link').classList.add('hidden');
                    }
                } else {
                    document.getElementById('dtl_attach_asset_code').textContent = '—';
                    document.getElementById('dtl_attach_asset_name').textContent = 'Not assigned to any asset';
                    document.getElementById('dtl_attach_asset_link').classList.add('hidden');
                }

                const unitCost = parseFloat(attachment.unit_cost || 0);
                const unitCostElem = document.getElementById('dtl_attach_unit_cost');
                if (unitCostElem) {
                    unitCostElem.textContent = unitCost > 0
                        ? '₱' + unitCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                        : '₱0.00';
                }

                @if(!$isReadOnlyStaff)
                const editBtn = document.getElementById('dtl_attach_edit_btn');
                if (editBtn) {
                    editBtn.onclick = function() {
                        window.closeModal('view-attachment-modal');
                        window.closeModal('attachment-details-modal');
                        openEditAttachmentModal(attachment);
                    };
                }
                @endif

                window.openModal('view-attachment-modal');
            }

            function openAttachmentDetailsModal(attachment) {
                openViewAttachmentModal(attachment);
            }

            // Unified View Accessory Modal (Available for all roles)
            function openViewAccessoryModal(acc, assetName, assetCode) {
                document.getElementById('dtl_acc_name').textContent = acc.accessory_name || 'Unnamed Accessory';
                document.getElementById('dtl_acc_type').textContent = acc.asset_type || 'Accessory';
                
                const qty = parseInt(acc.quantity || 0, 10);
                document.getElementById('dtl_acc_quantity').textContent = qty > 0 ? `${qty} unit(s)` : 'No Available';

                const accAssetType = acc.asset_type || (acc.asset && acc.asset.type ? acc.asset.type.name : 'General');
                const dtlAccAssetType = document.getElementById('dtl_acc_asset_type');
                if (dtlAccAssetType) dtlAccAssetType.textContent = accAssetType;

                const unitCost = parseFloat(acc.unit_cost || 0);
                document.getElementById('dtl_acc_unit_cost').textContent = unitCost > 0 
                    ? '₱' + unitCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                    : '₱0.00';

                const totalVal = parseFloat(acc.total_value || (qty * unitCost) || 0);
                const totalValElem = document.getElementById('dtl_acc_total_value');
                if (totalValElem) {
                    totalValElem.textContent = totalVal > 0 
                        ? '₱' + totalVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        : '₱0.00';
                }

                // Condition Badge
                let accCond = (acc.condition || 'good').toLowerCase();
                if (accCond === 'excellent') accCond = 'good';
                const accCondBadge = accCond === 'good'
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">GOOD</span>'
                    : (accCond === 'fair'
                        ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">FAIR</span>'
                        : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">POOR</span>');
                document.getElementById('dtl_acc_cond_badge').innerHTML = accCondBadge;

                // Status Badge
                if (qty > 0) {
                    document.getElementById('dtl_acc_status_badge').innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-check-circle text-[9px]"></i> Available</span>';
                } else {
                    document.getElementById('dtl_acc_status_badge').innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200"><i class="fas fa-circle-xmark text-[9px]"></i> No Available</span>';
                }

                // Linked Asset
                const aName = assetName || (acc.asset ? acc.asset.name : '');
                const aCode = assetCode || (acc.asset ? acc.asset.code : '');
                const aId = acc.asset_id || (acc.asset ? acc.asset.id : null);

                if (aName && aName.trim() !== '') {
                    document.getElementById('dtl_acc_asset_code').textContent = aCode || 'CODE';
                    document.getElementById('dtl_acc_asset_name').textContent = aName;
                    if (aId) {
                        document.getElementById('dtl_acc_asset_link').href = `/assets/${aId}`;
                        document.getElementById('dtl_acc_asset_link').classList.remove('hidden');
                    } else {
                        document.getElementById('dtl_acc_asset_link').classList.add('hidden');
                    }
                } else {
                    document.getElementById('dtl_acc_asset_code').textContent = '—';
                    document.getElementById('dtl_acc_asset_name').textContent = 'Not assigned to any asset';
                    document.getElementById('dtl_acc_asset_link').classList.add('hidden');
                }

                @if(!$isReadOnlyStaff)
                const editBtn = document.getElementById('dtl_acc_edit_btn');
                if (editBtn) {
                    editBtn.onclick = function() {
                        window.closeModal('view-accessory-modal');
                        window.closeModal('accessory-details-modal');
                        openEditAccessoryModal(acc);
                    };
                }
                @endif

                window.openModal('view-accessory-modal');
            }

            function openAccessoryDetailsModal(acc) {
                openViewAccessoryModal(acc);
            }

            @if(!$isReadOnlyStaff)
                // Modifying Modals (Admin and Inventory Officers only)
                function openAddAttachmentModal() {
                    window.openModal('add-attachment-modal');
                }

                function openEditAttachmentModal(attachment) {
                    const form = document.getElementById('edit-attachment-form');
                    form.action = `/attachments/${attachment.id}`;
                    document.getElementById('edit_attach_name').value = attachment.name || '';
                    document.getElementById('edit_attach_type').value = attachment.type || '';
                    document.getElementById('edit_attach_asset_type_id').value = attachment.asset_type_id || '';
                    document.getElementById('edit_attach_asset_id').value = attachment.asset_id || '';
                    document.getElementById('edit_attach_quantity').value = attachment.quantity || 1;
                    let editCond = (attachment.condition || 'good').toLowerCase();
                    if (editCond === 'excellent') editCond = 'good';
                    document.getElementById('edit_attach_condition').value = editCond;
                    window.openModal('edit-attachment-modal');
                }

                function openAddAccessoryModal() {
                    window.openModal('add-accessory-modal');
                }
                function openAddModal() {
                    openAddAccessoryModal();
                }

                function openEditAccessoryModal(acc) {
                    const form = document.getElementById('edit-accessory-form');
                    form.action = `/asset-accessories/${acc.id}`;
                    document.getElementById('edit_accessory_name').value = acc.accessory_name || '';
                    document.getElementById('edit_accessory_asset_id').value = acc.asset_id || '';
                    document.getElementById('edit_accessory_quantity').value = acc.quantity || 0;
                    document.getElementById('edit_accessory_unit_cost').value = parseFloat(acc.unit_cost || 0).toFixed(2);
                    window.openModal('edit-accessory-modal');
                }
                function openEditModal(acc) {
                    openEditAccessoryModal(acc);
                }

                function openAdjustModal(id, name, currentQty) {
                    const form = document.getElementById('adjust-quantity-form');
                    form.action = `/asset-accessories/${id}/adjust-quantity`;
                    document.getElementById('adjust_accessory_name').textContent = name;
                    document.getElementById('adjust_current_qty').textContent = currentQty;
                    document.getElementById('adjust_amount_input').value = '';
                    form.querySelector('input[name="action"][value="add"]').checked = true;
                    window.openModal('adjust-quantity-modal');
                }
            @endif

            // Backward-compatibility Aliases
            function openAttachmentDetailsModal(attachment) {
                openViewAttachmentModal(attachment);
            }

            function openAccessoryDetailsModal(acc) {
                openViewAccessoryModal(acc);
            }
        </script>
    @endpush
@endsection
