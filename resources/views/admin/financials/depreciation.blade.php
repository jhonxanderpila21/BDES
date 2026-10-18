@extends('layouts.admin')

@section('title', 'Depreciation Tracking - BDES')

@section('content')
<div class="space-y-6" x-data="{ editModal: false, selectedAsset: {} }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-blue-100 text-blue-700 rounded-lg"><i class="fas fa-arrow-trend-down"></i></span>
                Asset Depreciation Schedule
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Financials'],
                ['label' => 'Depreciation Tracking']
            ]" />
        </div>
        <div class="flex items-center gap-3">
            <a
                href="{{ route('financials.depreciation.export') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition"
            >
                <i class="fas fa-file-csv text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <a
                href="{{ route('financials.salvage') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium shadow-sm transition"
            >
                <i class="fas fa-recycle"></i>
                <span>Salvage Records</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}

    {{-- Philippine Government Accounting Standard Statutory Notice (COA Circular No. 2022-004) --}}
    <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-xl flex items-start gap-3.5">
        <div class="p-2 bg-blue-600 text-white rounded-lg mt-0.5 shadow-sm">
            <i class="fas fa-scale-balanced text-sm"></i>
        </div>
        <div class="space-y-1">
            <h4 class="text-xs font-bold text-blue-950 uppercase tracking-wider">Philippine Government Accounting Standard (COA Circular No. 2022-004 & GAM for LGUs)</h4>
            <p class="text-xs text-blue-900 leading-relaxed">
                Tangible items with acquisition costs <span class="font-bold underline">₱50,000.00 and above</span> are capitalized as <strong>Property, Plant, and Equipment (PPE)</strong> and amortized using the Straight-Line Depreciation method with a standard <strong>10% residual value</strong> (COA Circular No. 2003-007). Items below ₱50,000.00 are classified as <strong>Semi-Expendable Property</strong> (issued via Inventory Custodian Slip) and are legally non-depreciable (Depreciation & Salvage = ₱0.00).
            </p>
        </div>
    </div>

    {{-- Financial KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Total Acquisition Cost</span>
                <span class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i class="fas fa-receipt"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-2">₱{{ number_format($totalAcquisitionCost, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total recorded capitalization</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Accumulated Depreciation</span>
                <span class="p-2 bg-rose-50 text-rose-600 rounded-lg"><i class="fas fa-chart-line-down"></i></span>
            </div>
            <p class="text-2xl font-bold text-rose-600 mt-2">₱{{ number_format($totalAccumulatedDepreciation, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Recognized straight-line amortization</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Current Book Value</span>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg"><i class="fas fa-vault"></i></span>
            </div>
            <p class="text-2xl font-bold text-emerald-700 mt-2">₱{{ number_format($totalCurrentBookValue, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Current net asset carrying worth</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Annual Depreciation</span>
                <span class="p-2 bg-amber-50 text-amber-600 rounded-lg"><i class="fas fa-calendar-days"></i></span>
            </div>
            <p class="text-2xl font-bold text-amber-700 mt-2">₱{{ number_format($totalAnnualDepreciation, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Annual amortization run rate</p>
        </div>
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
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'status']))
                    <a href="{{ route('financials.depreciation') }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">
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
                Straight-Line Depreciation Registry
            </h2>
            <span class="text-xs text-gray-500">Government Standard: Straight-Line (10% Residual)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Asset</th>
                        <th class="px-4 py-3">Acquisition Cost</th>
                        <th class="px-4 py-3">Useful Life / Age</th>
                        <th class="px-4 py-3">Salvage Value</th>
                        <th class="px-4 py-3">Annual Dep.</th>
                        <th class="px-4 py-3">Accumulated Dep.</th>
                        <th class="px-4 py-3">Net Book Value</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-gray-50/80 transition {{ !$asset->is_capitalized_ppe ? 'bg-amber-50/20' : '' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="font-semibold text-gray-900">{{ $asset->name }}</div>
                                    @if($asset->is_capitalized_ppe)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">PPE</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Semi-Expendable (< ₱50,000) - Statutory non-depreciable under COA Circular No. 2022-004">Semi-Expendable</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                    <span class="font-mono">{{ $asset->code }}</span>
                                    <span>•</span>
                                    <span>{{ $asset->category?->name ?? 'Uncategorized' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                ₱{{ number_format($asset->acquisition_cost, 2) }}
                                <div class="text-[11px] text-gray-500">{{ $asset->purchase_date?->format('M d, Y') ?? 'No date' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    <div class="font-medium">{{ $asset->effective_useful_life }} yrs</div>
                                    <div class="text-xs text-gray-500">Age: {{ $asset->age_in_years }} yrs</div>
                                @else
                                    <div class="font-medium text-gray-400">N/A</div>
                                    <div class="text-[10px] text-amber-700 font-medium">Non-Depreciable</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-700 whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    ₱{{ number_format($asset->calculated_salvage_value, 2) }}
                                    <div class="text-[10px] {{ $asset->salvage_value ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">
                                        {{ $asset->salvage_value ? 'Custom' : 'Standard 10%' }}
                                    </div>
                                @else
                                    <span class="text-gray-400">₱0.00</span>
                                    <div class="text-[10px] text-amber-700 font-medium">COA Limit (< ₱50k)</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    ₱{{ number_format($asset->annual_depreciation, 2) }}
                                    <div class="text-[11px] text-gray-500">₱{{ number_format($asset->monthly_depreciation, 2) }}/mo</div>
                                @else
                                    <span class="text-gray-400">₱0.00</span>
                                    <div class="text-[10px] text-gray-400">₱0.00/mo</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-rose-600 font-semibold whitespace-nowrap">
                                @if($asset->is_capitalized_ppe)
                                    ₱{{ number_format($asset->accumulated_depreciation, 2) }}
                                    <div class="text-[11px] text-gray-500 font-normal">{{ $asset->depreciation_percentage }}% depreciated</div>
                                @else
                                    <span class="text-gray-400 font-normal">₱0.00</span>
                                    <div class="text-[10px] text-gray-400 font-normal">0.0%</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-bold text-emerald-700">₱{{ number_format($asset->current_book_value, 2) }}</div>
                                @if($asset->is_capitalized_ppe)
                                    <div class="w-24 bg-gray-200 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                        @php
                                            $remainingPct = max(0, 100 - $asset->depreciation_percentage);
                                        @endphp
                                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $remainingPct }}%"></div>
                                    </div>
                                @else
                                    <div class="text-[10px] text-amber-700 font-medium mt-1">Carried at Cost</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
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
                                        <i class="fas fa-pen-to-square mr-1"></i> Edit Parameters
                                    </button>
                                @else
                                    <span class="px-2 py-1 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg inline-flex items-center gap-1 font-medium" title="Under COA Circular No. 2022-004, semi-expendable equipment (< ₱50,000) is legally non-depreciable">
                                        <i class="fas fa-ban text-[10px]"></i> Non-Depreciable
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
                    Adjust Depreciation Parameters
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
                        Useful Life (Years) <span class="text-red-500">*</span>
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
                    <p class="text-[11px] text-gray-500 mt-1">Recommended for standard LGU equipment: 5 to 10 years</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Custom Salvage Value (PHP)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        inputmode="decimal"
                        name="salvage_value"
                        x-model="selectedAsset.salvage_value"
                        placeholder="Leave blank to use default 10% residual"
                        class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <p class="text-[11px] text-gray-500 mt-1">Leave empty to auto-calculate standard 10% residual value</p>
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
                        Save Parameters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

