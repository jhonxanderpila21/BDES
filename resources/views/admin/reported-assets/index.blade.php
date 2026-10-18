@extends('layouts.admin')

@section('title', 'Reported Assets - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Reported Assets</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Reported Assets']
            ]" />
        </div>
        <button onclick="openAddModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            File Report
        </button>
    </div>



    {{-- Dashboard Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 mb-8">
        @php
            $statusIcons = [
                'Pending'    => 'fas fa-clock',
                'For Review' => 'fas fa-search',
                'Ongoing'    => 'fas fa-spinner',
                'Escalated'  => 'fas fa-exclamation-triangle',
                'Done'       => 'fas fa-check-circle',
                'Closed'     => 'fas fa-archive',
            ];
            $statusColors = [
                'Pending'    => 'border-yellow-400 bg-yellow-50 text-yellow-700',
                'For Review' => 'border-sky-400 bg-sky-50 text-sky-700',
                'Ongoing'    => 'border-blue-400 bg-blue-50 text-blue-700',
                'Escalated'  => 'border-red-400 bg-red-50 text-red-700',
                'Done'       => 'border-green-400 bg-green-50 text-green-700',
                'Closed'     => 'border-gray-400 bg-gray-50 text-gray-600',
            ];
        @endphp
        @foreach($statusCounts as $status => $count)
            <div class="rounded-lg border-l-4 p-4 {{ $statusColors[$status] ?? 'border-gray-300 bg-gray-50' }}">
                <div class="flex items-center gap-2 mb-1">
                    <i class="{{ $statusIcons[$status] ?? 'fas fa-info-circle' }} text-sm"></i>
                    <span class="text-xs font-semibold uppercase tracking-wider">{{ $status }}</span>
                </div>
                <span class="text-3xl font-bold">{{ $count }}</span>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('reported-assets.index') }}" method="GET" id="searchForm">
                @if(request('asset_category'))<input type="hidden" name="asset_category" value="{{ request('asset_category') }}">@endif
                @if(request('reported_type'))<input type="hidden" name="reported_type" value="{{ request('reported_type') }}">@endif
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search reports..." />
            </form>
        </div>

        {{-- Category Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('asset_category') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                {{ request('asset_category') ?: 'Category' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('reported-assets.index', array_merge(request()->except('asset_category', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('asset_category') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Categories</a>
                @foreach($assetCategories as $cat)
                    <a href="{{ route('reported-assets.index', array_merge(request()->except('page'), ['asset_category' => $cat])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('asset_category') === $cat ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $cat }}</a>
                @endforeach
            </div>
        </div>

        {{-- Type Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('reported_type') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                {{ request('reported_type') ?: 'Type' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('reported-assets.index', array_merge(request()->except('reported_type', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('reported_type') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Types</a>
                @foreach($reportedTypes as $rt)
                    <a href="{{ route('reported-assets.index', array_merge(request()->except('page'), ['reported_type' => $rt])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('reported_type') === $rt ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $rt }}</a>
                @endforeach
            </div>
        </div>

        {{-- Status Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('status') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                {{ request('status') ?: 'Status' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('reported-assets.index', array_merge(request()->except('status', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('status') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Statuses</a>
                @foreach($statuses as $s)
                    <a href="{{ route('reported-assets.index', array_merge(request()->except('page'), ['status' => $s])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === $s ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $s }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Reports Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reported</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($reports as $report)
                        @php
                            $rowHighlight = match($report->status) {
                                'Escalated' => 'bg-red-50/40',
                                'Ongoing'   => 'bg-blue-50/30',
                                default     => '',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $rowHighlight }}">
                            {{-- Asset --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $report->asset_name }}</p>
                                <p class="text-xs text-gray-500">Reported by {{ $report->reporter->name ?? 'Unknown' }}</p>
                            </td>

                            {{-- Category --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="info" size="sm">{{ $report->asset_category }}</x-badge>
                            </td>

                            {{-- Reported Type --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="{{ \App\Services\ReportedAssetService::typeBadgeVariant($report->reported_type) }}" size="sm">
                                    {{ $report->reported_type }}
                                </x-badge>
                            </td>

                            {{-- Location --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $report->location }}</td>

                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="{{ \App\Services\ReportedAssetService::statusBadgeVariant($report->status) }}" size="sm">
                                    @if($report->status === 'Escalated')
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                    @endif
                                    {{ $report->status }}
                                </x-badge>
                            </td>

                            {{-- Reported Date --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $report->reported_date->format('M d, Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- View Detail --}}
                                    <a href="{{ route('reported-assets.show', $report) }}"
                                       class="p-2 rounded hover:bg-blue-100 transition-colors text-blue-600" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- Edit --}}
                                    <button onclick="openEditModal({{ json_encode($report) }})"
                                            class="p-2 rounded hover:bg-gray-200 transition-colors text-yellow-600" title="Edit Report">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {{-- Delete --}}
                                    <form action="{{ route('reported-assets.destroy', $report) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this report? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded hover:bg-red-100 transition-colors text-red-600" title="Delete Report">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8">
                                <x-empty-state icon="fas fa-flag" title="No reported assets found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $reports->links() }}
        </div>
    </x-card>

    {{-- Add Report Modal --}}
    <x-modal id="add-report-modal" title="File a Report">
        <form class="space-y-4" action="{{ route('reported-assets.store') }}" method="POST">
            @csrf

            <x-form-group label="Asset" required>
                <select name="asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->category->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Report Type" required>
                    <select name="reported_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Type</option>
                        @foreach($reportedTypes as $rt)
                            <option value="{{ $rt }}" {{ old('reported_type') == $rt ? 'selected' : '' }}>{{ $rt }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Reported Date" required>
                    <input type="date" max="{{ date('Y-m-d') }}" name="reported_date" value="{{ old('reported_date', date('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <x-form-group label="Location" required>
                <input type="text" name="location" placeholder="e.g., Evacuation Center 1, Barangay Hall" value="{{ old('location') }}" list="location-list" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <datalist id="location-list">
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}">
                    @endforeach
                </datalist>
            </x-form-group>

            <x-form-group label="Description">
                <textarea name="description" rows="3" placeholder="Describe the issue..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </x-form-group>

            <x-form-group label="Action Taken / Planned">
                <input type="text" name="action" placeholder="e.g., Repair/Refill, Replace Unit" value="{{ old('action') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Submit Report</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['add-report-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Report Modal --}}
    <x-modal id="edit-report-modal" title="Edit Report">
        <form class="space-y-4" id="editForm" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Asset" required>
                <select name="asset_id" id="edit_asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->category->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Report Type" required>
                    <select name="reported_type" id="edit_reported_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($reportedTypes as $rt)
                            <option value="{{ $rt }}">{{ $rt }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Reported Date" required>
                    <input type="date" max="{{ date('Y-m-d') }}" name="reported_date" id="edit_reported_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <x-form-group label="Location" required>
                <input type="text" name="location" id="edit_location" list="location-list-edit" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <datalist id="location-list-edit">
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}">
                    @endforeach
                </datalist>
            </x-form-group>

            <x-form-group label="Description">
                <textarea name="description" id="edit_description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </x-form-group>

            <x-form-group label="Action Taken / Planned">
                <input type="text" name="action" id="edit_action" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Update Report</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['edit-report-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddModal() {
                window.openModal('add-report-modal');
            }

            function openEditModal(report) {
                document.getElementById('editForm').action = '/reported-assets/' + report.id;
                document.getElementById('edit_asset_id').value = report.asset_id;
                document.getElementById('edit_reported_type').value = report.reported_type;

                let dateStr = report.reported_date;
                if (dateStr && dateStr.includes('T')) {
                    dateStr = dateStr.split('T')[0];
                }
                document.getElementById('edit_reported_date').value = dateStr;

                document.getElementById('edit_location').value = report.location || '';
                document.getElementById('edit_description').value = report.description || '';
                document.getElementById('edit_action').value = report.action || '';
                window.openModal('edit-report-modal');
            }
        </script>
    @endpush
@endsection
