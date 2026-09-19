@extends('layouts.admin')

@section('title', 'Attachments and Accessories - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <i class="fas fa-paperclip text-blue-600"></i>
                Attachments and Accessories
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Attachments & Accessories']
            ]" />
        </div>

        {{-- Contextual Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            @if($activeTab === 'accessories')
                <button onclick="openAddAccessoryModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Accessory
                </button>
            @else
                <button onclick="openAddAttachmentModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Attachment
                </button>
            @endif
        </div>
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger" class="mb-6">{{ $errors->first() }}</x-alert>
    @endif

    {{-- High-Level KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total Attachments --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Attachments</p>
                <p class="text-2xl font-bold text-gray-900">{{ $metrics['total_attachments'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fas fa-paperclip"></i>
            </div>
        </div>

        {{-- Attachment Units --}}
        <div class="bg-white rounded-xl p-5 border border-indigo-200 bg-indigo-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 mb-1">Attachment Units</p>
                <p class="text-2xl font-bold text-indigo-700">{{ $metrics['total_attachment_items'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fas fa-boxes-stacked"></i>
            </div>
        </div>

        {{-- Total Accessories --}}
        <div class="bg-white rounded-xl p-5 border border-purple-200 bg-purple-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-700 mb-1">Total Accessories</p>
                <p class="text-2xl font-bold text-purple-700">{{ $metrics['total_accessories'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
                <i class="fas fa-tools"></i>
            </div>
        </div>

        {{-- Total Accessory Inventory Value --}}
        <div class="bg-white rounded-xl p-5 border border-emerald-200 bg-emerald-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Accessory Value</p>
                <p class="text-2xl font-bold text-emerald-700">₱{{ number_format($metrics['total_inventory_value'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>

    {{-- Module Navigation Tabs --}}
    <div class="flex border-b border-gray-200 mb-6 space-x-1">
        <a 
            href="{{ route('attachments.index', ['tab' => 'attachments']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'attachments' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-paperclip"></i>
            <span>Asset Attachments</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'attachments' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $attachments->total() }}
            </span>
        </a>

        <a 
            href="{{ route('attachments.index', ['tab' => 'accessories']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'accessories' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-tools"></i>
            <span>Asset Accessories</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'accessories' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $accessories->total() }}
            </span>
        </a>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: ASSET ATTACHMENTS                                                  --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'attachments')
        {{-- Filters & Search --}}
        <form action="{{ route('attachments.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="attachments">

            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search attachment name, type, or linked asset..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Types</option>
                @foreach(['Hose', 'Bracket', 'Cable', 'Mount', 'Hardware', 'Adapter'] as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>

            <select name="condition" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Conditions</option>
                @foreach(['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor'] as $key => $label)
                    <option value="{{ $key }}" {{ request('condition') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="asset_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Linked Assets</option>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                        {{ $asset->code }} — {{ $asset->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>

            @if(request()->hasAny(['search', 'type', 'condition', 'asset_id']))
                <a href="{{ route('attachments.index', ['tab' => 'attachments']) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            @endif
        </form>

        {{-- Attachments Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Attachment Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">For Asset Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Linked Asset</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Condition</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($attachments as $attachment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm font-medium text-gray-900">{{ $attachment->name }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $attachment->type ?: 'General' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $attachment->assetType->name ?? 'Any' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attachment->asset)
                                        <p class="text-sm font-medium text-gray-800">{{ $attachment->asset->name }}</p>
                                        <p class="text-xs font-mono text-gray-500">{{ $attachment->asset->code }}</p>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-sm text-gray-900">
                                    {{ $attachment->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $cond = strtolower($attachment->condition ?? 'good');
                                        $condColor = match($cond) {
                                            'excellent', 'good' => 'success',
                                            'fair' => 'warning',
                                            'poor' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <x-badge variant="{{ $condColor }}" size="sm">{{ ucfirst($cond) }}</x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            onclick="openEditAttachmentModal({{ json_encode($attachment) }})" 
                                            class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                            title="Edit Attachment"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('attachments.destroy', $attachment) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Attachment">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <x-empty-state icon="fas fa-paperclip" title="No attachments found" description="Click Add Attachment above to record specialized equipment attachments." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $attachments->links() }}
            </div>
        </x-card>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: ASSET ACCESSORIES                                                  --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'accessories')
        {{-- Budgeting Summary (Inventory Value by Asset Type) --}}
        @if($budgetSummary->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-3">Inventory Value by Asset Type</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($budgetSummary as $type => $total)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col justify-between">
                            <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ $type }}</span>
                            <span class="text-2xl font-bold text-gray-900 mt-2">₱{{ number_format($total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Filters & Search --}}
        <form action="{{ route('attachments.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="accessories">

            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search accessory name, asset type, or linked asset..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="asset_type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Asset Types</option>
                @foreach($accessoryAssetTypes as $t)
                    <option value="{{ $t }}" {{ request('asset_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Statuses</option>
                @foreach($accessoryStatuses as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>

            @if(request()->hasAny(['search', 'asset_type', 'status']))
                <a href="{{ route('attachments.index', ['tab' => 'accessories']) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            @endif
        </form>

        {{-- Accessories Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Accessory Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">For Asset Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Linked Asset</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Unit Cost</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total Value</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($accessories as $acc)
                            <tr class="hover:bg-gray-50 transition-colors {{ $acc->status === 'Not Available' ? 'opacity-75' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ $acc->accessory_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge variant="info" size="sm">{{ $acc->asset_type }}</x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm font-medium text-gray-800">{{ $acc->asset->name ?? 'Unknown Asset' }}</p>
                                    <p class="text-xs font-mono text-gray-500">{{ $acc->asset->code ?? '' }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold {{ $acc->quantity > 0 ? 'text-gray-900' : 'text-red-600' }}">
                                    {{ $acc->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 font-mono">
                                    ₱{{ number_format($acc->unit_cost, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 font-mono">
                                    ₱{{ number_format($acc->total_value, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($acc->status === 'Available')
                                        <x-badge variant="success" size="sm">{{ $acc->status }}</x-badge>
                                    @else
                                        <x-badge variant="danger" size="sm">{{ $acc->status }}</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Adjust Quantity --}}
                                        <button 
                                            onclick="openAdjustModal({{ $acc->id }}, '{{ addslashes($acc->accessory_name) }}', {{ $acc->quantity }})"
                                            class="p-1.5 rounded hover:bg-gray-100 text-blue-600 hover:text-blue-800 transition-colors" 
                                            title="Restock / Consume Quantity"
                                        >
                                            <i class="fas fa-boxes-packing"></i>
                                        </button>
                                        {{-- Edit --}}
                                        <button 
                                            onclick="openEditAccessoryModal({{ json_encode($acc) }})"
                                            class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                            title="Edit Accessory"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {{-- Delete --}}
                                        <form action="{{ route('asset-accessories.destroy', $acc) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this accessory?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Accessory">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <x-empty-state icon="fas fa-tools" title="No accessories found" description="Click Add Accessory above to register auxiliary parts and components." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $accessories->links() }}
            </div>
        </x-card>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODALS: ATTACHMENTS                                                       --}}
    {{-- ========================================================================= --}}
    {{-- Add Attachment Modal --}}
    <x-modal id="add-attachment-modal" title="Add Asset Attachment">
        <form action="{{ route('attachments.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-form-group label="Attachment Name" required>
                <input type="text" name="name" placeholder="e.g., Heavy Duty Fire Hose (30m)" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Attachment Type">
                    <input type="text" name="type" placeholder="e.g., Hose, Bracket, Mount, Cable" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <x-form-group label="For Asset Type">
                    <select name="asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Optional Asset Type --</option>
                        @foreach($assetTypes as $at)
                            <option value="{{ $at->id }}">{{ $at->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>
            </div>

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

            <x-form-group label="Condition" required>
                <select name="condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="excellent">Excellent</option>
                    <option value="good" selected>Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                </select>
            </x-form-group>

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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Attachment Type">
                    <input type="text" name="type" id="edit_attach_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <x-form-group label="For Asset Type">
                    <select name="asset_type_id" id="edit_attach_asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Optional Asset Type --</option>
                        @foreach($assetTypes as $at)
                            <option value="{{ $at->id }}">{{ $at->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>
            </div>

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
                    <option value="excellent">Excellent</option>
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
    {{-- MODALS: ACCESSORIES                                                       --}}
    {{-- ========================================================================= --}}
    {{-- Add Accessory Modal --}}
    <x-modal id="add-accessory-modal" title="Add Accessory">
        <form class="space-y-4" action="{{ route('asset-accessories.store') }}" method="POST">
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

    @push('scripts')
        <script>
            // Attachment Modals
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
                document.getElementById('edit_attach_condition').value = attachment.condition || 'good';
                window.openModal('edit-attachment-modal');
            }

            // Accessory Modals
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
        </script>
    @endpush
@endsection
