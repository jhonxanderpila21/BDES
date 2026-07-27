@extends('layouts.admin')

@section('title', 'Incident Reports - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Incident Reports</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Incident Reports']
            ]" />
        </div>
        <button onclick="openIncidentModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            File Incident
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('incident-reports.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search incidents..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Severity"
            :options="['critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low']"
        />
        <x-filter-dropdown 
            label="Filter by Status"
            :options="['open' => 'Open', 'investigating' => 'Investigating', 'resolved' => 'Resolved']"
        />
    </div>

    {{-- Incidents Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Incident Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reported By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($incidentReports as $incident)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $incident->asset->name ?? 'Unknown Asset' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $incident->reported_by ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $incident->report_date ? \Carbon\Carbon::parse($incident->report_date)->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $severityColor = match($incident->severity) {
                                        'critical' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'warning',
                                        'low' => 'success',
                                        default => 'secondary'
                                    };
                                @endphp
                                <x-badge variant="{{ $severityColor }}" size="sm">{{ ucfirst($incident->severity) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = match($incident->status) {
                                        'resolved' => 'success',
                                        'investigating' => 'warning',
                                        'open' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <x-badge variant="{{ $statusColor }}" size="sm">{{ ucfirst($incident->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button onclick="editIncident({{ $incident->id }})" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('incident-reports.destroy', $incident) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this incident report?');">
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
                                <x-empty-state icon="fas fa-exclamation-triangle" title="No incidents found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $incidentReports->links() }}
        </div>
    </x-card>

    {{-- Incident Modal --}}
    <x-modal id="incident-modal" title="File Incident Report">
        <form class="space-y-4">
            <x-form-group label="Asset" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Select Asset</option>
                    <option>Fire Extinguisher #001</option>
                    <option>Rescue Rope #002</option>
                    <option>Defibrillator #001</option>
                </select>
            </x-form-group>

            <x-form-group label="Incident Type" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Loss/Theft</option>
                    <option>Damage</option>
                    <option>Malfunction</option>
                    <option>Expired Items</option>
                    <option>Misuse</option>
                    <option>Other</option>
                </select>
            </x-form-group>

            <x-form-group label="Severity" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Critical</option>
                    <option>High</option>
                    <option>Medium</option>
                    <option>Low</option>
                </select>
            </x-form-group>

            <x-form-group label="Reported By" required>
                <input type="text" placeholder="Name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Description" required>
                <textarea placeholder="Describe the incident..." rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1">File Report</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('incident-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openIncidentModal() {
                window.openModal('incident-modal');
            }
            function viewIncident(asset) { alert(`View: ${asset}`); }
            function editIncident(asset) { alert(`Edit: ${asset}`); }
            function handlePageChange(page) { alert(`Page: ${page}`); }
        </script>
    @endpush
@endsection
