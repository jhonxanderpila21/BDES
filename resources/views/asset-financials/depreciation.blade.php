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

    {{-- Flash Notifications --}}

    {{-- Accounting Policy Standard Notice --}}
    <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-xl flex items-start gap-3.5">
        <div class="p-2 bg-blue-600 text-white rounded-lg mt-0.5 shadow-sm">
            <i class="fas fa-scale-balanced text-sm"></i>
        </div>
        <div class="space-y-1">
            <h4 class="text-xs font-bold text-blue-950 uppercase tracking-wider">Accounting Standard & Capitalization Threshold</h4>
            <p class="text-xs text-blue-900 leading-relaxed">
                Equipment with purchase costs <span class="font-bold underline">₱20,000.00 and above</span> is capitalized as <strong>Property, Plant, and Equipment (PPE)</strong> and depreciates yearly using the straight-line method with a standard <strong>10% salvage value</strong>. Items below ₱20,000.00 are classified as semi-expendable and do not depreciate.
            </p>
        </div>
    </div>

    {{-- Financial KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            title="Total Purchase Cost"
            :value="'₱' . number_format($totalAcquisitionCost, 2)"
            icon="fas fa-receipt"
            color="blue"
            subtitle="Original purchase cost of recorded assets"
            :href="route('financials.depreciation')"
            :active="!request()->anyFilled(['search', 'category_id', 'status'])"
            tooltip="Show all recorded assets"
        />

        <x-kpi-card
            title="Total Depreciation"
            :value="'₱' . number_format($totalAccumulatedDepreciation, 2)"
            icon="fas fa-chart-line-down"
            color="rose"
            subtitle="Total value lost over time"
            :href="route('financials.depreciation')"
            tooltip="View depreciation schedule"
        />

        <x-kpi-card
            title="Current Value"
            :value="'₱' . number_format($totalCurrentBookValue, 2)"
            icon="fas fa-vault"
            color="emerald"
            subtitle="Current estimated net asset worth"
            :href="route('financials.depreciation', ['status' => 'available'])"
            :active="request('status') === 'available'"
            tooltip="Filter by available assets"
        />

        <x-kpi-card
            title="Yearly Depreciation"
            :value="'₱' . number_format($totalAnnualDepreciation, 2)"
            icon="fas fa-calendar-days"
            color="amber"
            subtitle="Value lost per year"
            :href="route('financials.salvage')"
            tooltip="View salvage value records"
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
    <x-card>
        <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-table-list text-blue-600"></i>
                Asset Depreciation List
            </h2>
            <span class="text-xs text-gray-500">Straight-Line Method (10% Residual)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Asset Name & Code</th>
                        <th class="px-4 py-3 text-right">Purchase Cost</th>
                        <th class="px-4 py-3 text-center">Lifespan / Age</th>
                        <th class="px-4 py-3 text-right">Salvage Value</th>
                        <th class="px-4 py-3 text-right">Yearly Dep.</th>
                        <th class="px-4 py-3 text-right">Total Depreciated</th>
                        <th class="px-4 py-3 text-right">Current Value</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($assets as $asset)
                        @php
                            $depDetail = [
                                'name'                     => $asset->name,
                                'code'                     => $asset->code,
                                'category'                 => $asset->category?->name ?? 'Uncategorized',
                                'classification'           => $asset->is_capitalized_ppe ? 'PPE (Capitalized)' : 'Semi-Expendable (< ₱20k)',
                                'purchase_date'            => $asset->purchase_date?->format('M d, Y') ?? 'No date',
                                'acquisition_cost'         => '₱' . number_format($asset->acquisition_cost, 2),
                                'useful_life'              => $asset->is_capitalized_ppe ? ($asset->effective_useful_life . ' yrs (Age: ' . $asset->age_in_years . ' yrs)') : 'N/A (No Depreciation)',
                                'salvage_value'            => '₱' . number_format($asset->is_capitalized_ppe ? $asset->calculated_salvage_value : 0, 2),
                                'salvage_mode'             => $asset->is_capitalized_ppe ? ($asset->salvage_value ? 'Custom Appraised' : 'Standard 10%') : 'Under ₱20k',
                                'annual_depreciation'      => '₱' . number_format($asset->is_capitalized_ppe ? $asset->annual_depreciation : 0, 2),
                                'monthly_depreciation'     => '₱' . number_format($asset->is_capitalized_ppe ? $asset->monthly_depreciation : 0, 2) . '/mo',
                                'accumulated_depreciation' => '₱' . number_format($asset->is_capitalized_ppe ? $asset->accumulated_depreciation : 0, 2),
                                'depreciation_pct'         => ($asset->is_capitalized_ppe ? $asset->depreciation_percentage : 0) . '% depreciated',
                                'current_book_value'       => '₱' . number_format($asset->current_book_value, 2),
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/40 cursor-pointer transition {{ !$asset->is_capitalized_ppe ? 'bg-amber-50/20' : '' }}"
                            onclick='if(!event.target.closest("button, a, form, input, select")) openDepreciationDetailsModal(@json($depDetail));'
                            title="Click to view depreciation details">
                            <td class="px-4 py-3 align-middle text-left">
                                <div class="flex items-center gap-2">
                                    <div class="font-semibold text-gray-900">{{ $asset->name }}</div>
                                    @if($asset->is_capitalized_ppe)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">PPE</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Semi-Expendable (< ₱20,000) - Does not depreciate under accounting rules">Semi-Expendable</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                    <span class="font-mono">{{ $asset->code }}</span>
                                    <span>•</span>
                                    <span>{{ $asset->category?->name ?? 'Uncategorized' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-right font-medium text-gray-900 whitespace-nowrap">
                                ₱{{ number_format($asset->acquisition_cost, 2) }}
                                <div class="text-[11px] text-gray-500">{{ $asset->purchase_date?->format('M d, Y') ?? 'No date' }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center text-gray-700 whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    <div class="font-medium">{{ $asset->effective_useful_life }} yrs</div>
                                    <div class="text-xs text-gray-500">Age: {{ $asset->age_in_years }} yrs</div>
                                @else
                                    <div class="font-medium text-gray-400">N/A</div>
                                    <div class="text-[10px] text-amber-700 font-medium">No Depreciation</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-right font-medium text-gray-700 whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    ₱{{ number_format($asset->calculated_salvage_value, 2) }}
                                    <div class="text-[10px] {{ $asset->salvage_value ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">
                                        {{ $asset->salvage_value ? 'Custom' : 'Standard 10%' }}
                                    </div>
                                @else
                                    <span class="text-gray-400">₱0.00</span>
                                    <div class="text-[10px] text-amber-700 font-medium">Under ₱20k</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-right text-gray-700 whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    ₱{{ number_format($asset->annual_depreciation, 2) }}
                                    <div class="text-[11px] text-gray-500">₱{{ number_format($asset->monthly_depreciation, 2) }}/mo</div>
                                @else
                                    <span class="text-gray-400">₱0.00</span>
                                    <div class="text-[10px] text-gray-400">₱0.00/mo</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-right text-rose-600 font-semibold whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    ₱{{ number_format($asset->accumulated_depreciation, 2) }}
                                    <div class="text-[11px] text-gray-500 font-normal">{{ $asset->depreciation_percentage }}% depreciated</div>
                                @else
                                    <span class="text-gray-400 font-normal">₱0.00</span>
                                    <div class="text-[10px] text-gray-400 font-normal">0.0%</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-right whitespace-nowrap">
                                <div class="font-bold text-emerald-700">₱{{ number_format($asset->current_book_value, 2) }}</div>
                                @if($asset->is_capitalized_ppe)
                                    <div class="w-24 bg-gray-200 rounded-full h-1.5 mt-1.5 ml-auto overflow-hidden">
                                        @php
                                            $remainingPct = max(0, 100 - $asset->depreciation_percentage);
                                        @endphp
                                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $remainingPct }}%"></div>
                                    </div>
                                @else
                                    <div class="text-[10px] text-amber-700 font-medium mt-1">Original Value</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-right whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    <button
                                        type="button"
                                        @click="selectedAsset = {
                                            id: {{ $asset->id }},
                                            name: '{{ addslashes($asset->name) }}',
                                            code: '{{ $asset->code }}',
                                            useful_life_years: {{ $asset->effective_useful_life }},
                                            salvage_value: '{{ $asset->salvage_value ?? '' }}'
                                        }; editModal = true;"
                                        class="px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                                    >
                                        <i class="fas fa-pen-to-square mr-1"></i> Edit Details
                                    </button>
                                @else
                                    <span class="px-2 py-1 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg inline-flex items-center gap-1 font-medium" title="Semi-expendable equipment (< ₱20,000) does not depreciate">
                                        <i class="fas fa-ban text-[10px]"></i> No Depreciation
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">
                                <i class="fas fa-coins text-3xl text-gray-300 mb-2"></i>
                                <p class="font-medium">No assets with recorded acquisition costs found.</p>
                                <p class="text-xs text-gray-400 mt-1">Ensure assets have an acquisition cost recorded in the Asset Registry.</p>
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
                    Edit Depreciation Details
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

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Salvage Value (₱)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        inputmode="decimal"
                        name="salvage_value"
                        x-model="selectedAsset.salvage_value"
                        placeholder="Leave blank to use default 10% value"
                        class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <p class="text-[11px] text-gray-500 mt-1">Leave blank to automatically use standard 10% value</p>
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
                    <span id="dep_detail_classification" class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 mb-1.5"></span>
                    <h4 id="dep_detail_name" class="text-base font-bold text-gray-900"></h4>
                    <p id="dep_detail_meta" class="text-xs font-mono text-gray-600 mt-0.5"></p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs font-semibold uppercase text-gray-500">Current Book Value</p>
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
                    <p class="text-xs font-semibold uppercase text-gray-500">Useful Life</p>
                    <p id="dep_detail_life" class="font-medium text-gray-900 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Annual Depreciation</p>
                    <p id="dep_detail_annual" class="font-bold text-gray-900 font-mono mt-0.5"></p>
                    <p id="dep_detail_monthly" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Accumulated Depreciation</p>
                    <p id="dep_detail_accum" class="font-bold text-rose-600 font-mono mt-0.5"></p>
                    <p id="dep_detail_pct" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg border border-gray-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">Salvage Value</p>
                    <p id="dep_detail_salvage_mode" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <p id="dep_detail_salvage" class="font-bold text-gray-900 font-mono"></p>
            </div>

            <div class="flex justify-end pt-2 border-t border-gray-200">
                <button type="button" onclick="closeModal('depreciation-details-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Close</button>
            </div>
        </div>
    </x-modal>

    <script>
        function openDepreciationDetailsModal(d) {
            document.getElementById('dep_detail_classification').textContent = d.classification || '';
            document.getElementById('dep_detail_name').textContent = d.name || '';
            document.getElementById('dep_detail_meta').textContent = (d.code || '') + ' • ' + (d.category || '');
            document.getElementById('dep_detail_book_value').textContent = d.current_book_value || '₱0.00';
            document.getElementById('dep_detail_cost').textContent = d.acquisition_cost || '₱0.00';
            document.getElementById('dep_detail_purchase_date').textContent = 'Purchased: ' + (d.purchase_date || 'N/A');
            document.getElementById('dep_detail_life').textContent = d.useful_life || 'N/A';
            document.getElementById('dep_detail_annual').textContent = d.annual_depreciation || '₱0.00';
            document.getElementById('dep_detail_monthly').textContent = d.monthly_depreciation || '';
            document.getElementById('dep_detail_accum').textContent = d.accumulated_depreciation || '₱0.00';
            document.getElementById('dep_detail_pct').textContent = d.depreciation_pct || '';
            document.getElementById('dep_detail_salvage').textContent = d.salvage_value || '₱0.00';
            document.getElementById('dep_detail_salvage_mode').textContent = d.salvage_mode || '';
            openModal('depreciation-details-modal');
        }
    </script>
</div>
@endsection

