@extends('layouts.admin')

@section('title', 'Operational Requirements & Supplies - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <i class="fas fa-list-check text-blue-600"></i>
                Operational Requirements & Supplies
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Operational Requirements & Supplies']
            ]" />
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($activeTab === 'needs')
                <button onclick="openAddNeedModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Equipment Need
                </button>
            @else
                <button onclick="openAddRequirementModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Requirement
                </button>
            @endif
        </div>
    </div>

    {{-- High-Level KPI Summary Cards --}}
    {{-- High-Level KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if($activeTab === 'needs')
            {{-- Total Needs --}}
            <a href="{{ route('operational-requirements.index', ['tab' => 'needs']) }}" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all flex items-center justify-between group block">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 group-hover:text-blue-600 transition-colors">Total Equipment Needs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['total_needs'] ?? $assetNeeds->total() }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fas fa-gas-pump"></i>
                </div>
            </a>

            {{-- Healthy Needs --}}
            <a href="{{ route('operational-requirements.index', ['tab' => 'needs', 'status' => 'Monitoring Only']) }}" class="bg-white rounded-xl p-5 border border-emerald-200 bg-emerald-50/20 shadow-sm hover:border-emerald-400 hover:shadow-md transition-all flex items-center justify-between group block" title="Filter by Monitoring Only">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Adequate (Monitoring)</p>
                    <p class="text-2xl font-bold text-emerald-700">{{ $metrics['needs_monitoring'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <i class="fas fa-circle-check"></i>
                </div>
            </a>

            {{-- Attention Needed --}}
            <a href="{{ route('operational-requirements.index', ['tab' => 'needs', 'status' => 'Requires Maintenance']) }}" class="bg-white rounded-xl p-5 border border-amber-200 bg-amber-50/20 shadow-sm hover:border-amber-400 hover:shadow-md transition-all flex items-center justify-between group block" title="Filter by Requires Maintenance">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-1">Requires Refill / Maint.</p>
                    <p class="text-2xl font-bold text-amber-700">{{ $metrics['needs_attention'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </a>

            {{-- Equipment Models --}}
            <a href="{{ route('operational-requirements.index', ['tab' => 'needs']) }}" class="bg-white rounded-xl p-5 border border-purple-200 bg-purple-50/20 shadow-sm hover:border-purple-400 hover:shadow-md transition-all flex items-center justify-between group block">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-700 mb-1">Equipment Models</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $needAssetTypes->count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xl group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </a>
        @else
            {{-- Total Requirements --}}
            <a href="{{ route('operational-requirements.index', ['tab' => 'requirements']) }}" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all flex items-center justify-between group block">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 group-hover:text-blue-600 transition-colors">Warehouse Supplies</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['total_requirements'] ?? $requirements->total() }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </a>

            {{-- Adequate Reserves --}}
            <a href="{{ route('operational-requirements.index', ['tab' => 'requirements', 'status' => 'adequate']) }}" class="bg-white rounded-xl p-5 border border-emerald-200 bg-emerald-50/20 shadow-sm hover:border-emerald-400 hover:shadow-md transition-all flex items-center justify-between group block" title="Filter by Adequate Reserves">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Adequate Reserves</p>
                    <p class="text-2xl font-bold text-emerald-700">{{ $metrics['adequate_stock'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <i class="fas fa-circle-check"></i>
                </div>
            </a>

            {{-- Low Stock Alert --}}
            <a href="{{ route('operational-requirements.index', ['tab' => 'requirements', 'status' => 'low']) }}" class="bg-white rounded-xl p-5 border border-rose-200 bg-rose-50/20 shadow-sm hover:border-rose-400 hover:shadow-md transition-all flex items-center justify-between group block" title="Filter by Low Stock Alert">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-rose-700 mb-1">Low Stock Alert</p>
                    <p class="text-2xl font-bold text-rose-700">{{ $metrics['low_stock'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-xl group-hover:bg-rose-600 group-hover:text-white transition-colors">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </a>

            {{-- Covered Asset Types --}}
            <a href="{{ route('operational-requirements.index', ['tab' => 'requirements']) }}" class="bg-white rounded-xl p-5 border border-indigo-200 bg-indigo-50/20 shadow-sm hover:border-indigo-400 hover:shadow-md transition-all flex items-center justify-between group block">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 mb-1">Equipment Types Covered</p>
                    <p class="text-2xl font-bold text-indigo-700">{{ $metrics['monitored_types'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <i class="fas fa-tags"></i>
                </div>
            </a>
        @endif
    </div>

    {{-- Module Navigation Tabs --}}
    <div class="flex border-b border-gray-200 mb-6 space-x-1">
        <a 
            href="{{ route('operational-requirements.index', ['tab' => 'requirements']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'requirements' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-boxes-stacked"></i>
            <span>Warehouse Consumables & Supplies</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'requirements' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $requirements->total() }}
            </span>
        </a>

        <a 
            href="{{ route('operational-requirements.index', ['tab' => 'needs']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'needs' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-gas-pump"></i>
            <span>Equipment Needs & Allocations</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'needs' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $assetNeeds->total() }}
            </span>
        </a>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: WAREHOUSE CONSUMABLES & OPERATIONAL REQUIREMENTS                   --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'requirements')
        {{-- Filters & Search --}}
        <form action="{{ route('operational-requirements.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="requirements">

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
                <a href="{{ route('operational-requirements.index', ['tab' => 'requirements']) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
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
                                $usingCount = $requirement->using_assets ? $requirement->using_assets->count() : 0;
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
                                        <form action="{{ route('operational-requirements.destroy', $requirement) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Supply: {{ addslashes($requirement->requirement_name) }}', 'This requirement will be removed from the warehouse supplies catalog.');" onclick="event.stopPropagation()">
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
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: EQUIPMENT NEEDS & ALLOCATIONS                                      --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'needs')
        {{-- Filters & Search --}}
        <form action="{{ route('operational-requirements.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="needs">

            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by requirement, asset name, or SKU code..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="asset_type" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Equipment Models</option>
                @foreach($needAssetTypes as $type)
                    <option value="{{ $type }}" {{ request('asset_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>

            <select name="unit" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Units</option>
                @foreach($units as $u)
                    <option value="{{ $u }}" {{ request('unit') === $u ? 'selected' : '' }}>{{ $u }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                Filter
            </button>

            @if(request()->hasAny(['search', 'asset_type', 'unit', 'status']))
                <a href="{{ route('operational-requirements.index', ['tab' => 'needs']) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    Reset
                </a>
            @endif
        </form>

        {{-- Equipment Needs Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Target Equipment</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Supply / Requirement</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($assetNeeds as $need)
                            <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer group" onclick="openAssetNeedModal(@js($need))">
                                {{-- Target Asset --}}
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
                                    <div class="space-y-1 max-w-[200px]">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-bold font-mono text-gray-900">{{ (float)$need->current_level }} {{ $need->unit }}</span>
                                            <span class="text-gray-400 text-[11px] font-mono">Max: {{ (float)$need->maximum_capacity }}</span>
                                        </div>
                                        @php
                                            $nMax = (float)$need->maximum_capacity;
                                            $nCur = (float)$need->current_level;
                                            $nPct = $nMax > 0 ? min(100, round(($nCur / $nMax) * 100)) : 0;
                                            $nIsCrit = $need->isCritical();
                                        @endphp
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $nIsCrit ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $nPct }}%"></div>
                                        </div>
                                        <span class="text-[10px] text-gray-400 block">Min Threshold: {{ (float)$need->minimum_level }} {{ $need->unit }}</span>
                                    </div>
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
                                        {{-- Replenish --}}
                                        <button 
                                            type="button"
                                            onclick="event.stopPropagation(); openReplenishModal({{ $need->id }}, @js($need->requirement), {{ (float)$need->current_level }}, {{ (float)$need->maximum_capacity }}, @js($need->unit))"
                                            class="p-1.5 rounded hover:bg-emerald-50 text-emerald-600 hover:text-emerald-800 transition-colors" 
                                            title="Replenish"
                                        >
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        {{-- Edit --}}
                                        <button 
                                            type="button"
                                            onclick="event.stopPropagation(); openEditNeedModal({{ json_encode($need) }})"
                                            class="p-1.5 rounded hover:bg-gray-100 transition-colors text-amber-600 hover:text-amber-800" 
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {{-- Delete --}}
                                        <form action="{{ route('asset-needs.destroy', $need) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Equipment Need: {{ addslashes($need->need_name) }} ({{ addslashes($need->assetType->name ?? 'Asset') }})', 'This will delete this equipment operational need specification.');" onclick="event.stopPropagation()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete" onclick="event.stopPropagation()">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <x-empty-state icon="fas fa-box-open" title="No equipment supply allocations found" description="Click Add Equipment Need above to record consumable requirements for individual equipment units." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $assetNeeds->links() }}
            </div>
        </x-card>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODALS: OPERATIONAL REQUIREMENTS (WAREHOUSE)                              --}}
    {{-- ========================================================================= --}}

    {{-- Requirement Details & Allocation Modal --}}
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

    {{-- ========================================================================= --}}
    {{-- MODALS: EQUIPMENT NEEDS & ALLOCATIONS                                     --}}
    {{-- ========================================================================= --}}

    {{-- Asset Need Detail Modal --}}
    <x-modal id="asset-need-modal" title="Equipment Supply Allocation" maxWidth="2xl">
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
                <button type="button" id="and_edit_btn" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-xl text-xs font-semibold border border-amber-200 transition-colors">
                    <i class="fas fa-edit text-[10px]"></i>
                    <span>Edit Need</span>
                </button>
                <button type="button" onclick="window.closeModal('asset-need-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Add Asset Need Modal --}}
    <x-modal id="add-asset-need-modal" title="Add Equipment Need">
        <form class="space-y-4" action="{{ route('asset-needs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <x-form-group label="Target Equipment" required>
                <select name="asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                    <option value="">Select Equipment...</option>
                    @foreach($assets as $a)
                        <option value="{{ $a->id }}" {{ old('asset_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->code }} — {{ $a->name }} ({{ $a->type->name ?? 'General' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Requirement Name" required>
                <input type="text" name="requirement" placeholder="e.g., Diesel Fuel, Battery Pack" value="{{ old('requirement') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <x-form-group label="Unit of Measurement" required>
                <select name="unit" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                    <option value="">Select Unit...</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}" {{ old('unit') == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Current Level" required>
                    <input type="number" step="0.01" min="0" id="need_add_current" name="current_level" value="{{ old('current_level', 0) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
                <x-form-group label="Maximum Capacity" required>
                    <input type="number" step="0.01" min="0.01" id="need_add_max" name="maximum_capacity" value="{{ old('maximum_capacity') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
                <x-form-group label="Minimum Level" required>
                    <input type="number" step="0.01" min="0" id="need_add_min" name="minimum_level" value="{{ old('minimum_level', 0) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
            </div>

            <x-receipt-input label="Specification Sheet or Receipt (Optional)" help="Upload document proof (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Create Need</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-asset-need-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Asset Need Modal --}}
    <x-modal id="edit-asset-need-modal" title="Edit Equipment Need">
        <form class="space-y-4" id="edit-need-form" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Target Equipment" required>
                <select name="asset_id" id="edit_need_asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                    @foreach($assets as $a)
                        <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Requirement Name" required>
                <input type="text" name="requirement" id="edit_need_requirement" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <x-form-group label="Unit of Measurement" required>
                <select name="unit" id="edit_need_unit" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                    @foreach($units as $u)
                        <option value="{{ $u }}">{{ $u }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Current Level" required>
                    <input type="number" step="0.01" min="0" id="edit_need_current_level" name="current_level" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
                <x-form-group label="Maximum Capacity" required>
                    <input type="number" step="0.01" min="0.01" id="edit_need_maximum_capacity" name="maximum_capacity" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
                <x-form-group label="Minimum Level" required>
                    <input type="number" step="0.01" min="0" id="edit_need_minimum_level" name="minimum_level" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
            </div>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-asset-need-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Replenish Asset Need Modal --}}
    <x-modal id="replenish-need-modal" title="Replenish Equipment Supply">
        <form class="space-y-4" id="replenish-need-form" method="POST">
            @csrf

            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-900">
                <span class="font-semibold" id="replenish_need_name">Requirement</span>
                <span class="text-xs text-blue-700 block mt-0.5">Current Level: <strong id="replenish_need_current">0</strong> / <span id="replenish_need_max">0</span> <span id="replenish_need_unit">units</span> (Remaining: <span id="replenish_need_remaining">0</span>)</span>
            </div>

            <x-form-group label="Amount to Replenish" required>
                <input type="number" step="0.01" min="0.01" name="amount" id="replenish_need_amount" placeholder="Enter amount..." required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    <i class="fas fa-fill-drip mr-2"></i> Replenish
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('replenish-need-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            // Requirement Handlers
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

                const restockBtn = document.getElementById('dtl_req_restock_btn');
                restockBtn.onclick = function() {
                    window.closeModal('requirement-details-modal');
                    openAddQuantityModal(req);
                };

                const registryLink = document.getElementById('dtl_req_registry_link');
                if (req.asset_type_id) {
                    registryLink.href = `/assets?type_id=${req.asset_type_id}&tab=assets`;
                    registryLink.classList.remove('hidden');
                } else {
                    registryLink.classList.add('hidden');
                }

                window.openModal('requirement-details-modal');
            }

            // Asset Need Handlers
            function openAddNeedModal() {
                window.openModal('add-asset-need-modal');
            }

            function openEditNeedModal(need) {
                document.getElementById('edit-need-form').action = '/asset-needs/' + need.id;
                document.getElementById('edit_need_asset_id').value = need.asset_id;
                document.getElementById('edit_need_requirement').value = need.requirement;
                document.getElementById('edit_need_unit').value = need.unit;
                document.getElementById('edit_need_maximum_capacity').value = need.maximum_capacity;
                document.getElementById('edit_need_minimum_level').value = need.minimum_level;
                document.getElementById('edit_need_current_level').value = need.current_level;
                window.openModal('edit-asset-need-modal');
            }

            function openReplenishModal(id, requirement, current, max, unit) {
                const remaining = Math.max(0, Math.round((max - current) * 100) / 100);
                document.getElementById('replenish-need-form').action = '/asset-needs/' + id + '/replenish';
                document.getElementById('replenish_need_name').textContent = requirement;
                document.getElementById('replenish_need_current').textContent = current;
                document.getElementById('replenish_need_max').textContent = max;
                document.getElementById('replenish_need_unit').textContent = unit;
                document.getElementById('replenish_need_remaining').textContent = `${remaining} ${unit}`;
                document.getElementById('replenish_need_amount').value = '';
                document.getElementById('replenish_need_amount').max = remaining;
                window.openModal('replenish-need-modal');
            }

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

                const editBtn = document.getElementById('and_edit_btn');
                editBtn.onclick = function() {
                    window.closeModal('asset-need-modal');
                    openEditNeedModal(need);
                };

                window.openModal('asset-need-modal');
            }
        </script>
    @endpush
@endsection
