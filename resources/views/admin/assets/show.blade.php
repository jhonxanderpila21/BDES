@extends('layouts.admin')

@section('title', 'Asset Dossier: ' . $asset->name . ' (' . $asset->code . ') - BDES')

@section('content')
<div x-data="{ activeTab: 'overview', showUploadModal: false, showRetireModal: false }" class="space-y-6">

    {{-- Top Header & Quick Action Bar --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm print:shadow-none print:border-none">
        <div class="flex items-start gap-4">
            <a href="{{ route('assets.index', ['tab' => 'assets']) }}" class="mt-1 p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors print:hidden" title="Back to Registry">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $asset->name }}</h1>
                    <span class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1 rounded-lg">
                        {{ $asset->code }}
                    </span>
                    @php
                        $statusStyles = match($asset->status) {
                            'available'         => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'deployed'          => 'bg-blue-100 text-blue-800 border-blue-300',
                            'under_maintenance' => 'bg-amber-100 text-amber-800 border-amber-300',
                            'out_of_service'    => 'bg-rose-100 text-rose-800 border-rose-300',
                            'lost'              => 'bg-purple-100 text-purple-800 border-purple-300',
                            'retired'           => 'bg-gray-200 text-gray-800 border-gray-400',
                            default             => 'bg-gray-100 text-gray-700 border-gray-300',
                        };
                    @endphp
                    <span class="px-3 py-1 text-xs font-bold uppercase rounded-full border {{ $statusStyles }}">
                        @if($asset->status === 'available')
                            <i class="fas fa-check-circle mr-1 text-[11px]"></i> Available (Ready)
                        @else
                            {{ \App\Services\AssetLifecycleService::statuses()[$asset->status] ?? ucfirst(str_replace('_', ' ', $asset->status)) }}
                        @endif
                    </span>
                </div>
                <x-breadcrumb :items="[
                    ['label' => 'Dashboard', 'url' => route('dashboard')],
                    ['label' => 'Asset Registry', 'url' => route('assets.index')],
                    ['label' => $asset->name]
                ]" />
            </div>
        </div>

        {{-- Operational Quick Actions --}}
        <div class="flex flex-wrap items-center gap-2 print:hidden">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium text-sm transition-colors">
                <i class="fas fa-print"></i>
                <span class="hidden sm:inline">Print Dossier</span>
            </button>

            <button @click="showUploadModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl font-medium text-sm transition-colors">
                <i class="fas fa-file-upload"></i>
                <span>Add Document</span>
            </button>

            @if($asset->status === 'available')
                <a href="{{ route('deployment.create', ['asset_id' => $asset->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm shadow-sm transition-colors">
                    <i class="fas fa-truck-fast"></i>
                    <span>Deploy Asset</span>
                </a>
            @endif

            @if($asset->status !== 'retired' && auth()->user()?->isAdmin())
                <button @click="showRetireModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl font-medium text-sm transition-colors" title="Decommission / Retire">
                    <i class="fas fa-ban"></i>
                    <span>Retire</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Active Deployment or Retirement Notice Banners --}}
    @if($asset->status === 'deployed' && isset($stats['active_deployment']) && $stats['active_deployment'])
        @php $activeDep = $stats['active_deployment']; @endphp
        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-truck-ramp-box"></i>
                </div>
                <div>
                    <h4 class="font-bold text-blue-900 text-sm">Asset Currently Deployed in the Field</h4>
                    <p class="text-xs text-blue-700 mt-0.5">
                        Assigned to: <strong class="text-blue-950">{{ $activeDep->responsible_person ?? $activeDep->user?->name ?? 'Disaster Team' }}</strong> |
                        Destination: <strong class="text-blue-950">{{ $activeDep->location }}</strong> |
                        Deployed: {{ $activeDep->deployment_date?->format('M d, Y') }} |
                        Expected Return: {{ $activeDep->expected_return_date?->format('M d, Y') ?? 'Not specified' }}
                    </p>
                </div>
            </div>
            <form action="{{ route('deployment.return', $activeDep->id) }}" method="POST" class="print:hidden">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg shadow-sm whitespace-nowrap">
                    <i class="fas fa-arrow-rotate-left mr-1"></i> Process Return
                </button>
            </form>
        </div>
    @elseif($asset->status === 'retired')
        <div class="bg-gray-100 border-l-4 border-gray-600 p-4 rounded-xl flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-gray-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-box-archive"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 text-sm">Asset Formally Decommissioned / Retired</h4>
                <p class="text-xs text-gray-700 mt-0.5">
                    Retirement Date: <strong>{{ $asset->retirement_date ? $asset->retirement_date->format('M d, Y') : 'N/A' }}</strong> |
                    Authorized By: <strong>{{ $asset->retiredBy?->name ?? 'Authorized Official' }}</strong>
                </p>
                @if($asset->retirement_reason)
                    <p class="text-xs text-gray-600 italic mt-1">"{{ $asset->retirement_reason }}"</p>
                @endif
            </div>
        </div>
    @endif

    {{-- Key Facts Summary Cards --}}
    @php
        $condBadge = match($asset->condition) {
            'excellent', 'good' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
            'fair'              => 'text-amber-700 bg-amber-50 border border-amber-200',
            'poor'              => 'text-rose-700 bg-rose-50 border border-rose-200',
            default             => 'text-gray-700 bg-gray-50 border border-gray-200',
        };
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Status & Condition --}}
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Condition & Status</span>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase {{ $condBadge }}">
                        {{ ucfirst($asset->condition) }}
                    </span>
                    <span class="text-xs text-gray-600 font-medium">
                        @if($asset->status === 'available')
                            Available (Ready)
                        @else
                            {{ \App\Services\AssetLifecycleService::statuses()[$asset->status] ?? ucfirst(str_replace('_', ' ', $asset->status)) }}
                        @endif
                    </span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fas fa-heart-pulse"></i>
            </div>
        </div>

        {{-- Primary Station / Location --}}
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Assigned Location</span>
                <p class="text-sm font-bold text-gray-900 mt-1 truncate max-w-[170px]" title="{{ $asset->location->name ?? 'Not assigned' }}">
                    {{ $asset->location->name ?? 'Not assigned' }}
                </p>
                <p class="text-[11px] text-gray-400">{{ $asset->category->name ?? 'Equipment' }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fas fa-location-dot"></i>
            </div>
        </div>

        {{-- Acquisition Cost --}}
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Acquisition Cost</span>
                <p class="text-base font-bold text-gray-900 mt-1">
                    ₱{{ number_format($asset->acquisition_cost ?? 0, 2) }}
                </p>
                <p class="text-[11px] text-gray-400">
                    {{ $asset->purchase_date ? $asset->purchase_date->format('M d, Y') : 'No purchase date' }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                <i class="fas fa-receipt"></i>
            </div>
        </div>

        {{-- Activity Records --}}
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Operational History</span>
                <p class="text-sm font-bold text-gray-900 mt-1">
                    {{ $stats['total_deployments'] }} <span class="text-xs font-normal text-gray-500">Missions</span> · {{ $stats['total_maintenances'] }} <span class="text-xs font-normal text-gray-500">Repairs</span>
                </p>
                <p class="text-[11px] text-gray-400">
                    ₱{{ number_format($stats['total_maintenance_cost'], 2) }} upkeep
                </p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
    </div>

    {{-- Dossier Tab Navigation (Streamlined to 4 Focused Tabs) --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-4 bg-gray-50/50 flex flex-wrap gap-1 print:hidden">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-circle-info"></i>
                <span>Equipment Overview</span>
            </button>
            <button @click="activeTab = 'deployments'" :class="activeTab === 'deployments' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-truck"></i>
                <span>Deployments</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-600">{{ $stats['total_deployments'] }}</span>
            </button>
            <button @click="activeTab = 'maintenance'" :class="activeTab === 'maintenance' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-screwdriver-wrench"></i>
                <span>Maintenance & Repairs</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-600">{{ $stats['total_maintenances'] }}</span>
            </button>
            <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-timeline"></i>
                <span>Audit & History</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-600">{{ $asset->auditLogs->count() }}</span>
            </button>
        </div>

        <div class="p-6">
            {{-- TAB 1: EQUIPMENT OVERVIEW & ESSENTIAL DETAILS --}}
            <div x-show="activeTab === 'overview'" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Essential Asset Profile --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-id-card text-blue-600"></i> Essential Specifications
                            </h3>
                            <span class="text-xs text-gray-400">Registered: {{ $asset->created_at->format('M d, Y') }}</span>
                        </div>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase">Category & Type</dt>
                                <dd class="text-gray-900 font-bold mt-0.5 flex items-center gap-2">
                                    <span>{{ $asset->category->name ?? 'Uncategorized' }}</span>
                                    @if($asset->type)
                                        <span class="text-xs text-gray-500 font-medium">({{ $asset->type->name }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase">Brand / Manufacturer</dt>
                                <dd class="text-gray-900 font-bold mt-0.5">{{ $asset->manufacturer ?: 'Standard Specification' }}</dd>
                            </div>
                            <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase">Serial Number</dt>
                                <dd class="font-mono text-gray-900 font-bold mt-0.5">{{ $asset->serial_number ?: 'Unserialized' }}</dd>
                            </div>
                            <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase">Assigned Location</dt>
                                <dd class="text-gray-900 font-bold mt-0.5 flex items-center gap-1.5">
                                    <i class="fas fa-location-dot text-blue-600 text-xs"></i>
                                    {{ $asset->location->name ?? 'Not assigned' }}
                                </dd>
                            </div>
                            <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase">Purchase Cost & Date</dt>
                                <dd class="text-gray-900 font-bold mt-0.5">
                                    <span class="text-emerald-700">₱{{ number_format($asset->acquisition_cost ?? 0, 2) }}</span>
                                    @if($asset->purchase_date)
                                        <span class="text-xs text-gray-500 font-normal ml-1">({{ $asset->purchase_date->format('M d, Y') }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase">Warranty Status</dt>
                                <dd class="text-gray-900 font-medium mt-0.5 text-xs">
                                    @if($asset->warranty_expiry)
                                        @if($asset->warranty_expiry->isPast())
                                            <span class="text-rose-600 font-semibold"><i class="fas fa-circle-xmark mr-1"></i> Expired ({{ $asset->warranty_expiry->format('M d, Y') }})</span>
                                        @else
                                            <span class="text-emerald-700 font-semibold"><i class="fas fa-circle-check mr-1"></i> Valid until {{ $asset->warranty_expiry->format('M d, Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">No warranty on record</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @if($asset->notes)
                            <div class="bg-blue-50/40 border border-blue-100 p-3.5 rounded-xl">
                                <h4 class="text-xs font-bold text-blue-900 uppercase mb-1">Operational Notes</h4>
                                <p class="text-xs text-gray-700 leading-relaxed">{{ $asset->notes }}</p>
                            </div>
                        @endif

                        {{-- Official Receipts & Uploaded Documents --}}
                        <div class="bg-white rounded-xl p-4 border border-gray-200 mt-4">
                            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Attached Documents & Proof of Purchase</h4>
                            @if($asset->receipts && $asset->receipts->count() > 0)
                                <x-receipt-list :receipts="$asset->receipts" />
                            @else
                                <p class="text-xs text-gray-400 italic">No receipts or invoices attached.</p>
                            @endif
                        </div>

                        {{-- Operational Requirements & Consumables (Merged with Assets Using It) --}}
                        @if(($operationalRequirements && $operationalRequirements->count() > 0) || ($asset->assetNeeds && $asset->assetNeeds->count() > 0))
                            <div class="bg-white rounded-xl p-4 border border-gray-200 mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fas fa-gas-pump text-blue-600"></i>
                                        <span>Operational Requirements & Consumables</span>
                                    </h4>
                                    <a href="{{ route('operational-requirements.index') }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                                        Warehouse Inventory &rarr;
                                    </a>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($operationalRequirements as $req)
                                        @php
                                            $current = (float)$req->current_level;
                                            $max = (float)$req->maximum_capacity;
                                            $pct = $max > 0 ? min(100, round(($current / $max) * 100)) : 0;
                                            $isLow = $req->isLowStock();
                                            $usingAssetsCount = $req->using_assets ? $req->using_assets->count() : 0;
                                        @endphp
                                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50/20 transition-all cursor-pointer group" onclick="openAssetRequirementModal(@js($req))">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-xs font-bold text-gray-900 group-hover:text-blue-600 transition-colors flex items-center gap-1.5">
                                                    <i class="fas fa-oil-can text-blue-500 text-[10px]"></i>
                                                    {{ $req->requirement_type }}
                                                </span>
                                                @if($isLow)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Low Stock</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">In Stock</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center justify-between text-[11px] text-gray-500 font-mono mb-1">
                                                <span>{{ $current }} / {{ $max }} {{ $req->unit }}</span>
                                                <span>{{ $pct }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden mb-2">
                                                <div class="h-1.5 rounded-full {{ $isLow ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <div class="flex items-center justify-between text-[10px] text-blue-600 pt-1 border-t border-gray-200/60 font-medium">
                                                <span><i class="fas fa-boxes-stacked mr-1"></i> {{ $usingAssetsCount }} assets using this</span>
                                                <span class="group-hover:translate-x-0.5 transition-transform">Click to view &rarr;</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Identification & Tag Card --}}
                    <div class="bg-gray-50/70 border border-gray-200 rounded-2xl p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Asset Tag & Barcode</span>
                                <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                                    {{ $asset->code }}
                                </span>
                            </div>

                            <div class="bg-white p-4 rounded-xl border border-gray-200 text-center shadow-sm my-2">
                                <i class="fas fa-barcode text-4xl text-gray-800 mb-2 block"></i>
                                <p class="font-mono text-xs font-bold text-gray-900 tracking-wider">{{ $asset->code }}</p>
                                <p class="text-[10px] text-gray-400 mt-1">Barangay Disaster Equipment Identifier</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200 space-y-2">
                            <button onclick="window.print()" class="w-full py-2 px-3 bg-white border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors inline-flex items-center justify-center gap-2">
                                <i class="fas fa-print"></i> Print Asset Tag Label
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: FIELD DEPLOYMENTS & MISSIONS --}}
            <div x-show="activeTab === 'deployments'" class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-truck text-blue-600"></i> Field Dispatch & Mission Records
                    </h3>
                    @if($asset->status === 'available')
                        <a href="{{ route('deployment.create', ['asset_id' => $asset->id]) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors">
                            <i class="fas fa-plus mr-1"></i> New Deployment
                        </a>
                    @endif
                </div>

                @if($asset->deployments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left">Dispatch Date</th>
                                    <th class="px-4 py-3 text-left">Destination / Operation</th>
                                    <th class="px-4 py-3 text-left">Borrower / Custodian</th>
                                    <th class="px-4 py-3 text-center">Condition Out</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Return Condition</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($asset->deployments->sortByDesc('deployment_date') as $dep)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 align-middle font-medium whitespace-nowrap text-left">
                                            {{ $dep->deployment_date ? $dep->deployment_date->format('M d, Y') : 'N/A' }}
                                            <span class="text-xs text-gray-400 block">Due: {{ $dep->expected_return_date ? $dep->expected_return_date->format('M d, Y') : 'Open' }}</span>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-left">
                                            <p class="font-bold text-gray-900">{{ $dep->location }}</p>
                                            <p class="text-xs text-gray-500">{{ $dep->purpose }}</p>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-left">
                                            <p class="text-gray-900 font-medium">{{ $dep->responsible_person ?? $dep->user?->name ?? 'Disaster Team' }}</p>
                                            <p class="text-xs text-gray-400">Approved: {{ $dep->approvedByUser?->name ?? 'Official' }}</p>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                            <span class="capitalize text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-800">
                                                {{ $dep->condition_before ?? $asset->condition }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                            @if($dep->status === 'deployed')
                                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                                    In Field
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                    Returned
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                            @if($dep->returnRecord)
                                                <span class="capitalize text-xs font-bold px-2 py-0.5 rounded {{ $dep->returnRecord->condition === 'poor' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-50 text-emerald-800' }}">
                                                    {{ $dep->returnRecord->condition }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">Pending Return</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-500 bg-gray-50 rounded-xl">
                        <i class="fas fa-truck-ramp-box text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm font-medium">No deployment records found for this asset.</p>
                    </div>
                @endif
            </div>

            {{-- TAB 3: WORK ORDERS, REPAIRS & INSPECTIONS --}}
            <div x-show="activeTab === 'maintenance'" class="space-y-6">
                {{-- Work Orders --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-screwdriver-wrench text-amber-600"></i> Service & Work Orders
                        </h3>
                        <a href="{{ route('maintenance.create', ['asset_id' => $asset->id]) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors">
                            <i class="fas fa-plus mr-1"></i> New Work Order
                        </a>
                    </div>

                    @if($asset->maintenance->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Work Order #</th>
                                        <th class="px-4 py-3 text-left">Task & Description</th>
                                        <th class="px-4 py-3 text-center">Priority</th>
                                        <th class="px-4 py-3 text-left">Technician</th>
                                        <th class="px-4 py-3 text-right">Cost</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($asset->maintenance->sortByDesc('start_date') as $m)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 align-middle font-mono font-bold text-blue-700 whitespace-nowrap text-left">
                                                {{ $m->work_order_number ?? ('WO-' . str_pad($m->id, 5, '0', STR_PAD_LEFT)) }}
                                            </td>
                                            <td class="px-4 py-3 align-middle text-left">
                                                <p class="font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $m->type) }}</p>
                                                <p class="text-xs text-gray-600 line-clamp-1">{{ $m->description }}</p>
                                            </td>
                                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                                @php
                                                    $priColor = match($m->priority ?? 'medium') {
                                                        'urgent' => 'bg-rose-100 text-rose-800',
                                                        'high'   => 'bg-amber-100 text-amber-800',
                                                        'low'    => 'bg-blue-100 text-blue-800',
                                                        default  => 'bg-gray-100 text-gray-800',
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 text-xs font-bold uppercase rounded {{ $priColor }}">
                                                    {{ $m->priority ?? 'medium' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 align-middle whitespace-nowrap text-gray-700 text-left">
                                                {{ $m->assignedUser?->name ?? 'Unassigned' }}
                                            </td>
                                            <td class="px-4 py-3 align-middle font-bold text-gray-900 whitespace-nowrap text-right">
                                                ₱{{ number_format($m->cost ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-3 align-middle whitespace-nowrap text-center">
                                                @php
                                                    $mStatus = match($m->status) {
                                                        'completed'   => 'bg-emerald-100 text-emerald-800',
                                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                                        'cancelled'   => 'bg-gray-200 text-gray-700',
                                                        default       => 'bg-amber-100 text-amber-800',
                                                    };
                                                @endphp
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $mStatus }}">
                                                    {{ ucfirst(str_replace('_', ' ', $m->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic py-3">No maintenance work orders logged for this equipment.</p>
                    @endif
                </div>

                {{-- Safety Inspections --}}
                <div class="space-y-3 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-shield-check text-emerald-600"></i> Safety Inspections
                        </h3>
                        <a href="{{ route('inspection.create', ['asset_id' => $asset->id]) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors">
                            <i class="fas fa-plus mr-1"></i> Log Inspection
                        </a>
                    </div>

                    @if($asset->inspections->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Inspection Date</th>
                                        <th class="px-4 py-3 text-left">Inspector</th>
                                        <th class="px-4 py-3 text-center">Outcome</th>
                                        <th class="px-4 py-3 text-left">Findings & Remarks</th>
                                        <th class="px-4 py-3 text-left">Next Due</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($asset->inspections->sortByDesc('inspection_date') as $insp)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 align-middle font-medium whitespace-nowrap text-left">
                                                {{ $insp->inspection_date ? $insp->inspection_date->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 align-middle font-medium text-gray-900 text-left">
                                                {{ $insp->inspector }}
                                            </td>
                                            <td class="px-4 py-3 align-middle whitespace-nowrap text-center">
                                                @php
                                                    $iResult = match(strtolower($insp->result)) {
                                                        'passed', 'good', 'excellent' => 'bg-emerald-100 text-emerald-800',
                                                        'warning', 'fair', 'needs_attention' => 'bg-amber-100 text-amber-800',
                                                        default => 'bg-rose-100 text-rose-800',
                                                    };
                                                @endphp
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $iResult }}">
                                                    {{ ucfirst($insp->result) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 align-middle text-gray-700 text-left">
                                                {{ $insp->findings ?: 'No defects observed.' }}
                                            </td>
                                            <td class="px-4 py-3 align-middle text-xs text-gray-500 whitespace-nowrap text-left">
                                                {{ $insp->next_due_date ? $insp->next_due_date->format('M d, Y') : 'Unscheduled' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic py-3">No routine safety inspection reports recorded.</p>
                    @endif
                </div>
            </div>

            {{-- TAB 4: AUDIT TRAIL, HISTORY & ACCESSORIES --}}
            <div x-show="activeTab === 'history'" class="space-y-6">
                {{-- Accessories & Attachments Glimpse --}}
                @if($asset->attachments->count() > 0 || $asset->accessories->count() > 0)
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <i class="fas fa-paperclip text-blue-600"></i> Linked Attachments & Accessories
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($asset->attachments as $att)
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 text-xs">
                                    <div class="font-bold text-gray-900">{{ $att->name }}</div>
                                    <div class="text-gray-500 mt-0.5">Attachment (Qty: {{ $att->quantity ?? 1 }})</div>
                                </div>
                            @endforeach
                            @foreach($asset->accessories as $acc)
                                <div class="bg-purple-50/50 p-3 rounded-xl border border-purple-200 text-xs">
                                    <div class="font-bold text-purple-950">{{ $acc->name }}</div>
                                    <div class="text-purple-700 mt-0.5">Accessory (Qty: {{ $acc->quantity }})</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Activity Audit Trail --}}
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-timeline text-blue-600"></i> Activity & Custody History
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">Chronological record of status changes, verifications, and inspections.</p>
                    </div>

                    @if($asset->auditLogs->count() > 0)
                        <div class="relative pl-6 border-l-2 border-gray-200 space-y-5 my-3">
                            @foreach($asset->auditLogs as $log)
                                <div class="relative">
                                    @php
                                        $dotColor = match($log->event) {
                                            'created'        => 'bg-emerald-500 ring-emerald-100',
                                            'status_changed' => 'bg-amber-500 ring-amber-100',
                                            'deleted'        => 'bg-rose-500 ring-rose-100',
                                            default          => 'bg-blue-500 ring-blue-100',
                                        };
                                    @endphp
                                    <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full ring-4 {{ $dotColor }}"></div>

                                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3.5 text-xs">
                                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                            <span class="font-bold text-gray-900 uppercase">
                                                {{ strtoupper(str_replace('_', ' ', $log->event)) }}
                                            </span>
                                            <span class="text-gray-400 font-mono">
                                                {{ $log->created_at->format('M d, Y h:i A') }}
                                            </span>
                                        </div>
                                        <p class="text-gray-800 text-sm leading-relaxed my-1">{{ $log->description }}</p>
                                        <div class="text-[11px] text-gray-500 mt-2 pt-1 border-t border-gray-200 flex items-center justify-between">
                                            <span>By: <strong>{{ $log->user?->name ?? 'System' }}</strong></span>
                                            @if($log->ip_address)
                                                <span class="font-mono text-gray-400">IP: {{ $log->ip_address }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic py-3">No activity logs recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: UPLOAD DIGITAL DOCUMENT --}}
    <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showUploadModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-file-arrow-up text-indigo-600"></i> Upload Asset Document
                </h3>
                <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('assets.documents.store', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Document Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Chainsaw User Manual & Warranty Slip" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Document Type *</label>
                        <select name="document_type" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                            <option value="manual">User / Service Manual</option>
                            <option value="warranty">Warranty Certificate</option>
                            <option value="registration">Vehicle / Unit Registration</option>
                            <option value="certificate">Inspection Certificate</option>
                            <option value="invoice">Purchase Invoice / Receipt</option>
                            <option value="deed_of_donation">Deed of Donation</option>
                            <option value="other">Other Document</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">File Upload (PDF, Images, Docs - Max 20MB) *</label>
                    <input type="file" name="document_file" required class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Notes / Remarks</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes regarding this document..." class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showUploadModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm">
                        Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: DECOMMISSION / RETIRE ASSET --}}
    @if(auth()->user()?->isAdmin())
        <div x-show="showRetireModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
            <div @click.away="showRetireModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-lg font-bold text-rose-700 flex items-center gap-2">
                        <i class="fas fa-ban"></i> Decommission / Retire Asset
                    </h3>
                    <button @click="showRetireModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <p class="text-xs text-gray-600">
                    Retiring an asset marks it as decommissioned from disaster service. Its operational history, maintenance, and deployment records will be preserved for audit compliance.
                </p>

                <form action="{{ route('assets.retire', $asset) }}" method="POST" class="space-y-4 text-sm">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Retirement Justification / Reason *</label>
                        <textarea name="retirement_reason" required rows="3" placeholder="Provide reason (e.g. Beyond economic repair, destroyed during response, obsolete)..." class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="showRetireModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-sm">
                            Confirm Decommissioning
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL: OPERATIONAL REQUIREMENT ALLOCATION (MERGED) --}}
    <x-modal id="asset-requirement-modal" title="Operational Requirement & Equipment Allocation" maxWidth="3xl">
        <div class="space-y-5">
            {{-- Header card --}}
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 mb-1">
                        <i class="fas fa-gas-pump text-[10px]"></i>
                        <span id="ar_modal_type">Requirement</span>
                    </span>
                    <h3 id="ar_modal_name" class="text-lg font-bold text-gray-900">Diesel Fuel</h3>
                    <p id="ar_modal_desc" class="text-xs text-gray-600 mt-1 italic">Notes</p>
                </div>
                <div id="ar_modal_status_badge"></div>
            </div>

            {{-- Stock metrics --}}
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Warehouse Stock</span>
                    <span id="ar_modal_current" class="text-base font-bold text-gray-900">0</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Minimum Level</span>
                    <span id="ar_modal_min" class="text-base font-bold text-rose-600">0</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Max Capacity</span>
                    <span id="ar_modal_max" class="text-base font-bold text-blue-600">0</span>
                </div>
            </div>

            {{-- Assets Table --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                        <i class="fas fa-boxes-stacked text-blue-600"></i>
                        <span>All Equipment Utilizing This Supply</span>
                    </h4>
                    <span id="ar_modal_assets_count" class="text-xs text-gray-400 font-medium">0 assets</span>
                </div>
                <div class="border border-gray-200 rounded-xl overflow-hidden max-h-64 overflow-y-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-gray-100/75 border-b border-gray-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase">Asset</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase">Location</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase">Condition</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase">Status</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-700 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody id="ar_modal_assets_tbody" class="divide-y divide-gray-100 bg-white">
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                <a href="{{ route('operational-requirements.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl text-xs font-semibold border border-blue-200 transition-colors">
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                    <span>Manage All Supplies in Warehouse</span>
                </a>
                <button type="button" onclick="window.closeModal('asset-requirement-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
    function openAssetRequirementModal(req) {
        document.getElementById('ar_modal_name').textContent = req.requirement_type;
        document.getElementById('ar_modal_type').textContent = req.asset_type ? req.asset_type.name : (req.assetType ? req.assetType.name : 'General Consumable');
        document.getElementById('ar_modal_desc').textContent = req.description || 'Standard operational supply.';

        const current = parseFloat(req.current_level || 0);
        const max = parseFloat(req.maximum_capacity || 0);
        const min = parseFloat(req.minimum_level || 0);
        const unit = req.unit || 'units';

        document.getElementById('ar_modal_current').textContent = `${current} ${unit}`;
        document.getElementById('ar_modal_min').textContent = `${min} ${unit}`;
        document.getElementById('ar_modal_max').textContent = `${max} ${unit}`;

        const isLow = current <= min;
        document.getElementById('ar_modal_status_badge').innerHTML = isLow 
            ? '<span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200"><i class="fas fa-triangle-exclamation mr-1"></i> Low Stock</span>'
            : '<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-check-circle mr-1"></i> In Stock</span>';

        const assets = req.using_assets || [];
        document.getElementById('ar_modal_assets_count').textContent = `${assets.length} equipment unit${assets.length === 1 ? '' : 's'}`;

        const tbody = document.getElementById('ar_modal_assets_tbody');
        if (assets.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-xs">
                        No equipment currently registered as utilizing this requirement.
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = assets.map(asset => {
                const isCurrent = asset.id == {{ $asset->id }};
                return `
                    <tr class="hover:bg-blue-50/30 transition-colors ${isCurrent ? 'bg-blue-50/50 font-semibold' : ''}">
                        <td class="px-4 py-2.5 align-middle">
                            <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-1 py-0.5 rounded">${asset.code}</span>
                            <span class="ml-1 text-xs text-gray-900">${asset.name}</span>
                            ${isCurrent ? '<span class="ml-1 text-[10px] text-blue-600 font-bold">(Current Asset)</span>' : ''}
                        </td>
                        <td class="px-4 py-2.5 align-middle text-xs text-gray-600">
                            ${asset.location ? asset.location.name : 'Unassigned'}
                        </td>
                        <td class="px-4 py-2.5 align-middle text-center text-xs font-bold uppercase text-gray-700">
                            ${asset.condition}
                        </td>
                        <td class="px-4 py-2.5 align-middle text-center text-xs">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold ${asset.status === 'available' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700'}">
                                ${asset.status}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 align-middle text-right">
                            ${!isCurrent ? `<a href="/assets/${asset.id}" class="text-xs text-blue-600 hover:underline font-semibold">View</a>` : '<span class="text-xs text-gray-400">—</span>'}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        window.openModal('asset-requirement-modal');
    }
</script>
@endpush
@endsection
