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
        <div class="flex items-center gap-3">
            <button onclick="openGenericRecordReturnModal()" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium shadow-sm">
                <i class="fas fa-undo"></i>
                Record Return
            </button>
            <button onclick="openCreateDeploymentModal()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-sm">
                <i class="fas fa-plus"></i>
                Deploy Asset
            </button>
        </div>
    </div>

    {{-- Session Alerts --}}


    {{-- Dashboard Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total --}}
        <a href="{{ route('deployment.index') }}" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all flex items-center justify-between group block">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 group-hover:text-blue-600 transition-colors">Total Records</p>
                <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['total'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </a>

        {{-- Active --}}
        <a href="{{ route('deployment.index', ['status' => 'active']) }}" class="bg-white rounded-xl p-5 border border-emerald-200 bg-emerald-50/20 shadow-sm hover:border-emerald-400 hover:shadow-md transition-all flex items-center justify-between group block" title="Filter by Active">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Active in Field</p>
                <p class="text-2xl font-bold text-emerald-700">{{ $statusCounts['active'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <i class="fas fa-truck"></i>
            </div>
        </a>

        {{-- Pending Return --}}
        <a href="{{ route('deployment.index', ['status' => 'pending_return']) }}" class="bg-white rounded-xl p-5 border border-amber-200 bg-amber-50/20 shadow-sm hover:border-amber-400 hover:shadow-md transition-all flex items-center justify-between group block" title="Filter by Pending Return">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-1">Pending Return</p>
                <p class="text-2xl font-bold text-amber-700">{{ $statusCounts['pending_return'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition-colors">
                <i class="fas fa-clock"></i>
            </div>
        </a>

        {{-- Returned --}}
        <a href="{{ route('deployment.index', ['status' => 'returned']) }}" class="bg-white rounded-xl p-5 border border-purple-200 bg-purple-50/20 shadow-sm hover:border-purple-400 hover:shadow-md transition-all flex items-center justify-between group block" title="Filter by Returned">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-700 mb-1">Returned</p>
                <p class="text-2xl font-bold text-purple-700">{{ $statusCounts['returned'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xl group-hover:bg-purple-600 group-hover:text-white transition-colors">
                <i class="fas fa-circle-check"></i>
            </div>
        </a>
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
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Destination & Borrower</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Deployment Date</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Expected Return</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Inspection</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($deployments as $deployment)
                        <tr class="hover:bg-blue-50/50 cursor-pointer transition-colors group"
                            onclick="if(!event.target.closest('button, a, form, input, select')) openDeploymentDetailsModal(@json($deployment));"
                            title="Click to view full details for {{ $deployment->asset->name ?? 'Equipment' }}">
                            {{-- Asset Info --}}
                            <td class="px-6 py-4 align-middle text-left">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 rounded-lg bg-blue-50 text-blue-600 mt-1">
                                        <i class="fas fa-box text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2 py-0.5 text-xs font-mono font-semibold rounded bg-gray-100 text-gray-800 border border-gray-200 mb-1">
                                            {{ $deployment->asset->code ?? 'N/A' }}
                                        </span>
                                        <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $deployment->asset->name ?? 'Unknown Asset' }}</p>
                                        <p class="text-xs text-gray-500">{{ $deployment->asset->category->name ?? 'Uncategorized' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Destination & Borrower --}}
                            <td class="px-6 py-4 align-middle text-sm text-left">
                                <p class="font-medium text-gray-900 flex items-center gap-1.5">
                                    <i class="fas fa-location-dot text-red-500 text-xs"></i>
                                    {{ $deployment->destination }}
                                </p>
                                <p class="text-gray-700 text-xs mt-1 flex items-center gap-1.5 font-medium">
                                    @if($deployment->borrower_type === 'resident')
                                        <i class="fas fa-house-user text-purple-600 text-xs"></i>
                                        <span>{{ $deployment->borrower_name ?: ($deployment->responsible_person ?? 'Resident Borrower') }}</span>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] bg-purple-100 text-purple-800 font-semibold">Resident</span>
                                    @else
                                        <i class="fas fa-user-shield text-blue-600 text-xs"></i>
                                        <span>{{ $deployment->responsible_person ?: ($deployment->assigned_team ?? 'Field Team') }}</span>
                                    @endif
                                </p>
                                @if($deployment->purpose)
                                    <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs" title="{{ $deployment->purpose }}">
                                        {{ $deployment->purpose }}
                                    </p>
                                @endif
                            </td>

                            {{-- Deployment Date --}}
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 text-left">
                                {{ $deployment->deployment_date ? \Carbon\Carbon::parse($deployment->deployment_date)->format('M d, Y') : 'N/A' }}
                            </td>

                            {{-- Expected Return & Overdue Alert --}}
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-left">
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
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
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
                            <td class="px-6 py-4 align-middle text-sm text-left">
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
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Return Equipment Button (for active/pending deployments) --}}
                                    @if($deployment->status !== 'returned')
                                        <button 
                                            type="button"
                                            onclick='openRecordReturnModal(@json($deployment))' 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold transition-colors shadow-xs" 
                                            title="Record Return of this Borrowed Equipment"
                                        >
                                            <i class="fas fa-undo"></i>
                                            <span>Return</span>
                                        </button>
                                    @else
                                        {{-- View Return Details --}}
                                        @if($deployment->returnRecord)
                                            <button 
                                                type="button"
                                                onclick='openViewReturnModal(@json($deployment->returnRecord), @js($deployment->asset->name ?? 'Asset'), @js($deployment->asset->code ?? 'N/A'))' 
                                                class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 transition-colors" 
                                                title="View Return Inspection Details"
                                            >
                                                <i class="fas fa-clipboard-check"></i>
                                            </button>
                                        @endif
                                    @endif

                                    {{-- View Details Modal --}}
                                    <button 
                                        type="button"
                                        onclick='openDeploymentDetailsModal(@json($deployment))' 
                                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors" 
                                        title="View Full Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    {{-- Edit Deployment --}}
                                    <button 
                                        type="button"
                                        onclick='openEditDeploymentModal(@json($deployment))' 
                                        class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600 transition-colors" 
                                        title="Edit Deployment"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Delete Deployment --}}
                                    <form action="{{ route('deployment.destroy', $deployment) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Deployment: {{ addslashes($deployment->asset->name ?? 'Record') }} ({{ addslashes($deployment->destination ?? 'Field') }})', 'This deployment record will be permanently deleted from the dispatch logs.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-600 transition-colors" title="Delete">
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
        <form action="{{ route('deployment.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ borrowerType: 'personnel' }">
            @csrf

            <x-form-group label="Select Available Asset ({{ $availableAssets->count() }} Available)" required>
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

            {{-- Borrower Type Selector --}}
            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Borrower / Deployment Type</label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                        <input type="radio" name="borrower_type" value="personnel" x-model="borrowerType" class="text-blue-600 focus:ring-blue-500">
                        <span><i class="fas fa-user-shield text-blue-600 mr-1"></i>Barangay Personnel / Responder</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                        <input type="radio" name="borrower_type" value="resident" x-model="borrowerType" class="text-purple-600 focus:ring-purple-500">
                        <span><i class="fas fa-house-user text-purple-600 mr-1"></i>Barangay Resident</span>
                    </label>
                </div>
            </div>

            {{-- Resident Borrower Fields --}}
            <div x-show="borrowerType === 'resident'" x-cloak class="p-3 bg-purple-50/60 rounded-lg border border-purple-200 space-y-3">
                <p class="text-xs font-semibold text-purple-900 flex items-center gap-1.5">
                    <i class="fas fa-id-card text-purple-600"></i> Resident Borrower Credentials
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <x-form-group label="Resident Full Name">
                        <input type="text" name="borrower_name" placeholder="e.g. Maria Corazon Santos" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                    </x-form-group>
                    <x-form-group label="Contact Number">
                        <input type="text" name="borrower_contact" placeholder="e.g. 0917-123-4567" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                    </x-form-group>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-1">
                        <x-form-group label="Valid ID Type">
                            <select name="borrower_id_type" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white">
                                <option value="Barangay ID">Barangay ID</option>
                                <option value="PhilHealth">PhilHealth</option>
                                <option value="UMID">UMID / SSS</option>
                                <option value="Driver's License">Driver's License</option>
                                <option value="Voter's ID">Voter's ID</option>
                                <option value="Passport">Passport</option>
                            </select>
                        </x-form-group>
                    </div>
                    <div class="md:col-span-2">
                        <x-form-group label="Valid ID Number">
                            <input type="text" name="borrower_id_number" placeholder="e.g. BRGY-2026-9812" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                        </x-form-group>
                    </div>
                </div>
                <x-form-group label="Residential Address">
                    <input type="text" name="borrower_address" placeholder="e.g. House 14, Purok 3, Barangay Proper" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                </x-form-group>
            </div>

            <x-form-group label="Destination / Facility" required>
                <input type="text" name="destination" required placeholder="e.g. Purok A Evacuation Center, Zone 3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="borrowerType === 'personnel'">
                <x-form-group label="Assigned Team / Responder">
                    <input type="text" name="assigned_team" placeholder="e.g. Alpha Rescue Team, Tanod Night Watch" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="Responsible Custodian / Person">
                    <input type="text" name="responsible_person" placeholder="e.g. Officer Juan Dela Cruz" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Deployment Date" required>
                    <input type="date" name="deployment_date" id="create_deployment_date" value="{{ date('Y-m-d') }}" onchange="document.getElementById('create_expected_return_date').min = this.value" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="Expected Return Date">
                    <input type="date" name="expected_return_date" id="create_expected_return_date" min="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>
            </div>

            <x-form-group label="Purpose / Mission Description">
                <textarea name="purpose" rows="2" placeholder="State the reason or operation for this deployment..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
            </x-form-group>

            {{-- Signed Form / Valid ID / Slip File Input --}}
            <x-receipt-input 
                label="Signed Borrowing Slip, Resident ID, or Requisition Document" 
                help="Upload signed borrowing form, mission order, or resident ID photocopy (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." 
                :showReference="false" 
                icon="fas fa-file-signature" 
            />

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
        <form id="recordReturnForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Deployed Item Selector --}}
            <div id="return_item_selector_wrapper" class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                    Deployed / Borrowed Equipment to Return <span class="text-red-500">*</span>
                </label>
                @if($activeDeployments->isEmpty())
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs flex items-center gap-2">
                        <i class="fas fa-triangle-exclamation text-amber-600 text-sm"></i>
                        <span>There are currently no active deployed equipment to return.</span>
                    </div>
                @else
                    <select id="generic_deployment_select" onchange="onDeploymentSelectChange(this)" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-gray-800">
                        <option value="">-- Choose from {{ $activeDeployments->count() }} Deployed Equipment --</option>
                        @foreach($activeDeployments as $actDep)
                            <option value="{{ $actDep->id }}" data-deployment="{{ base64_encode(json_encode($actDep)) }}">
                                [{{ $actDep->asset->code ?? 'AST' }}] {{ $actDep->asset->name ?? 'Equipment' }} — Deployed: {{ $actDep->deployment_date ? \Carbon\Carbon::parse($actDep->deployment_date)->format('M d, Y') : 'Active' }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Deployment Context Badge --}}
            <div id="return_context_card" class="p-3.5 bg-blue-50/80 border border-blue-200 rounded-lg space-y-1">
                <div class="flex items-center justify-between">
                    <span id="return_asset_code" class="text-xs font-mono font-bold text-blue-900 bg-white px-2 py-0.5 rounded border border-blue-200">AST-000</span>
                    <span id="return_deployment_dest" class="text-xs text-blue-700 font-medium">Destination</span>
                </div>
                <h4 id="return_asset_name" class="text-sm font-bold text-gray-900">Asset Name</h4>
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <p id="return_deployment_team">Assigned Team</p>
                    <p id="return_deployment_date" class="text-gray-500"></p>
                </div>
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

            {{-- Return Inspection Photo / Document Upload --}}
            <x-receipt-input 
                label="Return Inspection Photo, Turn-In Slip, or Document" 
                help="Upload return inspection photos or signed turn-in slip (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." 
                :showReference="false" 
                icon="fas fa-clipboard-check" 
            />

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" id="submit_return_btn" class="flex-1" :disabled="$activeDeployments->isEmpty()">
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Assigned Team">
                    <input type="text" name="assigned_team" id="edit_assigned_team" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Responsible Custodian / Person">
                    <input type="text" name="responsible_person" id="edit_responsible_person" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

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

    {{-- ======================================================== --}}
    {{-- MODAL 5: View Full Deployment & Return Dossier            --}}
    {{-- ======================================================== --}}
    <x-modal id="view-deployment-modal" title="Deployment & Return Details">
        <div class="space-y-5 text-sm">
            {{-- Asset Identification Header --}}
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-lg flex-shrink-0 mt-0.5">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span id="dossier_asset_code" class="text-xs font-mono font-bold text-blue-800 bg-white px-2 py-0.5 rounded border border-blue-200">AST-000</span>
                            <span id="dossier_asset_serial" class="text-xs text-gray-500 font-mono"></span>
                        </div>
                        <h4 id="dossier_asset_name" class="font-bold text-gray-900 text-base mt-1">Equipment Name</h4>
                        <p id="dossier_asset_category" class="text-xs text-gray-500 mt-0.5">Category</p>
                    </div>
                </div>
                <div id="dossier_status_badge" class="flex items-center">
                    {{-- Status Badge injected dynamically --}}
                </div>
            </div>

            {{-- Deployment / Borrowing Information --}}
            <div class="border border-gray-200 rounded-xl p-4 bg-white space-y-3">
                <h5 class="text-xs font-bold uppercase tracking-wider text-blue-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i class="fas fa-truck-ramp-box text-blue-600"></i> Mission & Borrowing Record
                </h5>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <span class="text-xs text-gray-500 block">Destination / Facility</span>
                        <p id="dossier_destination" class="font-semibold text-gray-900 mt-0.5"></p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block">Borrower Classification</span>
                        <div id="dossier_borrower_type" class="mt-0.5 text-xs font-medium"></div>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block">Responsible Custodian / Borrower</span>
                        <p id="dossier_borrower_name" class="font-semibold text-gray-900 mt-0.5"></p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block">Assigned Unit / Team</span>
                        <p id="dossier_assigned_team" class="font-medium text-gray-800 mt-0.5"></p>
                    </div>
                </div>

                {{-- Resident Borrower Specific Credentials --}}
                <div id="dossier_resident_credentials" class="p-3 bg-purple-50/60 border border-purple-200 rounded-lg space-y-2 hidden">
                    <span class="text-xs font-bold text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-id-card text-purple-600"></i> Resident Borrower Credentials
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                        <div>
                            <span class="text-gray-500 block">Contact Phone:</span>
                            <span id="dossier_borrower_contact" class="font-semibold text-gray-900"></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Valid ID:</span>
                            <span id="dossier_borrower_id" class="font-semibold text-gray-900"></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Residential Address:</span>
                            <span id="dossier_borrower_address" class="font-medium text-gray-800"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2 border-t border-gray-100">
                    <div>
                        <span class="text-xs text-gray-500 block">Deployment Date</span>
                        <p id="dossier_deployment_date" class="font-medium text-gray-900 mt-0.5"></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Expected Return Date</span>
                        <p id="dossier_expected_return_date" class="font-medium text-gray-900 mt-0.5"></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Approving Officer</span>
                        <p id="dossier_approved_by" class="font-medium text-gray-900 mt-0.5"></p>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-gray-500 block">Purpose / Operation Notes</span>
                    <p id="dossier_purpose" class="text-xs text-gray-700 bg-gray-50 p-2.5 rounded-lg border border-gray-200 mt-1 leading-relaxed"></p>
                </div>

                {{-- Attached Borrowing Documents / Signed Slips --}}
                <div id="dossier_receipts_container" class="hidden pt-1">
                    <span class="text-xs text-gray-500 block mb-1.5 font-medium">Attached Requisition Slip / Documents</span>
                    <div id="dossier_receipts_list" class="flex flex-wrap gap-2"></div>
                </div>
            </div>

            {{-- Return Record & Inspection Details (Only shown if returned or return record exists) --}}
            <div id="dossier_return_section" class="border border-emerald-200 rounded-xl p-4 bg-emerald-50/20 space-y-3 hidden">
                <h5 class="text-xs font-bold uppercase tracking-wider text-emerald-900 border-b border-emerald-200/60 pb-2 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-emerald-600"></i> Return & Turn-in Inspection
                    </span>
                    <span id="dossier_return_status" class="text-xs uppercase px-2 py-0.5 rounded font-bold bg-white text-emerald-800 border border-emerald-200"></span>
                </h5>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <span class="text-xs text-gray-500 block">Return Date</span>
                        <p id="dossier_return_date" class="font-semibold text-gray-900 mt-0.5"></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Returned By</span>
                        <p id="dossier_returned_by" class="font-medium text-gray-900 mt-0.5"></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Receiving Officer</span>
                        <p id="dossier_received_by" class="font-medium text-gray-900 mt-0.5"></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Post-Return Condition</span>
                        <div id="dossier_return_condition" class="mt-1"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-emerald-200/50">
                    <div>
                        <span class="text-xs text-gray-500 block">Actual Storage Location</span>
                        <p id="dossier_actual_location" class="font-medium text-gray-900 mt-0.5"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <span class="text-xs text-gray-500 block">Damage Notes</span>
                        <div id="dossier_damage_notes" class="mt-1 p-2.5 bg-white border border-gray-200 rounded-lg text-xs text-gray-700 whitespace-pre-wrap">None reported</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Missing Accessories</span>
                        <div id="dossier_missing_accessories" class="mt-1 p-2.5 bg-white border border-gray-200 rounded-lg text-xs text-gray-700 whitespace-pre-wrap">None reported</div>
                    </div>
                </div>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="pt-4 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <button type="button" id="dossier_process_return_btn" onclick="closeDeploymentDetailsAndReturn()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors shadow-sm">
                        <i class="fas fa-undo"></i>
                        <span>Process Return of Asset</span>
                    </button>
                    <button type="button" onclick="closeDeploymentDetailsAndEdit()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-colors">
                        <i class="fas fa-edit"></i>
                        <span>Edit Deployment</span>
                    </button>
                </div>
                <x-button variant="secondary" type="button" onclick="window.closeModal('view-deployment-modal')">
                    Close Details
                </x-button>
            </div>
        </div>
    </x-modal>

    @push('scripts')
        <script>
            let currentDeploymentModalData = null;

            function openCreateDeploymentModal() {
                window.openModal('create-deployment-modal');
            }

            function openGenericRecordReturnModal() {
                const select = document.getElementById('generic_deployment_select');
                if (select) {
                    if (select.options.length > 1) {
                        if (!select.value) {
                            select.selectedIndex = 1;
                        }
                        onDeploymentSelectChange(select);
                    } else {
                        const form = document.getElementById('recordReturnForm');
                        form.action = '#';
                        const submitBtn = document.getElementById('submit_return_btn');
                        if (submitBtn) submitBtn.disabled = true;
                    }
                }
                window.openModal('record-return-modal');
            }

            function onDeploymentSelectChange(selectElem) {
                const selectedOption = selectElem.options[selectElem.selectedIndex];
                if (!selectedOption || !selectedOption.value) return;

                try {
                    const rawData = selectedOption.getAttribute('data-deployment');
                    if (rawData) {
                        const depData = JSON.parse(atob(rawData));
                        populateReturnModalFields(depData);
                    }
                } catch(e) {
                    console.error('Error parsing deployment option data', e);
                }
            }

            function openRecordReturnModal(deployment) {
                const select = document.getElementById('generic_deployment_select');
                if (select) {
                    select.value = deployment.id;
                }
                populateReturnModalFields(deployment);
                window.openModal('record-return-modal');
            }

            function populateReturnModalFields(deployment) {
                const form = document.getElementById('recordReturnForm');
                form.action = '/deployment/' + deployment.id + '/return';

                const submitBtn = document.getElementById('submit_return_btn');
                if (submitBtn) submitBtn.disabled = false;

                const asset = deployment.asset || {};
                document.getElementById('return_asset_code').textContent = asset.code || 'AST-N/A';
                document.getElementById('return_asset_name').textContent = asset.name || 'Unknown Asset';
                document.getElementById('return_deployment_dest').textContent = deployment.destination || 'Field';

                const borrowerDisplay = deployment.borrower_name || deployment.responsible_person || deployment.assigned_team || 'Field Team';
                document.getElementById('return_deployment_team').textContent = 'Borrower / Custodian: ' + borrowerDisplay;
                document.getElementById('return_returned_by').value = borrowerDisplay;

                let depDate = deployment.deployment_date || '';
                if (depDate.includes('T')) depDate = depDate.split('T')[0];
                const depDateElem = document.getElementById('return_deployment_date');
                if (depDateElem) {
                    depDateElem.textContent = depDate ? 'Deployed: ' + depDate : '';
                }

                if (depDate) {
                    const retDateField = document.getElementById('return_date');
                    retDateField.min = depDate;
                    if (retDateField.value < depDate) {
                        retDateField.value = depDate;
                    }
                }
            }

            function openDeploymentDetailsModal(deployment) {
                currentDeploymentModalData = deployment;

                // Asset Info
                const asset = deployment.asset || {};
                document.getElementById('dossier_asset_code').textContent = asset.code || 'AST-N/A';
                document.getElementById('dossier_asset_name').textContent = asset.name || 'Unknown Equipment';
                document.getElementById('dossier_asset_category').textContent = (asset.category ? asset.category.name : 'Uncategorized') + (asset.type ? ' · ' + asset.type.name : '');
                document.getElementById('dossier_asset_serial').textContent = asset.serial_number ? 'S/N: ' + asset.serial_number : 'Unserialized';

                // Status Badge
                const status = deployment.status || 'active';
                const statusBadges = {
                    'active': '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Active in Field</span>',
                    'pending_return': '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Pending Return</span>',
                    'returned': '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800 border border-purple-200">Returned</span>'
                };
                let statusHtml = statusBadges[status] || `<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-800">${status}</span>`;

                let expDate = deployment.expected_return_date || '';
                if (expDate.includes('T')) expDate = expDate.split('T')[0];
                const today = new Date().toISOString().split('T')[0];
                if (status !== 'returned' && expDate && expDate < today) {
                    statusHtml += ' <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-200 ml-1.5">Overdue</span>';
                }
                document.getElementById('dossier_status_badge').innerHTML = statusHtml;

                // Destination & Borrower
                document.getElementById('dossier_destination').textContent = deployment.destination || 'Not specified';

                const isResident = deployment.borrower_type === 'resident';
                const borrowerTypeElem = document.getElementById('dossier_borrower_type');
                if (borrowerTypeElem) {
                    borrowerTypeElem.innerHTML = isResident
                        ? '<span class="inline-flex items-center gap-1 text-purple-700 font-semibold"><i class="fas fa-house-user"></i> Barangay Resident</span>'
                        : '<span class="inline-flex items-center gap-1 text-blue-700 font-semibold"><i class="fas fa-user-shield"></i> Barangay Responder / Personnel</span>';
                }

                document.getElementById('dossier_borrower_name').textContent = deployment.borrower_name || deployment.responsible_person || 'Not specified';
                document.getElementById('dossier_assigned_team').textContent = deployment.assigned_team || 'No team assigned';

                // Resident specific credentials section
                const residentSection = document.getElementById('dossier_resident_credentials');
                if (residentSection) {
                    if (isResident) {
                        residentSection.classList.remove('hidden');
                        document.getElementById('dossier_borrower_contact').textContent = deployment.borrower_contact || 'None';
                        document.getElementById('dossier_borrower_id').textContent = (deployment.borrower_id_type || 'ID') + ': ' + (deployment.borrower_id_number || 'None');
                        document.getElementById('dossier_borrower_address').textContent = deployment.borrower_address || 'None';
                    } else {
                        residentSection.classList.add('hidden');
                    }
                }

                // Dates
                let depDate = deployment.deployment_date || '';
                if (depDate.includes('T')) depDate = depDate.split('T')[0];
                document.getElementById('dossier_deployment_date').textContent = depDate || 'Not specified';
                document.getElementById('dossier_expected_return_date').textContent = expDate || 'Open-ended';

                // Approving Officer & Condition Before
                const approvedBy = deployment.approved_by_user ? deployment.approved_by_user.name : (deployment.user ? deployment.user.name : 'Authorized Official');
                document.getElementById('dossier_approved_by').textContent = approvedBy;
                document.getElementById('dossier_condition_before').textContent = (deployment.condition_before || asset.condition || 'Good').toUpperCase();
                document.getElementById('dossier_purpose').textContent = deployment.purpose || 'None provided';

                // Attached receipts / borrow slip
                const receiptsContainer = document.getElementById('dossier_receipts_container');
                const receiptsList = document.getElementById('dossier_receipts_list');
                if (receiptsContainer && receiptsList) {
                    if (deployment.receipts && deployment.receipts.length > 0) {
                        receiptsContainer.classList.remove('hidden');
                        receiptsList.innerHTML = deployment.receipts.map(r => `
                            <a href="${r.file_url || ('/storage/' + r.file_path)}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-medium text-blue-700 transition-colors">
                                <i class="fas fa-file text-gray-500"></i>
                                <span>${r.original_name || 'Signed Form / Document'}</span>
                                <i class="fas fa-arrow-up-right-from-square text-[10px] text-gray-400"></i>
                            </a>
                        `).join('');
                    } else {
                        receiptsContainer.classList.add('hidden');
                        receiptsList.innerHTML = '';
                    }
                }

                // Return Inspection Section
                const returnSection = document.getElementById('dossier_return_section');
                const processReturnBtn = document.getElementById('dossier_process_return_btn');
                if (deployment.status === 'returned' || deployment.return_record) {
                    if (returnSection) returnSection.classList.remove('hidden');
                    if (processReturnBtn) processReturnBtn.classList.add('hidden');

                    const ret = deployment.return_record || {};
                    let retDate = ret.return_date || '';
                    if (retDate.includes('T')) retDate = retDate.split('T')[0];
                    document.getElementById('dossier_return_date').textContent = retDate || 'Recorded';
                    document.getElementById('dossier_returned_by').textContent = ret.returned_by || 'Not specified';
                    document.getElementById('dossier_received_by').textContent = (ret.received_by_user ? ret.received_by_user.name : 'Receiving Officer');
                    document.getElementById('dossier_actual_location').textContent = ret.actual_location || 'Not specified';
                    document.getElementById('dossier_return_status').textContent = (ret.status || 'received').replace(/_/g, ' ').toUpperCase();

                    const cond = (ret.condition || 'good').toUpperCase();
                    document.getElementById('dossier_return_condition').innerHTML = `<span class="px-2 py-0.5 text-xs font-bold rounded-full ${cond === 'EXCELLENT' || cond === 'GOOD' ? 'bg-emerald-100 text-emerald-800' : (cond === 'FAIR' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')}">${cond}</span>`;

                    document.getElementById('dossier_damage_notes').textContent = ret.damage_notes || 'None reported';
                    document.getElementById('dossier_missing_accessories').textContent = ret.missing_accessories || 'None reported';
                } else {
                    if (returnSection) returnSection.classList.add('hidden');
                    if (processReturnBtn) processReturnBtn.classList.remove('hidden');
                }

                window.openModal('view-deployment-modal');
            }

            function closeDeploymentDetailsAndReturn() {
                window.closeModal('view-deployment-modal');
                if (currentDeploymentModalData) {
                    openRecordReturnModal(currentDeploymentModalData);
                }
            }

            function closeDeploymentDetailsAndEdit() {
                window.closeModal('view-deployment-modal');
                if (currentDeploymentModalData) {
                    openEditDeploymentModal(currentDeploymentModalData);
                }
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
                document.getElementById('edit_responsible_person').value = deployment.responsible_person || '';

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
