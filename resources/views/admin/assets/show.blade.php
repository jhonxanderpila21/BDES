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
                        {{ \App\Services\AssetLifecycleService::statuses()[$asset->status] ?? ucfirst(str_replace('_', ' ', $asset->status)) }}
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

            @if($asset->status !== 'retired')
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

    {{-- 360° Summary KPI Strip --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Acquisition</span>
            <span class="text-lg font-bold text-gray-900 mt-1 block">
                ₱{{ number_format($asset->acquisition_cost ?? 0, 2) }}
            </span>
            <span class="text-xs text-gray-400">{{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : 'No date' }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Condition</span>
            @php
                $condBadge = match($asset->condition) {
                    'excellent', 'good' => 'text-emerald-700 bg-emerald-50',
                    'fair'              => 'text-amber-700 bg-amber-50',
                    'poor'              => 'text-rose-700 bg-rose-50',
                    default             => 'text-gray-700 bg-gray-50',
                };
            @endphp
            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase mt-1 {{ $condBadge }}">
                {{ ucfirst($asset->condition) }}
            </span>
            <span class="text-xs text-gray-400 block mt-1">Operational state</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Deployments</span>
            <span class="text-lg font-bold text-gray-900 mt-1 block">{{ $stats['total_deployments'] }}</span>
            <span class="text-xs text-gray-400">Total missions</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Maintenance</span>
            <span class="text-lg font-bold text-gray-900 mt-1 block">{{ $stats['total_maintenances'] }}</span>
            <span class="text-xs text-gray-400">Work orders</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Maint. Cost</span>
            <span class="text-lg font-bold text-gray-900 mt-1 block">₱{{ number_format($stats['total_maintenance_cost'], 2) }}</span>
            <span class="text-xs text-gray-400">Cumulative repairs</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Documents</span>
            <span class="text-lg font-bold text-gray-900 mt-1 block">{{ $stats['total_documents'] }}</span>
            <span class="text-xs text-gray-400">Digital records</span>
        </div>
    </div>

    {{-- Dossier Tab Navigation --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-4 bg-gray-50/50 flex flex-wrap gap-1 print:hidden">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-circle-info"></i>
                <span>1. Overview</span>
            </button>
            <button @click="activeTab = 'financials'" :class="activeTab === 'financials' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-coins"></i>
                <span>2. Acquisition & Financials</span>
            </button>
            <button @click="activeTab = 'deployments'" :class="activeTab === 'deployments' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-truck"></i>
                <span>3. Deployments ({{ $stats['total_deployments'] }})</span>
            </button>
            <button @click="activeTab = 'maintenance'" :class="activeTab === 'maintenance' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-screwdriver-wrench"></i>
                <span>4. Work Orders ({{ $stats['total_maintenances'] }})</span>
            </button>
            <button @click="activeTab = 'inspections'" :class="activeTab === 'inspections' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-shield-check"></i>
                <span>5. Inspections ({{ $stats['total_inspections'] }})</span>
            </button>
            <button @click="activeTab = 'accessories'" :class="activeTab === 'accessories' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-paperclip"></i>
                <span>6. Accessories & Attachments</span>
            </button>
            <button @click="activeTab = 'documents'" :class="activeTab === 'documents' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-file-lines"></i>
                <span>7. Digital Documents ({{ $stats['total_documents'] }})</span>
            </button>
            <button @click="activeTab = 'audit'" :class="activeTab === 'audit' ? 'border-blue-600 text-blue-600 bg-white shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 py-3 px-4 text-xs font-bold border-b-2 rounded-t-lg transition-colors">
                <i class="fas fa-clock-rotate-left"></i>
                <span>8. Audit Trail ({{ $asset->auditLogs->count() }})</span>
            </button>
        </div>

        <div class="p-6">
            {{-- TAB 1: OVERVIEW & TECHNICAL SPECS --}}
            <div x-show="activeTab === 'overview'" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Specs Table --}}
                    <div class="lg:col-span-2 space-y-4">
                        <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <i class="fas fa-microchip text-blue-600"></i> Technical Specifications & Inventory Profile
                        </h3>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <dt class="text-xs font-semibold text-gray-500 uppercase">Category</dt>
                                <dd class="text-gray-900 font-bold mt-0.5">{{ $asset->category->name ?? 'Uncategorized' }}</dd>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <dt class="text-xs font-semibold text-gray-500 uppercase">Asset Type</dt>
                                <dd class="text-gray-900 font-bold mt-0.5">{{ $asset->type->name ?? 'None' }}</dd>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <dt class="text-xs font-semibold text-gray-500 uppercase">Serial Number</dt>
                                <dd class="font-mono text-gray-900 font-bold mt-0.5">{{ $asset->serial_number ?: 'Not specified' }}</dd>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <dt class="text-xs font-semibold text-gray-500 uppercase">Manufacturer / Brand</dt>
                                <dd class="text-gray-900 font-bold mt-0.5">{{ $asset->manufacturer ?: 'Not specified' }}</dd>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <dt class="text-xs font-semibold text-gray-500 uppercase">Primary Station / Location</dt>
                                <dd class="text-gray-900 font-bold mt-0.5">{{ $asset->location->name ?? 'Not assigned' }}</dd>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <dt class="text-xs font-semibold text-gray-500 uppercase">Created Timestamp</dt>
                                <dd class="text-gray-900 font-medium mt-0.5">{{ $asset->created_at->format('M d, Y - h:i A') }}</dd>
                            </div>
                        </dl>

                        @if($asset->notes)
                            <div class="mt-4 bg-blue-50/40 border border-blue-100 p-4 rounded-xl">
                                <h4 class="text-xs font-bold text-blue-900 uppercase mb-1">Operational Notes & Usage Directives</h4>
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $asset->notes }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Barcode / QR Identification Simulation Card --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 flex flex-col items-center justify-between text-center">
                        <div class="w-full">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">LGU Barangay Disaster Asset Tag</span>
                            
                            {{-- Simulated Barcode Display --}}
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm inline-block w-full">
                                <div class="text-4xl tracking-widest text-gray-900 font-mono select-none py-2" style="letter-spacing: 0.25em;">
                                    ||||| | |||| || ||| |
                                </div>
                                <div class="font-mono font-bold text-gray-800 text-sm tracking-widest">
                                    {{ $asset->code }}
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-4 leading-relaxed">
                                Affix barcode tag to physical equipment chassis. Scan during field deployments, returns, and periodic audits.
                            </p>
                        </div>

                        <div class="w-full mt-6 pt-4 border-t border-gray-200 space-y-2">
                            <button onclick="window.print()" class="w-full py-2 px-3 bg-white border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors inline-flex items-center justify-center gap-2">
                                <i class="fas fa-barcode"></i> Print Barcode Label
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: ACQUISITION & FINANCIALS --}}
            <div x-show="activeTab === 'financials'" class="space-y-6">
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i class="fas fa-money-bill-wave text-emerald-600"></i> Financial Capitalization & Procurement Records
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-5 rounded-2xl bg-emerald-50/40 border border-emerald-200">
                        <span class="text-xs font-bold uppercase text-emerald-800 tracking-wider block">Acquisition Cost</span>
                        <div class="text-3xl font-extrabold text-emerald-700 mt-2">
                            ₱{{ number_format($asset->acquisition_cost ?? 0, 2) }}
                        </div>
                        <p class="text-xs text-emerald-600 mt-1">Book capital procurement expense</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-blue-50/40 border border-blue-200">
                        <span class="text-xs font-bold uppercase text-blue-800 tracking-wider block">Procurement / Purchase Date</span>
                        <div class="text-2xl font-bold text-blue-900 mt-2">
                            {{ $asset->purchase_date ? $asset->purchase_date->format('F d, Y') : 'Not on record' }}
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            @if($asset->purchase_date)
                                In service for {{ $asset->purchase_date->diffForHumans(null, true) }}
                            @else
                                Procurement date pending confirmation
                            @endif
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-amber-50/40 border border-amber-200">
                        <span class="text-xs font-bold uppercase text-amber-800 tracking-wider block">Warranty Expiration</span>
                        <div class="text-2xl font-bold text-amber-900 mt-2">
                            {{ $asset->warranty_expiry ? $asset->warranty_expiry->format('F d, Y') : 'No warranty recorded' }}
                        </div>
                        <p class="text-xs text-amber-700 mt-1">
                            @if($asset->warranty_expiry)
                                @if($asset->warranty_expiry->isPast())
                                    <span class="text-rose-600 font-semibold"><i class="fas fa-circle-xmark"></i> Warranty Expired</span>
                                @else
                                    <span class="text-emerald-700 font-semibold"><i class="fas fa-circle-check"></i> Active ({{ $asset->warranty_expiry->diffForHumans() }})</span>
                                @endif
                            @endif
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3">Total Cost of Ownership (TCO) Breakdown</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div class="flex justify-between border-b border-gray-200 pb-2">
                            <span class="text-gray-600">Initial Acquisition Cost:</span>
                            <span class="font-bold text-gray-900">₱{{ number_format($asset->acquisition_cost ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-200 pb-2">
                            <span class="text-gray-600">Total Maintenance & Repairs:</span>
                            <span class="font-bold text-gray-900">₱{{ number_format($stats['total_maintenance_cost'], 2) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-200 pb-2">
                            <span class="text-gray-600">Gross Asset Capitalization:</span>
                            <span class="font-bold text-blue-700">₱{{ number_format(($asset->acquisition_cost ?? 0) + $stats['total_maintenance_cost'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: OPERATIONAL DEPLOYMENTS --}}
            <div x-show="activeTab === 'deployments'" class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-truck text-blue-600"></i> Field Dispatch & Return Registry
                    </h3>
                    @if($asset->status === 'available')
                        <a href="{{ route('deployment.create', ['asset_id' => $asset->id]) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors">
                            <i class="fas fa-plus mr-1"></i> New Deployment
                        </a>
                    @endif
                </div>

                @if($asset->deployments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Dispatch Date</th>
                                    <th class="px-4 py-3">Destination / Operation</th>
                                    <th class="px-4 py-3">Custodian</th>
                                    <th class="px-4 py-3">Condition Out</th>
                                    <th class="px-4 py-3">Return Status</th>
                                    <th class="px-4 py-3">Condition Returned</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($asset->deployments->sortByDesc('deployment_date') as $dep)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium whitespace-nowrap">
                                            {{ $dep->deployment_date ? $dep->deployment_date->format('M d, Y') : 'N/A' }}
                                            <span class="text-xs text-gray-400 block">Due: {{ $dep->expected_return_date ? $dep->expected_return_date->format('M d, Y') : 'Open' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-bold text-gray-900">{{ $dep->location }}</p>
                                            <p class="text-xs text-gray-500">{{ $dep->purpose }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="text-gray-900 font-medium">{{ $dep->responsible_person ?? $dep->user?->name ?? 'Disaster Team' }}</p>
                                            <p class="text-xs text-gray-400">Approved by: {{ $dep->approvedByUser?->name ?? 'Official' }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="capitalize text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-800">
                                                {{ $dep->condition_before ?? $asset->condition }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($dep->status === 'deployed')
                                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                                    In Field
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                    Returned ({{ $dep->returnRecord?->return_date ? $dep->returnRecord->return_date->format('M d') : 'Yes' }})
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($dep->returnRecord)
                                                <span class="capitalize text-xs font-bold px-2 py-0.5 rounded {{ $dep->returnRecord->condition === 'poor' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-50 text-emerald-800' }}">
                                                    {{ $dep->returnRecord->condition }}
                                                </span>
                                                @if($dep->returnRecord->remarks)
                                                    <p class="text-xs text-gray-500 italic mt-0.5">{{ $dep->returnRecord->remarks }}</p>
                                                @endif
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

            {{-- TAB 4: WORK ORDERS & MAINTENANCE --}}
            <div x-show="activeTab === 'maintenance'" class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-screwdriver-wrench text-amber-600"></i> Work Orders & Corrective Maintenance
                    </h3>
                    <a href="{{ route('maintenance.create', ['asset_id' => $asset->id]) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors">
                        <i class="fas fa-plus mr-1"></i> New Work Order
                    </a>
                </div>

                @if($asset->maintenance->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Work Order #</th>
                                    <th class="px-4 py-3">Type & Description</th>
                                    <th class="px-4 py-3">Priority</th>
                                    <th class="px-4 py-3">Assigned Tech</th>
                                    <th class="px-4 py-3">Cost (PHP)</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Dates</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($asset->maintenance->sortByDesc('start_date') as $m)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-mono font-bold text-blue-700 whitespace-nowrap">
                                            {{ $m->work_order_number ?? ('WO-' . str_pad($m->id, 5, '0', STR_PAD_LEFT)) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $m->type) }}</p>
                                            <p class="text-xs text-gray-600 line-clamp-1">{{ $m->description }}</p>
                                            @if($m->parts_used)
                                                <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-puzzle-piece"></i> Parts: {{ $m->parts_used }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
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
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                            {{ $m->assignedUser?->name ?? 'Unassigned' }}
                                        </td>
                                        <td class="px-4 py-3 font-bold text-gray-900 whitespace-nowrap">
                                            ₱{{ number_format($m->cost ?? 0, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
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
                                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                            <div>Start: {{ $m->start_date ? $m->start_date->format('M d, Y') : 'N/A' }}</div>
                                            <div>End: {{ $m->completed_at ? $m->completed_at->format('M d, Y') : ($m->end_date ? $m->end_date->format('M d, Y') : 'Pending') }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-500 bg-gray-50 rounded-xl">
                        <i class="fas fa-wrench text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm font-medium">No maintenance work orders logged for this equipment.</p>
                    </div>
                @endif
            </div>

            {{-- TAB 5: INSPECTIONS LOG --}}
            <div x-show="activeTab === 'inspections'" class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-shield-check text-emerald-600"></i> Routine & Safety Inspection Records
                    </h3>
                    <a href="{{ route('inspection.create', ['asset_id' => $asset->id]) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors">
                        <i class="fas fa-plus mr-1"></i> Log Inspection
                    </a>
                </div>

                @if($asset->inspections->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Inspector</th>
                                    <th class="px-4 py-3">Rating / Outcome</th>
                                    <th class="px-4 py-3">Findings & Remarks</th>
                                    <th class="px-4 py-3">Next Due Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($asset->inspections->sortByDesc('inspection_date') as $insp)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium whitespace-nowrap">
                                            {{ $insp->inspection_date ? $insp->inspection_date->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $insp->inspector }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
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
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $insp->findings ?: 'No defects observed.' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                            {{ $insp->next_due_date ? $insp->next_due_date->format('M d, Y') : 'Unscheduled' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-500 bg-gray-50 rounded-xl">
                        <i class="fas fa-clipboard-check text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm font-medium">No formal inspection reports recorded.</p>
                    </div>
                @endif
            </div>

            {{-- TAB 6: ACCESSORIES & ATTACHMENTS --}}
            <div x-show="activeTab === 'accessories'" class="space-y-6">
                {{-- Attachments Section --}}
                <div>
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2 mb-4">
                        <i class="fas fa-paperclip text-blue-600"></i> Physical Equipment Attachments
                    </h3>
                    @if($asset->attachments->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($asset->attachments as $att)
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-gray-900 text-sm">{{ $att->name }}</span>
                                        <span class="font-mono text-xs text-gray-500">{{ $att->code ?? 'N/A' }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mb-2">{{ $att->description ?? 'No description.' }}</p>
                                    <div class="flex justify-between text-xs text-gray-500 pt-2 border-t border-gray-200">
                                        <span>Status: <strong class="text-gray-800">{{ ucfirst($att->status ?? 'Active') }}</strong></span>
                                        <span>Qty: <strong class="text-gray-800">{{ $att->quantity ?? 1 }}</strong></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 bg-gray-50 p-4 rounded-xl">No physical attachments linked to this asset.</p>
                    @endif
                </div>

                {{-- Accessories Section --}}
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2 mb-4">
                        <i class="fas fa-puzzle-piece text-purple-600"></i> Linked Spare Accessories & Peripherals
                    </h3>
                    @if($asset->accessories->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($asset->accessories as $acc)
                                <div class="bg-purple-50/40 p-4 rounded-xl border border-purple-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-purple-950 text-sm">{{ $acc->name }}</span>
                                        <span class="font-bold text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded">Qty: {{ $acc->quantity }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600">{{ $acc->description ?: 'No remarks' }}</p>
                                    <div class="text-xs text-purple-700 mt-2 font-medium">Condition: {{ ucfirst($acc->condition ?? 'good') }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 bg-gray-50 p-4 rounded-xl">No paired accessories recorded.</p>
                    @endif
                </div>
            </div>

            {{-- TAB 7: DIGITAL DOCUMENTS & CERTIFICATES --}}
            <div x-show="activeTab === 'documents'" class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-file-shield text-indigo-600"></i> Digital Equipment Documents & Compliance Dossier
                        </h3>
                        <p class="text-xs text-gray-500">Operation manuals, warranty certificates, registration cards, donation deeds, and purchase invoices.</p>
                    </div>
                    <button @click="showUploadModal = true" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors">
                        <i class="fas fa-plus mr-1"></i> Upload Document
                    </button>
                </div>

                @if($asset->documents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Document Title</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">File Specs</th>
                                    <th class="px-4 py-3">Expiry Compliance</th>
                                    <th class="px-4 py-3">Uploaded By</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($asset->documents as $doc)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-file-pdf text-red-500 text-lg"></i>
                                                <div>
                                                    <p class="font-bold text-gray-900">{{ $doc->title }}</p>
                                                    @if($doc->notes)
                                                        <p class="text-xs text-gray-500">{{ $doc->notes }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-800 uppercase">
                                                {{ str_replace('_', ' ', $doc->document_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600">
                                            <div>{{ $doc->formatted_file_size }}</div>
                                            <div class="text-gray-400 font-mono text-[11px] truncate max-w-[150px]">{{ $doc->file_name }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs">
                                            @if($doc->expiry_date)
                                                @if($doc->is_expired)
                                                    <span class="text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded font-bold">
                                                        <i class="fas fa-circle-exclamation"></i> Expired ({{ $doc->expiry_date->format('M d, Y') }})
                                                    </span>
                                                @else
                                                    <span class="text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded font-medium">
                                                        Valid until {{ $doc->expiry_date->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">No expiration</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600">
                                            <div>{{ $doc->uploader?->name ?? 'System' }}</div>
                                            <div class="text-gray-400">{{ $doc->created_at->format('M d, Y') }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-xs">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('assets.documents.download', $doc) }}" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg" title="Download Document">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form action="{{ route('assets.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete document [{{ $doc->title }}]?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg" title="Delete Document">
                                                        <i class="fas fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <i class="fas fa-folder-open text-4xl text-gray-300 mb-2"></i>
                        <h4 class="text-sm font-bold text-gray-700">No Digital Documents Attached</h4>
                        <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Upload operations manuals, equipment warranties, donation deeds, or certifications for official LGU record-keeping.</p>
                        <button @click="showUploadModal = true" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-file-arrow-up mr-1"></i> Upload First Document
                        </button>
                    </div>
                @endif
            </div>

            {{-- TAB 8: AUDIT TRAIL & HISTORY --}}
            <div x-show="activeTab === 'audit'" class="space-y-4">
                <div class="border-b border-gray-100 pb-2">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-timeline text-blue-600"></i> Immutable Audit Trail & Custody Movement History
                    </h3>
                    <p class="text-xs text-gray-500">Chronological, tamper-evident log of every change, state transition, and user interaction on this asset.</p>
                </div>

                @if($asset->auditLogs->count() > 0)
                    <div class="relative pl-6 border-l-2 border-gray-200 space-y-6 my-4">
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

                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                        <span class="font-bold text-gray-900 text-xs uppercase tracking-wider">
                                            {{ strtoupper(str_replace('_', ' ', $log->event)) }}
                                        </span>
                                        <span class="text-xs text-gray-400 font-mono">
                                            {{ $log->created_at->format('Y-m-d H:i:s') }} ({{ $log->created_at->diffForHumans() }})
                                        </span>
                                    </div>
                                    <p class="text-gray-800 text-sm leading-relaxed">{{ $log->description }}</p>
                                    
                                    <div class="flex flex-wrap items-center justify-between gap-2 mt-3 pt-2 border-t border-gray-200 text-xs text-gray-500">
                                        <span>
                                            <i class="fas fa-user-circle text-gray-400 mr-1"></i>
                                            Actor: <strong class="text-gray-700">{{ $log->user?->name ?? 'System Process' }}</strong>
                                        </span>
                                        @if($log->ip_address)
                                            <span class="font-mono text-gray-400">IP: {{ $log->ip_address }}</span>
                                        @endif
                                    </div>

                                    @if($log->new_values || $log->old_values)
                                        <details class="mt-2 text-xs">
                                            <summary class="cursor-pointer text-blue-600 hover:text-blue-800 font-medium">
                                                View Field Level Changes
                                            </summary>
                                            <div class="mt-2 p-2 bg-white rounded border border-gray-200 font-mono text-[11px] overflow-x-auto space-y-1">
                                                @if($log->old_values)
                                                    <div><strong class="text-rose-600">Old:</strong> {{ json_encode($log->old_values) }}</div>
                                                @endif
                                                @if($log->new_values)
                                                    <div><strong class="text-emerald-600">New:</strong> {{ json_encode($log->new_values) }}</div>
                                                @endif
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-gray-500 bg-gray-50 rounded-xl">
                        <i class="fas fa-fingerprint text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm font-medium">No audit events recorded yet.</p>
                    </div>
                @endif
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
                Retiring an asset marks it as decommissioned from disaster service. Its operational history, maintenance, and deployment records will be immutably preserved for audit compliance.
            </p>

            <form action="{{ route('assets.retire', $asset) }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Retirement Justification / Reason *</label>
                    <textarea name="retirement_reason" required rows="3" placeholder="Provide detailed justification (e.g. Beyond economic repair, destroyed during Typhoon response, obsolete)..." class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
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
</div>
@endsection
