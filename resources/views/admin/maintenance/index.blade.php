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
                @if($activeTab === 'schedules')
                    <button onclick="openAddScheduleModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                        <i class="fas fa-plus"></i>
                        Add Schedule
                    </button>
                @elseif($activeTab === 'logs')
                    <button onclick="openCreateMaintenanceModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                        <i class="fas fa-plus"></i>
                        Log Maintenance
                    </button>
                @elseif($activeTab === 'inspections')
                    <button onclick="openCreateInspectionModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                        <i class="fas fa-plus"></i>
                        Record Inspection
                    </button>
                @endif
            @endif
        </div>
    </div>

    {{-- Session Alerts --}}


    {{-- High-Level KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
        {{-- Total Schedules --}}
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-0.5">Schedules</p>
                <p class="text-xl font-bold text-gray-900">{{ $metrics['total_schedules'] }}</p>
                <p class="text-[10px] text-gray-400">Preventive routines</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>

        {{-- Attention Due --}}
        <div class="bg-white rounded-xl p-4 border border-amber-200 bg-amber-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-0.5">Due / Overdue</p>
                <p class="text-xl font-bold text-amber-700">{{ $metrics['schedules_due'] }}</p>
                <p class="text-[10px] text-amber-600">Requires attention</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
        </div>

        {{-- Active Maintenance --}}
        <div class="bg-white rounded-xl p-4 border border-indigo-200 bg-indigo-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 mb-0.5">Active Orders</p>
                <p class="text-xl font-bold text-indigo-700">{{ $metrics['active_maintenance'] }}</p>
                <p class="text-[10px] text-indigo-600">In-progress repairs</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">
                <i class="fas fa-wrench"></i>
            </div>
        </div>

        {{-- Inspections Count --}}
        <div class="bg-white rounded-xl p-4 border border-emerald-200 bg-emerald-50/20 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-0.5">Inspections</p>
                <p class="text-xl font-bold text-emerald-700">{{ $metrics['total_inspections'] }}</p>
                <p class="text-[10px] text-emerald-600">Quality audits</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>

        {{-- Completed Tasks --}}
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-0.5">Completed Tasks</p>
                <p class="text-xl font-bold text-gray-900">{{ $metrics['completed_maintenance'] ?? 0 }}</p>
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('financials.expenses') }}" class="text-[10px] text-blue-600 hover:underline">View costs in Financials &rarr;</a>
                @else
                    <p class="text-[10px] text-gray-400">Resolved work orders</p>
                @endif
            </div>
            <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center text-lg">
                <i class="fas fa-check-double"></i>
            </div>
        </div>
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
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ ($activeTab === 'inspections' || $activeTab === 'schedules') ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-clipboard-check"></i>
            <span>Asset Inspection</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ ($activeTab === 'inspections' || $activeTab === 'schedules') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $schedules->total() + $inspections->total() }}
            </span>
        </a>
    </div>

    @if($activeTab === 'inspections' || $activeTab === 'schedules')
        {{-- Asset Inspection Sub-navigation Pills --}}
        <div class="flex items-center gap-2 mb-6 p-1.5 bg-gray-100/80 rounded-xl w-fit border border-gray-200">
            <a
                href="{{ route('maintenance.index', ['tab' => 'schedules']) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ $activeTab === 'schedules' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50' }}"
            >
                <i class="fas fa-calendar-check text-blue-600"></i>
                <span>Preventive Schedules</span>
                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $activeTab === 'schedules' ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-700' }}">
                    {{ $schedules->total() }}
                </span>
            </a>

            <a
                href="{{ route('maintenance.index', ['tab' => 'inspections']) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ $activeTab === 'inspections' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50' }}"
            >
                <i class="fas fa-clipboard-check text-emerald-600"></i>
                <span>Asset Inspections</span>
                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $activeTab === 'inspections' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700' }}">
                    {{ $inspections->total() }}
                </span>
            </a>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 1: PREVENTIVE SCHEDULES                                               --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'schedules')
        {{-- Search & Filters for Schedules --}}
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="flex-1">
                <form action="{{ route('maintenance.index') }}" method="GET" id="searchScheduleForm">
                    <input type="hidden" name="tab" value="schedules">
                    @if(request('asset_type'))<input type="hidden" name="asset_type" value="{{ request('asset_type') }}">@endif
                    @if(request('interval_type'))<input type="hidden" name="interval_type" value="{{ request('interval_type') }}">@endif
                    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                    <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by task, asset, or asset type..." />
                </form>
            </div>

            {{-- Asset Type Filter --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('asset_type') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    <span>{{ request('asset_type') ?: 'Asset Type' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                    <a href="{{ route('maintenance.index', array_merge(request()->except('asset_type', 'schedules_page'), ['tab' => 'schedules'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('asset_type') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Types</a>
                    @foreach($assetTypes as $type)
                        <a href="{{ route('maintenance.index', array_merge(request()->except('schedules_page'), ['tab' => 'schedules', 'asset_type' => $type])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('asset_type') === $type ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ $type }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Interval Filter --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('interval_type') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    <span>{{ request('interval_type') ?: 'Interval' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                    <a href="{{ route('maintenance.index', array_merge(request()->except('interval_type', 'schedules_page'), ['tab' => 'schedules'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('interval_type') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Intervals</a>
                    @foreach($intervalTypes as $interval)
                        <a href="{{ route('maintenance.index', array_merge(request()->except('schedules_page'), ['tab' => 'schedules', 'interval_type' => $interval])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('interval_type') === $interval ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ $interval }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Status Filter --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('status') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    <span>{{ request('status') ?: 'Status' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                    <a href="{{ route('maintenance.index', array_merge(request()->except('status', 'schedules_page'), ['tab' => 'schedules'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('status') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Statuses</a>
                    @foreach($scheduleStatuses as $s)
                        <a href="{{ route('maintenance.index', array_merge(request()->except('schedules_page'), ['tab' => 'schedules', 'status' => $s])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === $s ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ $s }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Schedules Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Asset</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Maintenance Task</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Interval</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Last Done</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Next Due Date</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 align-middle text-left">
                                    <div class="flex items-start gap-2">
                                        <div class="mt-0.5 text-blue-600">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div>
                                            <span class="inline-block px-2 py-0.5 text-xs font-mono font-semibold rounded bg-gray-100 text-gray-700 border border-gray-200 mb-0.5">
                                                {{ $schedule->asset->code ?? 'N/A' }}
                                            </span>
                                            <p class="text-sm font-semibold text-gray-900">{{ $schedule->asset->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-gray-500">{{ $schedule->asset_type }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle text-left text-sm font-medium text-gray-900">
                                    {{ $schedule->maintenance_task }}
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap text-center text-sm text-gray-600">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-repeat text-xs text-gray-400"></i>
                                        {{ $schedule->interval_type }} ({{ $schedule->interval_days }}d)
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap text-left text-sm text-gray-600">
                                    {{ $schedule->last_maintenance_date ? \Carbon\Carbon::parse($schedule->last_maintenance_date)->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap text-left text-sm">
                                    <span class="font-medium text-gray-800">
                                        {{ $schedule->next_due_date ? \Carbon\Carbon::parse($schedule->next_due_date)->format('M d, Y') : 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                    @php
                                        $schedColor = match($schedule->status) {
                                            'Monitoring Only' => 'success',
                                            'Maintenance Due' => 'warning',
                                            'Overdue'         => 'danger',
                                            default           => 'secondary'
                                        };
                                    @endphp
                                    <x-badge variant="{{ $schedColor }}" size="sm">{{ $schedule->status }}</x-badge>
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            type="button"
                                            onclick='openViewScheduleModal(@json($schedule))'
                                            class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-blue-600 transition-colors"
                                            title="View Schedule Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$isReadOnlyStaff)
                                            <button 
                                                type="button"
                                                onclick='openEditScheduleModal(@json($schedule))'
                                                class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors"
                                                title="Edit Schedule"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('maintenance-schedules.destroy', $schedule) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this preventive maintenance schedule?');">
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
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <x-empty-state icon="fas fa-calendar-check" title="No schedules found" description="Create recurring maintenance schedules to monitor equipment health." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $schedules->links() }}
            </div>
        </x-card>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: MAINTENANCE LOGS & REPAIRS                                         --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'logs')
        {{-- Search & Filters for Logs --}}
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="flex-1">
                <form action="{{ route('maintenance.index') }}" method="GET" id="searchLogForm">
                    <input type="hidden" name="tab" value="logs">
                    @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                    <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by technician, notes, or asset..." />
                </form>
            </div>

            {{-- Type Filter --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('type') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    <span>{{ request('type') ? ucfirst(request('type')) : 'Maintenance Type' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                    <a href="{{ route('maintenance.index', array_merge(request()->except('type', 'logs_page'), ['tab' => 'logs'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('type') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Types</a>
                    @foreach($maintenanceTypes as $t)
                        <a href="{{ route('maintenance.index', array_merge(request()->except('logs_page'), ['tab' => 'logs', 'type' => $t])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('type') === $t ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ ucfirst($t) }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Status Filter --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('status') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    <span>{{ request('status') ? ucfirst(str_replace('_', ' ', request('status'))) : 'Status' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                    <a href="{{ route('maintenance.index', array_merge(request()->except('status', 'logs_page'), ['tab' => 'logs'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('status') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Statuses</a>
                    @foreach($maintenanceStatuses as $st)
                        <a href="{{ route('maintenance.index', array_merge(request()->except('logs_page'), ['tab' => 'logs', 'status' => $st])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === $st ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Maintenance Logs Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Work Order / Asset</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type & Priority</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Technician / Assigned</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Parts & Materials</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Service Dates</th>
                            <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($maintenances as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 align-middle text-left">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                                            {{ $item->work_order_number ?? ('WO-' . str_pad($item->id, 5, '0', STR_PAD_LEFT)) }}
                                        </span>
                                        @if($item->asset)
                                            @if($isReadOnlyStaff)
                                                <span class="text-xs font-mono text-gray-500">
                                                    [{{ $item->asset->code }}]
                                                </span>
                                            @else
                                                <a href="{{ route('assets.show', $item->asset_id) }}" class="text-xs font-mono text-gray-500 hover:text-blue-600">
                                                    [{{ $item->asset->code }}]
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">{{ $item->asset->name ?? 'Unknown Asset' }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $item->asset->category->name ?? 'Uncategorized' }}</p>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
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
                                        <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase rounded {{ $priBadge }}">
                                            {{ $item->priority ?? 'medium' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-700">
                                    <p class="font-medium text-gray-900">{{ $item->assignedUser?->name ?? $item->technician ?? 'Unassigned' }}</p>
                                    @if($item->assignedUser && $item->technician && $item->technician !== $item->assignedUser->name)
                                        <p class="text-xs text-gray-400">Shop: {{ $item->technician }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs">
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
                                </td>
                                <td class="px-5 py-3.5 align-middle whitespace-nowrap text-xs text-gray-600 text-left">
                                    <div>Sched: {{ $item->scheduled_date ? \Carbon\Carbon::parse($item->scheduled_date)->format('M d, Y') : 'N/A' }}</div>
                                    @if($item->completed_at)
                                        <div class="text-emerald-700 font-medium">Done: {{ $item->completed_at->format('M d, Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 align-middle whitespace-nowrap text-center">
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
                                </td>
                                <td class="px-5 py-3.5 align-middle whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button 
                                            type="button" 
                                            onclick='openViewMaintenanceModal(@json($item))'
                                            class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-blue-600 transition-colors"
                                            title="View Work Order Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$isReadOnlyStaff)
                                            <button 
                                                type="button" 
                                                onclick='openEditMaintenanceModal(@json($item))'
                                                class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors"
                                                title="Edit Work Order"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('maintenance.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this maintenance record?');">
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
                                    <x-empty-state icon="fas fa-wrench" title="No maintenance logs found" description="Create a work order or complete a preventive schedule to log maintenance." />
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
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 3: ASSET INSPECTIONS                                                  --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'inspections')
        {{-- Search & Filters for Inspections --}}
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="flex-1">
                <form action="{{ route('maintenance.index') }}" method="GET" id="searchInspectionForm">
                    <input type="hidden" name="tab" value="inspections">
                    @if(request('result'))<input type="hidden" name="result" value="{{ request('result') }}">@endif
                    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                    <x-search-bar name="search" value="{{ request('search') }}" placeholder="Search by inspector, findings, or asset..." />
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
                <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 border rounded-lg transition-colors {{ request('status') ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    <span>{{ request('status') ? ucfirst(request('status')) : 'Status' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 py-1">
                    <a href="{{ route('maintenance.index', array_merge(request()->except('status', 'inspections_page'), ['tab' => 'inspections'])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('status') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">All Statuses</a>
                    @foreach($inspectionStatuses as $st)
                        <a href="{{ route('maintenance.index', array_merge(request()->except('inspections_page'), ['tab' => 'inspections', 'status' => $st])) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('status') === $st ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">{{ ucfirst($st) }}</a>
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
                                            onclick='openViewInspectionModal(@json($insp))'
                                            class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-blue-600 transition-colors"
                                            title="View Inspection Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$isReadOnlyStaff)
                                            <button 
                                                type="button"
                                                onclick='openEditInspectionModal(@json($insp))'
                                                class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors"
                                                title="Edit Inspection"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('inspection.destroy', $insp) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this inspection record?');">
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
    @endif

    {{-- ========================================================================= --}}
    {{-- MODALS: PREVENTIVE SCHEDULES                                              --}}
    {{-- ========================================================================= --}}
    @if(!$isReadOnlyStaff)
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

            <x-form-group label="Completion Date" required>
                <input type="date" name="completion_date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </x-form-group>

            <x-form-group label="Technician / Service Provider">
                <input type="text" name="technician" placeholder="Who executed this task?" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
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
                <x-form-group label="Cost (PHP)">
                    <input type="number" step="0.01" min="0" name="cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
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
                <x-form-group label="Cost (PHP)">
                    <input type="number" step="0.01" min="0" name="cost" id="edit_maint_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
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
                <x-form-group label="Final Service Cost (PHP)">
                    <input type="number" step="0.01" min="0" name="cost" id="complete_wo_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
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
    @endif

    {{-- ========================================================================= --}}
    {{-- READ-ONLY VIEW MODALS                                                     --}}
    {{-- ========================================================================= --}}
    {{-- View Schedule Modal (Read-Only) --}}
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

    {{-- View Maintenance Work Order Modal (Read-Only) --}}
    <x-modal id="view-maintenance-modal" title="Maintenance Work Order Details">
        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                    <div>
                        <span class="text-xs font-mono font-bold text-blue-700 bg-blue-100 border border-blue-200 px-2.5 py-1 rounded" id="view_maint_wo_number">
                            -
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="view_maint_type_badge" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">-</span>
                        <span id="view_maint_priority_badge" class="px-2 py-0.5 text-xs font-bold uppercase rounded bg-gray-100 text-gray-800">-</span>
                        <span id="view_maint_status_badge" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-gray-200">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Asset</p>
                        <p class="text-sm font-bold text-gray-900" id="view_maint_asset_name">-</p>
                        <p class="text-xs font-mono text-gray-500" id="view_maint_asset_code">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</p>
                        <p class="text-sm text-gray-800" id="view_maint_asset_category">-</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Technician / Shop</p>
                    <p class="text-sm font-medium text-gray-900" id="view_maint_technician">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Staff Officer</p>
                    <p class="text-sm text-gray-800" id="view_maint_assigned">-</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Scheduled Date</p>
                    <p class="text-sm text-gray-800" id="view_maint_sched_date">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Completed Date</p>
                    <p class="text-sm text-gray-800" id="view_maint_comp_date">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimated Duration</p>
                    <p class="text-sm text-gray-800" id="view_maint_duration">-</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Cost (PHP)</p>
                    <p class="text-sm font-bold text-gray-900" id="view_maint_cost">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Parts & Materials</p>
                    <p class="text-sm text-gray-800" id="view_maint_parts">-</p>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes & Descriptions</p>
                <div class="mt-1 p-3 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-line border border-gray-200" id="view_maint_notes">-</div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <x-button variant="secondary" type="button" onclick="window.closeModal('view-maintenance-modal')">
                    Close
                </x-button>
            </div>
        </div>
    </x-modal>

    {{-- View Inspection Modal (Read-Only) --}}
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
            // View Schedule Modal
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

            // View Maintenance Modal
            function openViewMaintenanceModal(item) {
                document.getElementById('view_maint_wo_number').textContent = item.work_order_number || ('WO-' + String(item.id).padStart(5, '0'));
                document.getElementById('view_maint_asset_name').textContent = (item.asset && item.asset.name) ? item.asset.name : 'Unknown Asset';
                document.getElementById('view_maint_asset_code').textContent = (item.asset && item.asset.code) ? '[' + item.asset.code + ']' : '-';
                document.getElementById('view_maint_asset_category').textContent = (item.asset && item.asset.category && item.asset.category.name) ? item.asset.category.name : 'Uncategorized';
                
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

                document.getElementById('view_maint_technician').textContent = item.technician || 'Unspecified';
                document.getElementById('view_maint_assigned').textContent = (item.assigned_user && item.assigned_user.name) ? item.assigned_user.name : (item.assignedUser && item.assignedUser.name ? item.assignedUser.name : 'Unassigned');

                let sDate = item.scheduled_date || 'N/A';
                if (sDate && sDate.includes('T')) sDate = sDate.split('T')[0];
                document.getElementById('view_maint_sched_date').textContent = sDate;

                let cDate = item.completed_at || 'N/A';
                if (cDate && cDate.includes('T')) cDate = cDate.split('T')[0];
                document.getElementById('view_maint_comp_date').textContent = cDate;

                document.getElementById('view_maint_duration').textContent = item.estimated_duration || 'N/A';
                document.getElementById('view_maint_cost').textContent = item.cost ? ('₱' + parseFloat(item.cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})) : '₱0.00';
                document.getElementById('view_maint_parts').textContent = item.parts_used || 'None specified';
                document.getElementById('view_maint_notes').textContent = item.notes || 'No remarks recorded.';

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
            @endif
        </script>
    @endpush
@endsection
