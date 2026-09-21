@extends('layouts.admin')

@section('title', 'Executive Dashboard - BDES')

@section('content')
<div class="space-y-6">

    {{-- Executive Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3">
                <i class="fas fa-shield-halved text-blue-600"></i>
                Barangay Disaster Asset Dashboard
            </h1>
            <p class="text-gray-600 text-sm mt-1">Real-time inventory readiness, field deployments, and operational integrity monitoring.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('assets.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                <i class="fas fa-plus"></i>
                <span>Register Asset</span>
            </a>
            <a href="{{ route('deployment.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition-colors">
                <i class="fas fa-truck-fast"></i>
                <span>Deploy Equipment</span>
            </a>
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition-colors">
                    <i class="fas fa-file-invoice"></i>
                    <span>Reports Center</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Reconciled Clickable KPI Metric Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">
        {{-- Total Assets --}}
        <a href="{{ route('assets.index', ['tab' => 'assets']) }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Total Assets</span>
                    <span class="text-2xl font-bold text-gray-900 mt-1 block group-hover:text-blue-600 transition-colors">
                        {{ number_format($stats['total_assets']) }}
                    </span>
                    <span class="text-[10px] text-gray-400 block mt-0.5 font-medium">₱{{ number_format($stats['total_value'], 0) }} total</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </a>

        {{-- Available & Ready --}}
        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'available']) }}" class="bg-white p-4 rounded-xl border border-emerald-200 bg-emerald-50/20 shadow-sm hover:border-emerald-400 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">Available</span>
                    <span class="text-2xl font-bold text-emerald-700 mt-1 block">
                        {{ number_format($stats['available']) }}
                    </span>
                    <span class="text-[10px] text-emerald-600 block mt-0.5 font-medium">Ready for dispatch</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </a>

        {{-- Deployed in Field --}}
        <a href="{{ route('deployment.index') }}" class="bg-white p-4 rounded-xl border border-blue-200 bg-blue-50/20 shadow-sm hover:border-blue-400 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-blue-700 uppercase tracking-wider block">Deployed</span>
                    <span class="text-2xl font-bold text-blue-700 mt-1 block">
                        {{ number_format($stats['deployed']) }}
                    </span>
                    <span class="text-[10px] text-blue-600 block mt-0.5 font-medium">In field operations</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fas fa-truck-ramp-box"></i>
                </div>
            </div>
        </a>

        {{-- Under Maintenance --}}
        <a href="{{ route('maintenance.index', ['tab' => 'logs']) }}" class="bg-white p-4 rounded-xl border border-amber-200 bg-amber-50/20 shadow-sm hover:border-amber-400 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider block">Maintenance</span>
                    <span class="text-2xl font-bold text-amber-700 mt-1 block">
                        {{ number_format($stats['under_maintenance']) }}
                    </span>
                    <span class="text-[10px] text-amber-600 block mt-0.5 font-medium">Under repair / overhaul</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-lg group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <i class="fas fa-screwdriver-wrench"></i>
                </div>
            </div>
        </a>

        {{-- Out of Service / Lost --}}
        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'out_of_service']) }}" class="bg-white p-4 rounded-xl border border-rose-200 bg-rose-50/20 shadow-sm hover:border-rose-400 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider block">Out of Service</span>
                    <span class="text-2xl font-bold text-rose-700 mt-1 block">
                        {{ number_format($stats['out_of_service'] + $stats['lost']) }}
                    </span>
                    <span class="text-[10px] text-rose-600 block mt-0.5 font-medium">Defective / Missing</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-lg group-hover:bg-rose-600 group-hover:text-white transition-colors">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
        </a>

        {{-- Asset Inspections --}}
        <a href="{{ route('maintenance.index', ['tab' => 'inspections']) }}" class="bg-white p-4 rounded-xl border border-purple-200 bg-purple-50/20 shadow-sm hover:border-purple-400 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-purple-700 uppercase tracking-wider block">Inspections</span>
                    <span class="text-2xl font-bold text-purple-700 mt-1 block">
                        {{ number_format($stats['inspections']) }}
                    </span>
                    <span class="text-[10px] text-purple-600 block mt-0.5 font-medium">Evaluations & checkups</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-lg group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
        </a>
    </div>

    {{-- "NEEDS ATTENTION" OPERATIONAL ALERTS --}}
    @php
        $hasAlerts = $attentionItems['overdue_deployments']->isNotEmpty()
            || $attentionItems['overdue_maintenance']->isNotEmpty()
            || $attentionItems['high_risk_assets']->isNotEmpty();
    @endphp

    @if($hasAlerts)
        <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></div>
                    <h2 class="text-base font-bold text-amber-950 uppercase tracking-wider">
                        Operational Attention Required
                    </h2>
                </div>
                <span class="text-xs font-semibold text-amber-800 bg-amber-100 border border-amber-300 px-2.5 py-0.5 rounded-full">
                    Action Items Detected
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                {{-- Overdue Deployments --}}
                @if($attentionItems['overdue_deployments']->isNotEmpty())
                    <div class="bg-white p-4 rounded-xl border border-amber-200 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-rose-700 flex items-center gap-1.5">
                                <i class="fas fa-clock"></i> Overdue Deployments ({{ $attentionItems['overdue_deployments']->count() }})
                            </span>
                            <a href="{{ route('deployment.index', ['status' => 'active']) }}" class="text-blue-600 hover:underline">View</a>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            @foreach($attentionItems['overdue_deployments']->take(2) as $od)
                                <div class="p-2 rounded bg-rose-50 border border-rose-100">
                                    <p class="font-bold text-gray-900">{{ $od->asset?->name ?? 'Asset' }}</p>
                                    <p class="text-gray-500">Dest: {{ $od->destination }}</p>
                                    <p class="text-rose-600 font-medium">Due: {{ $od->expected_return_date?->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Overdue Maintenance --}}
                @if($attentionItems['overdue_maintenance']->isNotEmpty())
                    <div class="bg-white p-4 rounded-xl border border-amber-200 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-amber-800 flex items-center gap-1.5">
                                <i class="fas fa-wrench"></i> Pending Work Orders ({{ $attentionItems['overdue_maintenance']->count() }})
                            </span>
                            <a href="{{ route('maintenance.index', ['tab' => 'logs']) }}" class="text-blue-600 hover:underline">View</a>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            @foreach($attentionItems['overdue_maintenance']->take(2) as $om)
                                <div class="p-2 rounded bg-amber-50 border border-amber-100">
                                    <p class="font-bold text-gray-900">{{ $om->work_order_number }} - {{ $om->asset?->name }}</p>
                                    <p class="text-gray-500">Sched: {{ $om->scheduled_date?->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- High Risk / Defective Assets --}}
                @if($attentionItems['high_risk_assets']->isNotEmpty())
                    <div class="bg-white p-4 rounded-xl border border-amber-200 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-rose-700 flex items-center gap-1.5">
                                <i class="fas fa-triangle-exclamation"></i> Defective / Poor Assets ({{ $attentionItems['high_risk_assets']->count() }})
                            </span>
                            <a href="{{ route('assets.index', ['status' => 'out_of_service']) }}" class="text-blue-600 hover:underline">View</a>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            @foreach($attentionItems['high_risk_assets']->take(2) as $ha)
                                <div class="p-2 rounded bg-gray-50 border border-gray-200">
                                    <p class="font-bold text-gray-900">{{ $ha->code }} - {{ $ha->name }}</p>
                                    <p class="text-gray-500">State: <span class="capitalize font-medium">{{ $ha->status }} ({{ $ha->condition }})</span></p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Balanced Charts & Operational Timelines (Mitigating Blank Space) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        {{-- Category Breakdown with Progress Indicators --}}
        <div class="lg:col-span-7 flex flex-col">
            <x-card class="flex-1 flex flex-col">
                <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-pie text-blue-600"></i> Assets by Category Distribution
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Categorical inventory allocation across all operational divisions.</p>
                    </div>
                    <a href="{{ route('assets.index', ['tab' => 'categories']) }}" class="text-xs text-blue-600 hover:underline font-semibold">Categories</a>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-center">
                    @if(!empty($assetsByCategory['labels']))
                        @php
                            $catTotal = !empty($assetsByCategory['data']) ? array_sum($assetsByCategory['data']) : 0;
                            $palette = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300', '#4a3aa7', '#e34948'];
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                            {{-- Doughnut Chart --}}
                            <div class="w-full h-64 flex items-center justify-center">
                                <canvas data-chart="assets-by-category"></canvas>
                            </div>

                            {{-- Detailed Breakdown List Filling Blank Space --}}
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                @foreach($assetsByCategory['labels'] as $idx => $label)
                                    @php
                                        $val = $assetsByCategory['data'][$idx] ?? 0;
                                        $pct = $catTotal > 0 ? round(($val / $catTotal) * 100, 1) : 0;
                                        $color = $palette[$idx % count($palette)];
                                    @endphp
                                    <div class="p-2 rounded-lg bg-gray-50/80 border border-gray-100 hover:bg-gray-100/80 transition-colors">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $color }}"></span>
                                                <span class="font-semibold text-gray-800 truncate" title="{{ $label }}">{{ $label }}</span>
                                            </div>
                                            <span class="font-bold text-gray-900 whitespace-nowrap ml-2">
                                                {{ $val }} <span class="text-gray-400 font-normal">({{ $pct }}%)</span>
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full" style="width: {{ $pct }}%; background-color: {{ $color }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <script type="application/json" id="assets-by-category-data">{!! json_encode($assetsByCategory, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                    @else
                        <div class="w-full h-64 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-chart-pie text-4xl text-gray-300 mb-2 block"></i>
                                <p class="text-gray-600 text-sm font-medium">No category data recorded yet</p>
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Unified Operational Timelines & Schedules Card --}}
        <div class="lg:col-span-5 flex flex-col">
            <x-card class="flex-1 flex flex-col">
                <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-calendar-check text-emerald-600"></i> Operational Schedules & Actions
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Upcoming maintenance routines and physical asset inspections.</p>
                    </div>
                    <a href="{{ route('maintenance.index') }}" class="text-xs text-blue-600 hover:underline font-semibold">Maintenance Hub</a>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    {{-- Upcoming Inspections Block --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-clipboard-check"></i> Scheduled Inspections ({{ $upcomingInspections->count() }})
                            </span>
                            <a href="{{ route('maintenance.index', ['tab' => 'inspections']) }}" class="text-[11px] text-blue-600 hover:underline">View All</a>
                        </div>
                        @if($upcomingInspections->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($upcomingInspections->take(3) as $inspection)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50/40 border border-emerald-100 text-xs">
                                        <div class="min-w-0 flex-1 pr-2">
                                            <p class="font-bold text-gray-900 truncate">{{ $inspection->asset?->name ?? 'Asset' }}</p>
                                            <p class="text-gray-500 font-mono text-[10px]">Code: {{ $inspection->asset?->code ?? 'N/A' }}</p>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-semibold text-[10px] whitespace-nowrap">
                                            Due {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('M d') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 text-xs text-gray-500 flex items-center gap-2">
                                <i class="fas fa-circle-check text-emerald-500"></i>
                                <span>All asset physical inspections up to date.</span>
                            </div>
                        @endif
                    </div>

                    {{-- Maintenance Reminders Block --}}
                    <div class="pt-2 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-wrench"></i> Pending Maintenance ({{ $maintenanceReminders->count() }})
                            </span>
                            <a href="{{ route('maintenance.index', ['tab' => 'logs']) }}" class="text-[11px] text-blue-600 hover:underline">View All</a>
                        </div>
                        @if($maintenanceReminders->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($maintenanceReminders->take(3) as $reminder)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/40 border border-amber-100 text-xs">
                                        <div class="min-w-0 flex-1 pr-2">
                                            <p class="font-bold text-gray-900 truncate">{{ $reminder->asset?->name ?? 'Asset' }}</p>
                                            <p class="text-gray-500 text-[10px] capitalize">{{ $reminder->type }} service</p>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-semibold text-[10px] whitespace-nowrap">
                                            {{ $reminder->scheduled_date ? \Carbon\Carbon::parse($reminder->scheduled_date)->format('M d') : 'Queued' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 text-xs text-gray-500 flex items-center gap-2">
                                <i class="fas fa-circle-check text-blue-500"></i>
                                <span>No pending or overdue maintenance work orders.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Real Immutable Audit Activity Trail --}}
    <x-card>
        <div class="p-5 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-timeline text-blue-600"></i> Recent Operational Audit Activities
                </h2>
                <p class="text-xs text-gray-500">Live, tamper-evident log of system operations and asset movements.</p>
            </div>
            <a href="{{ route('asset-history.index') }}" class="text-xs text-blue-600 hover:underline font-semibold">View Full History</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-600 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">Actor</th>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3 text-right">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentAuditLogs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-mono text-gray-500 whitespace-nowrap">
                                {{ $log->created_at->format('M d, H:i') }}
                            </td>
                            <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap">
                                {{ $log->user?->name ?? 'System' }}
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] bg-gray-100 text-gray-700">
                                    {{ strtoupper($log->event) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-700 max-w-md">
                                {{ $log->description }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-gray-500 whitespace-nowrap">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">No operational audit activities recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>

@push('scripts')
    @vite('resources/js/dashboard-charts.js')
@endpush
@endsection
