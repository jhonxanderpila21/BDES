@extends('layouts.admin')

@section('title', 'Asset Categories - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Categories</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Configuration'],
                ['label' => 'Categories']
            ]" />
        </div>
        <button onclick="openAddCategoryModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Category
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('asset-categories.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search categories..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Status"
            :options="['active' => 'Active', 'inactive' => 'Inactive']"
        />
    </div>

    {{-- Categories Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset Count</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $category->description ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="info" size="sm">{{ $category->assets_count ?? $category->assets()->count() }} assets</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="{{ $category->status === 'active' ? 'success' : 'secondary' }}" size="sm">{{ ucfirst($category->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <button
                                        onclick="openEditCategoryModal({{ $category->id }}, @js($category->name), @js($category->description), @js($category->status))"
                                        class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600 hover:text-yellow-800" title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('asset-categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('Archive this category? It will be hidden from the active list.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-red-600 hover:text-red-800" title="Archive">
                                            <i class="fas fa-box-archive"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8">
                                <x-empty-state
                                    icon="fas fa-layer-group"
                                    title="No categories found"
                                    message="Start by adding your first asset category to the system"
                                >
                                    <button onclick="openAddCategoryModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                        <i class="fas fa-plus"></i>
                                        Add Category
                                    </button>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $categories->links() }}
        </div>
    </x-card>

    {{-- Add Category Modal --}}
    <x-modal id="add-category-modal" title="Add Category">
        <form class="space-y-4" action="{{ route('asset-categories.store') }}" method="POST">
            @csrf

            <x-form-group label="Category Name" required>
                <input type="text" name="name" placeholder="e.g., Emergency Response Equipment" value="{{ old('name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Description" required>
                <textarea name="description" placeholder="Brief description..." rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
            </x-form-group>

            <x-form-group label="Status" required>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Create Category</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-category-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Category Modal --}}
    <x-modal id="edit-category-modal" title="Edit Category">
        <form class="space-y-4" id="edit-category-form" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Category Name" required>
                <input type="text" name="name" id="edit-category-name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Description" required>
                <textarea name="description" id="edit-category-description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <x-form-group label="Status" required>
                <select name="status" id="edit-category-status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-category-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddCategoryModal() { window.openModal('add-category-modal'); }

            function openEditCategoryModal(id, name, description, status) {
                const form = document.getElementById('edit-category-form');
                form.action = `/asset-categories/${id}`;
                document.getElementById('edit-category-name').value = name;
                document.getElementById('edit-category-description').value = description ?? '';
                document.getElementById('edit-category-status').value = status;
                window.openModal('edit-category-modal');
            }

            function handlePageChange(page) { alert(`Page: ${page}`); }
        </script>
    @endpush
@endsection
