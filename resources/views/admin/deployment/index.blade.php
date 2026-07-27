@extends('layouts.admin')

@section('title', 'Deployment - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Deployment</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Deployment']
            ]" />
        </div>
        <button onclick="openDeploymentModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Create Deployment
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('deployment.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by asset or team..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Status"
            :options="['pending' => 'Pending', 'active' => 'Active', 'returned' => 'Returned']"
        />
    </div>

    {{-- Deployment Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Destination</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Assigned Team</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Deployment Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Expected Return</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($deployments as $deployment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $deployment->asset->name ?? 'Unknown Asset' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $deployment->destination }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $deployment->assigned_team ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $deployment->deployment_date ? \Carbon\Carbon::parse($deployment->deployment_date)->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $deployment->expected_return_date ? \Carbon\Carbon::parse($deployment->expected_return_date)->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = match($deployment->status) {
                                        'active' => 'success',
                                        'pending_return' => 'warning',
                                        'returned' => 'secondary',
                                        default => 'info'
                                    };
                                @endphp
                                <x-badge variant="{{ $statusColor }}" size="sm">{{ ucfirst(str_replace('_', ' ', $deployment->status)) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button onclick="editDeployment({{ $deployment->id }})" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('deployment.destroy', $deployment) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this deployment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-red-600">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8">
                                <x-empty-state icon="fas fa-truck" title="No deployments found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $deployments->links() }}
        </div>
    </x-card>

    {{-- Deployment Modal --}}
    <x-modal id="deployment-modal" title="Create Deployment">
        <form class="space-y-4">
            <x-form-group label="Asset" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Select Asset</option>
                    <option>Fire Extinguisher #001</option>
                    <option>First Aid Kit #005</option>
                    <option>Rescue Rope #002</option>
                </select>
            </x-form-group>

            <x-form-group label="Quantity" required>
                <input type="number" placeholder="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Destination" required>
                <input type="text" placeholder="e.g., Purok A, Community Center" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Assigned Team" required>
                <input type="text" placeholder="Team name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Purpose" required>
                <textarea placeholder="Reason for deployment" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Deployment Date" required>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Expected Return Date" required>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1">Create Deployment</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('deployment-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openDeploymentModal() { window.openModal('deployment-modal'); }
            function viewDeployment(asset) { alert(`View: ${asset}`); }
            function editDeployment(asset) { alert(`Edit: ${asset}`); }
            function deleteDeployment(asset) { if(confirm(`Delete?`)) alert(`Deleted`); }
            function handlePageChange(page) { alert(`Page: ${page}`); }
        </script>
    @endpush
@endsection
