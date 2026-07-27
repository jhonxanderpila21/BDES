@extends('layouts.admin')

@section('title', 'Maintenance - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Maintenance Records</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Maintenance']
            ]" />
        </div>
        <button onclick="openMaintenanceModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Schedule Maintenance
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('maintenance.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by asset or technician..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Status"
            :options="['scheduled' => 'Scheduled', 'in-progress' => 'In Progress', 'completed' => 'Completed']"
        />
    </div>

    {{-- Maintenance Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Maintenance Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Technician</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Notes</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($maintenances as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $item->asset->name ?? 'Unknown Asset' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="info" size="sm">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->technician ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->scheduled_date ? \Carbon\Carbon::parse($item->scheduled_date)->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($item->notes, 30) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = match($item->status) {
                                        'completed' => 'success',
                                        'in_progress' => 'warning',
                                        'scheduled' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <x-badge variant="{{ $statusColor }}" size="sm">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button onclick="editMaintenance({{ $item->id }})" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('maintenance.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this maintenance record?');">
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
                                <x-empty-state icon="fas fa-wrench" title="No maintenance records found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $maintenances->links() }}
        </div>
    </x-card>

    {{-- Maintenance Modal --}}
    <x-modal id="maintenance-modal" title="Schedule Maintenance">
        <form class="space-y-4">
            <x-form-group label="Asset" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Select Asset</option>
                    <option>Fire Extinguisher #001</option>
                    <option>Generator #002</option>
                    <option>Pump #001</option>
                </select>
            </x-form-group>

            <x-form-group label="Maintenance Type" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Refill & Service</option>
                    <option>Oil Change</option>
                    <option>Inspection</option>
                    <option>Overhaul</option>
                    <option>Repair</option>
                </select>
            </x-form-group>

            <x-form-group label="Assigned Technician" required>
                <input type="text" placeholder="Technician name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Scheduled Date" required>
                <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Estimated Duration">
                <input type="text" placeholder="e.g., 2 hours, 1 day" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Notes">
                <textarea placeholder="Additional notes..." rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1">Schedule Maintenance</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('maintenance-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openMaintenanceModal() {
                window.openModal('maintenance-modal');
            }
            function editMaintenance(asset) { alert(`Edit: ${asset}`); }
            function deleteMaintenance(asset) { if(confirm(`Delete?`)) alert(`Deleted`); }
            function handlePageChange(page) { alert(`Page: ${page}`); }
        </script>
    @endpush
@endsection
