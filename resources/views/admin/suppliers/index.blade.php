@extends('layouts.admin')

@section('title', 'Suppliers - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Suppliers</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Configuration'],
                ['label' => 'Suppliers']
            ]" />
        </div>
        <button onclick="openAddSupplierModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Supplier
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('suppliers.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search suppliers..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Status"
            :options="['active' => 'Active', 'inactive' => 'Inactive']"
        />
    </div>

    {{-- Suppliers Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($suppliers as $supplier)
            <x-card>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $supplier->name }}</h3>
                            <div class="mt-2 space-y-1 text-sm text-gray-600">
                                <p><i class="fas fa-user mr-2 text-gray-400"></i>{{ $supplier->contact_person ?? 'N/A' }}</p>
                                <p><i class="fas fa-phone mr-2 text-gray-400"></i>{{ $supplier->phone ?? 'N/A' }}</p>
                                <p><i class="fas fa-envelope mr-2 text-gray-400"></i>{{ $supplier->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <x-badge 
                            :variant="$supplier->status === 'active' ? 'success' : 'secondary'" 
                            size="sm"
                        >
                            {{ ucfirst($supplier->status) }}
                        </x-badge>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex gap-2">
                            <button onclick="editSupplier({{ $supplier->id }})" class="flex-1 px-3 py-2 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="flex-1" onsubmit="return confirm('Delete this supplier?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors text-sm font-medium">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-3">
                <x-empty-state icon="fas fa-building" title="No suppliers found" />
            </div>
        @endforelse
    </div>
    
    <div class="mt-6">
        {{ $suppliers->links() }}
    </div>

    {{-- Add Supplier Modal --}}
    <x-modal id="add-supplier-modal" title="Add Supplier">
        <form class="space-y-4">
            <x-form-group label="Supplier Name" required>
                <input type="text" placeholder="Company name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Contact Person" required>
                    <input type="text" placeholder="Full name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Phone Number" required>
                    <input type="tel" placeholder="(123) 456-7890" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>
            </div>

            <x-form-group label="Email" required>
                <input type="email" placeholder="email@supplier.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Address">
                <textarea placeholder="Full address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <x-form-group label="Status" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1">Create Supplier</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-supplier-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddSupplierModal() {
                window.openModal('add-supplier-modal');
            }
            function editSupplier(name) { alert(`Edit: ${name}`); }
            function deleteSupplier(name) { if(confirm(`Delete ${name}?`)) alert(`Deleted`); }
        </script>
    @endpush
@endsection
