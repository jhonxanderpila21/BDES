@extends('layouts.admin')

@section('title', 'Reported Assets - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <i class="fas fa-flag text-amber-500"></i>
                Reported Assets
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Reported Assets']
            ]" />
        </div>
        <button 
            type="button"
            onclick="openAddModal()" 
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm"
        >
            <i class="fas fa-plus"></i>
            File Report
        </button>
    </div>

    {{-- Dashboard Summary KPI Cards (Clickable quick status filters) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 mb-8">
        @php
            $statusConfig = [
                'Pending'    => ['icon' => 'fas fa-clock', 'border' => 'border-amber-200', 'bg' => 'bg-amber-50/40', 'iconBg' => 'bg-amber-100 text-amber-600', 'textColor' => 'text-amber-700', 'label' => 'Awaiting Review'],
                'For Review' => ['icon' => 'fas fa-search', 'border' => 'border-sky-200', 'bg' => 'bg-sky-50/40', 'iconBg' => 'bg-sky-100 text-sky-600', 'textColor' => 'text-sky-700', 'label' => 'Admin Review'],
                'Ongoing'    => ['icon' => 'fas fa-spinner', 'border' => 'border-blue-200', 'bg' => 'bg-blue-50/40', 'iconBg' => 'bg-blue-100 text-blue-600', 'textColor' => 'text-blue-700', 'label' => 'In Progress'],
                'Escalated'  => ['icon' => 'fas fa-triangle-exclamation', 'border' => 'border-red-200', 'bg' => 'bg-red-50/40', 'iconBg' => 'bg-red-100 text-red-600', 'textColor' => 'text-red-700', 'label' => 'Critical Alert'],
                'Done'       => ['icon' => 'fas fa-circle-check', 'border' => 'border-emerald-200', 'bg' => 'bg-emerald-50/40', 'iconBg' => 'bg-emerald-100 text-emerald-600', 'textColor' => 'text-emerald-700', 'label' => 'Resolved'],
                'Closed'     => ['icon' => 'fas fa-archive', 'border' => 'border-gray-200', 'bg' => 'bg-gray-50/60', 'iconBg' => 'bg-gray-200 text-gray-700', 'textColor' => 'text-gray-700', 'label' => 'Archived'],
            ];
        @endphp
        @foreach($statusCounts as $status => $count)
            @php 
                $cfg = $statusConfig[$status] ?? ['icon' => 'fas fa-info-circle', 'border' => 'border-gray-200', 'bg' => 'bg-white', 'iconBg' => 'bg-gray-100 text-gray-600', 'textColor' => 'text-gray-700', 'label' => ''];
                $isActive = request('status') === $status;
            @endphp
            <a 
                href="{{ route('reported-assets.index', array_merge(request()->except('status', 'page'), $isActive ? [] : ['status' => $status])) }}"
                class="bg-white rounded-xl p-4 border transition-all duration-150 block {{ $cfg['border'] }} {{ $cfg['bg'] }} {{ $isActive ? 'ring-2 ring-blue-500 shadow-md scale-[1.02]' : 'hover:shadow-sm hover:border-blue-300' }}"
                title="Click to filter by {{ $status }}"
            >
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1 pr-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wider {{ $cfg['textColor'] }} truncate">{{ $status }}</p>
                        <p class="text-2xl font-bold {{ $cfg['textColor'] }} mt-0.5">{{ $count }}</p>
                        <p class="text-[10px] text-gray-500 mt-0.5 truncate">{{ $cfg['label'] }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-lg {{ $cfg['iconBg'] }} flex items-center justify-center text-sm flex-shrink-0">
                        <i class="{{ $cfg['icon'] }}"></i>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Unified Filters & Search Bar --}}
    <form action="{{ route('reported-assets.index') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-6">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        {{-- Search Input --}}
        <div class="flex-1 min-w-[220px]">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search asset name, code, category, location, reporter..." 
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                >
            </div>
        </div>

        {{-- Category Dropdown --}}
        <select name="asset_category" onchange="this.form.submit()" class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">All Categories</option>
            @foreach($assetCategories as $cat)
                <option value="{{ $cat }}" {{ request('asset_category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>

        {{-- Report / Incident Type Dropdown --}}
        <select name="reported_type" onchange="this.form.submit()" class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">All Incident Types</option>
            @foreach($reportedTypes as $rt)
                <option value="{{ $rt }}" {{ request('reported_type') === $rt ? 'selected' : '' }}>{{ $rt }}</option>
            @endforeach
        </select>

        {{-- Time / Timeframe Dropdown --}}
        <select name="timeframe" onchange="this.form.submit()" class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">All Time</option>
            <option value="today" {{ request('timeframe') === 'today' ? 'selected' : '' }}>Today</option>
            <option value="this_week" {{ request('timeframe') === 'this_week' ? 'selected' : '' }}>Past 7 Days (This Week)</option>
            <option value="this_month" {{ request('timeframe') === 'this_month' ? 'selected' : '' }}>Past 30 Days (This Month)</option>
            <option value="this_year" {{ request('timeframe') === 'this_year' ? 'selected' : '' }}>This Year</option>
        </select>

        {{-- Status Dropdown --}}
        <select name="status" onchange="this.form.submit()" class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">All Statuses</option>
            @foreach($statuses as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
            Filter
        </button>

        @if(request()->hasAny(['search', 'asset_category', 'reported_type', 'status', 'timeframe', 'date_from', 'date_to']))
            <a href="{{ route('reported-assets.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center transition-colors">
                Reset
            </a>
        @endif
    </form>

    {{-- Reports Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Incident Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reported By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reported Date & Time</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($reports as $report)
                        @php
                            $rowHighlight = match($report->status) {
                                'Escalated' => 'bg-red-50/30',
                                'Ongoing'   => 'bg-blue-50/20',
                                'Done'      => 'bg-emerald-50/20',
                                default     => '',
                            };
                        @endphp
                        <tr 
                            class="hover:bg-blue-50/40 cursor-pointer transition-colors group {{ $rowHighlight }}"
                            onclick="if(!event.target.closest('button, a, form, input, select')) window.location.href='{{ route('reported-assets.show', $report) }}';"
                            title="Click to view Report #{{ $report->id }} details"
                        >
                            {{-- Asset --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('reported-assets.show', $report) }}" class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors block">
                                    {{ $report->asset?->name ?? $report->asset_name }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5 text-xs text-gray-500">
                                    @if($report->asset?->code)
                                        <span class="font-mono text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.2 rounded text-[11px] font-semibold">{{ $report->asset->code }}</span>
                                        <span class="text-gray-300">•</span>
                                    @endif
                                    <span>{{ $report->asset_category }}</span>
                                </div>
                            </td>

                            {{-- Incident / Reported Type --}}
                            <td class="px-6 py-4">
                                <x-badge variant="{{ \App\Services\ReportedAssetService::typeBadgeVariant($report->reported_type) }}" size="sm">
                                    {{ $report->reported_type }}
                                </x-badge>
                                @if($report->description)
                                    <p class="text-xs text-gray-500 mt-1 truncate max-w-xs" title="{{ $report->description }}">{{ $report->description }}</p>
                                @endif
                            </td>

                            {{-- Location --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700 font-medium">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    {{ $report->location }}
                                </span>
                            </td>

                            {{-- Reported By --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $report->reporter->name ?? 'Unknown' }}</p>
                                <p class="text-[11px] text-gray-400">{{ ucfirst($report->reporter->role ?? 'Officer') }}</p>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="{{ \App\Services\ReportedAssetService::statusBadgeVariant($report->status) }}" size="sm">
                                    @if($report->status === 'Escalated')
                                        <i class="fas fa-triangle-exclamation mr-1 text-[10px]"></i>
                                    @elseif($report->status === 'Done')
                                        <i class="fas fa-check mr-1 text-[10px]"></i>
                                    @elseif($report->status === 'Ongoing')
                                        <i class="fas fa-spinner fa-spin mr-1 text-[10px]"></i>
                                    @endif
                                    {{ $report->status }}
                                </x-badge>
                            </td>

                            {{-- Reported Date & Time --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700">
                                <span class="font-semibold block text-gray-900">{{ $report->reported_date->format('M d, Y') }}</span>
                                <span class="text-gray-400 text-[11px] flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-clock text-[10px]"></i>
                                    {{ $report->created_at ? $report->created_at->format('h:i A') : 'Recorded' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- View Details --}}
                                    <a 
                                        href="{{ route('reported-assets.show', $report) }}"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-600 hover:text-blue-800 transition-colors" 
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <button 
                                        type="button"
                                        onclick="openEditModal({{ $report->id }}, {{ $report->asset_id }}, @js($report->reported_type), @js($report->location), @js($report->description), @js($report->action))"
                                        class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600 hover:text-amber-800 transition-colors" 
                                        title="Edit Report"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Delete (Admin-only) --}}
                                    @if(auth()->user()->isAdmin())
                                        <form action="{{ route('reported-assets.destroy', $report) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Report: [{{ addslashes($report->asset_name) }}] (#{{ $report->id }})', 'This incident report record will be permanently deleted from the registry.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors" title="Delete Report">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <x-empty-state icon="fas fa-flag" title="No reported assets found" description="Click File Report above to record equipment defects, damages, or incidents." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $reports->links() }}
        </div>
    </x-card>

    {{-- Add Report Modal (Reported Date removed, defaults to current timestamp automatically) --}}
    <x-modal id="add-report-modal" title="File an Incident / Damage Report">
        <form class="space-y-4" action="{{ route('reported-assets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <x-form-group label="Select Asset" required>
                <select name="asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                    <option value="">-- Choose Equipment --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->category->name ?? 'Uncategorized' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Report / Incident Type" required>
                    <select name="reported_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                        <option value="">Select Type</option>
                        @foreach($reportedTypes as $rt)
                            <option value="{{ $rt }}" {{ old('reported_type') == $rt ? 'selected' : '' }}>{{ $rt }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Location / Station" required>
                    <input type="text" name="location" placeholder="e.g., Evacuation Center 1, Storage Bay" value="{{ old('location') }}" list="location-list" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <datalist id="location-list">
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}">
                        @endforeach
                    </datalist>
                </x-form-group>
            </div>

            {{-- Auto Timestamp Notice (as requested, date input removed) --}}
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 flex items-center gap-2">
                <i class="fas fa-clock text-blue-600 text-sm"></i>
                <span>Report date and time will be recorded automatically as <strong>{{ now()->format('M d, Y h:i A') }}</strong>.</span>
            </div>

            <x-form-group label="Issue Description" required>
                <textarea name="description" rows="3" placeholder="Describe what happened, symptoms of damage, or equipment failure..." required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">{{ old('description') }}</textarea>
            </x-form-group>

            <x-form-group label="Action Taken / Planned Next Steps">
                <input type="text" name="action" placeholder="e.g., Dispatched to technician, Quoted for replacement" value="{{ old('action') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <x-receipt-input label="Photo Evidence or Damage Documentation (Optional)" :showReference="false" icon="fas fa-camera" help="Upload incident photos or repair estimates (PDF, JPG, PNG, up to 5 MB each)." />

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Submit Report</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-report-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Report Modal (Fixed Cancel and click actions) --}}
    <x-modal id="edit-report-modal" title="Edit Incident Report">
        <form class="space-y-4" id="editForm" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Asset" required>
                <select name="asset_id" id="edit_asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->category->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Incident Type" required>
                    <select name="reported_type" id="edit_reported_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                        @foreach($reportedTypes as $rt)
                            <option value="{{ $rt }}">{{ $rt }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Location" required>
                    <input type="text" name="location" id="edit_location" list="location-list-edit" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <datalist id="location-list-edit">
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}">
                        @endforeach
                    </datalist>
                </x-form-group>
            </div>

            <x-form-group label="Issue Description">
                <textarea name="description" id="edit_description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
            </x-form-group>

            <x-form-group label="Action Taken / Planned">
                <input type="text" name="action" id="edit_action" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Update Report</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-report-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddModal() {
                window.openModal('add-report-modal');
            }

            function openEditModal(reportId, assetId, reportedType, location, description, action) {
                const form = document.getElementById('editForm');
                form.action = '/reported-assets/' + reportId;
                document.getElementById('edit_asset_id').value = assetId;
                document.getElementById('edit_reported_type').value = reportedType;
                document.getElementById('edit_location').value = location || '';
                document.getElementById('edit_description').value = description || '';
                document.getElementById('edit_action').value = action || '';
                window.openModal('edit-report-modal');
            }
        </script>
    @endpush
@endsection
