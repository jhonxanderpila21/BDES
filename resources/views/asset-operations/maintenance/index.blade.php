@extends('layouts.admin')

@section('title', 'Maintenance & Inspection - BDES')

@section('content')
    @php
        $isReadOnlyStaff = auth()->user()?->isViewer() || auth()->user()?->hasRole('viewer', 'staff');
    @endphp

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Maintenance</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Operations'],
                ['label' => 'Maintenance']
            ]" />
        </div>
        
        {{-- Contextual Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            @if($isReadOnlyStaff)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                    <i class="fas fa-shield-alt text-amber-600"></i>
                    Read-Only Staff Access
                </span>
            @else
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
            @endif
        </div>
    </div>

    {{-- Session Alerts --}}


    {{-- High-Level KPI Summary Cards --}}
    @php
        $isLogsTab = $activeTab === 'logs';
        $isInspTab = $activeTab === 'inspections';
        $currStatus = request('status');

        $activeOrdersActive = $isLogsTab && in_array($currStatus, ['active', 'in_progress', 'scheduled', 'pending']);
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
            <div class="mb-4">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-wrench text-blue-600"></i>
                    <span>Maintenance Work Orders & Repair Logs</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 font-semibold">
                        {{ $maintenances->total() }}
                    </span>
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Track work orders, corrective repairs, servicing logs, and scheduled preventive routines.</p>
            </div>

            {{-- Search & Filters for Merged Maintenance Logs --}}
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <form action="{{ route('maintenance.index') }}" method="GET" id="searchLogForm">
                        <input type="hidden" name="tab" value="logs">
                        @if(request('record_type'))<input type="hidden" name="record_type" value="{{ request('record_type') }}">@endif
                        @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                        @if(request('priority'))<input type="hidden" name="priority" value="{{ request('priority') }}">@endif
                        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                        <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search work orders, schedules, tasks, technician, or asset..." />
                    </form>
                </div>

                {{-- Record Type Filter (Requirement 2) --}}
                <div x-data="{ open: false }" class="relative">
                    @php
                        $currentRecordType = request('record_type', 'all');
                        $recordTypeLabels = [
                            'all'         => 'All Records',
                            'work_orders' => 'Work Orders (WO)',
                            'schedules'   => 'Schedules (SCHED)',
                        ];
                    @endphp
                    <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ $currentRecordType !== 'all' ? 'bg-indigo-50 border-indigo-300 text-indigo-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                        <span>{{ $recordTypeLabels[$currentRecordType] ?? 'Record Type' }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                        <a href="{{ route('maintenance.index', array_merge(request()->except('record_type', 'logs_page'), ['tab' => 'logs', 'record_type' => 'all'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $currentRecordType === 'all' ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Records</a>
                        <a href="{{ route('maintenance.index', array_merge(request()->except('record_type', 'logs_page'), ['tab' => 'logs', 'record_type' => 'work_orders'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $currentRecordType === 'work_orders' ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">
                            <i class="fas fa-tools text-blue-500 mr-1.5"></i>Work Orders (WO)
                        </a>
                        <a href="{{ route('maintenance.index', array_merge(request()->except('record_type', 'logs_page'), ['tab' => 'logs', 'record_type' => 'schedules'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $currentRecordType === 'schedules' ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">
                            <i class="fas fa-calendar-check text-purple-500 mr-1.5"></i>Maintenance Schedules (SCHED)
                        </a>
                    </div>
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
                            'active'           => 'Active Orders',
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
                                <th class="px-3.5 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Work Order / Asset</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type & Priority</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Task & Assigned</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Parts &amp; Materials</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Service Dates</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-3.5 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($maintenances as $item)
                                <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer group"
                                    onclick="if(!event.target.closest('button, a, form, input, select')) {{ !empty($item->is_schedule) ? 'openViewScheduleModal(' . json_encode($item) . ')' : 'openViewMaintenanceModal(' . json_encode($item) . ')' }};"
                                    title="Click to view details">
                                    {{-- Work Order / Asset Column --}}
                                    <td class="px-3.5 py-3 align-middle text-left">
                                        @if(!empty($item->is_schedule))
                                            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                                <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded whitespace-nowrap">
                                                    <i class="fas fa-calendar-check text-[10px]"></i> SCHED-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}
                                                </span>
                                                @if($item->asset)
                                                    @if($isReadOnlyStaff)
                                                        <span class="text-xs font-mono text-gray-500 whitespace-nowrap">
                                                            [{{ $item->asset->code }}]
                                                        </span>
                                                    @else
                                                        <a href="{{ route('assets.show', $item->asset_id) }}" class="text-xs font-mono text-gray-500 hover:text-blue-600 whitespace-nowrap">
                                                            [{{ $item->asset->code }}]
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                            <p class="text-sm font-bold text-gray-900">{{ $item->asset->name ?? 'Unknown Asset' }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $item->asset_type ?: ($item->asset->category->name ?? 'Preventive Schedule') }}</p>
                                        @else
                                            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                                <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded whitespace-nowrap">
                                                    <i class="fas fa-tools text-[10px]"></i> {{ $item->work_order_number ?? ('WO-' . str_pad($item->id, 5, '0', STR_PAD_LEFT)) }}
                                                </span>
                                                @if($item->asset)
                                                    @if($isReadOnlyStaff)
                                                        <span class="text-xs font-mono text-gray-500 whitespace-nowrap">
                                                            [{{ $item->asset->code }}]
                                                        </span>
                                                    @else
                                                        <a href="{{ route('assets.show', $item->asset_id) }}" class="text-xs font-mono text-gray-500 hover:text-blue-600 whitespace-nowrap">
                                                            [{{ $item->asset->code }}]
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                            <p class="text-sm font-bold text-gray-900">{{ $item->asset->name ?? 'Unknown Asset' }}</p>
                                            <div class="flex items-center gap-2 text-[11px] text-gray-400">
                                                <span>{{ $item->asset->category->name ?? 'Uncategorized' }}</span>
                                                @if($item->inspection_id)
                                                    <span class="inline-flex items-center gap-1 text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200 font-medium" title="Origin: Inspection #{{ $item->inspection_id }}">
                                                        <i class="fas fa-clipboard-check text-[9px] text-amber-600"></i> Insp #{{ $item->inspection_id }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Type & Priority Column --}}
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        @if(!empty($item->is_schedule))
                                            <div class="space-y-1">
                                                <x-badge variant="primary" size="sm">Preventive</x-badge>
                                                <div>
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-100">
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
                                    <td class="px-3 py-3 text-sm text-gray-700">
                                        @if(!empty($item->is_schedule))
                                            <p class="font-medium text-gray-900">{{ $item->maintenance_task }}</p>
                                            <p class="text-xs text-purple-600 italic">
                                                <i class="fas fa-calendar-check mr-1"></i>Scheduled Routine
                                            </p>
                                        @else
                                            @if($item->assignedPersonnel && $item->assignedPersonnel->isNotEmpty())
                                                <div class="flex flex-wrap items-center gap-1 max-w-[180px]">
                                                    @foreach($item->assignedPersonnel as $person)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200" title="{{ $person->name }} ({{ ucfirst($person->role) }})">
                                                             <i class="fas fa-user-circle text-[10px] text-blue-500"></i>
                                                             {{ $person->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="font-medium text-gray-900">{{ $item->assignedUser?->name ?? $item->technician ?? 'Unassigned' }}</p>
                                            @endif
                                            @if($item->technician && ($item->assignedPersonnel->isEmpty() || $item->technician !== $item->assignedUser?->name))
                                                <p class="text-xs text-gray-400 mt-0.5">Shop/Tech: {{ $item->technician }}</p>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Parts & Materials Column (Requirement 1 & 5) --}}
                                    <td class="px-3 py-3 text-xs">
                                        @if(!empty($item->is_schedule))
                                            <div class="space-y-0.5">
                                                <span class="text-gray-600 font-medium">Routine Cadence</span>
                                                <p class="text-[11px] text-gray-400">{{ ucfirst($item->interval_type) }} cycle</p>
                                            </div>
                                        @else
                                            @if($item->parts && $item->parts->isNotEmpty())
                                                <div class="space-y-1">
                                                    @foreach($item->parts->take(2) as $p)
                                                        <div class="flex items-center gap-1 text-xs">
                                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-slate-100 text-slate-800 border border-slate-200 font-mono">
                                                                {{ $p->quantity }} {{ $p->unit }}
                                                            </span>
                                                            <span class="font-medium text-gray-800 truncate max-w-[110px]" title="{{ $p->part_name }}">{{ $p->part_name }}</span>
                                                            @if($p->total_cost > 0)
                                                                <span class="text-gray-400 text-[10px]">(₱{{ number_format($p->total_cost, 2) }})</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                    @if($item->parts->count() > 2)
                                                        <span class="text-[10px] text-blue-600 font-semibold">+{{ $item->parts->count() - 2 }} more parts</span>
                                                    @endif
                                                </div>
                                            @elseif($item->parts_used)
                                                <p class="font-medium text-gray-800 truncate max-w-[140px]" title="{{ $item->parts_used }}">
                                                    <i class="fas fa-boxes text-gray-400 mr-1"></i>{{ $item->parts_used }}
                                                </p>
                                            @else
                                                <span class="text-gray-400 italic">No parts consumed</span>
                                            @endif

                                            @if($item->cost > 0)
                                                <div class="text-[11px] font-semibold text-emerald-700 mt-1">
                                                    <i class="fas fa-coins text-[10px] mr-1"></i>₱{{ number_format($item->cost, 2) }}
                                                </div>
                                            @endif

                                            @if($item->receipts && $item->receipts->isNotEmpty())
                                                <div class="mt-1">
                                                    <x-receipt-list :receipts="$item->receipts" compact />
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Service Dates Column (Requirement 3) --}}
                                    <td class="px-3 py-3 align-middle whitespace-nowrap text-xs text-gray-600 text-left">
                                        @if(!empty($item->is_schedule))
                                            <div class="space-y-0.5">
                                                <div><span class="text-gray-500 font-normal">Last Serviced:</span> <span class="font-medium text-gray-900">{{ $item->last_maintenance_date ? \Carbon\Carbon::parse($item->last_maintenance_date)->format('M d, Y') : 'Never' }}</span></div>
                                                <div><span class="text-gray-500 font-normal">Next Due:</span> <span class="font-semibold {{ $item->status === 'Overdue' ? 'text-red-600 font-bold' : 'text-gray-900' }}">{{ $item->next_due_date ? \Carbon\Carbon::parse($item->next_due_date)->format('M d, Y') : 'N/A' }}</span></div>
                                            </div>
                                        @else
                                            <div class="space-y-0.5">
                                                <div><span class="text-gray-400">Created:</span> <span class="text-gray-700">{{ $item->created_at ? $item->created_at->format('M d, Y') : '—' }}</span></div>
                                                <div><span class="text-gray-400">Scheduled:</span> <span class="text-gray-700">{{ $item->scheduled_date ? \Carbon\Carbon::parse($item->scheduled_date)->format('M d, Y') : 'Not set' }}</span></div>
                                                @if($item->started_at)
                                                    <div><span class="text-blue-500 font-medium">Started:</span> <span class="text-blue-700 font-medium">{{ $item->started_at->format('M d, Y') }}</span></div>
                                                @endif
                                                <div><span class="text-gray-400">Status:</span> <span class="{{ $item->completed_at ? 'text-emerald-700 font-medium' : 'text-gray-400 italic' }}">{{ $item->completed_at ? $item->completed_at->format('M d, Y') : ($item->status === 'completed' ? 'Completed' : 'Pending') }}</span></div>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Status Column --}}
                                    <td class="px-3 py-3 align-middle whitespace-nowrap text-center">
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
                                            <div class="mt-1">
                                                @if($item->isConfirmed())
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200" title="Confirmed by {{ $item->confirmedByUser?->name ?? 'Staff' }} on {{ $item->confirmed_at->format('M d, Y') }}">
                                                        <i class="fas fa-check-double text-[9px] text-emerald-600"></i>
                                                        Confirmed
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                                        <i class="fas fa-clock text-[9px] text-amber-600"></i>
                                                        Awaiting Confirm
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Actions Column --}}
                                    <td class="px-3.5 py-3 align-middle whitespace-nowrap text-right text-sm">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if(!empty($item->is_schedule))
                                                @if(!$isReadOnlyStaff)
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
                                                @endif
                                            @else
                                                @if(!$isReadOnlyStaff)
                                                    @if($item->status !== 'completed')
                                                        <button 
                                                            type="button" 
                                                            onclick='openCompleteWorkOrderModal(@json($item))'
                                                            class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 hover:text-emerald-800 transition-colors"
                                                            title="Complete & Verify Operational Status"
                                                        >
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    @endif
                                                    <button 
                                                        type="button"
                                                        onclick='openAssignMaintenancePersonnelModal(@json($item))'
                                                        class="p-1.5 rounded-lg hover:bg-indigo-50 text-indigo-600 hover:text-indigo-800 transition-colors"
                                                        title="Assign Personnel"
                                                    >
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        onclick='openEditMaintenanceModal(@json($item))'
                                                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors"
                                                        title="Edit Work Order"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('maintenance.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Work Order: [{{ addslashes($item->work_order_number) }}] ({{ addslashes($item->asset->name ?? 'Asset') }})', 'This maintenance work order record will be permanently deleted.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
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
                <div class="mb-4">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-emerald-600"></i>
                        <span>Asset Inspections & Safety Audits</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-semibold">
                            {{ $inspections->total() }}
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Physical inspections, routine safety evaluations, and condition verification.</p>
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
                                    <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer group"
                                    onclick="if(!event.target.closest('button, a, form, input, select')) openViewInspectionModal(@json($insp));"
                                    title="Click to view inspection details">
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
                                            @if($insp->workOrders && $insp->workOrders->isNotEmpty())
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach($insp->workOrders as $linkedWo)
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-mono bg-blue-50 text-blue-700 border border-blue-200" title="Work Order linked">
                                                            <i class="fas fa-tools text-[8px]"></i> {{ $linkedWo->work_order_number }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
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
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if(!$isReadOnlyStaff)
                                                    <button 
                                                        type="button"
                                                        onclick='openCreateWorkOrderFromInspection(@json($insp))'
                                                        class="p-2 rounded-lg hover:bg-amber-50 text-amber-600 hover:text-amber-800 transition-colors"
                                                        title="Create Work Order from Inspection Findings"
                                                    >
                                                        <i class="fas fa-wrench"></i>
                                                    </button>
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
                                                @endif
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
    @if(!$isReadOnlyStaff)
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
        <form action="{{ route('maintenance.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="createMaintenanceForm">
            @csrf
            <input type="hidden" name="inspection_id" id="create_maint_inspection_id" value="">

            <div id="create_maint_inspection_alert" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <i class="fas fa-clipboard-check text-amber-600"></i> Origin: Inspection Findings
                </div>
                <div id="create_maint_inspection_alert_text"></div>
            </div>

            <x-form-group label="Select Asset" required>
                <select name="asset_id" id="create_maint_asset_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <option value="">-- Choose Asset --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Maintenance Type" required>
                    <select name="type" id="create_maint_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="routine">Routine</option>
                        <option value="repair" selected>Repair / Corrective</option>
                        <option value="preventive">Preventive</option>
                        <option value="inspection">Inspection</option>
                        <option value="overhaul">Overhaul</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </x-form-group>

                <x-form-group label="Priority" required>
                    <select name="priority" id="create_maint_priority" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </x-form-group>

                <x-form-group label="Status" required>
                    <select name="status" id="create_maint_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="scheduled">Scheduled</option>
                        <option value="in_progress" selected>In Progress (Set Under Maintenance)</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </x-form-group>
            </div>

            <x-form-group label="Assign Personnel to Work on Maintenance (Multiple)">
                <div class="border border-gray-200 rounded-lg p-3 max-h-36 overflow-y-auto space-y-2 bg-gray-50/50">
                    @foreach($users as $u)
                        <label class="flex items-center gap-2.5 text-xs text-gray-700 hover:text-gray-900 cursor-pointer">
                            <input type="checkbox" name="assigned_personnel[]" value="{{ $u->id }}" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="font-medium text-gray-900">{{ $u->name }}</span>
                            <span class="text-gray-400">({{ ucfirst($u->role) }})</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-[11px] text-gray-500 mt-1">Select one or more personnel assigned to perform this work.</p>
            </x-form-group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Primary Staff Officer (Optional)">
                    <select name="assigned_to" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="">-- Auto-assigned from personnel --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Technician / Service Shop (External / Third Party)">
                    <input type="text" name="technician" placeholder="e.g. In-house Mechanic, AutoFix" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-lg flex items-start gap-2.5">
                <input type="checkbox" id="maint_confirm_immediately" name="confirm_immediately" value="1" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4 mt-0.5">
                <label for="maint_confirm_immediately" class="text-xs text-emerald-900 cursor-pointer">
                    <span class="font-semibold block">Confirm Work Order Immediately</span>
                    <span class="text-emerald-700">Check this box to confirm this work order upon creation with your verification credentials.</span>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Cost (PHP) (Optional)">
                    <input type="number" step="0.01" min="0" name="cost" id="create_maint_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="text-[11px] text-gray-500 mt-1">Optional. Auto-updated from parts consumed or enter total cost.</p>
                </x-form-group>

                <x-form-group label="Scheduled / Service Date">
                    <input type="date" name="scheduled_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Estimated Duration">
                    <input type="text" name="estimated_duration" placeholder="e.g. 2 hours, 3 days" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Quick Parts Summary (Legacy Text)">
                    <input type="text" name="parts_used" id="create_maint_parts_used" placeholder="e.g. Spark plug, hydraulic seals" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>
            </div>

            {{-- Dynamic Parts & Materials Selection (Requirement 1 & 5) --}}
            <div class="border border-gray-200 rounded-xl p-3 bg-gray-50/70 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-boxes text-blue-600"></i> Parts & Materials Consumed
                    </label>
                    <span class="text-[11px] text-gray-500">Automatically decrements spare parts inventory</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 text-xs">
                    <div class="sm:col-span-5">
                        <select id="create_part_select" onchange="onPartSelectChange('create')" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Select Spare Part from Inventory --</option>
                            <optgroup label="Components & Parts Inventory">
                                @foreach($inventoryParts as $inv)
                                    <option value="{{ $inv['inventory_key'] }}" data-name="{{ $inv['name'] }}" data-type="{{ $inv['inventory_type'] }}" data-id="{{ $inv['inventory_id'] }}" data-price="{{ $inv['unit_cost'] }}" data-stock="{{ $inv['stock'] }}" data-unit="{{ $inv['unit'] }}">
                                        {{ $inv['name'] }} (Stock: {{ $inv['stock'] }} | ₱{{ number_format($inv['unit_cost'], 2) }})
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Custom">
                                <option value="custom">+ Custom / Non-Inventory Part</option>
                            </optgroup>
                        </select>
                        <input type="text" id="create_part_custom_name" placeholder="Enter custom part name..." class="hidden mt-1 w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="number" id="create_part_qty" min="1" value="1" placeholder="Qty" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Quantity">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="text" id="create_part_unit" value="pcs" placeholder="Unit" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Unit">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="number" step="0.01" min="0" id="create_part_cost" placeholder="₱ Cost" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Unit Cost (PHP)">
                    </div>
                    <div class="sm:col-span-1 flex items-center">
                        <button type="button" onclick="addPartRow('create')" class="w-full px-2 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold flex items-center justify-center transition-colors" title="Add Part">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border border-gray-200 rounded-lg bg-white hidden" id="create_parts_table">
                        <thead class="bg-gray-100 text-gray-600 text-[11px] uppercase">
                            <tr>
                                <th class="px-2.5 py-1.5">Part Name</th>
                                <th class="px-2.5 py-1.5 text-center">Qty</th>
                                <th class="px-2.5 py-1.5 text-right">Unit Cost</th>
                                <th class="px-2.5 py-1.5 text-right">Total</th>
                                <th class="px-2.5 py-1.5 text-center w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="create_parts_tbody" class="divide-y divide-gray-100"></tbody>
                    </table>
                    <p id="create_parts_empty" class="text-[11px] text-gray-400 italic">No parts added yet. Select an item above and click (+) to record parts used.</p>
                </div>
            </div>

            <x-receipt-input label="Proof of Cost / Service Receipt / Official Receipt (Optional)" help="Upload service receipt, repair quotation, or invoice as proof of maintenance cost (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

            <x-form-group label="Work Notes & Description">
                <textarea name="notes" id="create_maint_notes" rows="2" placeholder="Describe the problem, service required, or components..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
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

            <x-form-group label="Assigned Personnel / Technicians (Multiple)">
                <div class="border border-gray-200 rounded-lg p-3 max-h-36 overflow-y-auto space-y-2 bg-gray-50/50" id="edit_maint_personnel_container">
                    @foreach($users as $u)
                        <label class="flex items-center gap-2.5 text-xs text-gray-700 hover:text-gray-900 cursor-pointer">
                            <input type="checkbox" name="assigned_personnel[]" value="{{ $u->id }}" class="edit-maint-personnel-checkbox rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="font-medium text-gray-900">{{ $u->name }}</span>
                            <span class="text-gray-400">({{ ucfirst($u->role) }})</span>
                        </label>
                    @endforeach
                </div>
            </x-form-group>

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

            {{-- Dynamic Parts & Materials Selection for Edit (Requirement 1 & 5) --}}
            <div class="border border-gray-200 rounded-xl p-3 bg-gray-50/70 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-boxes text-blue-600"></i> Parts & Materials Consumed
                    </label>
                    <span class="text-[11px] text-gray-500">Inventory stock updates automatically</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 text-xs">
                    <div class="sm:col-span-5">
                        <select id="edit_part_select" onchange="onPartSelectChange('edit')" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Select Spare Part from Inventory --</option>
                            <optgroup label="Components & Parts Inventory">
                                @foreach($inventoryParts as $inv)
                                    <option value="{{ $inv['inventory_key'] }}" data-name="{{ $inv['name'] }}" data-type="{{ $inv['inventory_type'] }}" data-id="{{ $inv['inventory_id'] }}" data-price="{{ $inv['unit_cost'] }}" data-stock="{{ $inv['stock'] }}" data-unit="{{ $inv['unit'] }}">
                                        {{ $inv['name'] }} (Stock: {{ $inv['stock'] }} | ₱{{ number_format($inv['unit_cost'], 2) }})
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Custom">
                                <option value="custom">+ Custom / Non-Inventory Part</option>
                            </optgroup>
                        </select>
                        <input type="text" id="edit_part_custom_name" placeholder="Enter custom part name..." class="hidden mt-1 w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="number" id="edit_part_qty" min="1" value="1" placeholder="Qty" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Quantity">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="text" id="edit_part_unit" value="pcs" placeholder="Unit" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Unit">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="number" step="0.01" min="0" id="edit_part_cost" placeholder="₱ Cost" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Unit Cost (PHP)">
                    </div>
                    <div class="sm:col-span-1 flex items-center">
                        <button type="button" onclick="addPartRow('edit')" class="w-full px-2 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold flex items-center justify-center transition-colors" title="Add Part">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border border-gray-200 rounded-lg bg-white hidden" id="edit_parts_table">
                        <thead class="bg-gray-100 text-gray-600 text-[11px] uppercase">
                            <tr>
                                <th class="px-2.5 py-1.5">Part Name</th>
                                <th class="px-2.5 py-1.5 text-center">Qty</th>
                                <th class="px-2.5 py-1.5 text-right">Unit Cost</th>
                                <th class="px-2.5 py-1.5 text-right">Total</th>
                                <th class="px-2.5 py-1.5 text-center w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="edit_parts_tbody" class="divide-y divide-gray-100"></tbody>
                    </table>
                    <p id="edit_parts_empty" class="text-[11px] text-gray-400 italic">No dynamic parts recorded.</p>
                </div>
            </div>

            <x-form-group label="Legacy Parts Summary (Text)">
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

    {{-- Confirm Maintenance Work Order Modal --}}
    <x-modal id="confirm-maintenance-modal" title="Confirm Maintenance Work Order">
        <form id="confirmMaintenanceForm" method="POST" class="space-y-4">
            @csrf
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-xs text-blue-900 font-medium">
                    Confirming Work Order: <strong id="confirm_wo_number" class="font-bold text-blue-950"></strong>
                </p>
                <p class="text-[11px] text-blue-700 mt-0.5">
                    Asset: <span id="confirm_wo_asset" class="font-semibold"></span>. Confirming verifies this work order and logs your confirmation timestamp.
                </p>
            </div>

            <x-form-group label="Assign Personnel to Work on This Maintenance (Multiple)">
                <div class="border border-gray-200 rounded-lg p-3 max-h-48 overflow-y-auto space-y-2 bg-gray-50/50">
                    @foreach($users as $u)
                        <label class="flex items-center gap-2.5 text-xs text-gray-700 hover:text-gray-900 cursor-pointer">
                            <input type="checkbox" name="personnel[]" value="{{ $u->id }}" class="confirm-maint-personnel-checkbox rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="font-medium text-gray-900">{{ $u->name }}</span>
                            <span class="text-gray-400">({{ ucfirst($u->role) }})</span>
                        </label>
                    @endforeach
                </div>
            </x-form-group>

            <x-form-group label="Confirmation Remarks (Optional)">
                <textarea name="notes" rows="2" placeholder="e.g. Work verified and authorized by supervisor..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    <i class="fas fa-check-double mr-1.5"></i>
                    Confirm Work Order & Assign
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('confirm-maintenance-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Assign Maintenance Personnel Modal --}}
    <x-modal id="assign-maintenance-personnel-modal" title="Assign Personnel to Work Order">
        <form id="assignMaintenancePersonnelForm" method="POST" class="space-y-4">
            @csrf
            <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                <p class="text-xs text-indigo-900 font-medium">
                    Assigning personnel to Work Order: <strong id="assign_wo_number" class="font-bold text-indigo-950"></strong>
                </p>
                <p class="text-[11px] text-indigo-700 mt-0.5">
                    Asset: <span id="assign_wo_asset" class="font-semibold"></span>
                </p>
            </div>

            <x-form-group label="Select Assigned Personnel (Multiple)">
                <div class="border border-gray-200 rounded-lg p-3 max-h-48 overflow-y-auto space-y-2 bg-gray-50/50">
                    @foreach($users as $u)
                        <label class="flex items-center gap-2.5 text-xs text-gray-700 hover:text-gray-900 cursor-pointer">
                            <input type="checkbox" name="personnel[]" value="{{ $u->id }}" class="assign-maint-personnel-checkbox rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                            <span class="font-medium text-gray-900">{{ $u->name }}</span>
                            <span class="text-gray-400">({{ ucfirst($u->role) }})</span>
                        </label>
                    @endforeach
                </div>
            </x-form-group>

            <x-form-group label="Assignment Notes (Optional)">
                <textarea name="notes" rows="2" placeholder="Instructions, scope division, or technician notes..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 mt-6">
                <x-button variant="primary" type="submit" class="flex-1">
                    <i class="fas fa-users-cog mr-1.5"></i>
                    Save Assigned Personnel
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('assign-maintenance-personnel-modal')" class="flex-1">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Complete Work Order Modal (Requirements 6 & 8) --}}
    <x-modal id="complete-workorder-modal" title="Complete Maintenance Work Order & Verify Status">
        <form id="completeWorkOrderForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-blue-900 font-bold">
                            Completing Work Order: <span id="complete_wo_number" class="font-mono text-blue-950"></span>
                        </p>
                        <p class="text-xs text-blue-800 mt-0.5">
                            Asset: <span id="complete_wo_asset_name" class="font-semibold text-blue-950">-</span>
                        </p>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-blue-100 text-blue-800 border border-blue-200">
                        Operational Sign-Off
                    </span>
                </div>
            </div>

            {{-- Operational Condition & Status Verification (Requirement 6 & 8) --}}
            <div class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-3.5 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-emerald-900 uppercase tracking-wider">
                    <i class="fas fa-clipboard-check text-emerald-600"></i>
                    Physical Condition & Operational Status Verification
                </div>
                <p class="text-[11px] text-emerald-800">
                    Verify actual operational testing and condition before returning asset to service. Do not mark Available if further repairs or recertification are pending.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <x-form-group label="Verified Asset Physical Condition" required>
                        <select name="final_condition" id="complete_final_condition" required onchange="if(this.value === 'fair') { document.getElementById('complete_final_status').value = 'under_maintenance'; }" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs bg-white">
                            <option value="good" selected>Good — Fully functional, minor normal wear</option>
                            <option value="excellent">Excellent — Like new / optimal operational order</option>
                            <option value="fair">Fair — Needs further maintenance (Cannot be deployed)</option>
                            <option value="poor">Poor — Restricted operation / partial capability</option>
                        </select>
                    </x-form-group>

                    <x-form-group label="Verified Operational Status" required>
                        <select name="final_status" id="complete_final_status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs bg-white">
                            <option value="available" selected>Available & Ready for Deployment</option>
                            <option value="under_maintenance">Keep Under Maintenance (Further work needed)</option>
                            <option value="out_of_service">Out of Service (Unrepairable / Defective)</option>
                            <option value="retired">Retired / Decommissioned</option>
                        </select>
                    </x-form-group>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <x-form-group label="Verified By (Inspector / Officer)">
                        <select name="verified_by" id="complete_verified_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs bg-white">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ auth()->id() === $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ ucfirst($u->role) }})
                                </option>
                            @endforeach
                        </select>
                    </x-form-group>

                    <x-form-group label="Operational Verification Checklist Notes">
                        <input type="text" name="verification_notes" id="complete_verification_notes" placeholder="e.g. Engine tested for 30 mins, pressure checked, all safety interlocks verified." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs">
                    </x-form-group>
                </div>
            </div>

            {{-- Parts & Materials Used during completion --}}
            <div class="border border-gray-200 rounded-xl p-3 bg-gray-50/70 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-boxes text-emerald-600"></i> Parts & Materials Consumed (Optional)
                    </label>
                    <span class="text-[11px] text-gray-500">Record parts installed during sign-off</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 text-xs">
                    <div class="sm:col-span-5">
                        <select id="complete_part_select" onchange="onPartSelectChange('complete')" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Choose Spare Part from Inventory --</option>
                            <optgroup label="Components & Parts Inventory">
                                @foreach($inventoryParts as $inv)
                                    <option value="{{ $inv['inventory_key'] }}" data-name="{{ $inv['name'] }}" data-type="{{ $inv['inventory_type'] }}" data-id="{{ $inv['inventory_id'] }}" data-price="{{ $inv['unit_cost'] }}" data-stock="{{ $inv['stock'] }}" data-unit="{{ $inv['unit'] }}">
                                        {{ $inv['name'] }} (Stock: {{ $inv['stock'] }} | ₱{{ number_format($inv['unit_cost'], 2) }})
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Custom">
                                <option value="custom">+ Custom / Non-Inventory Part</option>
                            </optgroup>
                        </select>
                        <input type="text" id="complete_part_custom_name" placeholder="Enter custom part name..." class="hidden mt-1 w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="number" id="complete_part_qty" min="1" value="1" placeholder="Qty" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Quantity">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="text" id="complete_part_unit" value="pcs" placeholder="Unit" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Unit">
                    </div>
                    <div class="sm:col-span-2">
                        <input type="number" step="0.01" min="0" id="complete_part_cost" placeholder="₱ Cost" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white" title="Unit Cost (PHP)">
                    </div>
                    <div class="sm:col-span-1 flex items-center">
                        <button type="button" onclick="addPartRow('complete')" class="w-full px-2 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold flex items-center justify-center transition-colors" title="Add Part">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border border-gray-200 rounded-lg bg-white hidden" id="complete_parts_table">
                        <thead class="bg-gray-100 text-gray-600 text-[11px] uppercase">
                            <tr>
                                <th class="px-2.5 py-1.5">Part Name</th>
                                <th class="px-2.5 py-1.5 text-center">Qty</th>
                                <th class="px-2.5 py-1.5 text-right">Unit Cost</th>
                                <th class="px-2.5 py-1.5 text-right">Total</th>
                                <th class="px-2.5 py-1.5 text-center w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="complete_parts_tbody" class="divide-y divide-gray-100"></tbody>
                    </table>
                    <p id="complete_parts_empty" class="text-[11px] text-gray-400 italic">No additional parts added for sign-off.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Final Service / Maintenance Cost (PHP)">
                    <input type="number" step="0.01" min="0" name="cost" id="complete_wo_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                    <p class="text-[11px] text-gray-500 mt-1">Recorded in Asset Expenses ledger.</p>
                </x-form-group>

                <x-form-group label="Summary of Parts (Legacy text)">
                    <input type="text" name="parts_used" id="complete_wo_parts_used" placeholder="e.g. Filter seal, brake pads" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                </x-form-group>
            </div>

            <x-receipt-input label="Proof of Cost / Official Receipt (OR) / Final Invoice (Optional)" help="Upload official receipt or final service invoice as proof of cost (PDF, JPG, PNG, WEBP, up to 5 files, 5 MB each)." />

            <x-form-group label="Sign-Off Remarks & Work Performed Description">
                <textarea name="notes" rows="2" placeholder="Quality verification remarks, technician sign-off, or work summary..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"></textarea>
            </x-form-group>

            <div class="flex gap-3 pt-3 border-t border-gray-200">
                <x-button variant="primary" type="submit" class="flex-1">
                    <i class="fas fa-check-circle mr-1.5"></i>
                    Verify & Sign Off Maintenance
                </x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('complete-workorder-modal')" class="flex-1">
                    Cancel
                </x-button>
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

    
    
    @endif

    <x-modal id="view-schedule-modal" title="Preventive Maintenance Schedule Details">
        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Asset</p>
                        <p class="text-sm font-bold text-gray-900" id="view_sched_asset_name">-</p>
                        <p class="text-xs font-mono text-blue-600" id="view_sched_asset_code">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Asset Type</p>
                        <p class="text-sm font-semibold text-gray-800" id="view_sched_asset_type">-</p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Maintenance Task</p>
                <p class="text-base font-semibold text-gray-900" id="view_sched_task">-</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Interval</p>
                    <p class="text-sm text-gray-800" id="view_sched_interval">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</p>
                    <span id="view_sched_status_badge" class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Done Date</p>
                    <p class="text-sm text-gray-800" id="view_sched_last_date">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Next Due Date</p>
                    <p class="text-sm font-semibold text-gray-900" id="view_sched_next_date">-</p>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <x-button variant="secondary" type="button" onclick="window.closeModal('view-schedule-modal')">
                    Close
                </x-button>
            </div>
        </div>
    </x-modal>

<x-modal id="view-maintenance-modal" title="Maintenance Work Order Details">
        <div class="space-y-4">
            {{-- Top Banner: WO / Status Badges --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-blue-700 bg-blue-100 border border-blue-200 px-2.5 py-1 rounded" id="view_maint_wo_number">
                            -
                        </span>
                        <span id="view_maint_record_type_badge" class="px-2 py-0.5 text-xs font-bold rounded bg-indigo-100 text-indigo-800">
                            WORK ORDER
                        </span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="view_maint_type_badge" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">-</span>
                        <span id="view_maint_priority_badge" class="px-2 py-0.5 text-xs font-bold uppercase rounded bg-gray-100 text-gray-800">-</span>
                        <span id="view_maint_status_badge" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                    </div>
                </div>

                {{-- Asset Information --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-gray-200 text-xs">
                    <div>
                        <p class="font-semibold text-gray-500 uppercase tracking-wider text-[10px]">Asset Name</p>
                        <p class="text-sm font-bold text-gray-900" id="view_maint_asset_name">-</p>
                        <p class="font-mono text-gray-500" id="view_maint_asset_code">-</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-500 uppercase tracking-wider text-[10px]">Category & Type</p>
                        <p class="text-xs font-medium text-gray-800" id="view_maint_asset_category">-</p>
                        <p class="text-gray-500 text-[11px]" id="view_maint_asset_type">-</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-500 uppercase tracking-wider text-[10px]">Current Status</p>
                        <span id="view_maint_asset_status" class="inline-block mt-0.5 px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">-</span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-500 uppercase tracking-wider text-[10px]">Current Condition</p>
                        <span id="view_maint_asset_condition" class="inline-block mt-0.5 px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">-</span>
                    </div>
                </div>
            </div>

            {{-- Linked Inspection Findings (Requirement 4 & 7) --}}
            <div id="view_maint_inspection_card" class="hidden bg-amber-50/70 border border-amber-200 rounded-xl p-3.5 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-900 flex items-center gap-1.5 uppercase tracking-wider">
                        <i class="fas fa-clipboard-check text-amber-600"></i> Linked Inspection Findings
                    </span>
                    <span id="view_maint_inspection_result_badge" class="px-2 py-0.5 text-xs font-bold rounded uppercase bg-amber-100 text-amber-800">
                        Result
                    </span>
                </div>
                <div class="text-xs text-amber-800 flex items-center gap-4">
                    <span><strong>Inspector:</strong> <span id="view_maint_inspection_inspector">-</span></span>
                    <span><strong>Date:</strong> <span id="view_maint_inspection_date">-</span></span>
                </div>
                <div class="mt-1 p-2 bg-white/80 rounded border border-amber-200 text-xs text-gray-800 whitespace-pre-line" id="view_maint_inspection_findings">
                    -
                </div>
            </div>

            {{-- Problem Description & Work Scope --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Problem Description & Scope</p>
                <div class="p-3 bg-gray-50 rounded-lg text-xs text-gray-800 whitespace-pre-line border border-gray-200" id="view_maint_notes">
                    -
                </div>
            </div>

            {{-- Assigned Personnel & Servicing Team --}}
            <div class="border border-gray-200 rounded-xl p-3.5 bg-gray-50/50 space-y-3">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Assigned Personnel (Multiple)</p>
                    <div id="view_maint_personnel_list" class="flex flex-wrap gap-1.5 min-h-[26px] items-center">
                        <span class="text-xs text-gray-400 italic">None assigned</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-200 text-xs">
                    <div>
                        <p class="font-semibold text-gray-500 uppercase tracking-wider text-[10px]">Primary Staff Officer</p>
                        <p class="font-medium text-gray-900 mt-0.5" id="view_maint_officer">-</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-500 uppercase tracking-wider text-[10px]">Technician / External Shop</p>
                        <p class="font-medium text-gray-900 mt-0.5" id="view_maint_technician">-</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-500 uppercase tracking-wider text-[10px]">Confirmation Status</p>
                        <div id="view_maint_confirmed" class="mt-0.5">-</div>
                    </div>
                </div>
            </div>

            {{-- Service Dates & Timeline (Requirement 3) --}}
            <div class="border border-gray-200 rounded-xl p-3.5 bg-gray-50/50">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="fas fa-calendar-alt text-blue-600"></i> Service Timeline & Dates
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                    <div>
                        <p class="text-gray-500 text-[10px] font-semibold uppercase">Created</p>
                        <p class="font-medium text-gray-900 mt-0.5" id="view_maint_created_date">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-[10px] font-semibold uppercase">Scheduled</p>
                        <p class="font-medium text-gray-900 mt-0.5" id="view_maint_sched_date">-</p>
                    </div>
                    <div>
                        <p class="text-blue-600 text-[10px] font-semibold uppercase">Started</p>
                        <p class="font-medium text-blue-800 mt-0.5" id="view_maint_started_date">-</p>
                    </div>
                    <div>
                        <p class="text-emerald-600 text-[10px] font-semibold uppercase">Completed</p>
                        <p class="font-medium text-emerald-800 mt-0.5" id="view_maint_comp_date">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-[10px] font-semibold uppercase">Duration</p>
                        <p class="font-medium text-gray-900 mt-0.5" id="view_maint_duration">-</p>
                    </div>
                </div>
            </div>

            {{-- Parts & Materials Consumed (Requirement 1, 4 & 5) --}}
            <div class="border border-gray-200 rounded-xl p-3.5 bg-gray-50/50 space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-boxes text-blue-600"></i> Parts & Materials Consumed
                    </p>
                    <span class="text-[11px] font-semibold text-emerald-700" id="view_maint_parts_total_badge"></span>
                </div>
                <div id="view_maint_parts_table_container" class="overflow-x-auto">
                    <table class="w-full text-xs text-left border border-gray-200 rounded-lg bg-white hidden" id="view_maint_parts_table">
                        <thead class="bg-gray-100 text-gray-600 text-[11px] uppercase">
                            <tr>
                                <th class="px-3 py-1.5">Part / Material</th>
                                <th class="px-3 py-1.5 text-center">Quantity</th>
                                <th class="px-3 py-1.5 text-right">Unit Cost</th>
                                <th class="px-3 py-1.5 text-right">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody id="view_maint_parts_tbody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <div id="view_maint_parts_legacy_container" class="text-xs text-gray-700 p-2 bg-white rounded border border-gray-200">
                    <span id="view_maint_parts">-</span>
                </div>
            </div>

            {{-- Maintenance Costs --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl">
                    <p class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Total Recorded Maintenance Cost</p>
                    <p class="text-lg font-bold text-emerald-950 mt-0.5" id="view_maint_cost">₱0.00</p>
                    <p class="text-[11px] text-emerald-700">Reflected in Asset Expenses Ledger.</p>
                </div>
                <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl">
                    <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider">Parts & Materials Subtotal</p>
                    <p class="text-lg font-bold text-blue-950 mt-0.5" id="view_maint_parts_subtotal">₱0.00</p>
                    <p class="text-[11px] text-blue-700">Computed from consumed items inventory.</p>
                </div>
            </div>

            {{-- Completion & Operational Verification Details (Requirement 6 & 8) --}}
            <div id="view_maint_verification_card" class="hidden border border-emerald-300 bg-emerald-50/70 rounded-xl p-3.5 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-check-double text-emerald-600"></i> Completion & Operational Verification Sign-Off
                    </span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase bg-emerald-200 text-emerald-900">
                        Verified
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <p class="text-[10px] font-semibold text-emerald-800 uppercase">Verified Condition</p>
                        <span id="view_maint_verified_condition" class="inline-block mt-0.5 px-2 py-0.5 rounded text-xs font-bold bg-white text-emerald-900 border border-emerald-300">-</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-emerald-800 uppercase">Verified Operational Status</p>
                        <span id="view_maint_verified_status" class="inline-block mt-0.5 px-2 py-0.5 rounded text-xs font-bold bg-white text-emerald-900 border border-emerald-300">-</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-emerald-800 uppercase">Verified By</p>
                        <p class="font-medium text-emerald-950 mt-0.5" id="view_maint_verified_by">-</p>
                    </div>
                </div>
                <div class="pt-2 border-t border-emerald-200/60">
                    <p class="text-[10px] font-semibold text-emerald-800 uppercase">Verification & Quality Checklist Notes</p>
                    <p class="text-xs text-emerald-950 mt-0.5 whitespace-pre-line italic" id="view_maint_verification_notes">-</p>
                </div>
            </div>

            {{-- Supporting Receipts / Documents --}}
            <div class="border border-gray-200 rounded-xl p-3.5 bg-gray-50/50">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="fas fa-file-invoice text-blue-600"></i> Supporting Receipts & Documents
                </p>
                <div id="view_maint_receipts_container">
                    <p class="text-xs text-gray-400 italic">No supporting documents or receipts attached.</p>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <x-button variant="secondary" type="button" onclick="window.closeModal('view-maintenance-modal')">
                    Close
                </x-button>
            </div>
        </div>
    </x-modal>

<x-modal id="view-inspection-modal" title="Asset Inspection Details">
        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Asset</p>
                        <p class="text-sm font-bold text-gray-900" id="view_insp_asset_name">-</p>
                        <p class="text-xs font-mono text-blue-600" id="view_insp_asset_code">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</p>
                        <p class="text-sm text-gray-800" id="view_insp_asset_category">-</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Inspector / Evaluator</p>
                    <p class="text-sm font-semibold text-gray-900" id="view_insp_inspector">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Result</p>
                    <span id="view_insp_result_badge" class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Inspection Date</p>
                    <p class="text-sm text-gray-800" id="view_insp_date">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Next Due Date</p>
                    <p class="text-sm text-gray-800" id="view_insp_next_due">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</p>
                    <span id="view_insp_status_badge" class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Findings & Recommendations</p>
                <div class="mt-1 p-3 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-line border border-gray-200" id="view_insp_findings">-</div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <x-button variant="secondary" type="button" onclick="window.closeModal('view-inspection-modal')">
                    Close
                </x-button>
            </div>
        </div>
    </x-modal>

    @push('scripts')
        <script>

            // Global state for dynamic parts selector
            let partsState = {
                create: [],
                edit: [],
                complete: []
            };

            function onPartSelectChange(prefix) {
                const select = document.getElementById(prefix + '_part_select');
                const customInput = document.getElementById(prefix + '_part_custom_name');
                const costInput = document.getElementById(prefix + '_part_cost');
                const unitInput = document.getElementById(prefix + '_part_unit');

                if (!select) return;

                if (select.value === 'custom') {
                    if (customInput) customInput.classList.remove('hidden');
                    if (costInput) costInput.value = '';
                    if (unitInput) unitInput.value = 'pcs';
                } else if (select.value) {
                    if (customInput) customInput.classList.add('hidden');
                    const opt = select.options[select.selectedIndex];
                    if (opt) {
                        if (costInput) costInput.value = opt.getAttribute('data-price') || '0';
                        if (unitInput) unitInput.value = opt.getAttribute('data-unit') || 'pcs';
                    }
                } else {
                    if (customInput) customInput.classList.add('hidden');
                    if (costInput) costInput.value = '';
                }
            }

            function addPartRow(prefix) {
                const select = document.getElementById(prefix + '_part_select');
                const customInput = document.getElementById(prefix + '_part_custom_name');
                const qtyInput = document.getElementById(prefix + '_part_qty');
                const unitInput = document.getElementById(prefix + '_part_unit');
                const costInput = document.getElementById(prefix + '_part_cost');

                if (!select || !select.value) {
                    alert('Please select a spare part or choose Custom.');
                    return;
                }

                let partName = '';
                let invType = 'custom';
                let invId = null;

                if (select.value === 'custom') {
                    partName = customInput ? customInput.value.trim() : '';
                    if (!partName) {
                        alert('Please enter a name for the custom part.');
                        return;
                    }
                } else {
                    const opt = select.options[select.selectedIndex];
                    partName = opt.getAttribute('data-name');
                    invType = opt.getAttribute('data-type');
                    invId = opt.getAttribute('data-id');
                }

                const qty = Math.max(1, parseInt(qtyInput.value) || 1);
                const unit = unitInput && unitInput.value.trim() ? unitInput.value.trim() : 'pcs';
                const unitCost = Math.max(0, parseFloat(costInput.value) || 0);
                const total = Math.round(qty * unitCost * 100) / 100;

                partsState[prefix].push({
                    inventory_type: invType,
                    inventory_id: invId,
                    part_name: partName,
                    quantity: qty,
                    unit: unit,
                    unit_cost: unitCost,
                    total_cost: total
                });

                renderPartsTable(prefix);

                // Reset inputs
                select.value = '';
                if (customInput) {
                    customInput.value = '';
                    customInput.classList.add('hidden');
                }
                qtyInput.value = 1;
                costInput.value = '';
            }

            function removePartRow(prefix, index) {
                partsState[prefix].splice(index, 1);
                renderPartsTable(prefix);
            }

            function renderPartsTable(prefix) {
                const tbody = document.getElementById(prefix + '_parts_tbody');
                const table = document.getElementById(prefix + '_parts_table');
                const empty = document.getElementById(prefix + '_parts_empty');
                if (!tbody) return;

                tbody.innerHTML = '';
                const items = partsState[prefix] || [];

                let subtotal = 0;

                if (items.length === 0) {
                    if (table) table.classList.add('hidden');
                    if (empty) empty.classList.remove('hidden');
                } else {
                    if (table) table.classList.remove('hidden');
                    if (empty) empty.classList.add('hidden');

                    items.forEach((item, idx) => {
                        subtotal += (item.total_cost || 0);
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';
                        tr.innerHTML = `
                            <td class="px-2.5 py-1.5 font-medium text-gray-900">
                                ${item.part_name}
                                <input type="hidden" name="parts[${idx}][inventory_type]" value="${item.inventory_type || ''}">
                                <input type="hidden" name="parts[${idx}][inventory_id]" value="${item.inventory_id || ''}">
                                <input type="hidden" name="parts[${idx}][part_name]" value="${item.part_name}">
                                <input type="hidden" name="parts[${idx}][quantity]" value="${item.quantity}">
                                <input type="hidden" name="parts[${idx}][unit]" value="${item.unit}">
                                <input type="hidden" name="parts[${idx}][unit_cost]" value="${item.unit_cost}">
                            </td>
                            <td class="px-2.5 py-1.5 text-center font-mono">${item.quantity} ${item.unit}</td>
                            <td class="px-2.5 py-1.5 text-right font-mono">₱${parseFloat(item.unit_cost).toFixed(2)}</td>
                            <td class="px-2.5 py-1.5 text-right font-mono font-semibold text-gray-900">₱${parseFloat(item.total_cost).toFixed(2)}</td>
                            <td class="px-2.5 py-1.5 text-center">
                                <button type="button" onclick="removePartRow('${prefix}', ${idx})" class="text-red-500 hover:text-red-700 text-xs p-1" title="Remove part">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                }

                // Auto-sync cost input if currently zero/empty
                const costTarget = prefix === 'complete' ? document.getElementById('complete_wo_cost') : (prefix === 'create' ? document.getElementById('create_maint_cost') : document.getElementById('edit_maint_cost'));
                if (costTarget && (!costTarget.value || parseFloat(costTarget.value) === 0) && subtotal > 0) {
                    costTarget.value = subtotal.toFixed(2);
                }
            }

            function openViewScheduleModal(schedule) {
                document.getElementById('view_sched_asset_name').textContent = (schedule.asset && schedule.asset.name) ? schedule.asset.name : 'Unknown';
                document.getElementById('view_sched_asset_code').textContent = (schedule.asset && schedule.asset.code) ? '[' + schedule.asset.code + ']' : '-';
                document.getElementById('view_sched_asset_type').textContent = schedule.asset_type || '-';
                document.getElementById('view_sched_task').textContent = schedule.maintenance_task || '-';
                document.getElementById('view_sched_interval').textContent = (schedule.interval_type || '-') + ' (' + (schedule.interval_days || 0) + ' days)';
                
                let lastDate = schedule.last_maintenance_date || 'N/A';
                if (lastDate && lastDate.includes('T')) lastDate = lastDate.split('T')[0];
                document.getElementById('view_sched_last_date').textContent = lastDate;

                let nextDate = schedule.next_due_date || 'N/A';
                if (nextDate && nextDate.includes('T')) nextDate = nextDate.split('T')[0];
                document.getElementById('view_sched_next_date').textContent = nextDate;

                const statusEl = document.getElementById('view_sched_status_badge');
                statusEl.textContent = schedule.status || '-';
                statusEl.className = 'inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full ' + (
                    schedule.status === 'Monitoring Only' ? 'bg-emerald-100 text-emerald-800' :
                    schedule.status === 'Maintenance Due' ? 'bg-amber-100 text-amber-800' :
                    schedule.status === 'Overdue' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-800'
                );

                window.openModal('view-schedule-modal');
            }

            // View Maintenance Modal (Requirement 4)
            function openViewMaintenanceModal(item) {
                document.getElementById('view_maint_wo_number').textContent = item.work_order_number || ('WO-' + String(item.id).padStart(5, '0'));
                document.getElementById('view_maint_asset_name').textContent = (item.asset && item.asset.name) ? item.asset.name : 'Unknown Asset';
                document.getElementById('view_maint_asset_code').textContent = (item.asset && item.asset.code) ? '[' + item.asset.code + ']' : '-';
                document.getElementById('view_maint_asset_category').textContent = (item.asset && item.asset.category && item.asset.category.name) ? item.asset.category.name : 'Uncategorized';
                document.getElementById('view_maint_asset_type').textContent = (item.asset && item.asset.type && item.asset.type.name) ? item.asset.type.name : '-';
                
                // Asset current status and condition
                const assetStatusEl = document.getElementById('view_maint_asset_status');
                if (assetStatusEl) {
                    const aStat = (item.asset && item.asset.status) ? item.asset.status.replace('_', ' ').toUpperCase() : '-';
                    assetStatusEl.textContent = aStat;
                }
                const assetCondEl = document.getElementById('view_maint_asset_condition');
                if (assetCondEl) {
                    const aCond = (item.asset && item.asset.condition) ? item.asset.condition.toUpperCase() : '-';
                    assetCondEl.textContent = aCond;
                }

                document.getElementById('view_maint_type_badge').textContent = (item.type || '-').toUpperCase();
                
                const priBadge = document.getElementById('view_maint_priority_badge');
                priBadge.textContent = (item.priority || 'medium').toUpperCase();
                priBadge.className = 'px-2 py-0.5 text-xs font-bold uppercase rounded ' + (
                    item.priority === 'urgent' ? 'bg-rose-100 text-rose-800' :
                    item.priority === 'high' ? 'bg-amber-100 text-amber-800' :
                    item.priority === 'low' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                );

                const statBadge = document.getElementById('view_maint_status_badge');
                statBadge.textContent = (item.status || '-').replace('_', ' ').toUpperCase();
                statBadge.className = 'px-2 py-0.5 text-xs font-semibold rounded-full ' + (
                    item.status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                    item.status === 'in_progress' ? 'bg-amber-100 text-amber-800' :
                    item.status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                );

                // Staff Officer & Technician
                const officerEl = document.getElementById('view_maint_officer');
                if (officerEl) {
                    officerEl.textContent = (item.assigned_user || item.assignedUser) ? (item.assigned_user || item.assignedUser).name : 'Auto-assigned / None';
                }
                document.getElementById('view_maint_technician').textContent = item.technician || 'Unspecified';
                
                // Render Assigned Personnel badges
                const pContainer = document.getElementById('view_maint_personnel_list');
                pContainer.innerHTML = '';
                if (item.assigned_personnel && item.assigned_personnel.length > 0) {
                    item.assigned_personnel.forEach(p => {
                        const span = document.createElement('span');
                        span.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200';
                        span.innerHTML = '<i class="fas fa-user-circle text-[10px] text-blue-500"></i> ' + (p.name || 'User');
                        pContainer.appendChild(span);
                    });
                } else if (item.assigned_user || item.assignedUser) {
                    const u = item.assigned_user || item.assignedUser;
                    const span = document.createElement('span');
                    span.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200';
                    span.innerHTML = '<i class="fas fa-user-circle text-[10px] text-blue-500"></i> ' + u.name;
                    pContainer.appendChild(span);
                } else {
                    pContainer.innerHTML = '<span class="text-xs text-gray-400 italic">No personnel assigned</span>';
                }

                // Confirmation status
                const confirmedEl = document.getElementById('view_maint_confirmed');
                if (item.confirmed_at) {
                    const byName = (item.confirmed_by_user && item.confirmed_by_user.name) ? item.confirmed_by_user.name : 'Staff';
                    confirmedEl.innerHTML = '<span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200"><i class="fas fa-check-double text-emerald-600"></i> Confirmed by ' + byName + '</span>';
                } else {
                    confirmedEl.innerHTML = '<span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200"><i class="fas fa-clock text-amber-600"></i> Awaiting Confirmation</span>';
                }

                // Linked Inspection (Requirement 4 & 7)
                const inspCard = document.getElementById('view_maint_inspection_card');
                if (item.inspection) {
                    inspCard.classList.remove('hidden');
                    document.getElementById('view_maint_inspection_inspector').textContent = item.inspection.inspector || 'Staff';
                    let inspDate = item.inspection.inspection_date || '';
                    if (inspDate.includes('T')) inspDate = inspDate.split('T')[0];
                    document.getElementById('view_maint_inspection_date').textContent = inspDate;
                    document.getElementById('view_maint_inspection_result_badge').textContent = (item.inspection.result || '-').replace('_', ' ').toUpperCase();
                    document.getElementById('view_maint_inspection_findings').textContent = item.inspection.findings || 'No inspection findings recorded.';
                } else {
                    inspCard.classList.add('hidden');
                }

                // Timeline & Service Dates (Requirement 3)
                let crDate = item.created_at || '';
                if (crDate.includes('T')) crDate = crDate.split('T')[0];
                document.getElementById('view_maint_created_date').textContent = crDate || '-';

                let sDate = item.scheduled_date || 'N/A';
                if (sDate && sDate.includes('T')) sDate = sDate.split('T')[0];
                document.getElementById('view_maint_sched_date').textContent = sDate;

                let stDate = item.started_at || 'Pending';
                if (stDate && stDate.includes('T')) stDate = stDate.split('T')[0];
                document.getElementById('view_maint_started_date').textContent = stDate;

                let cDate = item.completed_at || 'Pending';
                if (cDate && cDate.includes('T')) cDate = cDate.split('T')[0];
                document.getElementById('view_maint_comp_date').textContent = cDate;

                document.getElementById('view_maint_duration').textContent = item.estimated_duration || 'N/A';

                // Parts & Materials Consumed
                const partsTable = document.getElementById('view_maint_parts_table');
                const partsTbody = document.getElementById('view_maint_parts_tbody');
                const partsTotalBadge = document.getElementById('view_maint_parts_total_badge');
                let partsCostSum = 0;

                if (item.parts && item.parts.length > 0) {
                    partsTable.classList.remove('hidden');
                    partsTbody.innerHTML = '';
                    item.parts.forEach(p => {
                        const pTotal = parseFloat(p.total_cost || (p.quantity * (p.unit_cost || 0)));
                        partsCostSum += pTotal;
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';
                        tr.innerHTML = `
                            <td class="px-3 py-1.5 font-medium text-gray-900">${p.part_name}</td>
                            <td class="px-3 py-1.5 text-center font-mono">${p.quantity} ${p.unit || 'pcs'}</td>
                            <td class="px-3 py-1.5 text-right font-mono">₱${parseFloat(p.unit_cost || 0).toFixed(2)}</td>
                            <td class="px-3 py-1.5 text-right font-mono font-bold text-gray-900">₱${pTotal.toFixed(2)}</td>
                        `;
                        partsTbody.appendChild(tr);
                    });
                    partsTotalBadge.textContent = 'Parts Subtotal: ₱' + partsCostSum.toFixed(2);
                } else {
                    partsTable.classList.add('hidden');
                    partsTotalBadge.textContent = '';
                }

                document.getElementById('view_maint_parts').textContent = item.parts_used || (item.parts && item.parts.length > 0 ? (item.parts.length + ' item(s) logged') : 'None specified');
                document.getElementById('view_maint_cost').textContent = item.cost ? ('₱' + parseFloat(item.cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})) : '₱0.00';
                document.getElementById('view_maint_parts_subtotal').textContent = '₱' + partsCostSum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('view_maint_notes').textContent = item.notes || 'No remarks recorded.';

                // Operational Verification & Completion Details (Requirement 6 & 8)
                const verifCard = document.getElementById('view_maint_verification_card');
                if (item.status === 'completed') {
                    verifCard.classList.remove('hidden');
                    document.getElementById('view_maint_verified_condition').textContent = (item.final_condition || 'Good').toUpperCase();
                    const verifStatus = (item.asset && item.asset.status) ? item.asset.status.replace('_', ' ').toUpperCase() : 'AVAILABLE';
                    document.getElementById('view_maint_verified_status').textContent = verifStatus;
                    const verifierName = (item.verified_by_user && item.verified_by_user.name) ? item.verified_by_user.name : ((item.confirmed_by_user && item.confirmed_by_user.name) ? item.confirmed_by_user.name : 'Officer Sign-Off');
                    document.getElementById('view_maint_verified_by').textContent = verifierName;
                    document.getElementById('view_maint_verification_notes').textContent = item.verification_notes || 'Operational testing and condition verified upon completion.';
                } else {
                    verifCard.classList.add('hidden');
                }

                // Supporting Receipts / Documents preview
                const recContainer = document.getElementById('view_maint_receipts_container');
                recContainer.innerHTML = '';
                if (item.receipts && item.receipts.length > 0) {
                    const list = document.createElement('div');
                    list.className = 'grid grid-cols-1 sm:grid-cols-2 gap-2';
                    item.receipts.forEach(r => {
                        const a = document.createElement('a');
                        a.href = r.url || ('/storage/' + r.file_path);
                        a.target = '_blank';
                        a.className = 'flex items-center gap-2 p-2 bg-white rounded-lg border border-gray-200 hover:border-blue-300 text-xs text-gray-700 hover:text-blue-600 transition-colors';
                        a.innerHTML = '<i class="fas fa-paperclip text-blue-500"></i> <span class="truncate flex-1 font-medium">' + (r.original_filename || 'Receipt Document') + '</span> <i class="fas fa-external-link-alt text-[10px] text-gray-400"></i>';
                        list.appendChild(a);
                    });
                    recContainer.appendChild(list);
                } else {
                    recContainer.innerHTML = '<p class="text-xs text-gray-400 italic">No supporting documents or receipts attached.</p>';
                }

                window.openModal('view-maintenance-modal');
            }

            // View Inspection Modal
            function openViewInspectionModal(insp) {
                document.getElementById('view_insp_asset_name').textContent = (insp.asset && insp.asset.name) ? insp.asset.name : 'Unknown Asset';
                document.getElementById('view_insp_asset_code').textContent = (insp.asset && insp.asset.code) ? '[' + insp.asset.code + ']' : '-';
                document.getElementById('view_insp_asset_category').textContent = (insp.asset && insp.asset.category && insp.asset.category.name) ? insp.asset.category.name : 'Uncategorized';

                document.getElementById('view_insp_inspector').textContent = insp.inspector || 'Unassigned';

                const resBadge = document.getElementById('view_insp_result_badge');
                resBadge.textContent = (insp.result || '-').replace('_', ' ').toUpperCase();
                resBadge.className = 'inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full ' + (
                    insp.result === 'passed' ? 'bg-emerald-100 text-emerald-800' :
                    insp.result === 'failed' ? 'bg-rose-100 text-rose-800' :
                    insp.result === 'conditional_pass' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800'
                );

                let iDate = insp.inspection_date || 'N/A';
                if (iDate && iDate.includes('T')) iDate = iDate.split('T')[0];
                document.getElementById('view_insp_date').textContent = iDate;

                let nDate = insp.next_due_date || 'N/A';
                if (nDate && nDate.includes('T')) nDate = nDate.split('T')[0];
                document.getElementById('view_insp_next_due').textContent = nDate;

                const statBadge = document.getElementById('view_insp_status_badge');
                statBadge.textContent = (insp.status || '-').toUpperCase();
                statBadge.className = 'inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full ' + (
                    insp.status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                    insp.status === 'scheduled' ? 'bg-blue-100 text-blue-800' :
                    insp.status === 'overdue' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-800'
                );

                document.getElementById('view_insp_findings').textContent = insp.findings || 'No findings reported.';

                window.openModal('view-inspection-modal');
            }

            @if(!$isReadOnlyStaff)
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

            // Maintenance Logs & Work Orders
            function openCreateMaintenanceModal() {
                document.getElementById('create_maint_inspection_id').value = '';
                const alertEl = document.getElementById('create_maint_inspection_alert');
                if (alertEl) alertEl.classList.add('hidden');
                partsState.create = [];
                renderPartsTable('create');
                window.openModal('create-maintenance-modal');
            }

            // Create Work Order from Inspection Findings (Requirement 7)
            function openCreateWorkOrderFromInspection(insp) {
                const assetSelect = document.getElementById('create_maint_asset_id');
                if (assetSelect) assetSelect.value = insp.asset_id;

                const inspInput = document.getElementById('create_maint_inspection_id');
                if (inspInput) inspInput.value = insp.id;

                const typeSelect = document.getElementById('create_maint_type');
                if (typeSelect) typeSelect.value = 'repair';

                const priSelect = document.getElementById('create_maint_priority');
                if (priSelect) {
                    priSelect.value = (insp.result === 'failed') ? 'urgent' : ((insp.result === 'conditional_pass') ? 'high' : 'medium');
                }

                const alertEl = document.getElementById('create_maint_inspection_alert');
                const alertText = document.getElementById('create_maint_inspection_alert_text');
                if (alertEl && alertText) {
                    alertEl.classList.remove('hidden');
                    alertText.innerHTML = `Created from Inspection #${insp.id} (${(insp.result || '').toUpperCase()}). Inspector: <strong>${insp.inspector || 'Staff'}</strong>.<br>Findings: ${insp.findings || 'Corrective maintenance required.'}`;
                }

                const notesInput = document.getElementById('create_maint_notes');
                if (notesInput) {
                    notesInput.value = `[From Inspection Findings #${insp.id} - Result: ${(insp.result || '').toUpperCase()}]:\n${insp.findings || 'Corrective maintenance required.'}`;
                }

                partsState.create = [];
                renderPartsTable('create');

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

                // Pre-check assigned personnel
                const pIds = Array.isArray(item.assigned_personnel) 
                    ? item.assigned_personnel.map(p => typeof p === 'object' ? Number(p.id) : Number(p))
                    : [];
                document.querySelectorAll('.edit-maint-personnel-checkbox').forEach(cb => {
                    cb.checked = pIds.includes(Number(cb.value));
                });

                let sDate = item.scheduled_date || '';
                if (sDate.includes('T')) sDate = sDate.split('T')[0];
                document.getElementById('edit_maint_date').value = sDate;

                document.getElementById('edit_maint_duration').value = item.estimated_duration || '';
                document.getElementById('edit_maint_notes').value = item.notes || '';

                // Populate existing parts
                partsState.edit = [];
                if (item.parts && item.parts.length > 0) {
                    partsState.edit = item.parts.map(p => ({
                        inventory_type: p.inventory_type,
                        inventory_id: p.inventory_id,
                        part_name: p.part_name,
                        quantity: p.quantity,
                        unit: p.unit,
                        unit_cost: parseFloat(p.unit_cost || 0),
                        total_cost: parseFloat(p.total_cost || (p.quantity * (p.unit_cost || 0)))
                    }));
                }
                renderPartsTable('edit');

                window.openModal('edit-maintenance-modal');
            }

            function openConfirmMaintenanceModal(item) {
                const form = document.getElementById('confirmMaintenanceForm');
                form.action = '/maintenance/' + item.id + '/confirm';
                document.getElementById('confirm_wo_number').textContent = item.work_order_number || ('#' + item.id);
                document.getElementById('confirm_wo_asset').textContent = (item.asset && item.asset.name) ? item.asset.name : 'Equipment';

                const pIds = Array.isArray(item.assigned_personnel) 
                    ? item.assigned_personnel.map(p => typeof p === 'object' ? Number(p.id) : Number(p))
                    : (item.assigned_to ? [Number(item.assigned_to)] : []);
                document.querySelectorAll('.confirm-maint-personnel-checkbox').forEach(cb => {
                    cb.checked = pIds.includes(Number(cb.value));
                });

                window.openModal('confirm-maintenance-modal');
            }

            function openAssignMaintenancePersonnelModal(item) {
                const form = document.getElementById('assignMaintenancePersonnelForm');
                form.action = '/maintenance/' + item.id + '/assign-personnel';
                document.getElementById('assign_wo_number').textContent = item.work_order_number || ('#' + item.id);
                document.getElementById('assign_wo_asset').textContent = (item.asset && item.asset.name) ? item.asset.name : 'Equipment';

                const pIds = Array.isArray(item.assigned_personnel) 
                    ? item.assigned_personnel.map(p => typeof p === 'object' ? Number(p.id) : Number(p))
                    : (item.assigned_to ? [Number(item.assigned_to)] : []);
                document.querySelectorAll('.assign-maint-personnel-checkbox').forEach(cb => {
                    cb.checked = pIds.includes(Number(cb.value));
                });

                window.openModal('assign-maintenance-personnel-modal');
            }

            // Complete Work Order & Verify Status (Requirement 6 & 8)
            function openCompleteWorkOrderModal(itemOrId, woNumber, cost) {
                const form = document.getElementById('completeWorkOrderForm');
                let id = typeof itemOrId === 'object' ? itemOrId.id : itemOrId;
                form.action = '/maintenance/' + id + '/complete';

                const woNum = typeof itemOrId === 'object' ? (itemOrId.work_order_number || ('WO-' + String(itemOrId.id).padStart(5, '0'))) : woNumber;
                const woCost = typeof itemOrId === 'object' ? (itemOrId.cost || '') : (cost || '');

                document.getElementById('complete_wo_number').textContent = woNum;
                document.getElementById('complete_wo_cost').value = woCost;

                const assetNameEl = document.getElementById('complete_wo_asset_name');
                if (assetNameEl) {
                    if (typeof itemOrId === 'object' && itemOrId.asset) {
                        assetNameEl.textContent = itemOrId.asset.name + ' [' + itemOrId.asset.code + ']';
                    } else {
                        assetNameEl.textContent = 'Selected Equipment';
                    }
                }

                if (typeof itemOrId === 'object' && itemOrId.asset && itemOrId.asset.condition) {
                    const condSel = document.getElementById('complete_final_condition');
                    if (condSel) condSel.value = itemOrId.asset.condition;
                }

                partsState.complete = [];
                renderPartsTable('complete');

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
            @endif
        </script>
    @endpush
@endsection
