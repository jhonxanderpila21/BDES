@extends('layouts.admin')

@section('title', 'Assets - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <i class="fas fa-boxes-stacked text-blue-600"></i>
                Asset Management
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Assets']
            ]" />
        </div>
        <button onclick="openAddAssetModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Asset
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('assets.search') }}" method="GET">
                <x-search-bar name="q" value="{{ request('q') }}" placeholder="Search assets..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Category"
            :options="['emergency' => 'Emergency Equipment', 'rescue' => 'Rescue Equipment', 'medical' => 'Medical Supplies']"
        />
        <x-filter-dropdown 
            label="Filter by Status"
            :options="['available' => 'Available', 'deployed' => 'Deployed', 'maintenance' => 'Under Maintenance']"
        />
    </div>

    {{-- Assets Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset Code</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Serial Number</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Condition</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-blue-600">{{ $asset->code }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $asset->name }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $asset->category->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $asset->serial_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $conditionColor = match($asset->condition) {
                                        'excellent', 'good' => 'success',
                                        'fair' => 'warning',
                                        'poor' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <x-badge variant="{{ $conditionColor }}" size="sm">{{ ucfirst($asset->condition) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = match($asset->status) {
                                        'available' => 'success',
                                        'deployed' => 'info',
                                        'under_maintenance' => 'warning',
                                        'out_of_service' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <x-badge variant="{{ $statusColor }}" size="sm">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $asset->location->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button onclick="viewAsset('{{ $asset->id }}')" class="p-2 rounded hover:bg-gray-100 transition-colors text-blue-600" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editAsset('{{ $asset->id }}')" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this asset?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-red-600" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8">
                                <x-empty-state icon="fas fa-box" title="No assets found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $assets->links() }}
        </div>
    </x-card>

    {{-- Add Asset Modal --}}
    @include('admin.assets.partials._add_asset_modal')

    @push('scripts')
        <script>
            function openAddAssetModal() { window.openModal('add-asset-modal'); }
            function viewAsset(code) { alert(`View: ${code}`); }
            function editAsset(code) { alert(`Edit: ${code}`); }
            function deleteAsset(code) { if(confirm(`Delete ${code}?`)) alert(`Deleted`); }
            function handlePageChange(page) { alert(`Page: ${page}`); }
        </script>
    @endpush
@endsection
