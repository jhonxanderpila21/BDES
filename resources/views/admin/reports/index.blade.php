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
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors font-medium text-sm shadow-sm print:hidden">
                <i class="fas fa-print"></i>
                Print Report
            </button>
        </div>
    </div>

    {{-- Report Directory (10 Standard Reports Grid) --}}
    <div class="mb-8 print:hidden">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Standard Disaster Equipment Audit Reports</h2>
                <p class="text-sm text-gray-500">Select any official LGU compliance or operational report to preview data and export registers.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full">
                10 Available Reports
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
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
                   class="group block p-4 rounded-xl border transition-all duration-200 {{ $isActive ? 'bg-blue-50/80 border-blue-500 shadow-md ring-2 ring-blue-500/20' : 'bg-white border-gray-200 hover:border-blue-300 hover:bg-gray-50/60 shadow-sm' }}">
                    <div class="flex items-start justify-between mb-2">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm {{ $isActive ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-600 group-hover:bg-blue-100 group-hover:text-blue-600' }} transition-colors">
                            <i class="{{ $iconName }}"></i>
                        </div>
                        @if($isActive)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Active
                            </span>
                        @endif
                    </div>
                    <h3 class="font-semibold text-xs leading-snug line-clamp-2 {{ $isActive ? 'text-blue-900' : 'text-gray-800 group-hover:text-blue-600' }}">
                        {{ $meta['title'] }}
                    </h3>
                    <p class="text-[11px] text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                        {{ $meta['description'] }}
                    </p>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Official Report Document Container --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        {{-- LGU Government Official Header Banner (Prints nicely) --}}
        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50/30">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl shadow-md flex-shrink-0">
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
                        <div class="text-[11px] uppercase tracking-wider font-semibold text-blue-600">
                            Barangay Disaster Risk Reduction & Management Council (BDRRMC)
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mt-0.5">
                            {{ $types[$selectedType]['title'] }}
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $types[$selectedType]['description'] }}
                        </p>
                    </div>
                </div>

                <div class="text-left md:text-right text-xs text-gray-500 space-y-1">
                    <div><span class="font-semibold text-gray-700">Generated:</span> {{ now()->format('M d, Y h:i A') }}</div>
                    <div><span class="font-semibold text-gray-700">Official Report ID:</span> <span class="font-mono uppercase">REP-{{ strtoupper(substr(md5($selectedType), 0, 6)) }}-{{ date('Ymd') }}</span></div>
                    <div><span class="font-semibold text-gray-700">Records Shown:</span> {{ is_countable($data) ? count($data) : ($data->total() ?? 0) }} matching entries</div>
                </div>
            </div>

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
                        @elseif($selectedType === 'active_deployments')
                            <tr>
                                <th class="px-4 py-3">Asset Code</th>
                                <th class="px-4 py-3">Asset Name</th>
                                <th class="px-4 py-3">Deployed By</th>
                                <th class="px-4 py-3">Custodian / Responsible</th>
                                <th class="px-4 py-3">Field Destination</th>
                                <th class="px-4 py-3">Mission Purpose</th>
                                <th class="px-4 py-3">Deploy Date</th>
                                <th class="px-4 py-3">Expected Return</th>
                            </tr>
                        @elseif($selectedType === 'deployment_history')
                            <tr>
                                <th class="px-4 py-3">Asset Code</th>
                                <th class="px-4 py-3">Asset Name</th>
                                <th class="px-4 py-3">Destination</th>
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

                                @elseif($selectedType === 'active_deployments')
                                    <td class="px-4 py-3 font-semibold font-mono text-blue-600">
                                        <a href="{{ route('assets.show', $row->asset_id) }}" class="hover:underline">{{ $row->asset?->code ?? 'N/A' }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->asset?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->user?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row->responsible_person ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row->location }}</td>
                                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $row->purpose }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->deployment_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->expected_return_date?->format('M d, Y') ?? 'N/A' }}</td>

                                @elseif($selectedType === 'deployment_history')
                                    <td class="px-4 py-3 font-semibold font-mono text-blue-600">
                                        <a href="{{ route('assets.show', $row->asset_id) }}" class="hover:underline">{{ $row->asset?->code ?? 'N/A' }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->asset?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row->location }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->deployment_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->expected_return_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold {{ $row->status === 'returned' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($row->status) }}
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

    {{-- Official Sign-Off Section for Physical Printouts --}}
    <div class="hidden print:block mt-12 pt-8 border-t-2 border-gray-300">
        <div class="grid grid-cols-3 gap-8 text-center text-xs">
            <div>
                <div class="border-b border-gray-400 pb-1 mb-1 font-semibold text-gray-900">
                    {{ auth()->user()->name ?? 'System Officer' }}
                </div>
                <div class="text-gray-600">Prepared By (Custodian / Officer)</div>
            </div>
            <div>
                <div class="border-b border-gray-400 pb-1 mb-1 font-semibold text-gray-900">
                    ____________________________________
                </div>
                <div class="text-gray-600">Verified By (Barangay Secretary)</div>
            </div>
            <div>
                <div class="border-b border-gray-400 pb-1 mb-1 font-semibold text-gray-900">
                    ____________________________________
                </div>
                <div class="text-gray-600">Approved By (Punong Barangay / Chairman)</div>
            </div>
        </div>
    </div>
@endsection
