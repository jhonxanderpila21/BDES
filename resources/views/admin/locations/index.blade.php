@extends('layouts.admin')

@section('title', 'Asset Locations - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Locations</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Administration'],
                ['label' => 'Asset Locations']
            ]" />
        </div>
        <button onclick="openAddLocationModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Asset Location
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('locations.index') }}" method="GET">
                @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search locations by name, type, address..." />
            </form>
        </div>
        <form action="{{ route('locations.index') }}" method="GET">
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            <select name="type" onchange="this.form.submit()" class="flex items-center gap-2 pl-4 pr-10 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">All Types</option>
                <option value="storage" {{ request('type') === 'storage' ? 'selected' : '' }}>Storage</option>
                <option value="office" {{ request('type') === 'office' ? 'selected' : '' }}>Office</option>
                <option value="depot" {{ request('type') === 'depot' ? 'selected' : '' }}>Depot</option>
                <option value="field" {{ request('type') === 'field' ? 'selected' : '' }}>Field Station</option>
            </select>
        </form>
    </div>

    {{-- Locations Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Location Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Assets</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Capacity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($locations as $location)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $location->name }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="info" size="sm">{{ ucfirst($location->type) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $location->address ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $location->assets_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        @php 
                                            $capacity = $location->storage_capacity > 0 ? $location->storage_capacity : 100;
                                            $percentage = min(100, round(($location->assets_count / $capacity) * 100)); 
                                        @endphp
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600">{{ $percentage }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                @if(auth()->user()->isAdmin())
                                    <div class="flex items-center gap-2">
                                        <button onclick="editLocation({{ $location->id }}, @js($location->name), @js($location->type), @js($location->address), {{ $location->storage_capacity ?? 'null' }})" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600" title="Edit Location">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('locations.destroy', $location) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete location [{{ $location->name }}]?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded hover:bg-gray-100 transition-colors text-red-600" title="Delete Location">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Read-only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8">
                                <x-empty-state icon="fas fa-map-marker-alt" title="No asset locations found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $locations->links() }}
        </div>
    </x-card>

    @if(auth()->user()->isAdmin())
        {{-- Add Location Modal --}}
        <x-modal id="add-location-modal" title="Add Asset Location">
            <form class="space-y-4" action="{{ route('locations.store') }}" method="POST">
                @csrf
                <x-form-group label="Location Name" required>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Main Storage Facility" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="Location Type" required>
                    <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm bg-white">
                        <option value="storage">Storage</option>
                        <option value="office">Office</option>
                        <option value="depot">Depot</option>
                        <option value="field">Field Station</option>
                    </select>
                </x-form-group>

                <x-form-group label="Address">
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Street address and location" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="Storage Capacity">
                    <input type="number" min="0" name="storage_capacity" value="{{ old('storage_capacity') }}" placeholder="Maximum number of items" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <div class="flex gap-3 mt-6">
                    <x-button variant="primary" type="submit" class="flex-1">Create Asset Location</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('add-location-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>

        {{-- Edit Location Modal --}}
        <x-modal id="edit-location-modal" title="Edit Asset Location">
            <form class="space-y-4" id="edit-location-form" method="POST">
                @csrf
                @method('PUT')
                <x-form-group label="Location Name" required>
                    <input type="text" name="name" id="edit_location_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="Location Type" required>
                    <select name="type" id="edit_location_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm bg-white">
                        <option value="storage">Storage</option>
                        <option value="office">Office</option>
                        <option value="depot">Depot</option>
                        <option value="field">Field Station</option>
                    </select>
                </x-form-group>

                <x-form-group label="Address">
                    <input type="text" name="address" id="edit_location_address" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <x-form-group label="Storage Capacity">
                    <input type="number" min="0" name="storage_capacity" id="edit_location_capacity" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </x-form-group>

                <div class="flex gap-3 mt-6">
                    <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('edit-location-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>
    @endif

    @push('scripts')
        <script>
            function openAddLocationModal() {
                window.openModal('add-location-modal');
            }
            function editLocation(id, name, type, address, capacity) {
                const form = document.getElementById('edit-location-form');
                if (!form) return;
                form.action = '/locations/' + id;
                document.getElementById('edit_location_name').value = name;
                document.getElementById('edit_location_type').value = type;
                document.getElementById('edit_location_address').value = address || '';
                document.getElementById('edit_location_capacity').value = capacity !== null ? capacity : '';
                window.openModal('edit-location-modal');
            }
        </script>
    @endpush
@endsection
