@extends('layouts.admin')

@section('title', 'Inspection - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Inspections</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Inspection']
            ]" />
        </div>
        <button onclick="openInspectionModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Record Inspection
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('inspection.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by asset or inspector..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Result"
            :options="['passed' => 'Passed', 'failed' => 'Failed', 'pending' => 'Pending']"
        />
    </div>

    {{-- Inspections Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Inspector</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Inspection Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Next Due</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($inspections as $inspection)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $inspection->asset->name ?? 'Unknown Asset' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $inspection->inspector ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $inspection->next_due_date ? \Carbon\Carbon::parse($inspection->next_due_date)->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $resultColor = $inspection->result === 'passed' ? 'success' : ($inspection->result === 'failed' ? 'danger' : 'warning');
                                @endphp
                                <x-badge variant="{{ $resultColor }}" size="sm">{{ ucfirst(str_replace('_', ' ', $inspection->result)) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = match($inspection->status) {
                                        'completed' => 'success',
                                        'scheduled' => 'info',
                                        'overdue' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <x-badge variant="{{ $statusColor }}" size="sm">{{ ucfirst($inspection->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button onclick="editInspection({{ $inspection->id }})" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('inspection.destroy', $inspection) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this inspection record?');">
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
                                <x-empty-state icon="fas fa-clipboard-check" title="No inspections found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $inspections->links() }}
        </div>
    </x-card>

    {{-- Inspection Modal --}}
    <x-modal id="inspection-modal" title="Record Inspection">
        <form class="space-y-4">
            <x-form-group label="Asset" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Select Asset</option>
                    <option>Fire Extinguisher #001</option>
                    <option>Defibrillator #003</option>
                    <option>Rescue Rope #002</option>
                </select>
            </x-form-group>

            <x-form-group label="Inspector" required>
                <input type="text" placeholder="Inspector name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Inspection Date" required>
                <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Result" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Passed</option>
                    <option>Failed</option>
                    <option>Conditional Pass</option>
                </select>
            </x-form-group>

            <x-form-group label="Findings">
                <textarea placeholder="Inspection findings..." rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <x-form-group label="Next Inspection Due" required>
                <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                    <x-button variant="primary" class="flex-1">Save Inspection</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('inspection-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openInspectionModal() {
                    window.openModal('inspection-modal');
            }
            function viewInspection(asset) { alert(`View: ${asset}`); }
            function editInspection(asset) { alert(`Edit: ${asset}`); }
            function handlePageChange(page) { alert(`Page: ${page}`); }
        </script>
    @endpush
@endsection
