@extends('layouts.admin')

@section('title', 'Executive Dashboard - BDES')

@section('content')
<div class="space-y-6">

    {{-- Executive Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-[#E5E7EB] shadow-sm">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#003366] flex items-center gap-3">
                <i class="fas fa-shield-halved text-[#003366]"></i>
                Barangay Disaster Asset Dashboard
            </h1>
            <p class="text-[#6B7280] text-sm mt-1">Real-time inventory readiness, field deployments, and operational integrity monitoring.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasRole('inventory_officer', 'bdrrmc_officer')))
                <a href="{{ route('assets.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                    <i class="fas fa-plus"></i>
                    <span>Register Asset</span>
                </a>
                <a href="{{ route('deployment.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-[#2563EB] border border-[#60A5FA]/40 rounded-xl text-xs font-bold transition-colors">
                    <i class="fas fa-truck-fast"></i>
                    <span>Deploy Equipment</span>
                </a>
            @endif
            @if(auth()->check() && auth()->user()->hasRole('maintenance_officer'))
                <a href="{{ route('maintenance.index', ['tab' => 'logs']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                    <i class="fas fa-screwdriver-wrench"></i>
                    <span>Work Orders</span>
                </a>
            @endif
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('financials.expenses') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-xl text-xs font-bold transition-colors">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Asset expenses</span>
                </a>
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-green-50 hover:bg-green-100 text-[#2E7D32] border border-[#2E7D32]/30 rounded-xl text-xs font-bold transition-colors">
                    <i class="fas fa-file-invoice"></i>
                    <span>Reports Center</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Reconciled Clickable KPI Metric Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">
        {{-- Total Assets --}}
        <a href="{{ route('assets.index', ['tab' => 'assets']) }}" class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm hover:border-[#2563EB] hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider block">Total Assets</span>
                    <span class="text-2xl font-bold text-[#003366] mt-1 block group-hover:text-[#2563EB] transition-colors">
                        {{ number_format($stats['total_assets']) }}
                    </span>
                    @if(auth()->user()?->isAdmin())
                        <span class="text-[10px] text-blue-600 block mt-0.5 font-medium hover:underline">Financials &rarr;</span>
                    @else
                        <span class="text-[10px] text-[#6B7280] block mt-0.5 font-medium">Active Inventory</span>
                    @endif
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-[#003366] flex items-center justify-center text-lg group-hover:bg-[#003366] group-hover:text-white transition-colors">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </a>

        {{-- Available & Ready --}}
        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'available']) }}" class="bg-white p-4 rounded-xl border border-[#2E7D32]/30 bg-[#2E7D32]/5 shadow-sm hover:border-[#2E7D32] hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#2E7D32] uppercase tracking-wider block">Available Quantity</span>
                    <span class="text-2xl font-bold text-[#2E7D32] mt-1 block">
                        {{ number_format($stats['available']) }} Available
                    </span>
                    <span class="text-[10px] text-[#2E7D32]/90 block mt-0.5 font-medium">Ready for immediate dispatch</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#2E7D32]/10 text-[#2E7D32] flex items-center justify-center text-lg group-hover:bg-[#2E7D32] group-hover:text-white transition-colors">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </a>

        {{-- Deployed in Field --}}
        <a href="{{ route('deployment.index') }}" class="bg-white p-4 rounded-xl border border-[#2563EB]/30 bg-[#2563EB]/5 shadow-sm hover:border-[#2563EB] hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#2563EB] uppercase tracking-wider block">Deployed</span>
                    <span class="text-2xl font-bold text-[#2563EB] mt-1 block">
                        {{ number_format($stats['deployed']) }}
                    </span>
                    <span class="text-[10px] text-[#2563EB]/90 block mt-0.5 font-medium">In field operations</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#2563EB]/10 text-[#2563EB] flex items-center justify-center text-lg group-hover:bg-[#2563EB] group-hover:text-white transition-colors">
                    <i class="fas fa-truck-ramp-box"></i>
                </div>
            </div>
        </a>

        {{-- Under Maintenance --}}
        <a href="{{ route('maintenance.index', ['tab' => 'logs']) }}" class="bg-white p-4 rounded-xl border border-[#F59E0B]/30 bg-[#F59E0B]/5 shadow-sm hover:border-[#F59E0B] hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#B45309] uppercase tracking-wider block">Maintenance</span>
                    <span class="text-2xl font-bold text-[#B45309] mt-1 block">
                        {{ number_format($stats['under_maintenance']) }}
                    </span>
                    <span class="text-[10px] text-[#B45309]/90 block mt-0.5 font-medium">Under repair / overhaul</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#F59E0B]/15 text-[#B45309] flex items-center justify-center text-lg group-hover:bg-[#F59E0B] group-hover:text-white transition-colors">
                    <i class="fas fa-screwdriver-wrench"></i>
                </div>
            </div>
        </a>

        {{-- Out of Service / Lost --}}
        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'out_of_service']) }}" class="bg-white p-4 rounded-xl border border-[#DC2626]/30 bg-[#DC2626]/5 shadow-sm hover:border-[#DC2626] hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#DC2626] uppercase tracking-wider block">Out of Service</span>
                    <span class="text-2xl font-bold text-[#DC2626] mt-1 block">
                        {{ number_format($stats['out_of_service'] + $stats['lost']) }}
                    </span>
                    <span class="text-[10px] text-[#DC2626]/90 block mt-0.5 font-medium">Defective / Missing</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#DC2626]/10 text-[#DC2626] flex items-center justify-center text-lg group-hover:bg-[#DC2626] group-hover:text-white transition-colors">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
        </a>

        {{-- Asset Inspections --}}
        <a href="{{ route('maintenance.index', ['tab' => 'inspections']) }}" class="bg-white p-4 rounded-xl border border-[#60A5FA]/30 bg-[#60A5FA]/5 shadow-sm hover:border-[#2563EB] hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#2563EB] uppercase tracking-wider block">Inspections</span>
                    <span class="text-2xl font-bold text-[#2563EB] mt-1 block">
                        {{ number_format($stats['inspections']) }}
                    </span>
                    <span class="text-[10px] text-[#6B7280] block mt-0.5 font-medium">Evaluations & checkups</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-[#2563EB] flex items-center justify-center text-lg group-hover:bg-[#2563EB] group-hover:text-white transition-colors">
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
        <div class="bg-amber-50/60 border border-[#F59E0B]/30 rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-[#F59E0B] animate-pulse"></div>
                    <h2 class="text-base font-bold text-[#B45309] uppercase tracking-wider">
                        Operational Attention Required
                    </h2>
                </div>
                <span class="text-xs font-semibold text-[#B45309] bg-amber-100 border border-[#F59E0B]/30 px-2.5 py-0.5 rounded-full">
                    Action Items Detected
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                {{-- Overdue Deployments --}}
                @if($attentionItems['overdue_deployments']->isNotEmpty())
                    <div class="bg-white p-4 rounded-xl border border-[#DC2626]/20 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#DC2626] flex items-center gap-1.5">
                                <i class="fas fa-clock"></i> Overdue Deployments ({{ $attentionItems['overdue_deployments']->count() }})
                            </span>
                            <a href="{{ route('deployment.index', ['status' => 'active']) }}" class="text-[#2563EB] hover:underline font-medium">View</a>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            @foreach($attentionItems['overdue_deployments']->take(2) as $od)
                                <div class="p-2 rounded bg-rose-50 border border-rose-100">
                                    <p class="font-bold text-[#1F2937]">{{ $od->asset?->name ?? 'Asset' }}</p>
                                    <p class="text-[#6B7280]">Dest: {{ $od->destination }}</p>
                                    <p class="text-[#DC2626] font-medium">Due: {{ $od->expected_return_date?->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Overdue Maintenance --}}
                @if($attentionItems['overdue_maintenance']->isNotEmpty())
                    <div class="bg-white p-4 rounded-xl border border-[#F59E0B]/30 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#B45309] flex items-center gap-1.5">
                                <i class="fas fa-wrench"></i> Pending Work Orders ({{ $attentionItems['overdue_maintenance']->count() }})
                            </span>
                            <a href="{{ route('maintenance.index', ['tab' => 'logs']) }}" class="text-[#2563EB] hover:underline font-medium">View</a>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            @foreach($attentionItems['overdue_maintenance']->take(2) as $om)
                                <div class="p-2 rounded bg-amber-50 border border-amber-100">
                                    <p class="font-bold text-[#1F2937]">{{ $om->work_order_number }} - {{ $om->asset?->name }}</p>
                                    <p class="text-[#6B7280]">Sched: {{ $om->scheduled_date?->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- High Risk / Defective Assets --}}
                @if($attentionItems['high_risk_assets']->isNotEmpty())
                    <div class="bg-white p-4 rounded-xl border border-[#DC2626]/20 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#DC2626] flex items-center gap-1.5">
                                <i class="fas fa-triangle-exclamation"></i> Defective / Poor Assets ({{ $attentionItems['high_risk_assets']->count() }})
                            </span>
                            <a href="{{ route('assets.index', ['status' => 'out_of_service']) }}" class="text-[#2563EB] hover:underline font-medium">View</a>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            @foreach($attentionItems['high_risk_assets']->take(2) as $ha)
                                <div class="p-2 rounded bg-gray-50 border border-[#E5E7EB]">
                                    <p class="font-bold text-[#1F2937]">{{ $ha->code }} - {{ $ha->name }}</p>
                                    <p class="text-[#6B7280]">State: <span class="capitalize font-medium">{{ $ha->status }} ({{ $ha->condition }})</span></p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Balanced Charts & Operational Timelines --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        {{-- Category Breakdown with Progress Indicators --}}
        <div class="lg:col-span-7 flex flex-col">
            <x-card class="flex-1 flex flex-col">
                <div class="p-4 sm:p-5 border-b border-[#E5E7EB] flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#1F2937] flex items-center gap-2">
                            <i class="fas fa-chart-pie text-[#2563EB]"></i> Assets by Category Distribution
                        </h2>
                        <p class="text-xs text-[#6B7280] mt-0.5">Categorical inventory allocation across all operational divisions.</p>
                    </div>
                    <a href="{{ route('assets.index', ['tab' => 'categories']) }}" class="text-xs text-[#2563EB] hover:underline font-semibold">Categories</a>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-center">
                    @if(!empty($assetsByCategory['labels']))
                        @php
                            $catTotal = !empty($assetsByCategory['data']) ? array_sum($assetsByCategory['data']) : 0;
                            $palette = ['#003366', '#2563EB', '#2E7D32', '#F59E0B', '#60A5FA', '#1F2937', '#DC2626', '#4a3aa7'];
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
                                    <div class="p-2 rounded-lg bg-[#FAFAF9] border border-[#E5E7EB] hover:bg-gray-100/80 transition-colors">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $color }}"></span>
                                                <span class="font-semibold text-[#1F2937] truncate" title="{{ $label }}">{{ $label }}</span>
                                            </div>
                                            <span class="font-bold text-[#1F2937] whitespace-nowrap ml-2">
                                                {{ $val }} <span class="text-[#6B7280] font-normal">({{ $pct }}%)</span>
                                            </span>
                                        </div>
                                        <div class="w-full bg-[#E5E7EB] rounded-full h-1.5 overflow-hidden">
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
                                <p class="text-[#6B7280] text-sm font-medium">No category data recorded yet</p>
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Unified Operational Timelines & Schedules Card --}}
        <div class="lg:col-span-5 flex flex-col">
            <x-card class="flex-1 flex flex-col">
                <div class="p-4 sm:p-5 border-b border-[#E5E7EB] flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#1F2937] flex items-center gap-2">
                            <i class="fas fa-calendar-check text-[#2E7D32]"></i> Operational Schedules & Actions
                        </h2>
                        <p class="text-xs text-[#6B7280] mt-0.5">Upcoming maintenance routines and physical asset inspections.</p>
                    </div>
                    <a href="{{ route('maintenance.index') }}" class="text-xs text-[#2563EB] hover:underline font-semibold">Maintenance Hub</a>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    {{-- Upcoming Inspections Block --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-[#2E7D32] uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-clipboard-check"></i> Scheduled Inspections ({{ $upcomingInspections->count() }})
                            </span>
                            <a href="{{ route('maintenance.index', ['tab' => 'inspections']) }}" class="text-[11px] text-[#2563EB] hover:underline">View All</a>
                        </div>
                        @if($upcomingInspections->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($upcomingInspections->take(3) as $inspection)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-green-50/50 border border-green-100 text-xs">
                                        <div class="min-w-0 flex-1 pr-2">
                                            <p class="font-bold text-[#1F2937] truncate">{{ $inspection->asset?->name ?? 'Asset' }}</p>
                                            <p class="text-[#6B7280] font-mono text-[10px]">Code: {{ $inspection->asset?->code ?? 'N/A' }}</p>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-green-100 text-[#2E7D32] font-semibold text-[10px] whitespace-nowrap">
                                            Due {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('M d') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2.5 rounded-xl bg-[#FAFAF9] border border-[#E5E7EB] text-xs text-[#6B7280] flex items-center gap-2">
                                <i class="fas fa-circle-check text-[#2E7D32]"></i>
                                <span>All asset physical inspections up to date.</span>
                            </div>
                        @endif
                    </div>

                    {{-- Maintenance Reminders Block --}}
                    <div class="pt-2 border-t border-[#E5E7EB]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-[#B45309] uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-wrench"></i> Pending Maintenance ({{ $maintenanceReminders->count() }})
                            </span>
                            <a href="{{ route('maintenance.index', ['tab' => 'logs']) }}" class="text-[11px] text-[#2563EB] hover:underline">View All</a>
                        </div>
                        @if($maintenanceReminders->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($maintenanceReminders->take(3) as $reminder)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/50 border border-amber-100 text-xs">
                                        <div class="min-w-0 flex-1 pr-2">
                                            <p class="font-bold text-[#1F2937] truncate">{{ $reminder->asset?->name ?? 'Asset' }}</p>
                                            <p class="text-[#6B7280] text-[10px] capitalize">{{ $reminder->type }} service</p>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-[#B45309] font-semibold text-[10px] whitespace-nowrap">
                                            {{ $reminder->scheduled_date ? \Carbon\Carbon::parse($reminder->scheduled_date)->format('M d') : 'Queued' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2.5 rounded-xl bg-[#FAFAF9] border border-[#E5E7EB] text-xs text-[#6B7280] flex items-center gap-2">
                                <i class="fas fa-circle-check text-[#2563EB]"></i>
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
        <div class="p-5 border-b border-[#E5E7EB] flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-[#1F2937] flex items-center gap-2">
                    <i class="fas fa-timeline text-[#2563EB]"></i> Recent Operational Audit Activities
                </h2>
                <p class="text-xs text-[#6B7280]">Live, tamper-evident log of system operations and asset movements.</p>
            </div>
            <a href="{{ route('asset-history.index') }}" class="text-xs text-[#2563EB] hover:underline font-semibold">View Full History</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAFAF9] text-[#6B7280] uppercase font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">Actor</th>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3 text-right">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($recentAuditLogs as $log)
                        <tr class="hover:bg-[#FAFAF9] transition-colors">
                            <td class="px-5 py-3 font-mono text-[#6B7280] whitespace-nowrap">
                                {{ $log->created_at->format('M d, H:i') }}
                            </td>
                            <td class="px-5 py-3 font-medium text-[#1F2937] whitespace-nowrap">
                                {{ $log->user?->name ?? 'System' }}
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] bg-blue-50 text-[#003366] border border-[#60A5FA]/30">
                                    {{ strtoupper($log->event) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-[#1F2937] max-w-md">
                                {{ $log->description }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-[#6B7280] whitespace-nowrap">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-[#6B7280]">No operational audit activities recorded yet.</td>
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
