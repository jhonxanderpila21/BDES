@extends('layouts.admin')

@section('title', 'Depreciation Value - BDES')

@section('content')
<div class="space-y-6" x-data="{ editModal: false, selectedAsset: {} }">
    {{-- Header --}}
    <x-page-header
        title="Asset Depreciation Value"
        subtitle="Asset Depreciation Schedule & straight-line book value tracking."
        icon="fas fa-arrow-trend-down"
        :read-only-badge="false"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asset Financials'],
            ['label' => 'Depreciation Value']
        ]"
    >
        <x-slot:actions>
            <a
                href="{{ route('financials.depreciation.export') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition"
            >
                <i class="fas fa-file-csv text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Financial KPI Cards --}}
    @php
        $activeView = request('view', 'purchase_cost');
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            title="Total Purchase Cost"
            :value="'₱' . number_format($totalAcquisitionCost, 2)"
            icon="fas fa-receipt"
            color="blue"
            subtitle="Sum of Purchase Cost column"
            :href="route('financials.depreciation', ['view' => 'purchase_cost'])"
            :active="$activeView === 'purchase_cost'"
            tooltip="Highlight Purchase Cost column"
        />

        <x-kpi-card
            title="Total Depreciation"
            :value="'₱' . number_format($totalAccumulatedDepreciation, 2)"
            icon="fas fa-arrow-trend-down"
            color="rose"
            subtitle="Sum of Total Depreciated column"
            :href="route('financials.depreciation', ['view' => 'total_depreciation'])"
            :active="$activeView === 'total_depreciation'"
            tooltip="Highlight Total Depreciated column"
        />

        <x-kpi-card
            title="Current Value"
            :value="'₱' . number_format($totalCurrentBookValue, 2)"
            icon="fas fa-vault"
            color="emerald"
            subtitle="Sum of Current Value column"
            :href="route('financials.depreciation', ['view' => 'current_value'])"
            :active="$activeView === 'current_value'"
            tooltip="Highlight Current Value column"
        />

        <x-kpi-card
            title="Yearly Depreciation"
            :value="'₱' . number_format($totalAnnualDepreciation, 2)"
            icon="fas fa-calendar-days"
            color="amber"
            subtitle="Sum of Yearly Dep. column"
            :href="route('financials.depreciation', ['view' => 'yearly_depreciation'])"
            :active="$activeView === 'yearly_depreciation'"
            tooltip="Highlight Yearly Dep. column"
        />
    </div>

    {{-- Filter Bar --}}
    <x-card>
        <form action="{{ route('financials.depreciation') }}" method="GET" class="p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by asset code, name..."
                    class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div>
                <select name="category_id" class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Statuses</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="deployed" {{ request('status') == 'deployed' ? 'selected' : '' }}>Deployed</option>
                    <option value="under_maintenance" {{ request('status') == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                    <option value="out_of_service" {{ request('status') == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                    <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>Retired</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'category_id', 'status']))
                    <a href="{{ route('financials.depreciation') }}" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-sm font-semibold transition shadow-xs">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    {{-- Depreciation Schedule Table --}}
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-700 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-left w-[26%]">Asset Name & Code</th>
                        <th class="px-4 py-3.5 text-center w-[15%] {{ $activeView === 'purchase_cost' ? 'bg-blue-50/80 text-blue-800' : '' }}">Purchase Cost</th>
                        <th class="px-4 py-3.5 text-center w-[13%]">Lifespan / Age</th>
                        <th class="px-4 py-3.5 text-center w-[14%] {{ $activeView === 'yearly_depreciation' ? 'bg-amber-50/80 text-amber-800' : '' }}">Yearly Dep.</th>
                        <th class="px-4 py-3.5 text-center w-[14%] {{ $activeView === 'total_depreciation' ? 'bg-rose-50/80 text-rose-800' : '' }}">Total Depreciated</th>
                        <th class="px-4 py-3.5 text-center w-[12%] {{ $activeView === 'current_value' ? 'bg-emerald-50/80 text-emerald-800' : '' }}">Current Value</th>
                        <th class="px-4 py-3.5 text-right w-[6%]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($assets as $asset)
                        @php
                            $depDetail = [
                                'name'                     => $asset->name,
                                'code'                     => $asset->code,
                                'category'                 => $asset->category?->name ?? 'Uncategorized',
                                'purchase_date'            => $asset->purchase_date?->format('M d, Y') ?? 'No date',
                                'acquisition_cost'         => '₱' . number_format($asset->acquisition_cost, 2),
                                'useful_life'              => $asset->effective_useful_life . ' yrs (Age: ' . $asset->age_in_years . ' yrs)',
                                'annual_depreciation'      => '₱' . number_format($asset->annual_depreciation, 2),
                                'monthly_depreciation'     => '₱' . number_format($asset->monthly_depreciation, 2) . '/mo',
                                'accumulated_depreciation' => '₱' . number_format($asset->accumulated_depreciation, 2),
                                'depreciation_pct'         => $asset->depreciation_percentage . '% depreciated',
                                'current_book_value'       => '₱' . number_format($asset->current_book_value, 2),
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/40 cursor-pointer transition"
                            onclick='if(!event.target.closest("button, a, form, input, select")) openDepreciationDetailsModal(@json($depDetail));'
                            title="Click to view depreciation details">
                            <td class="px-4 py-3.5 align-middle text-left">
                                <div class="font-semibold text-gray-900">{{ $asset->name }}</div>
                                <div class="text-xs font-mono font-bold text-blue-700 mt-0.5">{{ $asset->code }}</div>
                            </td>
                            <td class="px-4 py-3.5 align-middle text-center font-medium text-gray-900 whitespace-nowrap {{ $activeView === 'purchase_cost' ? 'bg-blue-50/30 font-bold' : '' }}">
                                ₱{{ number_format($asset->acquisition_cost, 2) }}
                            </td>
                            <td class="px-4 py-3.5 align-middle text-center font-medium text-gray-700 whitespace-nowrap">
                                {{ $asset->effective_useful_life }} yrs
                            </td>
                            <td class="px-4 py-3.5 align-middle text-center font-medium text-gray-700 whitespace-nowrap {{ $activeView === 'yearly_depreciation' ? 'bg-amber-50/30 font-bold' : '' }}">
                                ₱{{ number_format($asset->annual_depreciation, 2) }}
                            </td>
                            <td class="px-4 py-3.5 align-middle text-center text-rose-600 font-semibold whitespace-nowrap {{ $activeView === 'total_depreciation' ? 'bg-rose-50/30 font-bold' : '' }}">
                                ₱{{ number_format($asset->accumulated_depreciation, 2) }}
                            </td>
                            <td class="px-4 py-3.5 align-middle text-center whitespace-nowrap {{ $activeView === 'current_value' ? 'bg-emerald-50/30' : '' }}">
                                <div class="font-bold text-emerald-700">₱{{ number_format($asset->current_book_value, 2) }}</div>
                            </td>
                            <td class="px-4 py-3.5 align-middle text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    @click="selectedAsset = {
                                        id: {{ $asset->id }},
                                        name: '{{ addslashes($asset->name) }}',
                                        code: '{{ $asset->code }}',
                                        useful_life_years: {{ $asset->effective_useful_life }}
                                    }; editModal = true;"
                                    class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600 hover:text-amber-800 transition-colors"
                                    title="Edit Lifespan"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <i class="fas fa-coins text-3xl text-gray-300 mb-2"></i>
                                <p class="font-medium">No depreciable assets found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $assets->links() }}
            </div>
        @endif
    </x-card>

    {{-- Edit Parameters Modal --}}
    <div
        x-show="editModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4"
        x-transition
    >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="editModal = false">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h3 class="text-base font-bold text-gray-900">
                    Edit Depreciation Lifespan
                </h3>
                <button @click="editModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'/financials/depreciation/' + selectedAsset.id" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Asset</label>
                    <p class="text-sm font-bold text-gray-900" x-text="selectedAsset.code + ' - ' + selectedAsset.name"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Estimated Lifespan (Years) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="useful_life_years"
                        x-model="selectedAsset.useful_life_years"
                        min="1"
                        max="50"
                        required
                        class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <p class="text-[11px] text-gray-500 mt-1">Typical lifespan for barangay equipment is 5 to 10 years</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200">
                    <button
                        type="button"
                        @click="editModal = false"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium shadow-sm transition"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Depreciation Details Modal --}}
    <x-modal id="depreciation-details-modal" title="Depreciation Details">
        <div class="space-y-4 text-sm">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex items-start justify-between gap-4">
                <div>
                    <h4 id="dep_detail_name" class="text-base font-bold text-gray-900"></h4>
                    <p id="dep_detail_meta" class="text-xs font-mono text-gray-600 mt-0.5"></p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs font-semibold uppercase text-gray-500">Current Value</p>
                    <p id="dep_detail_book_value" class="text-xl font-bold text-emerald-700 font-mono"></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Purchase Cost</p>
                    <p id="dep_detail_cost" class="font-bold text-gray-900 font-mono mt-0.5"></p>
                    <p id="dep_detail_purchase_date" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Useful Life / Age</p>
                    <p id="dep_detail_life" class="font-medium text-gray-900 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Yearly Depreciation</p>
                    <p id="dep_detail_annual" class="font-bold text-gray-900 font-mono mt-0.5"></p>
                    <p id="dep_detail_monthly" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Total Depreciated</p>
                    <p id="dep_detail_accum" class="font-bold text-rose-600 font-mono mt-0.5"></p>
                    <p id="dep_detail_pct" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-gray-200">
                <button type="button" onclick="closeModal('depreciation-details-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Close</button>
            </div>
        </div>
    </x-modal>

    <script>
        function openDepreciationDetailsModal(d) {
            document.getElementById('dep_detail_name').textContent = d.name || '';
            document.getElementById('dep_detail_meta').textContent = d.code || '';
            document.getElementById('dep_detail_book_value').textContent = d.current_book_value || '₱0.00';
            document.getElementById('dep_detail_cost').textContent = d.acquisition_cost || '₱0.00';
            document.getElementById('dep_detail_purchase_date').textContent = 'Purchased: ' + (d.purchase_date || 'N/A');
            document.getElementById('dep_detail_life').textContent = d.useful_life || 'N/A';
            document.getElementById('dep_detail_annual').textContent = d.annual_depreciation || '₱0.00';
            document.getElementById('dep_detail_monthly').textContent = d.monthly_depreciation || '';
            document.getElementById('dep_detail_accum').textContent = d.accumulated_depreciation || '₱0.00';
            document.getElementById('dep_detail_pct').textContent = d.depreciation_pct || '';
            openModal('depreciation-details-modal');
        }
    </script>
</div>
@endsection

