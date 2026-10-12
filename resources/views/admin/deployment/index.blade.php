@extends('layouts.admin')

@section('title', 'Deployments & Returns - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Deployments & Returns</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Deployments & Returns']
            ]" />
        </div>
        <button onclick="openCreateDeploymentModal()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-sm">
            <i class="fas fa-plus"></i>
            Deploy Asset
        </button>
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger" class="mb-6">{{ $errors->first() }}</x-alert>
    @endif

    {{-- Dashboard Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Records</p>
                <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['total'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>

        {{-- Active --}}
        <div class="bg-white rounded-xl p-5 border border-emerald-200 bg-emerald-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Active in Field</p>
                <p class="text-2xl font-bold text-emerald-700">{{ $statusCounts['active'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-truck"></i>
            </div>
        </div>

        {{-- Pending Return --}}
        <div class="bg-white rounded-xl p-5 border border-amber-200 bg-amber-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-1">Pending Return</p>
                <p class="text-2xl font-bold text-amber-700">{{ $statusCounts['pending_return'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xl">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        {{-- Returned --}}
        <div class="bg-white rounded-xl p-5 border border-purple-200 bg-purple-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-700 mb-1">Returned</p>
                <p class="text-2xl font-bold text-purple-700">{{ $statusCounts['returned'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('deployment.index') }}" method="GET" id="searchForm">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by asset name, code, destination, team, or returnee..." />
            </form>
        </div>

        {{-- Status Filter Dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('status') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                <i class="fas fa-filter"></i>
                <span>{{ request('status') ? ucfirst(str_replace('_', ' ', request('status'))) : 'All Statuses' }}</span>
                <i class="fas fa-chevron-down text-xs ml-1"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                <a href="{{ route('deployment.index', array_merge(request()->except('status', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('status') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">
                    All Statuses
                </a>
                <a href="{{ route('deployment.index', array_merge(request()->except('page'), ['status' => 'active'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === 'active' ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">
                    Active
                </a>
                <a href="{{ route('deployment.index', array_merge(request()->except('page'), ['status' => 'pending_return'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === 'pending_return' ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">
                    Pending Return
                </a>
                <a href="{{ route('deployment.index', array_merge(request()->except('page'), ['status' => 'returned'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === 'returned' ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">
                    Returned
                </a>
            </div>
        </div>
    </div>

    {{-- Deployment Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Destination & Team</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Deployment Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Expected Return</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Return Inspection</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($deployments as $deployment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Asset Info --}}
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 rounded-lg bg-blue-50 text-blue-600 mt-1">
                                        <i class="fas fa-box text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2 py-0.5 text-xs font-mono font-semibold rounded bg-gray-100 text-gray-800 border border-gray-200 mb-1">
                                            {{ $deployment->asset->code ?? 'N/A' }}
                                        </span>
                                        <p class="text-sm font-semibold text-gray-900">{{ $deployment->asset->name ?? 'Unknown Asset' }}</p>
                                        <p class="text-xs text-gray-500">{{ $deployment->asset->category->name ?? 'Uncategorized' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Destination & Team --}}
                            <td class="px-6 py-4 text-sm">
                                <p class="font-medium text-gray-900 flex items-center gap-1.5">
                                    <i class="fas fa-location-dot text-red-500 text-xs"></i>
                                    {{ $deployment->destination }}
                                </p>
                                <p class="text-gray-500 text-xs mt-1 flex items-center gap-1.5">
                                    <i class="fas fa-users text-gray-400 text-xs"></i>
                                    {{ $deployment->assigned_team ?? 'No team assigned' }}
                                </p>
                                @if($deployment->purpose)
                                    <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs" title="{{ $deployment->purpose }}">
                                        {{ $deployment->purpose }}
                                    </p>
                                @endif
                            </td>

                            {{-- Deployment Date --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $deployment->deployment_date ? \Carbon\Carbon::parse($deployment->deployment_date)->format('M d, Y') : 'N/A' }}
                            </td>

                            {{-- Expected Return & Overdue Alert --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($deployment->expected_return_date)
                                    <span class="text-gray-700">{{ \Carbon\Carbon::parse($deployment->expected_return_date)->format('M d, Y') }}</span>
                                    @if($deployment->isOverdue())
                                        <span class="block mt-1">
                                            <x-badge variant="danger" size="sm">Overdue</x-badge>
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400">Open-ended</span>
                                @endif
                            </td>

                            {{-- Deployment Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusVariant = match($deployment->status) {
                                        'active' => 'success',
                                        'pending_return' => 'warning',
                                        'returned' => 'secondary',
                                        default => 'info'
                                    };
                                @endphp
                                <x-badge variant="{{ $statusVariant }}" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $deployment->status)) }}
                                </x-badge>
                            </td>

                            {{-- Return Inspection Summary --}}
                            <td class="px-6 py-4 text-sm">
                                @if($deployment->status === 'returned')
                                    @if($deployment->returnRecord)
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs text-gray-500">Returned:</span>
                                                <span class="text-xs font-medium text-gray-800">
                                                    {{ $deployment->returnRecord->return_date ? \Carbon\Carbon::parse($deployment->returnRecord->return_date)->format('M d, Y') : 'Recorded' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                @php
                                                    $cond = strtolower($deployment->returnRecord->condition ?? 'good');
                                                    $condColor = in_array($cond, ['excellent', 'good']) ? 'success' : ($cond === 'fair' ? 'warning' : 'danger');
                                                @endphp
                                                <x-badge variant="{{ $condColor }}" size="sm">
                                                    {{ ucfirst($cond) }}
                                                </x-badge>
                                                @if($deployment->returnRecord->status && $deployment->returnRecord->status !== 'received')
                                                    <x-badge variant="warning" size="sm">
                                                        {{ ucfirst(str_replace('_', ' ', $deployment->returnRecord->status)) }}
                                                    </x-badge>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500 italic">Marked Returned</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">In field</span>
                                @endif
                            </td>

                            {{-- Action Buttons --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-2">

                                    {{-- Edit Deployment --}}
                                    <button 
                                        type="button"
                                        onclick='openEditDeploymentModal(@json($deployment))' 
                                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors" 
                                        title="Edit Deployment"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Delete Deployment --}}
                                    <form action="{{ route('deployment.destroy', $deployment) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this deployment record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-600 transition-colors" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <x-empty-state 
                                    icon="fas fa-truck-ramp-box" 
                                    title="No deployments found" 
                                    description="Start by deploying an available asset to a team or location."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $deployments->links() }}
        </div>
    </x-card>

    {{-- ======================================================== --}}
    {{-- MODAL 1: Create Deployment                               --}}
    {{-- ======================================================== --}}
    <x-modal id="create-deployment-modal" title="Deploy Asset">
        <form action="{{ route('deployment.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-form-group label="Select Available Asset" required>
                @if($availableAssets->isEmpty())
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
                        <i class="fas fa-exclamation-triangle mr-1"></i> No assets are currently available for deployment.
                    </div>
                @else
                    <select name="asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="">-- Choose an Available Asset --</option>
                        @foreach($availableAssets as $availAsset)
                            <option value="{{ $availAsset->id }}">
                                {{ $availAsset->code }} — {{ $availAsset->name }} ({{ $availAsset->category->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </x-form-group>

            <x-form-group label="Destination / Facility" required>
                <input type="text" name="destination" required placeholder="e.g. Purok A Evacuation Center, Zone 3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <x-form-group label="Assigned Team / Responder">
                <input type="text" name="assigned_team" placeholder="e.g. Alpha Rescue Team, Tanod Night Watch" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Deployment Date" required>
                    <input type="date" name="deployment_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="Expected Return Date">
                    <input type="date" name="expected_return_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
            </div>

            <x-form-group label="Purpose / Mission Description">
                <textarea name="purpose" rows="3" placeholder="State the reason or operation for this deployment..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1" :disabled="$availableAssets->isEmpty()">
                    <i class="fas fa-truck mr-1.5"></i> Confirm Deployment
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('create-deployment-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- ======================================================== --}}
    {{-- MODAL 2: Record Return (Merged Action)                   --}}
    {{-- ======================================================== --}}
    <x-modal id="record-return-modal" title="Record Asset Return">
        <form id="recordReturnForm" method="POST" class="space-y-4">
            @csrf

            {{-- Deployment Context Badge --}}
            <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between mb-1">
                    <span id="return_asset_code" class="text-xs font-mono font-bold text-blue-900 bg-white px-2 py-0.5 rounded border border-blue-200">AST-000</span>
                    <span id="return_deployment_dest" class="text-xs text-blue-700 font-medium">Destination</span>
                </div>
                <h4 id="return_asset_name" class="text-sm font-bold text-gray-900 mb-0.5">Asset Name</h4>
                <p id="return_deployment_team" class="text-xs text-gray-600">Assigned Team</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Return Date" required>
                    <input type="date" name="return_date" id="return_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="Returned By / Received From">
                    <input type="text" name="returned_by" id="return_returned_by" placeholder="Person or team returning" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Asset Condition" required>
                    <select name="condition" id="return_condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="good" selected>Good (Ready for deployment)</option>
                        <option value="excellent">Excellent (Like new)</option>
                        <option value="fair">Fair (Wear observed)</option>
                        <option value="poor">Poor (Requires attention)</option>
                    </select>
                </x-form-group>

                <x-form-group label="Return Status" required>
                    <select name="status" id="return_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="received" selected>Received (Complete)</option>
                        <option value="damage_noted">Damage Noted</option>
                        <option value="incomplete_accessories">Incomplete Accessories</option>
                    </select>
                </x-form-group>
            </div>

            <x-form-group label="Actual Storage Location Upon Return">
                <input type="text" name="actual_location" id="return_actual_location" placeholder="e.g. Barangay Hall Storage, Warehouse A" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <x-form-group label="Damage Notes (if any)">
                <textarea name="damage_notes" id="return_damage_notes" rows="2" placeholder="Describe any observed damage or functional issues..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
            </x-form-group>

            <x-form-group label="Missing Accessories (if any)">
                <textarea name="missing_accessories" id="return_missing_accessories" rows="2" placeholder="List any missing cables, straps, cases, or parts..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    <i class="fas fa-check-circle mr-1.5"></i> Confirm Return
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('record-return-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- ======================================================== --}}
    {{-- MODAL 3: View Return Inspection Details                  --}}
    {{-- ======================================================== --}}
    <x-modal id="view-return-modal" title="Return Inspection Details">
        <div class="space-y-4 text-sm">
            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between">
                <div>
                    <span id="view_asset_code" class="text-xs font-mono font-bold text-gray-700 bg-white px-2 py-0.5 rounded border border-gray-200"></span>
                    <h4 id="view_asset_name" class="font-bold text-gray-900 mt-1"></h4>
                </div>
                <div id="view_return_condition_badge"></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-xs text-gray-500 block">Return Date</span>
                    <p id="view_return_date" class="font-medium text-gray-900 mt-0.5">-</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Returned By</span>
                    <p id="view_returned_by" class="font-medium text-gray-900 mt-0.5">-</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-xs text-gray-500 block">Return Status</span>
                    <p id="view_return_status" class="font-medium text-gray-900 mt-0.5">-</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Actual Storage Location</span>
                    <p id="view_actual_location" class="font-medium text-gray-900 mt-0.5">-</p>
                </div>
            </div>

            <div>
                <span class="text-xs text-gray-500 block">Damage Notes</span>
                <div id="view_damage_notes" class="mt-1 p-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 whitespace-pre-wrap">None reported</div>
            </div>

            <div>
                <span class="text-xs text-gray-500 block">Missing Accessories</span>
                <div id="view_missing_accessories" class="mt-1 p-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 whitespace-pre-wrap">None reported</div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <x-button variant="secondary" type="button" onclick="window.closeModal('view-return-modal')" class="w-full">
                    Close Details
                </x-button>
            </div>
        </div>
    </x-modal>

    {{-- ======================================================== --}}
    {{-- MODAL 4: Edit Deployment                                 --}}
    {{-- ======================================================== --}}
    <x-modal id="edit-deployment-modal" title="Edit Deployment Record">
        <form id="editDeploymentForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                <span class="text-xs text-gray-500 block">Asset</span>
                <p id="edit_asset_display" class="font-semibold text-gray-900 mt-0.5"></p>
            </div>

            <x-form-group label="Destination / Location" required>
                <input type="text" name="destination" id="edit_destination" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <x-form-group label="Assigned Team">
                <input type="text" name="assigned_team" id="edit_assigned_team" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Deployment Date" required>
                    <input type="date" name="deployment_date" id="edit_deployment_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Expected Return Date">
                    <input type="date" name="expected_return_date" id="edit_expected_return_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <x-form-group label="Status" required>
                <select name="status" id="edit_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="active">Active</option>
                    <option value="pending_return">Pending Return</option>
                    <option value="returned">Returned</option>
                </select>
            </x-form-group>

            <x-form-group label="Purpose">
                <textarea name="purpose" id="edit_purpose" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    Save Changes
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-deployment-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openCreateDeploymentModal() {
                window.openModal('create-deployment-modal');
            }

            function openRecordReturnModal(deployment) {
                const form = document.getElementById('recordReturnForm');
                form.action = '/deployment/' + deployment.id + '/return';

                document.getElementById('return_asset_code').textContent = deployment.asset ? deployment.asset.code : 'AST-N/A';
                document.getElementById('return_asset_name').textContent = deployment.asset ? deployment.asset.name : 'Unknown Asset';
                document.getElementById('return_deployment_dest').textContent = deployment.destination || 'Field';
                document.getElementById('return_deployment_team').textContent = 'Deployed to: ' + (deployment.assigned_team || 'Unassigned');
                document.getElementById('return_returned_by').value = deployment.assigned_team || '';

                window.openModal('record-return-modal');
            }

            function openViewReturnModal(returnRecord, assetName, assetCode) {
                document.getElementById('view_asset_code').textContent = assetCode || 'N/A';
                document.getElementById('view_asset_name').textContent = assetName || 'Asset';

                let returnDate = returnRecord.return_date || '';
                if (returnDate.includes('T')) returnDate = returnDate.split('T')[0];
                document.getElementById('view_return_date').textContent = returnDate || 'N/A';
                document.getElementById('view_returned_by').textContent = returnRecord.returned_by || 'N/A';
                document.getElementById('view_actual_location').textContent = returnRecord.actual_location || 'Not specified';
                document.getElementById('view_return_status').textContent = (returnRecord.status || 'received').replace(/_/g, ' ').toUpperCase();

                const cond = (returnRecord.condition || 'good').toUpperCase();
                document.getElementById('view_return_condition_badge').innerHTML = `<span class="px-2.5 py-1 text-xs font-semibold rounded-full ${cond === 'EXCELLENT' || cond === 'GOOD' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${cond}</span>`;

                document.getElementById('view_damage_notes').textContent = returnRecord.damage_notes || 'None reported';
                document.getElementById('view_missing_accessories').textContent = returnRecord.missing_accessories || 'None reported';

                window.openModal('view-return-modal');
            }

            function openEditDeploymentModal(deployment) {
                const form = document.getElementById('editDeploymentForm');
                form.action = '/deployment/' + deployment.id;

                const assetName = deployment.asset ? `${deployment.asset.code} — ${deployment.asset.name}` : 'Unknown Asset';
                document.getElementById('edit_asset_display').textContent = assetName;

                document.getElementById('edit_destination').value = deployment.destination || '';
                document.getElementById('edit_assigned_team').value = deployment.assigned_team || '';

                let depDate = deployment.deployment_date || '';
                if (depDate.includes('T')) depDate = depDate.split('T')[0];
                document.getElementById('edit_deployment_date').value = depDate;

                let expDate = deployment.expected_return_date || '';
                if (expDate.includes('T')) expDate = expDate.split('T')[0];
                document.getElementById('edit_expected_return_date').value = expDate;

                document.getElementById('edit_status').value = deployment.status || 'active';
                document.getElementById('edit_purpose').value = deployment.purpose || '';

                window.openModal('edit-deployment-modal');
            }
        </script>
    @endpush
@endsection
