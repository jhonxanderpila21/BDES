@extends('layouts.admin')

@section('title', 'Preventive Maintenance - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Preventive Maintenance</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Preventive Maintenance']
            ]" />
        </div>
        <button onclick="openAddModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class="fas fa-plus"></i>
            Add Schedule
        </button>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <form action="{{ route('maintenance-schedules.index') }}" method="GET" id="searchForm">
                @if(request('asset_type'))<input type="hidden" name="asset_type" value="{{ request('asset_type') }}">@endif
                @if(request('interval_type'))<input type="hidden" name="interval_type" value="{{ request('interval_type') }}">@endif
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by task or asset..." />
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
                <a href="{{ route('maintenance-schedules.index', array_merge(request()->except('asset_type', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('asset_type') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Types</a>
                @foreach($assetTypes as $type)
                    <a href="{{ route('maintenance-schedules.index', array_merge(request()->except('page'), ['asset_type' => $type])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('asset_type') === $type ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $type }}</a>
                @endforeach
            </div>
        </div>

        {{-- Interval Filter --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('interval_type') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                <i class="fas fa-clock"></i>
                {{ request('interval_type') ?: 'Interval' }}
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('maintenance-schedules.index', array_merge(request()->except('interval_type', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('interval_type') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Intervals</a>
                @foreach($intervalTypes as $interval)
                    <a href="{{ route('maintenance-schedules.index', array_merge(request()->except('page'), ['interval_type' => $interval])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('interval_type') === $interval ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $interval }}</a>
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
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('maintenance-schedules.index', array_merge(request()->except('status', 'page'))) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('status') ? 'text-blue-600 font-medium' : 'text-gray-700' }}">All Statuses</a>
                @foreach($statuses as $s)
                    <a href="{{ route('maintenance-schedules.index', array_merge(request()->except('page'), ['status' => $s])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === $s ? 'text-blue-600 font-medium' : 'text-gray-700' }}">{{ $s }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Schedules Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asset Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Task</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Interval</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Last Done</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Next Due</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($schedules as $schedule)
                        <tr class="hover:bg-gray-50 transition-colors {{ $schedule->status === 'Overdue' ? 'bg-red-50/30' : '' }}">
                            {{-- Asset --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $schedule->asset->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $schedule->asset->code ?? '' }}</p>
                            </td>
                            {{-- Asset Type --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="info" size="sm">{{ $schedule->asset_type }}</x-badge>
                            </td>
                            {{-- Task --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">{{ $schedule->maintenance_task }}</td>
                            {{-- Interval --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-700">{{ $schedule->interval_type }}</p>
                                <p class="text-xs text-gray-500">{{ $schedule->interval_days }} days</p>
                            </td>
                            {{-- Last Maintenance Date --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $schedule->last_maintenance_date->format('M d, Y') }}</td>
                            {{-- Next Due Date --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $schedule->isCritical() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                {{ $schedule->next_due_date->format('M d, Y') }}
                            </td>
                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($schedule->status === 'Monitoring Only')
                                    <x-badge variant="success" size="sm">{{ $schedule->status }}</x-badge>
                                @elseif($schedule->status === 'Maintenance Due')
                                    <x-badge variant="warning" size="sm">{{ $schedule->status }}</x-badge>
                                @else
                                    <x-badge variant="danger" size="sm">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> {{ $schedule->status }}
                                    </x-badge>
                                @endif
                            </td>
                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Mark Complete --}}
                                    <button onclick="openCompleteModal({{ $schedule->id }}, '{{ addslashes($schedule->maintenance_task) }}')"
                                            class="p-2 rounded hover:bg-green-100 transition-colors text-green-600" title="Mark as Completed">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    {{-- Edit --}}
                                    <button onclick="openEditModal({{ json_encode($schedule) }})"
                                            class="p-2 rounded hover:bg-gray-100 transition-colors text-yellow-600" title="Edit Schedule">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {{-- Delete --}}
                                    <form action="{{ route('maintenance-schedules.destroy', $schedule) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this maintenance schedule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded hover:bg-red-100 transition-colors text-red-600" title="Delete Schedule">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8">
                                <x-empty-state icon="fas fa-calendar-times" title="No preventive maintenance schedules found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $schedules->links() }}
        </div>
    </x-card>

    {{-- Add Schedule Modal --}}
    <x-modal id="add-schedule-modal" title="Add Preventive Maintenance">
        <form class="space-y-4" action="{{ route('maintenance-schedules.store') }}" method="POST">
            @csrf

            <x-form-group label="Asset" required>
                <select name="asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Maintenance Task" required>
                <input type="text" name="maintenance_task" placeholder="e.g., Oil Change, Hull Inspection" value="{{ old('maintenance_task') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Interval Type" required>
                    <select name="interval_type" id="add_interval_type" onchange="autoFillDays('add')" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Interval</option>
                        @foreach($intervalTypes as $interval)
                            <option value="{{ $interval }}" {{ old('interval_type') == $interval ? 'selected' : '' }}>{{ $interval }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Interval (Days)" required>
                    <input type="number" min="1" name="interval_days" id="add_interval_days" value="{{ old('interval_days') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <x-form-group label="Last Maintenance Date" required>
                <input type="date" max="{{ date('Y-m-d') }}" name="last_maintenance_date" value="{{ old('last_maintenance_date') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Create Schedule</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['add-schedule-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Schedule Modal --}}
    <x-modal id="edit-schedule-modal" title="Edit Maintenance Schedule">
        <form class="space-y-4" id="editForm" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Asset" required>
                <select name="asset_id" id="edit_asset_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Maintenance Task" required>
                <input type="text" name="maintenance_task" id="edit_maintenance_task" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Interval Type" required>
                    <select name="interval_type" id="edit_interval_type" onchange="autoFillDays('edit')" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($intervalTypes as $interval)
                            <option value="{{ $interval }}">{{ $interval }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Interval (Days)" required>
                    <input type="number" min="1" name="interval_days" id="edit_interval_days" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <x-form-group label="Last Maintenance Date" required>
                <input type="date" max="{{ date('Y-m-d') }}" name="last_maintenance_date" id="edit_last_maintenance_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">Update Schedule</x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['edit-schedule-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Complete Task Modal --}}
    <x-modal id="complete-modal" title="Mark as Completed">
        <form class="space-y-4" id="completeForm" method="POST">
            @csrf

            <div class="bg-blue-50 rounded-lg p-4 mb-2">
                <p class="text-sm text-blue-800">
                    You are marking the task <strong id="complete_task_name"></strong> as complete. This will update the Last Maintenance Date, calculate the new Next Due Date, and log the action in the Maintenance History.
                </p>
            </div>

            <x-form-group label="Date Completed" required>
                <input type="date" max="{{ date('Y-m-d') }}" name="completion_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <x-form-group label="Technician (Optional)">
                <input type="text" name="technician" placeholder="Who performed the task?" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <x-form-group label="Notes (Optional)">
                <textarea name="notes" rows="3" placeholder="Any relevant findings..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    <i class="fas fa-check-circle mr-2"></i> Confirm Completion
                </x-button>
                <x-button variant="secondary" type="button" @click="ModalStore['complete-modal'] = false" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function openAddModal() {
                window.openModal('add-schedule-modal');
            }

            function openEditModal(schedule) {
                document.getElementById('editForm').action = '/maintenance-schedules/' + schedule.id;
                document.getElementById('edit_asset_id').value = schedule.asset_id;
                document.getElementById('edit_maintenance_task').value = schedule.maintenance_task;
                document.getElementById('edit_interval_type').value = schedule.interval_type;
                document.getElementById('edit_interval_days').value = schedule.interval_days;
                
                // Format date correctly if it includes time
                let dateStr = schedule.last_maintenance_date;
                if(dateStr && dateStr.includes('T')) {
                    dateStr = dateStr.split('T')[0];
                }
                document.getElementById('edit_last_maintenance_date').value = dateStr;
                
                window.openModal('edit-schedule-modal');
            }

            function openCompleteModal(id, taskName) {
                document.getElementById('completeForm').action = '/maintenance-schedules/' + id + '/complete';
                document.getElementById('complete_task_name').textContent = taskName;
                window.openModal('complete-modal');
            }

            function autoFillDays(prefix) {
                const typeEl = document.getElementById(prefix + '_interval_type');
                const daysEl = document.getElementById(prefix + '_interval_days');
                
                if (typeEl.value === 'Monthly') daysEl.value = 30;
                else if (typeEl.value === 'Quarterly') daysEl.value = 90;
                else if (typeEl.value === 'Yearly') daysEl.value = 365;
            }
        </script>
    @endpush
@endsection
