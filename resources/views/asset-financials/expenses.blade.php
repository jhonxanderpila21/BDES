@extends('layouts.admin')

@section('title', 'Asset Expenses - BDES')

@section('content')
    {{-- Header --}}
    <x-page-header
        title="Asset Expenses"
        subtitle="Unified procurement, maintenance servicing, and parts Expenses Ledger."
        icon="fas fa-file-invoice-dollar"
        :read-only-badge="false"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asset Financials'],
            ['label' => 'Asset Expenses']
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('financials.expenses.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium shadow-sm">
                <i class="fas fa-file-csv text-emerald-600"></i>
                Export to CSV
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Financial KPI Metric Cards --}}
    @php
        $currCat = request('category', 'all');
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-kpi-card
            title="Total Expenses"
            :value="'₱' . number_format($kpis['total_expenses'], 2)"
            icon="fas fa-wallet"
            color="emerald"
            :subtitle="$kpis['total_count'] . ' recorded expenditures'"
            :href="route('financials.expenses')"
            :active="$currCat === 'all' || empty(request('category'))"
        />

        <x-kpi-card
            title="Asset Purchases"
            :value="'₱' . number_format($kpis['assets_total'], 2)"
            icon="fas fa-boxes-stacked"
            color="blue"
            subtitle="Equipment and vehicles"
            :href="route('financials.expenses', ['category' => 'asset'])"
            :active="$currCat === 'asset'"
            tooltip="Filter by Asset Purchases"
        />

        <x-kpi-card
            title="Maintenance & Repairs"
            :value="'₱' . number_format($kpis['maintenance_total'], 2)"
            icon="fas fa-wrench"
            color="amber"
            subtitle="Servicing, repairs & parts upkeep"
            :href="route('financials.expenses', ['category' => 'maintenance'])"
            :active="$currCat === 'maintenance'"
            tooltip="Filter by Maintenance & Repairs"
        />

        <x-kpi-card
            title="Supplies & Parts"
            :value="'₱' . number_format($kpis['supplies_total'] + $kpis['accessories_total'], 2)"
            icon="fas fa-gas-pump"
            color="purple"
            subtitle="Consumables and attachments"
            :href="route('financials.expenses', ['category' => 'operational_requirement'])"
            :active="$currCat === 'operational_requirement'"
            tooltip="Filter by Supplies & Consumables"
        />
    </div>

    {{-- Filters Card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <form action="{{ route('financials.expenses') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Search</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by asset name, code, reference, notes..."
                            class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                    </div>
                </div>

                {{-- Category Filter --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Expense Type</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                        <option value="all" {{ request('category') === 'all' || !request('category') ? 'selected' : '' }}>All Types</option>
                        <option value="accessory" {{ request('category') === 'accessory' ? 'selected' : '' }}>Accessories</option>
                        <option value="asset" {{ request('category') === 'asset' ? 'selected' : '' }}>Asset Purchases</option>
                        <option value="attachment" {{ request('category') === 'attachment' ? 'selected' : '' }}>Attachments</option>
                        <option value="maintenance" {{ request('category') === 'maintenance' ? 'selected' : '' }}>Maintenance & Repairs</option>
                        <option value="operational_requirement" {{ request('category') === 'operational_requirement' ? 'selected' : '' }}>Supplies & Restocks</option>
                    </select>
                </div>

                {{-- Sort Order --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sort By</label>
                    <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                        <option value="date" {{ request('sort_by') === 'date' || !request('sort_by') ? 'selected' : '' }}>Date (Ascending)</option>
                        <option value="item_name" {{ request('sort_by') === 'item_name' ? 'selected' : '' }}>Item Name (A to Z)</option>
                        <option value="total_cost" {{ request('sort_by') === 'total_cost' ? 'selected' : '' }}>Total Cost (Ascending)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2 border-t border-gray-100">
                {{-- Date From --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                </div>

                {{-- Date To --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                </div>

                {{-- Min Amount --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Min Amount (₱)</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="min_amount"
                        value="{{ request('min_amount') }}"
                        placeholder="0.00"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                </div>

                {{-- Actions --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
                        <i class="fas fa-filter mr-1"></i> Apply Filters
                    </button>
                    @if(request()->anyFilled(['search', 'category', 'date_from', 'date_to', 'min_amount', 'max_amount', 'has_receipts']))
                        <a href="{{ route('financials.expenses') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors" title="Reset Filters">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Expenses Ledger Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Item Name & Reference</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Cost per Unit</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Total Cost</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Receipts</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            {{-- Date --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium align-middle">
                                {{ $exp->formatted_date }}
                            </td>

                            {{-- Category Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center align-middle">
                                @php
                                    $badgeVariant = match($exp->type) {
                                        'asset' => 'primary',
                                        'maintenance' => 'warning',
                                        'operational_requirement' => 'success',
                                        'accessory' => 'info',
                                        'attachment' => 'secondary',
                                        default => 'light',
                                    };
                                @endphp
                                <x-badge variant="{{ $badgeVariant }}" size="sm">
                                    {{ $exp->category_label }}
                                </x-badge>
                            </td>

                            {{-- Item & Reference --}}
                            <td class="px-6 py-4 text-sm align-middle">
                                <div class="font-bold text-gray-900">{{ $exp->item_name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="inline-block px-1.5 py-0.5 text-[11px] font-mono font-semibold rounded bg-gray-100 text-gray-800 border border-gray-200">
                                        {{ $exp->reference }}
                                    </span>
                                    @if($exp->recorded_by)
                                        <span class="text-xs text-gray-500">By: {{ $exp->recorded_by }}</span>
                                    @endif
                                </div>
                                @if($exp->notes)
                                    <p class="text-xs text-gray-400 mt-1 truncate max-w-xs" title="{{ $exp->notes }}">
                                        {{ $exp->notes }}
                                    </p>
                                @endif
                            </td>

                            {{-- Qty --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-700 align-middle">
                                {{ number_format($exp->quantity) }}
                            </td>

                            {{-- Unit Cost --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600 font-mono align-middle">
                                ₱{{ number_format($exp->unit_cost, 2) }}
                            </td>

                            {{-- Total Cost --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-emerald-700 font-mono align-middle">
                                ₱{{ number_format($exp->total_cost, 2) }}
                            </td>

                            {{-- Receipts --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center align-middle">
                                @if($exp->receipts_count > 0)
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        @foreach($exp->receipts as $r)
                                            <a 
                                                href="{{ route('receipts.show', $r->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-medium transition-colors"
                                                title="{{ $r->file_name }}"
                                            >
                                                <i class="fas fa-file-invoice text-[10px]"></i>
                                                <span>{{ Str::limit($r->reference_number ?: $r->file_name, 12) }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">No receipt</span>
                                @endif
                            </td>

                            {{-- Action --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm align-middle">
                                @if(!empty($exp->route))
                                    <a href="{{ $exp->route }}" class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors inline-flex items-center justify-center" title="View Source Record">
                                        <i class="fas fa-arrow-up-right-from-square"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <x-empty-state 
                                    icon="fas fa-receipt" 
                                    title="No expense records found" 
                                    description="No transactions match the selected filter criteria."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $expenses->links() }}
        </div>
    </x-card>
@endsection

