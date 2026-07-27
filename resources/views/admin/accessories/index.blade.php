@extends('layouts.admin')

@section('title', 'Accessories - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Accessories</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Accessories']
            ]" />
        </div>
        <button onclick="openAddAccessoryModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Accessory
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('accessories.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search accessories..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Asset Type"
            :options="['fire-ext' => 'Fire Extinguisher', 'medical' => 'Medical Kit', 'rescue' => 'Rescue Equipment']"
        />
    </div>

    {{-- Accessories Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Accessory Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">For Asset Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Quantity in Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Unit Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($accessories as $accessory)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $accessory->name }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $accessory->assetType->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="info" size="sm">{{ $accessory->quantity_in_stock }} units</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱{{ number_format($accessory->unit_cost, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = strtolower($accessory->status) === 'in_stock' ? 'success' : 'warning';
                                @endphp
                                <x-badge variant="{{ $statusColor }}" size="sm">{{ ucfirst(str_replace('_', ' ', $accessory->status)) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button onclick="editAccessory({{ $accessory->id }})" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('accessories.destroy', $accessory) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this accessory?');">
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
                            <td colspan="6" class="px-6 py-8">
                                <x-empty-state icon="fas fa-puzzle-piece" title="No accessories found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $accessories->links() }}
        </div>
    </x-card>

    {{-- Add Accessory Modal --}}
    <x-modal id="add-accessory-modal" title="Add Accessory">
        <form class="space-y-4">
            <x-form-group label="Accessory Name" required>
                <input type="text" placeholder="e.g., Replacement Nozzle" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Asset Type" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Fire Extinguisher</option>
                    <option>First Aid Kit</option>
                    <option>Rescue Rope</option>
                </select>
            </x-form-group>

            <x-form-group label="Unit Cost" required>
                <input type="number" placeholder="0.00" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Initial Quantity" required>
                <input type="number" placeholder="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1">Create Accessory</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-accessory-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddAccessoryModal() { window.openModal('add-accessory-modal'); }
            function editAccessory(name) { alert(`Edit: ${name}`); }
            function deleteAccessory(name) { if(confirm(`Delete ${name}?`)) alert(`Deleted`); }
            function handlePageChange(page) { alert(`Page: ${page}`); }
        </script>
    @endpush
@endsection
