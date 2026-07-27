{{-- Add Asset Modal --}}
<x-modal id="add-asset-modal" title="Add New Asset" :show="$errors->any()">
    <form class="space-y-4" action="{{ route('assets.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-group label="Name" required>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="e.g., Fire Extinguisher"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </x-form-group>

            <x-form-group label="Category" required>
                <select
                    name="category_id"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Select Category</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </x-form-group>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-group label="Serial Number">
                <input
                    type="text"
                    name="serial_number"
                    value="{{ old('serial_number') }}"
                    placeholder="Serial number"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </x-form-group>

            <x-form-group label="Location" required>
                <select
                    name="location_id"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Select Location</option>
                    @foreach($locations ?? [] as $location)
                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Condition" required>
                <select
                    name="condition"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Select Condition</option>
                    @foreach(['excellent','good','fair','poor'] as $c)
                        <option value="{{ $c }}" {{ old('condition') === $c ? 'selected' : '' }}>
                            {{ ucfirst($c) }}
                        </option>
                    @endforeach
                </select>
            </x-form-group>
        </div>

        <x-form-group label="Status" required>
            <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select Status</option>
                <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="deployed" {{ old('status') === 'deployed' ? 'selected' : '' }}>Deployed</option>
                <option value="under_maintenance" {{ old('status') === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                <option value="out_of_service" {{ old('status') === 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
            </select>
        </x-form-group>

        <div class="flex gap-3 mt-6">
            <x-button variant="primary" class="flex-1">Create Asset</x-button>
            <x-button variant="secondary" type="button" onclick="window.closeModal('add-asset-modal')" class="flex-1">Cancel</x-button>
        </div>
    </form>
</x-modal>

