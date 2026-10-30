@extends('layouts.admin')

@section('title', 'Maintenance & Inspection - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Maintenance & Work Orders</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Maintenance & Work Orders']
            ]" />
        </div>
        
        {{-- Contextual Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            @if($activeTab === 'logs')
                <button onclick="openCreateMaintenanceModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Log Maintenance
                </button>
                <button onclick="openAddScheduleModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-calendar-plus"></i>
                    Add Schedule
                </button>
            @else
                <button onclick="openCreateInspectionModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Record Inspection
                </button>
            @endif
        </div>
    </div>

    {{-- Session Alerts --}}


    {{-- High-Level KPI Summary Cards --}}
    @php
        $isLogsTab = $activeTab === 'logs';
        $isInspTab = $activeTab === 'inspections';
        $currStatus = request('status');

        $activeOrdersActive = $isLogsTab && in_array($currStatus, ['active', 'in_progress', 'scheduled']);
        $completedLogsActive = $isLogsTab && $currStatus === 'completed';
        $allInspActive = $isInspTab && empty($currStatus);
        $dueSchedActive = $isLogsTab && in_array($currStatus, ['due_overdue', 'due', 'overdue']);
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Active Maintenance --}}
        <a href="{{ route('maintenance.index', ['tab' => 'logs', 'status' => 'active']) }}" class="bg-white rounded-xl p-4 border {{ $activeOrdersActive ? 'border-indigo-500 ring-2 ring-indigo-500 shadow-md' : 'border-indigo-200 hover:border-indigo-400 hover:shadow-md' }} bg-indigo-50/20 shadow-sm transition-all flex items-center justify-between group block" title="View active work orders">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 mb-0.5">Active Orders</p>
                <p class="text-xl font-bold text-indigo-700">{{ $metrics['active_maintenance'] }}</p>
                <p class="text-[10px] text-indigo-600">In-progress repairs</p>
            </div>
            <div class="w-10 h-10 rounded-lg {{ $activeOrdersActive ? 'bg-indigo-600 text-white' : 'bg-indigo-100 text-indigo-600' }} flex items-center justify-center text-lg group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                <i class="fas fa-wrench"></i>
            </div>
        </a>

        {{-- Completed Tasks --}}
        <a href="{{ route('maintenance.index', ['tab' => 'logs', 'status' => 'completed']) }}" class="bg-white rounded-xl p-4 border {{ $completedLogsActive ? 'border-emerald-500 ring-2 ring-emerald-500 shadow-md' : 'border-emerald-200 hover:border-emerald-400 hover:shadow-md' }} bg-emerald-50/20 shadow-sm transition-all flex items-center justify-between group block" title="View completed work orders">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-0.5">Completed Tasks</p>
                <p class="text-xl font-bold text-emerald-700">{{ $metrics['completed_maintenance'] ?? 0 }}</p>
                <p class="text-[10px] text-emerald-600">Resolved work orders</p>
            </div>
            <div class="w-10 h-10 rounded-lg {{ $completedLogsActive ? 'bg-emerald-600 text-white' : 'bg-emerald-100 text-emerald-600' }} flex items-center justify-center text-lg group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <i class="fas fa-check-double"></i>
            </div>
        </a>

        {{-- Inspections Count --}}
        <a href="{{ route('maintenance.index', ['tab' => 'inspections']) }}" class="bg-white rounded-xl p-4 border {{ $allInspActive ? 'border-blue-500 ring-2 ring-blue-500 shadow-md' : 'border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md' }} transition-all flex items-center justify-between group block" title="View asset inspections">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-0.5 group-hover:text-blue-600 transition-colors">Asset Inspections</p>
                <p class="text-xl font-bold text-gray-900">{{ $metrics['total_inspections'] }}</p>
                <p class="text-[10px] text-gray-400">Quality & safety audits</p>
            </div>
            <div class="w-10 h-10 rounded-lg {{ $allInspActive ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center text-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </a>

        {{-- Preventive Schedules (Due/Overdue) --}}
        <a href="{{ route('maintenance.index', ['tab' => 'logs', 'status' => 'due_overdue']) }}" class="bg-white rounded-xl p-4 border {{ $dueSchedActive ? 'border-amber-500 ring-2 ring-amber-500 shadow-md' : 'border-amber-200 hover:border-amber-400 hover:shadow-md' }} bg-amber-50/20 shadow-sm transition-all flex items-center justify-between group block" title="View due/overdue schedules">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-0.5">Preventive Schedules</p>
                <p class="text-xl font-bold text-amber-700">{{ $metrics['total_schedules'] }}</p>
                <p class="text-[10px] text-amber-600">{{ $metrics['schedules_due'] }} due / overdue</p>
            </div>
            <div class="w-10 h-10 rounded-lg {{ $dueSchedActive ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center text-lg group-hover:bg-amber-600 group-hover:text-white transition-colors">
                <i class="fas fa-calendar-check"></i>
            </div>
        </a>
    </div>

    {{-- Main Module Tabs Navigation --}}
    <div class="flex border-b border-gray-200 mb-6 space-x-1">
        <a 
            href="{{ route('maintenance.index', ['tab' => 'logs']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'logs' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-wrench"></i>
            <span>Maintenance Logs & Work Orders</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'logs' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $maintenances->total() }}
            </span>
        </a>

        <a 
            href="{{ route('maintenance.index', ['tab' => 'inspections']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'inspections' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-clipboard-check"></i>
            <span>Asset Inspections</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'inspections' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $inspections->total() }}
            </span>
        </a>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: MAINTENANCE LOGS & WORK ORDERS                                     --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'logs')
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-wrench text-blue-600"></i>
                        <span>Maintenance Work Orders & Repair Logs</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 font-semibold">
                            {{ $maintenances->total() }}
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Track work orders, corrective repairs, servicing logs, and scheduled preventive routines.</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <button 
                        type="button" 
                        onclick="openAddScheduleModal()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors"
                    >
                        <i class="fas fa-calendar-plus"></i>
                        <span>Add Schedule</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="openCreateMaintenanceModal()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Log Maintenance</span>
                    </button>
                </div>
            </div>

            {{-- Search & Filters for Merged Maintenance Logs --}}
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <form action="{{ route('maintenance.index') }}" method="GET" id="searchLogForm">
                        <input type="hidden" name="tab" value="logs">
                        @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                        @if(request('priority'))<input type="hidden" name="priority" value="{{ request('priority') }}">@endif
                        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                        <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search work orders, schedules, tasks, technician, or asset..." />
                    </form>
                </div>

                {{-- Type Filter --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('type') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                        <span>{{ request('type') ? ucfirst(request('type')) : 'Maintenance Type' }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                        <a href="{{ route('maintenance.index', array_merge(request()->except('type', 'logs_page'), ['tab' => 'logs'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('type') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Types</a>
                        <a href="{{ route('maintenance.index', array_merge(request()->except('logs_page'), ['tab' => 'logs', 'type' => 'preventive'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('type') === 'preventive' ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Preventive (Schedules)</a>
                        @foreach($maintenanceTypes as $t)
                            <a href="{{ route('maintenance.index', array_merge(request()->except('logs_page'), ['tab' => 'logs', 'type' => $t])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('type') === $t ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ ucfirst($t) }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Status Filter --}}
                <div x-data="{ open: false }" class="relative">
                    @php
                        $currentStatus = request('status');
                        $statusLabels = [
                            'due_overdue'      => 'Due / Overdue',
                            'pending'          => 'Pending',
                            'scheduled'        => 'Scheduled',
                            'in_progress'      => 'In Progress',
                            'completed'        => 'Completed',
                            'Monitoring Only'  => 'Monitoring Only',
                            'Maintenance Due'  => 'Maintenance Due',
                            'Overdue'          => 'Overdue',
                        ];
                    @endphp
                    <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ $currentStatus ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                        <span>{{ $currentStatus ? ($statusLabels[$currentStatus] ?? ucfirst(str_replace('_', ' ', $currentStatus))) : 'Status' }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                        <a href="{{ route('maintenance.index', array_merge(request()->except('status', 'logs_page'), ['tab' => 'logs'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !$currentStatus ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Statuses</a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <div class="px-4 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Work Order Statuses</div>
                        @foreach($maintenanceStatuses as $st)
                            <a href="{{ route('maintenance.index', array_merge(request()->except('logs_page'), ['tab' => 'logs', 'status' => $st])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $currentStatus === $st ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</a>
                        @endforeach
                        <div class="border-t border-gray-100 my-1"></div>
                        <div class="px-4 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Schedule Statuses</div>
                        <a href="{{ route('maintenance.index', array_merge(request()->except('logs_page'), ['tab' => 'logs', 'status' => 'due_overdue'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $currentStatus === 'due_overdue' ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Due / Overdue</a>
                        @foreach($scheduleStatuses as $s)
                            <a href="{{ route('maintenance.index', array_merge(request()->except('logs_page'), ['tab' => 'logs', 'status' => $s])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $currentStatus === $s ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ $s }}</a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Merged Maintenance Table --}}
            <x-card>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Work Order / Asset</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type & Priority</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Task & Assigned</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Parts & Details</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Service Dates</th>
                                <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($maintenances as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    {{-- Work Order / Asset Column --}}
                                    <td class="px-5 py-3.5 align-middle text-left">
                                        @if(!empty($item->is_schedule))
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded">
                                                    SCHED-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}
                                                </span>
                                                @if($item->asset)
                                                    <a href="{{ route('assets.show', $item->asset_id) }}" class="text-xs font-mono text-gray-500 hover:text-blue-600">
                                                        [{{ $item->asset->code }}]
                                                    </a>
                                                @endif
                                            </div>
                                            <p class="text-sm font-bold text-gray-900">{{ $item->asset->name ?? 'Unknown Asset' }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $item->asset_type ?: ($item->asset->category->name ?? 'Preventive Schedule') }}</p>
                                        @else
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                                                    {{ $item->work_order_number ?? ('WO-' . str_pad($item->id, 5, '0', STR_PAD_LEFT)) }}
                                                </span>
                                                @if($item->asset)
                                                    <a href="{{ route('assets.show', $item->asset_id) }}" class="text-xs font-mono text-gray-500 hover:text-blue-600">
                                                        [{{ $item->asset->code }}]
                                                    </a>
                                                @endif
                                            </div>
                                            <p class="text-sm font-bold text-gray-900">{{ $item->asset->name ?? 'Unknown Asset' }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $item->asset->category->name ?? 'Uncategorized' }}</p>
                                        @endif
                                    </td>

                                    {{-- Type & Priority Column --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if(!empty($item->is_schedule))
                                            <div class="space-y-1">
                                                <x-badge variant="primary" size="sm">Preventive</x-badge>
                                                <div>
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                                        <i class="fas fa-repeat text-[9px]"></i>
                                                        {{ $item->interval_type }} ({{ $item->interval_days }}d)
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="space-y-1">
                                                <x-badge variant="info" size="sm">{{ ucfirst($item->type) }}</x-badge>
                                                @php
                                                    $priBadge = match($item->priority ?? 'medium') {
                                                        'urgent' => 'bg-rose-100 text-rose-800',
                                                        'high'   => 'bg-amber-100 text-amber-800',
                                                        'low'    => 'bg-blue-100 text-blue-800',
                                                        default  => 'bg-gray-100 text-gray-800',
                                                    };
                                                @endphp
                                                <div>
                                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase rounded {{ $priBadge }}">
                                                        {{ $item->priority ?? 'medium' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Task & Assigned Column --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-700">
                                        @if(!empty($item->is_schedule))
                                            <p class="font-medium text-gray-900">{{ $item->maintenance_task }}</p>
                                            <p class="text-xs text-indigo-600 italic">
                                                <i class="fas fa-calendar-check mr-1"></i>Scheduled Routine
                                            </p>
                                        @else
                                            <p class="font-medium text-gray-900">{{ $item->assignedUser?->name ?? $item->technician ?? 'Unassigned' }}</p>
                                            @if($item->assignedUser && $item->technician && $item->technician !== $item->assignedUser->name)
                                                <p class="text-xs text-gray-400">Shop: {{ $item->technician }}</p>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Parts & Details Column --}}
                                    <td class="px-5 py-3.5 text-xs">
                                        @if(!empty($item->is_schedule))
                                            <span class="text-gray-500 italic">Interval cadence routine</span>
                                        @else
                                            @if($item->parts_used)
                                                <p class="font-medium text-gray-800 truncate max-w-[160px]" title="{{ $item->parts_used }}">
                                                    <i class="fas fa-puzzle-piece text-gray-400 mr-1"></i>{{ $item->parts_used }}
                                                </p>
                                            @else
                                                <span class="text-gray-400 italic">None specified</span>
                                            @endif
                                            @if($item->receipts && $item->receipts->isNotEmpty())
                                                <div class="mt-1">
                                                    <x-receipt-list :receipts="$item->receipts" compact />
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Service Dates Column --}}
                                    <td class="px-5 py-3.5 align-middle whitespace-nowrap text-xs text-gray-600 text-left">
                                        @if(!empty($item->is_schedule))
                                            <div>Last: {{ $item->last_maintenance_date ? \Carbon\Carbon::parse($item->last_maintenance_date)->format('M d, Y') : 'N/A' }}</div>
                                            <div class="font-semibold text-gray-800">Due: {{ $item->next_due_date ? \Carbon\Carbon::parse($item->next_due_date)->format('M d, Y') : 'N/A' }}</div>
                                        @else
                                            <div>Sched: {{ $item->scheduled_date ? \Carbon\Carbon::parse($item->scheduled_date)->format('M d, Y') : 'N/A' }}</div>
                                            @if($item->completed_at)
                                                <div class="text-emerald-700 font-medium">Done: {{ $item->completed_at->format('M d, Y') }}</div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Status Column --}}
                                    <td class="px-5 py-3.5 align-middle whitespace-nowrap text-center">
                                        @if(!empty($item->is_schedule))
                                            @php
                                                $schedColor = match($item->status) {
                                                    'Monitoring Only' => 'success',
                                                    'Maintenance Due' => 'warning',
                                                    'Overdue'         => 'danger',
                                                    default           => 'secondary'
                                                };
                                            @endphp
                                            <x-badge variant="{{ $schedColor }}" size="sm">{{ $item->status }}</x-badge>
                                        @else
                                            @php
                                                $mColor = match($item->status) {
                                                    'completed'   => 'success',
                                                    'in_progress' => 'warning',
                                                    'scheduled'   => 'info',
                                                    'pending'     => 'secondary',
                                                    default       => 'secondary'
                                                };
                                            @endphp
                                            <x-badge variant="{{ $mColor }}" size="sm">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</x-badge>
                                        @endif
                                    </td>

                                    {{-- Actions Column --}}
                                    <td class="px-5 py-3.5 align-middle whitespace-nowrap text-right text-sm">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if(!empty($item->is_schedule))
                                                <button 
                                                    type="button" 
                                                    onclick="openCompleteScheduleModal({{ $item->id }}, '{{ addslashes($item->maintenance_task) }}')"
                                                    class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 transition-colors"
                                                    title="Record Completion"
                                                >
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                <button 
                                                    type="button" 
                                                    onclick='openEditScheduleModal(@json($item))'
                                                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors"
                                                    title="Edit Schedule"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('maintenance-schedules.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Schedule: {{ addslashes($item->maintenance_task ?? 'Maintenance Schedule') }}', 'This planned maintenance routine will be deleted.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button 
                                                    type="button" 
                                                    onclick='openEditMaintenanceModal(@json($item))'
                                                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors"
                                                    title="Edit Work Order"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('maintenance.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Work Order: {{ addslashes($item->title ?? 'Maintenance Log') }}', 'This maintenance work order record will be permanently deleted.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors" title="Delete">
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
                                        <x-empty-state icon="fas fa-wrench" title="No maintenance records found" description="Create a work order or add a preventive schedule to log maintenance." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $maintenances->links() }}
                </div>
            </x-card>
        </div>
    @else
        <div>
            {{-- ----------------------------------------------------------------- --}}
            {{-- ASSET QUALITY & SAFETY INSPECTIONS                                --}}
            {{-- ----------------------------------------------------------------- --}}
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-clipboard-check text-emerald-600"></i>
                            <span>Asset Inspections & Safety Audits</span>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-semibold">
                                {{ $inspections->total() }}
                            </span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Physical inspections, routine safety evaluations, and condition verification.</p>
                    </div>
                    <button 
                        type="button" 
                        onclick="openCreateInspectionModal()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors self-start sm:self-auto"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Record Inspection</span>
                    </button>
                </div>

                {{-- Search & Filters for Inspections --}}
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="flex-1">
                        <form action="{{ route('maintenance.index') }}" method="GET" id="searchInspectionForm">
                            <input type="hidden" name="tab" value="inspections">
                            @if(request('result'))<input type="hidden" name="result" value="{{ request('result') }}">@endif
                            @if(request('inspection_status'))<input type="hidden" name="inspection_status" value="{{ request('inspection_status') }}">@endif
                            @if(request('status') && !in_array(request('status'), ['due_overdue', 'Monitoring Only', 'Maintenance Due', 'Overdue'], true))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                            <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search inspections by inspector, findings, or asset..." />
                        </form>
                    </div>

                    {{-- Result Filter --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('result') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                            <span>{{ request('result') ? ucfirst(str_replace('_', ' ', request('result'))) : 'Result' }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                            <a href="{{ route('maintenance.index', array_merge(request()->except('result', 'inspections_page'), ['tab' => 'inspections'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('result') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Results</a>
                            @foreach($inspectionResults as $res)
                                <a href="{{ route('maintenance.index', array_merge(request()->except('inspections_page'), ['tab' => 'inspections', 'result' => $res])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('result') === $res ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $res)) }}</a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div x-data="{ open: false }" class="relative">
                        @php
                            $inspStatusVal = request('inspection_status') ?? ((request('status') && !in_array(request('status'), ['due_overdue', 'Monitoring Only', 'Maintenance Due', 'Overdue'], true)) ? request('status') : null);
                        @endphp
                        <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ $inspStatusVal ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                            <span>{{ $inspStatusVal ? ucfirst($inspStatusVal) : 'Status' }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                            <a href="{{ route('maintenance.index', array_merge(request()->except('inspection_status', 'status', 'inspections_page'), ['tab' => 'inspections'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !$inspStatusVal ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Statuses</a>
                            @foreach($inspectionStatuses as $st)
                                <a href="{{ route('maintenance.index', array_merge(request()->except('inspection_status', 'status', 'inspections_page'), ['tab' => 'inspections', 'inspection_status' => $st])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $inspStatusVal === $st ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ ucfirst($st) }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Inspections Table --}}
                <x-card>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Asset</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Inspector</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Inspection Date</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Next Due</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Result</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Findings</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($inspections as $insp)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 align-middle text-left">
                                            <span class="inline-block px-2 py-0.5 text-xs font-mono font-semibold rounded bg-gray-100 text-gray-700 border border-gray-200 mb-0.5">
                                                {{ $insp->asset->code ?? 'N/A' }}
                                            </span>
                                            <p class="text-sm font-semibold text-gray-900">{{ $insp->asset->name ?? 'Unknown Asset' }}</p>
                                            <p class="text-xs text-gray-500">{{ $insp->asset->category->name ?? 'Uncategorized' }}</p>
                                        </td>
                                        <td class="px-6 py-4 align-middle whitespace-nowrap text-sm font-medium text-gray-800 text-left">
                                            {{ $insp->inspector ?? 'Unassigned' }}
                                        </td>
                                        <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 text-left">
                                            {{ \Carbon\Carbon::parse($insp->inspection_date)->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 text-left">
                                            {{ $insp->next_due_date ? \Carbon\Carbon::parse($insp->next_due_date)->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                            @php
                                                $rColor = match($insp->result) {
                                                    'passed'           => 'success',
                                                    'failed'           => 'danger',
                                                    'conditional_pass' => 'warning',
                                                    default            => 'secondary'
                                                };
                                            @endphp
                                            <x-badge variant="{{ $rColor }}" size="sm">{{ ucfirst(str_replace('_', ' ', $insp->result)) }}</x-badge>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-sm text-gray-600 max-w-xs text-left">
                                            <span class="truncate block" title="{{ $insp->findings }}">{{ Str::limit($insp->findings, 35) ?: 'None reported' }}</span>
                                        </td>
                                        <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                            @php
                                                $iStatColor = match($insp->status) {
                                                    'completed' => 'success',
                                                    'scheduled' => 'info',
                                                    'overdue'   => 'danger',
                                                    default     => 'secondary'
                                                };
                                            @endphp
                                            <x-badge variant="{{ $iStatColor }}" size="sm">{{ ucfirst($insp->status) }}</x-badge>
                                        </td>
                                        <td class="px-6 py-4 align-middle whitespace-nowrap text-right text-sm">
                                            <div class="flex items-center justify-end gap-2">
                                                <button 
                                                    type="button"
                                                    onclick='openEditInspectionModal(@json($insp))'
                                                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors"
                                                    title="Edit Inspection"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('inspection.destroy', $insp) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Inspection: {{ addslashes($insp->asset->name ?? 'Inspection Record') }}', 'This inspection record will be permanently deleted.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-600 transition-colors" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <x-empty-state icon="fas fa-clipboard-check" title="No inspection records found" description="Record inspection checklists and condition findings." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $inspections->links() }}
                    </div>
                </x-card>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODALS: PREVENTIVE SCHEDULES                                              --}}
    {{-- ========================================================================= --}}
    {{-- 1. Add Schedule Modal --}}
    <x-modal id="add-schedule-modal" title="Add Preventive Maintenance Schedule">
        <form action="{{ route('maintenance-schedules.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <x-form-group label="Select Asset" required>
                <select name="asset_id" id="add_sched_asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">-- Choose Asset --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }} ({{ $asset->type->name ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Maintenance Task Name" required>
                <input type="text" name="maintenance_task" placeholder="e.g. Spark Plug & Oil Change" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Interval Type" required>
                    <select name="interval_type" id="add_sched_interval_type" onchange="autoFillDays('add_sched')" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="Monthly">Monthly</option>
                        <option value="Quarterly">Quarterly</option>
                        <option value="Yearly">Yearly</option>
                    </select>
                </x-form-group>

                <x-form-group label="Interval in Days" required>
                    <input type="number" name="interval_days" id="add_sched_interval_days" value="30" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <x-form-group label="Last Maintenance Date" required>
                <input type="date" name="last_maintenance_date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <x-receipt-input label="Maintenance Procedure / Manual / Checklist (Optional)" :showReference="false" icon="fas fa-file-pdf" />

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    Save Schedule
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-schedule-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- 2. Edit Schedule Modal --}}
    <x-modal id="edit-schedule-modal" title="Edit Preventive Maintenance Schedule">
        <form id="editScheduleForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <x-form-group label="Asset" required>
                <select name="asset_id" id="edit_sched_asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group label="Maintenance Task" required>
                <input type="text" name="maintenance_task" id="edit_sched_task" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Interval Type" required>
                    <select name="interval_type" id="edit_sched_interval_type" onchange="autoFillDays('edit_sched')" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="Monthly">Monthly</option>
                        <option value="Quarterly">Quarterly</option>
                        <option value="Yearly">Yearly</option>
                    </select>
                </x-form-group>

                <x-form-group label="Interval in Days" required>
                    <input type="number" name="interval_days" id="edit_sched_interval_days" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <x-form-group label="Last Maintenance Date" required>
                <input type="date" name="last_maintenance_date" id="edit_sched_last_date" max="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    Update Schedule
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-schedule-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- 3. Complete Schedule Modal --}}
    <x-modal id="complete-schedule-modal" title="Complete Preventive Maintenance">
        <form id="completeScheduleForm" method="POST" class="space-y-4">
            @csrf

            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <p class="text-sm text-blue-900">
                    Marking task <strong id="complete_sched_task_name"></strong> as completed. This will calculate the new Next Due Date and record an entry into the Maintenance history log.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Completion Date" required>
                    <input type="date" name="completion_date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Technician / Service Provider">
                    <input type="text" name="technician" placeholder="Who executed this task?" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <x-form-group label="Service Cost (PHP) (Optional)">
                <input type="number" step="0.01" min="0" name="cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                <p class="text-[11px] text-gray-500 mt-1">Optional. If entered, this cost will be tracked under Asset Expenses.</p>
            </x-form-group>

            <x-form-group label="Notes & Findings">
                <textarea name="notes" rows="3" placeholder="Condition details, replacement parts, or service notes..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    <i class="fas fa-check-circle mr-1.5"></i> Confirm Completion
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('complete-schedule-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- ========================================================================= --}}
    {{-- MODALS: MAINTENANCE LOGS & REPAIRS                                        --}}
    {{-- ========================================================================= --}}
    {{-- 4. Create Maintenance Modal --}}
    <x-modal id="create-maintenance-modal" title="Log Maintenance or Repair Work Order">
        <form action="{{ route('maintenance.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <x-form-group label="Select Asset" required>
                <select name="asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <option value="">-- Choose Asset --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Maintenance Type" required>
                    <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="routine">Routine</option>
                        <option value="repair" selected>Repair / Corrective</option>
                        <option value="preventive">Preventive</option>
                        <option value="inspection">Inspection</option>
                        <option value="overhaul">Overhaul</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </x-form-group>

                <x-form-group label="Priority" required>
                    <select name="priority" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </x-form-group>

                <x-form-group label="Status" required>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="scheduled">Scheduled</option>
                        <option value="in_progress" selected>In Progress (Set Under Maintenance)</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Assigned Internal Staff / Officer">
                    <select name="assigned_to" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="">-- Optional: Assign Staff Officer --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Technician / Service Shop">
                    <input type="text" name="technician" placeholder="e.g. In-house Mechanic, AutoFix" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Cost (PHP) (Optional)">
                    <input type="number" step="0.01" min="0" name="cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="text-[11px] text-gray-500 mt-1">Optional. If entered, this cost will be tracked under Asset Expenses.</p>
                </x-form-group>

                <x-form-group label="Scheduled / Service Date">
                    <input type="date" name="scheduled_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Estimated Duration">
                    <input type="text" name="estimated_duration" placeholder="e.g. 2 hours, 3 days" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Spare Parts Used / Replaced">
                    <input type="text" name="parts_used" placeholder="e.g. Spark plug, hydraulic seals" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <x-receipt-input label="Proof of Cost / Service Receipt / Official Receipt (Optional)" help="Upload service receipt, repair quotation, or invoice as proof of maintenance cost (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

            <x-form-group label="Work Notes & Description">
                <textarea name="notes" rows="2" placeholder="Describe the problem, service required, or components..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    Create Work Order
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('create-maintenance-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- 5. Edit Maintenance Modal --}}
    <x-modal id="edit-maintenance-modal" title="Edit Maintenance Work Order">
        <form id="editMaintenanceForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <x-form-group label="Asset" required>
                <select name="asset_id" id="edit_maint_asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Maintenance Type" required>
                    <select name="type" id="edit_maint_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="routine">Routine</option>
                        <option value="repair">Repair</option>
                        <option value="preventive">Preventive</option>
                        <option value="inspection">Inspection</option>
                        <option value="overhaul">Overhaul</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </x-form-group>

                <x-form-group label="Priority" required>
                    <select name="priority" id="edit_maint_priority" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </x-form-group>

                <x-form-group label="Status" required>
                    <select name="status" id="edit_maint_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="scheduled">Scheduled</option>
                        <option value="in_progress">In Progress</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Assigned Staff Officer">
                    <select name="assigned_to" id="edit_maint_assigned_to" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="">-- Unassigned --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Technician / Shop">
                    <input type="text" name="technician" id="edit_maint_technician" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Cost (PHP) (Optional)">
                    <input type="number" step="0.01" min="0" name="cost" id="edit_maint_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="text-[11px] text-gray-500 mt-1">Optional (tracked in Asset Expenses)</p>
                </x-form-group>

                <x-form-group label="Service Date">
                    <input type="date" name="scheduled_date" id="edit_maint_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Estimated Duration">
                    <input type="text" name="estimated_duration" id="edit_maint_duration" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <x-form-group label="Parts Used">
                <input type="text" name="parts_used" id="edit_maint_parts" placeholder="Spare parts or materials" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <x-form-group label="Notes">
                <textarea name="notes" id="edit_maint_notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    Save Changes
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-maintenance-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Complete Work Order Modal --}}
    <x-modal id="complete-workorder-modal" title="Complete Maintenance Work Order">
        <form id="completeWorkOrderForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <p class="text-xs text-emerald-900 font-medium">
                    Signing off work order <strong id="complete_wo_number" class="font-mono font-bold text-emerald-950"></strong>. This will mark the maintenance as completed, log final expenditures, and restore the asset's status to <strong>Available</strong>.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Final Service Cost (PHP) (Optional)">
                    <input type="number" step="0.01" min="0" name="cost" id="complete_wo_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                    <p class="text-[11px] text-gray-500 mt-1">Optional. Recorded in Asset Expenses ledger.</p>
                </x-form-group>

                <x-form-group label="Parts & Materials Used">
                    <input type="text" name="parts_used" placeholder="e.g. 2L hydraulic oil, filter seal" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                </x-form-group>
            </div>

            <x-receipt-input label="Proof of Cost / Official Receipt (OR) / Final Invoice (Optional)" help="Upload official receipt or final service invoice as proof of cost (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

            <x-form-group label="Sign-Off & Quality Notes">
                <textarea name="notes" rows="2" placeholder="Quality verification remarks, technician sign-off..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 pt-3 border-t border-gray-200">
                <x-button variant="primary" type="submit" class="flex-1">Confirm & Sign Off</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('complete-workorder-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- ========================================================================= --}}
    {{-- MODALS: ASSET INSPECTIONS                                                 --}}
    {{-- ========================================================================= --}}
    {{-- 6. Create Inspection Modal --}}
    <x-modal id="create-inspection-modal" title="Record Asset Inspection">
        <form action="{{ route('inspection.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <x-form-group label="Select Asset" required>
                <select name="asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">-- Choose Asset --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Inspector / Evaluator" required>
                    <input type="text" name="inspector" placeholder="e.g. Officer Cruz, Lead Rescuer" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Inspection Date" required>
                    <input type="date" name="inspection_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Result" required>
                    <select name="result" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="passed" selected>Passed</option>
                        <option value="failed">Failed</option>
                        <option value="conditional_pass">Conditional Pass</option>
                    </select>
                </x-form-group>

                <x-form-group label="Status" required>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="completed" selected>Completed</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </x-form-group>
            </div>

            <x-form-group label="Next Due Date">
                <input type="date" name="next_due_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <x-form-group label="Findings & Recommendations">
                <textarea name="findings" rows="3" placeholder="Document physical condition, pressure, fraying, wear..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
            </x-form-group>

            <x-receipt-input label="Inspection Photos, Checklist or Report (Optional)" :showReference="false" icon="fas fa-camera" />

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    Record Inspection
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('create-inspection-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- 7. Edit Inspection Modal --}}
    <x-modal id="edit-inspection-modal" title="Edit Inspection Record">
        <form id="editInspectionForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <x-form-group label="Asset" required>
                <select name="asset_id" id="edit_insp_asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Inspector" required>
                    <input type="text" name="inspector" id="edit_insp_inspector" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Inspection Date" required>
                    <input type="date" name="inspection_date" id="edit_insp_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Result" required>
                    <select name="result" id="edit_insp_result" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="passed">Passed</option>
                        <option value="failed">Failed</option>
                        <option value="conditional_pass">Conditional Pass</option>
                    </select>
                </x-form-group>

                <x-form-group label="Status" required>
                    <select name="status" id="edit_insp_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="completed">Completed</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </x-form-group>
            </div>

            <x-form-group label="Next Due Date">
                <input type="date" name="next_due_date" id="edit_insp_next_due" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <x-form-group label="Findings">
                <textarea name="findings" id="edit_insp_findings" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    Save Changes
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-inspection-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            // Schedules
            function openAddScheduleModal() {
                window.openModal('add-schedule-modal');
            }

            function openEditScheduleModal(schedule) {
                const form = document.getElementById('editScheduleForm');
                form.action = '/maintenance-schedules/' + schedule.id;

                document.getElementById('edit_sched_asset_id').value = schedule.asset_id;
                document.getElementById('edit_sched_task').value = schedule.maintenance_task || '';
                document.getElementById('edit_sched_interval_type').value = schedule.interval_type || 'Monthly';
                document.getElementById('edit_sched_interval_days').value = schedule.interval_days || 30;

                let lastDate = schedule.last_maintenance_date || '';
                if (lastDate.includes('T')) lastDate = lastDate.split('T')[0];
                document.getElementById('edit_sched_last_date').value = lastDate;

                window.openModal('edit-schedule-modal');
            }

            function openCompleteScheduleModal(id, taskName) {
                const form = document.getElementById('completeScheduleForm');
                form.action = '/maintenance-schedules/' + id + '/complete';
                document.getElementById('complete_sched_task_name').textContent = taskName;
                window.openModal('complete-schedule-modal');
            }

            function autoFillDays(prefix) {
                const typeEl = document.getElementById(prefix + '_interval_type');
                const daysEl = document.getElementById(prefix + '_interval_days');
                if (typeEl.value === 'Monthly') daysEl.value = 30;
                else if (typeEl.value === 'Quarterly') daysEl.value = 90;
                else if (typeEl.value === 'Yearly') daysEl.value = 365;
            }

            // Maintenance Logs
            function openCreateMaintenanceModal() {
                window.openModal('create-maintenance-modal');
            }

            function openEditMaintenanceModal(item) {
                const form = document.getElementById('editMaintenanceForm');
                form.action = '/maintenance/' + item.id;

                document.getElementById('edit_maint_asset_id').value = item.asset_id;
                document.getElementById('edit_maint_type').value = item.type || 'routine';
                document.getElementById('edit_maint_priority').value = item.priority || 'medium';
                document.getElementById('edit_maint_status').value = item.status || 'scheduled';
                document.getElementById('edit_maint_assigned_to').value = item.assigned_to || '';
                document.getElementById('edit_maint_technician').value = item.technician || '';
                document.getElementById('edit_maint_cost').value = item.cost || '';
                document.getElementById('edit_maint_parts').value = item.parts_used || '';

                let sDate = item.scheduled_date || '';
                if (sDate.includes('T')) sDate = sDate.split('T')[0];
                document.getElementById('edit_maint_date').value = sDate;

                document.getElementById('edit_maint_duration').value = item.estimated_duration || '';
                document.getElementById('edit_maint_notes').value = item.notes || '';

                window.openModal('edit-maintenance-modal');
            }

            function openCompleteWorkOrderModal(id, woNumber, cost) {
                const form = document.getElementById('completeWorkOrderForm');
                form.action = '/maintenance/' + id + '/complete';
                document.getElementById('complete_wo_number').textContent = woNumber;
                document.getElementById('complete_wo_cost').value = cost || '';
                window.openModal('complete-workorder-modal');
            }

            // Inspections
            function openCreateInspectionModal() {
                window.openModal('create-inspection-modal');
            }

            function openEditInspectionModal(insp) {
                const form = document.getElementById('editInspectionForm');
                form.action = '/inspection/' + insp.id;

                document.getElementById('edit_insp_asset_id').value = insp.asset_id;
                document.getElementById('edit_insp_inspector').value = insp.inspector || '';

                let iDate = insp.inspection_date || '';
                if (iDate.includes('T')) iDate = iDate.split('T')[0];
                document.getElementById('edit_insp_date').value = iDate;

                let nDate = insp.next_due_date || '';
                if (nDate.includes('T')) nDate = nDate.split('T')[0];
                document.getElementById('edit_insp_next_due').value = nDate;

                document.getElementById('edit_insp_result').value = insp.result || 'passed';
                document.getElementById('edit_insp_status').value = insp.status || 'completed';
                document.getElementById('edit_insp_findings').value = insp.findings || '';

                window.openModal('edit-inspection-modal');
            }
        </script>
    @endpush
@endsection
