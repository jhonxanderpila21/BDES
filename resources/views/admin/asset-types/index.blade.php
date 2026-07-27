@extends('layouts.admin')

@section('title', 'Asset Types - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Types</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Configuration'],
                ['label' => 'Types']
            ]" />
        </div>
        <button onclick="openAddTypeModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Type
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('asset-types.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search asset types..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Category"
            :options="['emergency' => 'Emergency Equipment', 'rescue' => 'Rescue Equipment', 'medical' => 'Medical Supplies']"
        />
    </div>

    {{-- Asset Types Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Serialized</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Borrowable</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Maintenance</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Inspection</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Accessories</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Attachments</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Op. Requirements</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($types as $type)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $type->name }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $type->category->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($type->serialized)
                                    <span class="text-green-600"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($type->borrowable)
                                    <span class="text-green-600"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($type->requires_maintenance)
                                    <span class="text-green-600"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($type->requires_inspection)
                                    <span class="text-green-600"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($type->has_accessories)
                                    <span class="text-green-600"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($type->has_attachments)
                                    <span class="text-green-600"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($type->requires_operational_requirements)
                                    <span class="text-green-600"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button
                                        onclick="openEditTypeModal({{ $type->id }}, @js($type->name), {{ $type->category_id ?? 'null' }}, {{ $type->serialized ? 'true' : 'false' }}, {{ $type->borrowable ? 'true' : 'false' }}, {{ $type->requires_maintenance ? 'true' : 'false' }}, {{ $type->requires_inspection ? 'true' : 'false' }}, {{ $type->has_accessories ? 'true' : 'false' }}, {{ $type->has_attachments ? 'true' : 'false' }}, {{ $type->requires_operational_requirements ? 'true' : 'false' }})"
                                        class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600 hover:text-yellow-800" title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('asset-types.destroy', $type) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this asset type?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8">
                                <x-empty-state
                                    icon="fas fa-boxes"
                                    title="No types found"
                                    message="Start by adding your first asset type to the system"
                                >
                                    <button onclick="openAddTypeModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                        <i class="fas fa-plus"></i>
                                        Add Type
                                    </button>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $types->links() }}
        </div>
    </x-card>

    {{-- Add Type Modal --}}
    <x-modal id="add-type-modal" title="Add Asset Type">
        <form class="space-y-4" action="{{ route('asset-types.store') }}" method="POST">
            @csrf

            <x-form-group label="Type Name" required>
                <input
                    type="text"
                    name="name"
                    placeholder="e.g., Fire Extinguisher"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    value="{{ old('name') }}"
                >
            </x-form-group>

            <x-form-group label="Category" required>
                <select
                    name="category_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="space-y-3 border border-gray-300 rounded-lg p-4">
                {{-- Boolean inputs: use hidden 0 + checkbox 1 to ensure value is posted when unchecked --}}
                <input type="hidden" name="serialized" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="serialized"
                        value="1"
                        class="w-4 h-4 text-blue-600 rounded"
                        {{ old('serialized') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-700">Serialized</span>
                </label>

                <input type="hidden" name="borrowable" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="borrowable"
                        value="1"
                        class="w-4 h-4 text-blue-600 rounded"
                        {{ old('borrowable') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-700">Borrowable</span>
                </label>

                <input type="hidden" name="requires_maintenance" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="requires_maintenance"
                        value="1"
                        class="w-4 h-4 text-blue-600 rounded"
                        {{ old('requires_maintenance') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-700">Requires Maintenance</span>
                </label>

                <input type="hidden" name="requires_inspection" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="requires_inspection"
                        value="1"
                        class="w-4 h-4 text-blue-600 rounded"
                        {{ old('requires_inspection') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-700">Requires Inspection</span>
                </label>

                <input type="hidden" name="has_accessories" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="has_accessories"
                        value="1"
                        class="w-4 h-4 text-blue-600 rounded"
                        {{ old('has_accessories') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-700">Has Accessories</span>
                </label>

                <input type="hidden" name="has_attachments" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="has_attachments"
                        value="1"
                        class="w-4 h-4 text-blue-600 rounded"
                        {{ old('has_attachments') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-700">Has Attachments</span>
                </label>

                <input type="hidden" name="requires_operational_requirements" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="requires_operational_requirements"
                        value="1"
                        class="w-4 h-4 text-blue-600 rounded"
                        {{ old('requires_operational_requirements') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-700">Requires Operational Requirements</span>
                </label>
            </div>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1" type="submit">Create Type</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-type-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Type Modal --}}
    <x-modal id="edit-type-modal" title="Edit Asset Type">
        <form class="space-y-4" id="edit-type-form" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Type Name" required>
                <input
                    type="text"
                    name="name"
                    id="edit-type-name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </x-form-group>

            <x-form-group label="Category" required>
                <select
                    name="category_id"
                    id="edit-type-category"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="space-y-3 border border-gray-300 rounded-lg p-4">
                <input type="hidden" name="serialized" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="serialized" id="edit-type-serialized" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Serialized</span>
                </label>

                <input type="hidden" name="borrowable" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="borrowable" id="edit-type-borrowable" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Borrowable</span>
                </label>

                <input type="hidden" name="requires_maintenance" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_maintenance" id="edit-type-requires_maintenance" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Maintenance</span>
                </label>

                <input type="hidden" name="requires_inspection" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_inspection" id="edit-type-requires_inspection" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Inspection</span>
                </label>

                <input type="hidden" name="has_accessories" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_accessories" id="edit-type-has_accessories" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Has Accessories</span>
                </label>

                <input type="hidden" name="has_attachments" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_attachments" id="edit-type-has_attachments" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Has Attachments</span>
                </label>

                <input type="hidden" name="requires_operational_requirements" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_operational_requirements" id="edit-type-requires_operational_requirements" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Operational Requirements</span>
                </label>
            </div>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1" type="submit">Save Changes</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-type-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddTypeModal() {
                window.openModal('add-type-modal');
            }

            function openEditTypeModal(id, name, categoryId, serialized, borrowable, requiresMaintenance, requiresInspection, hasAccessories, hasAttachments, requiresOperationalRequirements) {
                const form = document.getElementById('edit-type-form');
                form.action = `/asset-types/${id}`;
                document.getElementById('edit-type-name').value = name;
                document.getElementById('edit-type-category').value = categoryId ?? '';
                document.getElementById('edit-type-serialized').checked = serialized;
                document.getElementById('edit-type-borrowable').checked = borrowable;
                document.getElementById('edit-type-requires_maintenance').checked = requiresMaintenance;
                document.getElementById('edit-type-requires_inspection').checked = requiresInspection;
                document.getElementById('edit-type-has_accessories').checked = hasAccessories;
                document.getElementById('edit-type-has_attachments').checked = hasAttachments;
                document.getElementById('edit-type-requires_operational_requirements').checked = requiresOperationalRequirements;
                window.openModal('edit-type-modal');
            }

            function handlePageChange(page) { alert(`Page: ${page}`); }
        </script>
    @endpush
@endsection
