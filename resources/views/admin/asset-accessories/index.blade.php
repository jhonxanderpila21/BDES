@extends('layouts.admin')

@section('title', 'Asset Accessories - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Accessories</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Asset Accessories']
            ]" />
        </div>
        <button onclick="openAddModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Accessory
        </button>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    
    @if($errors->any())
        <x-alert type="danger" class="mb-6">{{ $errors->first() }}</x-alert>
    @endif

    {{-- Budgeting Summary --}}
    @if($budgetSummary->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Inventory Value by Asset Type</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($budgetSummary as $type => $total)
                    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 flex flex-col">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ $type }}</span>
                        <span class="text-2xl font-bold text-gray-900 mt-1">₱{{ number_format($total, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('asset-accessories.index') }}" method="GET" id="searchForm">
                @if(request('asset_type'))<input type="hidden" name="asset_type" value="{{ request('asset_type') }}">@endif
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search accessories..." />
            </form>
        </div>

        {{-- Asset Type Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('asset_type') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                <i class="fas fa-filter"></i>
                {{ request('asset_type') ?: 'Asset Type' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('asset-accessories.index', array_merge(request()->except('asset_type', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('asset_type') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Types</a>
                @foreach($assetTypes as $type)
                    <a href="{{ route('asset-accessories.index', array_merge(request()->except('page'), ['asset_type' => $type])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('asset_type') === $type ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $type }}</a>
                @endforeach
            </div>
        </div>

        {{-- Status Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('status') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                <i class="fas fa-circle-info"></i>
                {{ request('status') ?: 'Status' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('asset-accessories.index', array_merge(request()->except('status', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('status') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Statuses</a>
                @foreach($statuses as $s)
                    <a href="{{ route('asset-accessories.index', array_merge(request()->except('page'), ['status' => $s])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === $s ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $s }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Accessories Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Accessory Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">For Asset Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Linked Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Unit Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($accessories as $acc)
                        <tr class="hover:bg-gray-50 transition-colors {{ $acc->status === 'Not Available' ? 'opacity-75' : '' }}">
                            {{-- Accessory Name --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $acc->accessory_name }}</td>
                            
                            {{-- For Asset Type --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="info" size="sm">{{ $acc->asset_type }}</x-badge>
                            </td>

                            {{-- Linked Asset --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-700">{{ $acc->asset->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $acc->asset->code ?? '' }}</p>
                            </td>

                            {{-- Quantity --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $acc->quantity > 0 ? 'text-gray-900' : 'text-red-600' }}">
                                {{ $acc->quantity }}
                            </td>

                            {{-- Unit Cost --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                ₱{{ number_format($acc->unit_cost, 2) }}
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($acc->status === 'Available')
                                    <x-badge variant="success" size="sm">{{ $acc->status }}</x-badge>
                                @else
                                    <x-badge variant="danger" size="sm">{{ $acc->status }}</x-badge>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Adjust Quantity --}}
                                    <button onclick="openAdjustModal({{ $acc->id }}, '{{ addslashes($acc->accessory_name) }}', {{ $acc->quantity }})"
                                            class="p-2 rounded hover:bg-gray-200 transition-colors text-blue-600" title="Adjust Quantity">
                                        <i class="fas fa-boxes"></i>
                                    </button>
                                    {{-- Edit --}}
                                    <button onclick="openEditModal({{ json_encode($acc) }})"
                                            class="p-2 rounded hover:bg-gray-200 transition-colors text-yellow-600" title="Edit Details">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {{-- Delete --}}
                                    <form action="{{ route('asset-accessories.destroy', $acc) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this accessory?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded hover:bg-red-100 transition-colors text-red-600" title="Delete Accessory">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8">
                                <x-empty-state icon="fas fa-tools" title="No accessories found" />
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
        <form class="space-y-4" action="{{ route('asset-accessories.store') }}" method="POST">
            @csrf

            <x-form-group label="Accessory Name" required>
                <input type="text" name="accessory_name" placeholder="e.g., Mounting Bracket, Spare Battery" value="{{ old('accessory_name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <x-form-group label="Linked Asset" required>
                <select name="asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Initial Quantity" required>
                    <input type="number" min="0" name="quantity" value="{{ old('quantity', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <x-form-group label="Unit Cost (₱)" required>
                    <input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost', '0.00') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Create Accessory</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['add-accessory-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Accessory Modal --}}
    <x-modal id="edit-accessory-modal" title="Edit Accessory Details">
        <form class="space-y-4" id="editForm" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Accessory Name" required>
                <input type="text" name="accessory_name" id="edit_accessory_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <x-form-group label="Linked Asset" required>
                <select name="asset_id" id="edit_asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Quantity" required>
                    <input type="number" min="0" name="quantity" id="edit_quantity" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <x-form-group label="Unit Cost (₱)" required>
                    <input type="number" step="0.01" min="0" name="unit_cost" id="edit_unit_cost" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Update Accessory</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['edit-accessory-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Adjust Quantity Modal --}}
    <x-modal id="adjust-quantity-modal" title="Adjust Quantity">
        <form class="space-y-4" id="adjustForm" method="POST">
            @csrf

            <div class="bg-blue-50 rounded-lg p-4 mb-2">
                <p class="text-sm text-blue-800">
                    <strong>Accessory:</strong> <span id="adjust_accessory_name"></span>
                </p>
                <p class="text-sm text-blue-800 mt-1">
                    <strong>Current Quantity:</strong> <span id="adjust_current_qty"></span>
                </p>
            </div>

            <x-form-group label="Action" required>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="action" value="add" checked class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                        <span>Restock (Add)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="action" value="subtract" class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                        <span>Consume (Subtract)</span>
                    </label>
                </div>
            </x-form-group>

            <x-form-group label="Amount" required>
                <input type="number" min="1" name="amount" placeholder="Quantity to add/subtract..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Update Quantity</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['adjust-quantity-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddModal() {
                window.openModal('add-accessory-modal');
            }

            function openEditModal(acc) {
                document.getElementById('editForm').action = '/asset-accessories/' + acc.id;
                document.getElementById('edit_accessory_name').value = acc.accessory_name;
                document.getElementById('edit_asset_id').value = acc.asset_id;
                document.getElementById('edit_quantity').value = acc.quantity;
                document.getElementById('edit_unit_cost').value = parseFloat(acc.unit_cost).toFixed(2);
                window.openModal('edit-accessory-modal');
            }

            function openAdjustModal(id, name, currentQty) {
                document.getElementById('adjustForm').action = '/asset-accessories/' + id + '/adjust-quantity';
                document.getElementById('adjust_accessory_name').textContent = name;
                document.getElementById('adjust_current_qty').textContent = currentQty;
                // reset amount field
                document.getElementById('adjustForm').querySelector('input[name="amount"]').value = '';
                // default to add
                document.getElementById('adjustForm').querySelector('input[name="action"][value="add"]').checked = true;
                window.openModal('adjust-quantity-modal');
            }
        </script>
    @endpush
@endsection
