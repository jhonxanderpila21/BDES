@extends('layouts.admin')

@section('title', 'Reports & Compliance - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <i class="fas fa-file-invoice text-blue-600"></i>
                Reports & Regulatory Compliance Center
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Tracking & Reporting'],
                ['label' => 'Reports Center']
            ]" />
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => $selectedType])) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium text-sm shadow-sm">
                <i class="fas fa-file-csv"></i>
                Export {{ $types[$selectedType]['title'] ?? 'Report' }} (CSV)
            </a>
        </div>
    </div>

    {{-- Compact Report Type Selector Bar --}}
    <div class="mb-6 bg-white rounded-xl shadow-xs border border-gray-200 p-2.5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 pb-2 border-b border-gray-100 mb-2">
            <div class="flex items-center gap-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                <i class="fas fa-layer-group text-blue-600"></i>
                <span>Report Modules:</span>
                <span class="text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md font-semibold normal-case">
                    {{ $types[$selectedType]['title'] }}
                </span>
            </div>
            <div class="text-xs text-gray-400">
                {{ count($types) }} standard reports available
            </div>
        </div>

        {{-- Desktop / Tablet Compact Pill Bar --}}
        <div class="hidden sm:flex flex-wrap items-center gap-1.5">
            @foreach($types as $key => $meta)
                @php
                    $isActive = ($selectedType === $key);
                    $iconName = match($meta['icon'] ?? 'clipboard-list') {
                        'clipboard-list' => 'fas fa-clipboard-list',
                        'check-circle' => 'fas fa-check-circle',
                        'truck' => 'fas fa-truck',
                        'clock' => 'fas fa-clock',
                        'wrench' => 'fas fa-wrench',
                        'currency-dollar' => 'fas fa-peso-sign',
                        'shield-check' => 'fas fa-shield-alt',
                        'exclamation-triangle' => 'fas fa-exclamation-triangle',
                        'archive' => 'fas fa-archive',
                        'fingerprint' => 'fas fa-fingerprint',
                        default => 'fas fa-file-alt'
                    };
                @endphp
                <a href="{{ route('reports.index', ['type' => $key]) }}"
                   title="{{ $meta['description'] }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ $isActive ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-gray-50 text-gray-700 hover:bg-blue-50 hover:text-blue-600 border border-gray-200/80 hover:border-blue-200' }}">
                    <i class="{{ $iconName }} text-[11px] {{ $isActive ? 'text-blue-200' : 'text-gray-400' }}"></i>
                    <span>{{ $meta['title'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Mobile Dropdown Selector --}}
        <div class="sm:hidden">
            <label for="mobile-report-select" class="sr-only">Select Report</label>
            <select id="mobile-report-select" onchange="window.location.href=this.value" class="w-full text-xs py-2 px-3 border border-gray-300 rounded-lg bg-gray-50 font-medium text-gray-800 focus:ring-2 focus:ring-blue-500">
                @foreach($types as $key => $meta)
                    <option value="{{ route('reports.index', ['type' => $key]) }}" {{ $selectedType === $key ? 'selected' : '' }}>
                        {{ $meta['title'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Official Report Document Container (Primary Focus & Emphasis) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        {{-- Report Focus Header Banner --}}
        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50/40">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-13 h-13 p-3 rounded-xl bg-blue-600 text-white flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                        @php
                            $activeIcon = match($types[$selectedType]['icon'] ?? 'clipboard-list') {
                                'clipboard-list' => 'fas fa-clipboard-list',
                                'check-circle' => 'fas fa-check-circle',
                                'truck' => 'fas fa-truck',
                                'clock' => 'fas fa-clock',
                                'wrench' => 'fas fa-wrench',
                                'currency-dollar' => 'fas fa-peso-sign',
                                'shield-check' => 'fas fa-shield-alt',
                                'exclamation-triangle' => 'fas fa-exclamation-triangle',
                                'archive' => 'fas fa-archive',
                                'fingerprint' => 'fas fa-fingerprint',
                                default => 'fas fa-file-alt'
                            };
                        @endphp
                        <i class="{{ $activeIcon }}"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] uppercase tracking-wider font-bold text-blue-600">
                                Barangay Disaster Risk Reduction & Management Council (BDRRMC)
                            </span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active View
                            </span>
                        </div>
                        <h2 class="text-2xl font-extrabold text-gray-900 mt-1">
                            {{ $types[$selectedType]['title'] }}
                        </h2>
                        <p class="text-sm text-gray-600 mt-1 max-w-2xl">
                            {{ $types[$selectedType]['description'] }}
                        </p>
                    </div>
                </div>

                <div class="text-left md:text-right text-xs text-gray-500 space-y-1 bg-white/70 backdrop-blur-xs p-3 rounded-lg border border-gray-200/60 shadow-2xs">
                    <div><span class="font-semibold text-gray-700">Generated:</span> {{ now()->format('M d, Y h:i A') }}</div>
                    <div><span class="font-semibold text-gray-700">Official Report ID:</span> <span class="font-mono uppercase font-semibold text-gray-800">REP-{{ strtoupper(substr(md5($selectedType), 0, 6)) }}-{{ date('Ymd') }}</span></div>
                    <div><span class="font-semibold text-gray-700">Records In View:</span> <span class="font-bold text-blue-700">{{ is_countable($data) ? count($data) : ($data->total() ?? 0) }} matching</span></div>
                </div>
            </div>

            {{-- Summary KPI Metric Highlight Deck --}}
            @if(!empty($summary))
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mt-5 pt-5 border-t border-gray-200/80">
                    @foreach($summary as $metric)
                        @php
                            $colorClasses = match($metric['color'] ?? 'blue') {
                                'emerald' => ['bg' => 'bg-emerald-50/70', 'border' => 'border-emerald-200', 'iconBg' => 'bg-emerald-100 text-emerald-600'],
                                'amber' => ['bg' => 'bg-amber-50/70', 'border' => 'border-amber-200', 'iconBg' => 'bg-amber-100 text-amber-600'],
                                'rose', 'red' => ['bg' => 'bg-rose-50/70', 'border' => 'border-rose-200', 'iconBg' => 'bg-rose-100 text-rose-600'],
                                'purple' => ['bg' => 'bg-purple-50/70', 'border' => 'border-purple-200', 'iconBg' => 'bg-purple-100 text-purple-600'],
                                'indigo' => ['bg' => 'bg-indigo-50/70', 'border' => 'border-indigo-200', 'iconBg' => 'bg-indigo-100 text-indigo-600'],
                                default => ['bg' => 'bg-blue-50/70', 'border' => 'border-blue-200', 'iconBg' => 'bg-blue-100 text-blue-600'],
                            };
                        @endphp
                        <div class="p-3.5 rounded-xl border {{ $colorClasses['border'] }} {{ $colorClasses['bg'] }} flex items-center justify-between shadow-2xs">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wider text-gray-600">{{ $metric['label'] }}</div>
                                <div class="text-xl font-extrabold text-gray-900 mt-0.5 tracking-tight">{{ $metric['value'] }}</div>
                                @if(!empty($metric['subtext']))
                                    <div class="text-[11px] text-gray-500 mt-0.5">{{ $metric['subtext'] }}</div>
                                @endif
                            </div>
                            <div class="w-10 h-10 rounded-lg {{ $colorClasses['iconBg'] }} flex items-center justify-center text-sm shadow-xs flex-shrink-0">
                                <i class="{{ $metric['icon'] }}"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Optional Filter Bar (if inventory) --}}
            @if($selectedType === 'inventory')
                <div class="mt-6 pt-4 border-t border-gray-200/80 print:hidden">
                    <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <input type="hidden" name="type" value="inventory">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Asset Category</label>
                            <select name="category_id" class="w-full text-xs pl-3 pr-9 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Operational Status</label>
                            <select name="status" class="w-full text-xs pl-3 pr-9 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">All Statuses</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="deployed" {{ request('status') === 'deployed' ? 'selected' : '' }}>Deployed</option>
                                <option value="under_maintenance" {{ request('status') === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                <option value="out_of_service" {{ request('status') === 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                                <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                                <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Retired / Decommissioned</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors">
                                <i class="fas fa-filter mr-1"></i> Filter Records
                            </button>
                            <a href="{{ route('reports.index', ['type' => 'inventory']) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-xs font-medium hover:bg-gray-200 transition-colors">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        {{-- Preview Table Content Area --}}
        <div class="overflow-x-auto">
            @if(empty($data) || (is_countable($data) && count($data) === 0) || (method_exists($data, 'total') && $data->total() === 0))
                <div class="text-center py-16 px-4">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-2xl">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800">No Records Found</h3>
                    <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">There are currently no records matching this report criteria in the system database.</p>
                </div>
            @else
                <table class="w-full text-left text-xs text-gray-700">
                    <thead class="bg-gray-50 text-gray-600 uppercase font-semibold tracking-wider text-[11px] border-b border-gray-200">
                        @if($selectedType === 'inventory')
                            <tr>
                                <th class="px-4 py-3">Asset Code</th>
                                <th class="px-4 py-3">Asset Name</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Condition</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Acquisition Cost</th>
                                <th class="px-4 py-3">Serial #</th>
                                <th class="px-4 py-3">Purchase Date</th>
                            </tr>
                        @elseif($selectedType === 'readiness')
                            <tr>
                                <th class="px-4 py-3">Asset Code</th>
                                <th class="px-4 py-3">Asset Name</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Station / Storage</th>
                                <th class="px-4 py-3">Condition</th>
                                <th class="px-4 py-3">Readiness Status</th>
                                <th class="px-4 py-3">Last Verified</th>
                            </tr>
                        @elseif($selectedType === 'active_deployments' || $selectedType === 'deployment_history')
                            <tr>
                                <th class="px-4 py-3">Asset Code</th>
                                <th class="px-4 py-3">Asset Name</th>
                                <th class="px-4 py-3">Deployed By</th>
                                <th class="px-4 py-3">Borrower / Custodian</th>
                                <th class="px-4 py-3">Field Destination</th>
                                <th class="px-4 py-3">Purpose</th>
                                <th class="px-4 py-3">Deploy Date</th>
                                <th class="px-4 py-3">Expected Return</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Returned Date</th>
                                <th class="px-4 py-3">Return Condition</th>
                                <th class="px-4 py-3">Received By</th>
                            </tr>
                        @elseif($selectedType === 'maintenance_overdue')
                            <tr>
                                <th class="px-4 py-3">Work Order #</th>
                                <th class="px-4 py-3">Asset Code & Name</th>
                                <th class="px-4 py-3">Maintenance Type</th>
                                <th class="px-4 py-3">Priority</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Scheduled Date</th>
                                <th class="px-4 py-3">Assigned Staff</th>
                                <th class="px-4 py-3 text-right">Cost (PHP)</th>
                            </tr>
                        @elseif($selectedType === 'maintenance_expenses')
                            <tr>
                                <th class="px-4 py-3">Work Order #</th>
                                <th class="px-4 py-3">Asset Code & Name</th>
                                <th class="px-4 py-3">Maintenance Type</th>
                                <th class="px-4 py-3">Completed Date</th>
                                <th class="px-4 py-3 text-right">Repair Cost</th>
                                <th class="px-4 py-3">Parts Replaced</th>
                                <th class="px-4 py-3">Technician / Lead</th>
                            </tr>
                        @elseif($selectedType === 'inspections')
                            <tr>
                                <th class="px-4 py-3">Asset Code</th>
                                <th class="px-4 py-3">Asset Name</th>
                                <th class="px-4 py-3">Inspector</th>
                                <th class="px-4 py-3">Inspection Date</th>
                                <th class="px-4 py-3">Result</th>
                                <th class="px-4 py-3">Findings & Remarks</th>
                                <th class="px-4 py-3">Next Due Date</th>
                            </tr>
                        @elseif($selectedType === 'high_risk')
                            <tr>
                                <th class="px-4 py-3">Asset Code</th>
                                <th class="px-4 py-3">Asset Name</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Condition</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Acquisition Cost</th>
                                <th class="px-4 py-3">Operational Notes</th>
                            </tr>
                        @elseif($selectedType === 'decommissioned')
                            <tr>
                                <th class="px-4 py-3">Asset Code</th>
                                <th class="px-4 py-3">Asset Name</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Retirement Date</th>
                                <th class="px-4 py-3">Decommission Justification</th>
                                <th class="px-4 py-3">Authorized Official</th>
                                <th class="px-4 py-3 text-right">Original Cost</th>
                            </tr>
                        @elseif($selectedType === 'audit_trail')
                            <tr>
                                <th class="px-4 py-3">Log ID</th>
                                <th class="px-4 py-3">Timestamp</th>
                                <th class="px-4 py-3">Authorized User</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Target Entity</th>
                                <th class="px-4 py-3">Audit Details</th>
                                <th class="px-4 py-3">IP Address</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($data as $row)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                @if($selectedType === 'inventory')
                                    <td class="px-4 py-3 font-semibold font-mono text-blue-600">
                                        <a href="{{ route('assets.show', $row->id) }}" class="hover:underline">{{ $row->code }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->category?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->location?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $row->condition === 'excellent' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $row->condition === 'good' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $row->condition === 'fair' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $row->condition === 'poor' ? 'bg-rose-100 text-rose-800' : '' }}
                                        ">
                                            {{ ucfirst($row->condition ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $row->status === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $row->status === 'deployed' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $row->status === 'under_maintenance' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $row->status === 'out_of_service' ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ $row->status === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $row->status === 'retired' ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-medium text-gray-900">
                                        ₱{{ number_format($row->acquisition_cost ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-500">{{ $row->serial_number ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $row->purchase_date ? $row->purchase_date->format('M d, Y') : 'N/A' }}</td>

                                @elseif($selectedType === 'readiness')
                                    <td class="px-4 py-3 font-semibold font-mono text-blue-600">
                                        <a href="{{ route('assets.show', $row->id) }}" class="hover:underline">{{ $row->code }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->category?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->location?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                            {{ ucfirst($row->condition) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle text-[10px]"></i> Operational & Ready
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $row->updated_at->format('M d, Y') }}</td>

                                @elseif($selectedType === 'active_deployments' || $selectedType === 'deployment_history')
                                    <td class="px-4 py-3 font-semibold font-mono text-blue-600">
                                        <a href="{{ route('assets.show', $row->asset_id) }}" class="hover:underline">{{ $row->asset?->code ?? 'N/A' }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->asset?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->user?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row->responsible_person ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row->location }}</td>
                                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $row->purpose ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->deployment_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->expected_return_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold {{ $row->status === 'returned' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $row->status === 'deployed' ? 'Active in Field' : ucfirst($row->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->returnRecord?->return_date?->format('M d, Y') ?? ($row->status === 'returned' ? 'Returned' : 'In Field') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ ucfirst($row->returnRecord?->condition ?? 'N/A') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->returnRecord?->receivedByUser?->name ?? 'N/A' }}</td>

                                @elseif($selectedType === 'maintenance_overdue')
                                    <td class="px-4 py-3 font-mono font-semibold text-blue-600">{{ $row->work_order_number ?? ('WO-' . $row->id) }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        <a href="{{ route('assets.show', $row->asset_id) }}" class="hover:underline text-blue-600">{{ $row->asset?->code }}</a> - {{ $row->asset?->name }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ ucfirst(str_replace('_', ' ', $row->type)) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $row->priority === 'urgent' ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ $row->priority === 'high' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $row->priority === 'medium' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $row->priority === 'low' ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ ucfirst($row->priority ?? 'medium') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">
                                            {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->start_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->assignedUser?->name ?? 'Unassigned' }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-medium text-gray-900">₱{{ number_format($row->cost ?? 0, 2) }}</td>

                                @elseif($selectedType === 'maintenance_expenses')
                                    <td class="px-4 py-3 font-mono font-semibold text-blue-600">{{ $row->work_order_number ?? ('WO-' . $row->id) }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        <a href="{{ route('assets.show', $row->asset_id) }}" class="hover:underline text-blue-600">{{ $row->asset?->code }}</a> - {{ $row->asset?->name }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ ucfirst(str_replace('_', ' ', $row->type)) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->completed_at ? $row->completed_at->format('M d, Y') : ($row->end_date ? $row->end_date->format('M d, Y') : 'N/A') }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">₱{{ number_format($row->cost ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $row->parts_used ?? 'None' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->assignedUser?->name ?? 'N/A' }}</td>

                                @elseif($selectedType === 'inspections')
                                    <td class="px-4 py-3 font-semibold font-mono text-blue-600">
                                        <a href="{{ route('assets.show', $row->asset_id) }}" class="hover:underline">{{ $row->asset?->code ?? 'N/A' }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->asset?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-700">{{ $row->inspector }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->inspection_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold {{ in_array($row->result, ['passed', 'good', 'excellent']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($row->result) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $row->findings ?? 'No findings noted' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->next_due_date?->format('M d, Y') ?? 'N/A' }}</td>

                                @elseif($selectedType === 'high_risk')
                                    <td class="px-4 py-3 font-semibold font-mono text-rose-600">
                                        <a href="{{ route('assets.show', $row->id) }}" class="hover:underline">{{ $row->code }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->category?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->location?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-100 text-rose-800">
                                            {{ ucfirst($row->condition) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-red-100 text-red-800">
                                            {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-medium text-gray-900">₱{{ number_format($row->acquisition_cost ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-rose-700 max-w-xs truncate">{{ $row->notes ?? 'Requires urgent attention' }}</td>

                                @elseif($selectedType === 'decommissioned')
                                    <td class="px-4 py-3 font-semibold font-mono text-gray-700">
                                        <a href="{{ route('assets.show', $row->id) }}" class="hover:underline">{{ $row->code }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->category?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->retirement_date ? $row->retirement_date->format('M d, Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ $row->retirement_reason ?? 'Decommissioned by council resolution' }}</td>
                                    <td class="px-4 py-3 text-gray-600 font-medium">{{ $row->retiredBy?->name ?? 'Authorized Official' }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-600">₱{{ number_format($row->acquisition_cost ?? 0, 2) }}</td>

                                @elseif($selectedType === 'audit_trail')
                                    <td class="px-4 py-3 font-mono text-gray-500">#{{ $row->id }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->user?->name ?? 'System' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            {{ in_array($row->event, ['created', 'approved', 'returned']) ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ in_array($row->event, ['updated', 'maintenance_completed']) ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ in_array($row->event, ['deleted', 'retired']) ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ !in_array($row->event, ['created', 'approved', 'returned', 'updated', 'maintenance_completed', 'deleted', 'retired']) ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ strtoupper($row->event) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-blue-600">
                                        {{ class_basename($row->auditable_type) }} #{{ $row->auditable?->code ?? $row->auditable_id }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 max-w-sm truncate">{{ $row->description }}</td>
                                    <td class="px-4 py-3 font-mono text-gray-500 text-[11px]">{{ $row->ip_address ?? 'N/A' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Pagination & Summary Footer --}}
        @if(method_exists($data, 'hasPages') && $data->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between print:hidden">
                <div class="text-xs text-gray-600">
                    Showing entries {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() ?? 0 }}
                </div>
                <div>
                    {{ $data->links() }}
                </div>
            </div>
        @endif
    </div>

@endsection
