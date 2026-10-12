@extends('layouts.admin')

@section('title', 'Operational Requirements - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Operational Requirements</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Operational Requirements']
            ]" />
        </div>
        <button onclick="openAddRequirementModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Requirement
        </button>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('operational-requirements.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search requirements..." />
            </form>
        </div>
        <a href="{{ route('operational-requirements.index', ['low' => request('low') ? null : 1]) }}"
           class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('low') ? 'bg-red-50 border-red-300 text-red-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
            <i class="fas fa-triangle-exclamation"></i>
            Low Stock Only
        </a>
    </div>

    {{-- Requirements Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Requirement Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($requirements as $requirement)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $requirement->assetType->name ?? 'Unknown Asset Type' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $requirement->requirement_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $requirement->unit }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($requirement->isLowStock())
                                    <x-badge variant="danger" size="sm">Low Stock</x-badge>
                                @else
                                    <x-badge variant="success" size="sm">OK</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button onclick="editRequirement({{ $requirement->id }})" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('operational-requirements.destroy', $requirement) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this requirement?');">
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
                            <td colspan="5" class="px-6 py-8">
                                <x-empty-state icon="fas fa-list-check" title="No requirements found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $requirements->links() }}
        </div>
    </x-card>

    {{-- Add Requirement Modal --}}
    <x-modal id="add-requirement-modal" title="Add Operational Requirement">
        <form class="space-y-4" action="{{ route('operational-requirements.store') }}" method="POST">
            @csrf

            <x-form-group label="Asset Type" required>
                <select name="asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Asset Type</option>
                    @foreach($assetTypes as $assetType)
                        <option value="{{ $assetType->id }}" {{ old('asset_type_id') == $assetType->id ? 'selected' : '' }}>{{ $assetType->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Requirement Type" required>
                <input type="text" name="requirement_type" placeholder="e.g., Diesel, Battery" value="{{ old('requirement_type') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Unit" required>
                <input type="text" name="unit" placeholder="e.g., liters, units" value="{{ old('unit') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Current Level" required>
                    <input type="number" step="0.01" min="0" name="current_level" value="{{ old('current_level', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
                <x-form-group label="Maximum Capacity" required>
                    <input type="number" step="0.01" min="0" name="maximum_capacity" value="{{ old('maximum_capacity') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
                <x-form-group label="Minimum Level" required>
                    <input type="number" step="0.01" min="0" name="minimum_level" value="{{ old('minimum_level', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <x-form-group label="Notes">
                <textarea name="description" placeholder="Optional notes..." rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Create Requirement</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['add-requirement-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddRequirementModal() {
                window.openModal('add-requirement-modal');
            }
            function editRequirement(id) { alert(`Edit: ${id}`); }
            function handlePageChange(page) { alert(`Page: ${page}`); }
            function setAmount(form, message) {
                const amount = prompt(message);
                if (amount === null || amount.trim() === '' || isNaN(amount) || Number(amount) <= 0) {
                    return false;
                }
                form.querySelector('input[name="amount"]').value = amount;
                return true;
            }
        </script>
    @endpush
@endsection
