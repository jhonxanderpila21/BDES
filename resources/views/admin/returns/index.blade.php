@extends('layouts.admin')

@section('title', 'Asset Returns - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset Returns</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Returns']
            ]" />
        </div>
        <button onclick="openReturnModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Record Return
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('returns.index') }}" method="GET">
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by asset or team..." />
            </form>
        </div>
        <x-filter-dropdown 
            label="Filter by Status"
            :options="['received' => 'Received', 'damaged' => 'Damaged', 'incomplete' => 'Incomplete']"
        />
    </div>

    {{-- Returns Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Returned By</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Return Date</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Condition</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($returns as $return)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-left">
                                <p class="text-sm font-medium text-gray-900">{{ $return->asset->name ?? 'Unknown Asset' }}</p>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 text-left">{{ $return->returned_by ?? 'N/A' }}</td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 text-left">{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                @php
                                    $condColor = strtolower($return->condition) === 'excellent' || strtolower($return->condition) === 'good' ? 'success' : 'warning';
                                @endphp
                                <x-badge variant="{{ $condColor }}" size="sm">{{ ucfirst($return->condition) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                <x-badge variant="info" size="sm">{{ ucfirst(str_replace('_', ' ', $return->status)) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="window.openModal('return-modal')" class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600" title="Edit Return">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('returns.destroy', $return) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Return: {{ addslashes($return->asset->name ?? 'Record') }} (#{{ $return->id }})', 'This return log entry will be permanently deleted.');">
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
                                <x-empty-state icon="fas fa-undo" title="No returns found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $returns->links() }}
        </div>
    </x-card>

    {{-- Return Modal --}}
    <x-modal id="return-modal" title="Record Asset Return">
        <form class="space-y-4">
            <x-form-group label="Asset" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Select Asset</option>
                    <option>Fire Extinguisher #001</option>
                    <option>First Aid Kit #005</option>
                    <option>Rescue Rope #002</option>
                </select>
            </x-form-group>

            <x-form-group label="Returned By" required>
                <input type="text" placeholder="Team or person name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Return Date" required>
                <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <x-form-group label="Condition" required>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option>Good</option>
                    <option>Fair</option>
                    <option>Damaged</option>
                    <option>Broken</option>
                </select>
            </x-form-group>

            <x-form-group label="Damage Notes">
                <textarea placeholder="Describe any damage or issues..." rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <x-form-group label="Missing Accessories">
                <textarea placeholder="List any missing accessories..." rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </x-form-group>

            <x-form-group label="Actual Location" required>
                <input type="text" placeholder="Where the asset is now" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" class="flex-1">Record Return</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('return-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openReturnModal() {
                window.openModal('return-modal');
            }
        </script>
    @endpush
@endsection
