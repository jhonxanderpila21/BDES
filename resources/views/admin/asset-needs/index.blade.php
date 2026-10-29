@extends('layouts.admin')

@section('title', 'Asset Needs - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Needs</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Asset Needs']
            ]" />
        </div>
        <button onclick="openAddModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Asset Need
        </button>
    </div>


    {{-- Module Navigation Tabs (Connecting Operational Requirements & Asset Needs) --}}
    <div class="flex border-b border-gray-200 mb-6 space-x-1">
        <a 
            href="{{ route('operational-requirements.index') }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors"
        >
            <i class="fas fa-list-check"></i>
            <span>Operational Consumables (Warehouse Stock)</span>
        </a>

        <a 
            href="{{ route('asset-needs.index') }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg transition-colors"
        >
            <i class="fas fa-gas-pump"></i>
            <span>Equipment Supply Needs (Asset Allocations)</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">
                {{ $assetNeeds->total() }}
            </span>
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('asset-needs.index') }}" method="GET" id="searchForm">
                {{-- Preserve existing filter params --}}
                @if(request('asset_type'))<input type="hidden" name="asset_type" value="{{ request('asset_type') }}">@endif
                @if(request('unit'))<input type="hidden" name="unit" value="{{ request('unit') }}">@endif
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by requirement, asset type, or asset name..." />
            </form>
        </div>

        {{-- Asset Type Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('asset_type') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                {{ request('asset_type') ?: 'Asset Type' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('asset-needs.index', array_merge(request()->except('asset_type', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('asset_type') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Types</a>
                @foreach($assetTypes as $type)
                    <a href="{{ route('asset-needs.index', array_merge(request()->except('page'), ['asset_type' => $type])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('asset_type') === $type ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $type }}</a>
                @endforeach
            </div>
        </div>

        {{-- Unit Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('unit') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                {{ request('unit') ?: 'Unit' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('asset-needs.index', array_merge(request()->except('unit', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('unit') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Units</a>
                @foreach($units as $u)
                    <a href="{{ route('asset-needs.index', array_merge(request()->except('page'), ['unit' => $u])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('unit') === $u ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $u }}</a>
                @endforeach
            </div>
        </div>

        {{-- Status Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('status') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                {{ request('status') ?: 'Status' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('asset-needs.index', array_merge(request()->except('status', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('status') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Statuses</a>
                @foreach($statuses as $s)
                    <a href="{{ route('asset-needs.index', array_merge(request()->except('page'), ['status' => $s])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === $s ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $s }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Asset Needs Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Target Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Supply / Requirement</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($assetNeeds as $need)
                        <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer group" onclick="openAssetNeedModal(@js($need))">
                            {{-- Asset & Type --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $need->asset->name ?? 'Unknown Asset' }}</p>
                                <div class="flex items-center gap-1.5 mt-0.5 text-xs text-gray-500">
                                    <span class="font-mono text-gray-600 bg-gray-100 px-1 py-0.5 rounded">{{ $need->asset->code ?? '' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span class="text-blue-600 font-medium">{{ $need->asset_type }}</span>
                                </div>
                            </td>
                            {{-- Requirement --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-oil-can text-blue-500 text-xs"></i>
                                    <span>{{ $need->requirement }}</span>
                                </div>
                            </td>
                            {{-- Current Stock --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="font-bold font-mono text-gray-900">{{ (float)$need->current_level }}</span>
                                <span class="text-xs text-gray-500 font-medium ml-0.5">{{ $need->unit }}</span>
                                <span class="text-xs text-gray-400 block mt-0.5">Capacity: {{ (float)$need->maximum_capacity }} {{ $need->unit }} (Min: {{ (float)$need->minimum_level }})</span>
                            </td>
                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($need->status === 'Monitoring Only')
                                    <x-badge variant="success" size="sm">{{ $need->status }}</x-badge>
                                @elseif($need->status === 'Requires Maintenance')
                                    <x-badge variant="warning" size="sm">{{ $need->status }}</x-badge>
                                @else
                                    <x-badge variant="danger" size="sm">{{ $need->status }}</x-badge>
                                @endif
                            </td>
                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                    {{-- View Details --}}
                                    <button 
                                        type="button"
                                        onclick="event.stopPropagation(); openAssetNeedModal(@js($need))"
                                        class="p-1.5 rounded hover:bg-blue-100 text-blue-600 hover:text-blue-800 transition-colors"
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    {{-- Edit --}}
                                    <button 
                                        type="button"
                                        onclick="event.stopPropagation(); openEditModal({{ json_encode($need) }})"
                                        class="p-1.5 rounded hover:bg-gray-100 transition-colors text-yellow-600" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {{-- Delete --}}
                                    <form action="{{ route('asset-needs.destroy', $need) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this asset need?');" onclick="event.stopPropagation()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded hover:bg-gray-100 transition-colors text-red-600" title="Delete" onclick="event.stopPropagation()">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8">
                                <x-empty-state icon="fas fa-box-open" title="No asset needs found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $assetNeeds->links() }}
        </div>
    </x-card>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Add Asset Need Modal                           --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <x-modal id="add-asset-need-modal" title="Add Asset Need">
        <form class="space-y-4" action="{{ route('asset-needs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <x-form-group label="Asset" required>
                <select name="asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Requirement" required>
                <input type="text" name="requirement" placeholder="e.g., Diesel Fuel, Battery Pack" value="{{ old('requirement') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Unit" required>
                <select name="unit" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}" {{ old('unit') == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Maximum Capacity" required>
                    <input type="number" step="0.01" min="0.01" name="maximum_capacity" value="{{ old('maximum_capacity') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
                <x-form-group label="Minimum Level" required>
                    <input type="number" step="0.01" min="0" name="minimum_level" value="{{ old('minimum_level', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
                <x-form-group label="Current Level" required>
                    <input type="number" step="0.01" min="0" name="current_level" value="{{ old('current_level', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <x-receipt-input label="Specification Sheet, Quotation, or Initial Receipt (Optional)" />

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Create Asset Need</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['add-asset-need-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Edit Asset Need Modal                          --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <x-modal id="edit-asset-need-modal" title="Edit Asset Need">
        <form class="space-y-4" id="editForm" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Asset" required>
                <select name="asset_id" id="edit_asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Requirement" required>
                <input type="text" name="requirement" id="edit_requirement" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Unit" required>
                <select name="unit" id="edit_unit" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach($units as $u)
                        <option value="{{ $u }}">{{ $u }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Maximum Capacity" required>
                    <input type="number" step="0.01" min="0.01" name="maximum_capacity" id="edit_maximum_capacity" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
                <x-form-group label="Minimum Level" required>
                    <input type="number" step="0.01" min="0" name="minimum_level" id="edit_minimum_level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
                <x-form-group label="Current Level" required>
                    <input type="number" step="0.01" min="0" name="current_level" id="edit_current_level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Update Asset Need</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['edit-asset-need-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Replenish Modal                                --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <x-modal id="replenish-modal" title="Replenish Stock">
        <form class="space-y-4" id="replenishForm" method="POST">
            @csrf

            <div class="bg-blue-50 rounded-lg p-4 mb-2">
                <p class="text-sm text-blue-800">
                    <strong>Requirement:</strong> <span id="replenish_requirement"></span>
                </p>
                <p class="text-sm text-blue-800 mt-1">
                    <strong>Current:</strong> <span id="replenish_current"></span> / <span id="replenish_max"></span> <span id="replenish_unit"></span>
                </p>
                <p class="text-sm text-blue-800 mt-1">
                    <strong>Remaining capacity:</strong> <span id="replenish_remaining"></span> <span id="replenish_unit2"></span>
                </p>
            </div>

            <x-form-group label="Amount to Replenish" required>
                <input type="number" step="0.01" min="0.01" name="amount" id="replenish_amount" placeholder="Enter amount..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    <i class="fas fa-fill-drip mr-2"></i> Replenish
                </x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['replenish-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Asset Need Detail Modal --}}
    <x-modal id="asset-need-modal" title="Equipment Supply Requirement" maxWidth="2xl">
        <div class="space-y-5">
            {{-- Header card --}}
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 mb-1">
                        <i class="fas fa-gas-pump text-[10px]"></i>
                        <span id="and_type">Consumable</span>
                    </span>
                    <h3 id="and_requirement" class="text-lg font-bold text-gray-900">Diesel Fuel</h3>
                </div>
                <div id="and_status_badge"></div>
            </div>

            {{-- Target Asset Card --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600 flex items-center gap-2">
                    <i class="fas fa-cube text-blue-600"></i>
                    <span>Target Equipment Assignment</span>
                </h4>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                            <i class="fas fa-box"></i>
                        </span>
                        <div>
                            <span id="and_asset_code" class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">CODE</span>
                            <span id="and_asset_name" class="font-semibold text-gray-900 text-sm ml-1">Asset Name</span>
                        </div>
                    </div>
                    <a id="and_asset_link" href="#" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold border border-blue-200 transition-colors">
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                        <span>View Asset Dossier</span>
                    </a>
                </div>
            </div>

            {{-- Stock gauges --}}
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Current Level</span>
                    <span id="and_current" class="text-base font-bold text-gray-900">0</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Min Threshold</span>
                    <span id="and_min" class="text-base font-bold text-rose-600">0</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Max Capacity</span>
                    <span id="and_max" class="text-base font-bold text-blue-600">0</span>
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                <a href="{{ route('operational-requirements.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl text-xs font-semibold border border-blue-200 transition-colors">
                    <i class="fas fa-list-check text-[10px]"></i>
                    <span>Warehouse Supplies Hub</span>
                </a>
                <button type="button" onclick="window.closeModal('asset-need-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    @push('scripts')
        <script>
            function openAssetNeedModal(need) {
                document.getElementById('and_requirement').textContent = need.requirement;
                document.getElementById('and_type').textContent = need.asset_type || 'Consumable Supply';
                
                const current = parseFloat(need.current_level || 0);
                const max = parseFloat(need.maximum_capacity || 0);
                const min = parseFloat(need.minimum_level || 0);
                const unit = need.unit || 'units';

                document.getElementById('and_current').textContent = `${current} ${unit}`;
                document.getElementById('and_min').textContent = `${min} ${unit}`;
                document.getElementById('and_max').textContent = `${max} ${unit}`;

                if (need.status === 'Monitoring Only') {
                    document.getElementById('and_status_badge').innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Monitoring Only (Adequate)</span>';
                } else if (need.status === 'Requires Maintenance') {
                    document.getElementById('and_status_badge').innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Requires Maintenance</span>';
                } else {
                    document.getElementById('and_status_badge').innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">Critical / Low</span>';
                }

                if (need.asset) {
                    document.getElementById('and_asset_code').textContent = need.asset.code;
                    document.getElementById('and_asset_name').textContent = need.asset.name;
                    document.getElementById('and_asset_link').href = `/assets/${need.asset.id}`;
                    document.getElementById('and_asset_link').classList.remove('hidden');
                } else {
                    document.getElementById('and_asset_code').textContent = '—';
                    document.getElementById('and_asset_name').textContent = 'Unknown Equipment';
                    document.getElementById('and_asset_link').classList.add('hidden');
                }

                window.openModal('asset-need-modal');
            }
            function openAddModal() {
                window.openModal('add-asset-need-modal');
            }

            function openEditModal(need) {
                document.getElementById('editForm').action = '/asset-needs/' + need.id;
                document.getElementById('edit_asset_id').value = need.asset_id;
                document.getElementById('edit_requirement').value = need.requirement;
                document.getElementById('edit_unit').value = need.unit;
                document.getElementById('edit_maximum_capacity').value = need.maximum_capacity;
                document.getElementById('edit_minimum_level').value = need.minimum_level;
                document.getElementById('edit_current_level').value = need.current_level;
                window.openModal('edit-asset-need-modal');
            }

            function openReplenishModal(id, requirement, current, max, unit) {
                var remaining = Math.round((max - current) * 100) / 100;
                document.getElementById('replenishForm').action = '/asset-needs/' + id + '/replenish';
                document.getElementById('replenish_requirement').textContent = requirement;
                document.getElementById('replenish_current').textContent = current;
                document.getElementById('replenish_max').textContent = max;
                document.getElementById('replenish_unit').textContent = unit;
                document.getElementById('replenish_unit2').textContent = unit;
                document.getElementById('replenish_remaining').textContent = remaining;
                document.getElementById('replenish_amount').value = '';
                document.getElementById('replenish_amount').max = remaining;
                window.openModal('replenish-modal');
            }
        </script>
    @endpush
@endsection
