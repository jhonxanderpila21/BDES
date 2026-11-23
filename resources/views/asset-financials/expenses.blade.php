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
            subtitle="Consumables, components & parts"
            :href="route('financials.expenses', ['category' => 'supplies_and_parts'])"
            :active="$currCat === 'supplies_and_parts'"
            tooltip="Filter by Supplies & Parts"
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
                        <option value="asset" {{ request('category') === 'asset' ? 'selected' : '' }}>Asset Purchases</option>
                        <option value="component_part" {{ request('category') === 'component_part' ? 'selected' : '' }}>Components & Parts</option>
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
                    @if(request()->anyFilled(['search', 'category', 'date_from', 'date_to', 'min_amount', 'max_amount', 'has_receipts']))
                        <a href="{{ route('financials.expenses') }}" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-sm font-semibold transition-colors shadow-xs" title="Reset Filters">
                            <i class="fas fa-rotate-left mr-1"></i> Reset
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
                        @php
                            $expenseDetail = [
                                'category_label' => $exp->category_label,
                                'item_name'      => $exp->item_name,
                                'reference'      => $exp->reference,
                                'formatted_date' => $exp->formatted_date,
                                'quantity'       => number_format($exp->quantity),
                                'unit_cost'      => '₱' . number_format($exp->unit_cost, 2),
                                'total_cost'     => '₱' . number_format($exp->total_cost, 2),
                                'recorded_by'    => $exp->recorded_by ?: 'N/A',
                                'notes'          => $exp->notes ?: 'No additional notes recorded.',
                                'receipts'       => ($exp->receipts ?? collect())->map(fn ($r) => [
                                    'label'     => $r->reference_number ?: $r->file_name,
                                    'file_name' => $r->file_name,
                                    'url'       => route('receipts.show', $r->id),
                                ])->values(),
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/40 cursor-pointer transition-colors"
                            onclick="if(!event.target.closest('button, a, form, input, select')) openExpenseDetailsModal(@json($expenseDetail));"
                            title="Click to view expense details">
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
                                        'component_part' => 'info',
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
                                <button
                                    type="button"
                                    onclick='openExpenseDetailsModal(@json($expenseDetail))'
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 text-xs font-medium transition-colors"
                                    title="View Expense Details"
                                >
                                    <i class="fas fa-eye"></i>
                                    <span>Details</span>
                                </button>
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

    {{-- Expense Details Modal Card --}}
    <x-modal id="expense-details-modal" title="Expense Details">
        <div class="space-y-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex items-start justify-between gap-4">
                <div>
                    <span id="exp_detail_category" class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-1.5"></span>
                    <h4 id="exp_detail_item_name" class="text-base font-bold text-gray-900"></h4>
                    <p id="exp_detail_reference" class="text-xs font-mono text-gray-600 mt-0.5"></p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Cost</p>
                    <p id="exp_detail_total_cost" class="text-xl font-bold text-emerald-700 font-mono"></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Transaction Date</p>
                    <p id="exp_detail_date" class="font-medium text-gray-900 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Recorded By</p>
                    <p id="exp_detail_recorded_by" class="font-medium text-gray-900 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Quantity</p>
                    <p id="exp_detail_quantity" class="font-medium text-gray-900 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Cost per Unit</p>
                    <p id="exp_detail_unit_cost" class="font-medium text-gray-900 font-mono mt-0.5"></p>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg border border-gray-200 text-sm">
                <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Notes</p>
                <p id="exp_detail_notes" class="text-gray-700 whitespace-pre-line"></p>
            </div>

            <div class="p-3 bg-white rounded-lg border border-gray-200 text-sm">
                <p class="text-xs font-semibold uppercase text-gray-500 mb-1.5">Attached Receipts</p>
                <div id="exp_detail_receipts" class="flex flex-wrap gap-2"></div>
            </div>

            <div class="flex justify-end pt-2 border-t border-gray-200">
                <button type="button" onclick="closeModal('expense-details-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    <script>
        function openExpenseDetailsModal(exp) {
            document.getElementById('exp_detail_category').textContent = exp.category_label || 'Expense';
            document.getElementById('exp_detail_item_name').textContent = exp.item_name || 'N/A';
            document.getElementById('exp_detail_reference').textContent = exp.reference || '';
            document.getElementById('exp_detail_total_cost').textContent = exp.total_cost || '₱0.00';
            document.getElementById('exp_detail_date').textContent = exp.formatted_date || 'N/A';
            document.getElementById('exp_detail_recorded_by').textContent = exp.recorded_by || 'N/A';
            document.getElementById('exp_detail_quantity').textContent = exp.quantity || '1';
            document.getElementById('exp_detail_unit_cost').textContent = exp.unit_cost || '₱0.00';
            document.getElementById('exp_detail_notes').textContent = exp.notes || 'No additional notes recorded.';

            const receiptsContainer = document.getElementById('exp_detail_receipts');
            receiptsContainer.innerHTML = '';
            if (Array.isArray(exp.receipts) && exp.receipts.length > 0) {
                exp.receipts.forEach(function (r) {
                    const link = document.createElement('a');
                    link.href = r.url;
                    link.target = '_blank';
                    link.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-medium transition-colors';
                    link.title = r.file_name || r.label;
                    link.innerHTML = '<i class="fas fa-file-invoice text-[10px]"></i><span></span>';
                    link.querySelector('span').textContent = r.label || r.file_name || 'Receipt';
                    receiptsContainer.appendChild(link);
                });
            } else {
                const empty = document.createElement('span');
                empty.className = 'text-xs text-gray-400 italic';
                empty.textContent = 'No receipt attached';
                receiptsContainer.appendChild(empty);
            }

            openModal('expense-details-modal');
        }
    </script>
@endsection

