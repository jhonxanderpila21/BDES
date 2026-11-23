@extends('layouts.admin')

@section('title', 'Salvage Value Records - BDES')

@section('content')
<div class="space-y-6" x-data="{ editModal: false, selectedAsset: {} }">
    {{-- Header --}}
    <x-page-header
        title="Salvage Value Records & Appraisal"
        subtitle="Salvage Value appraisal and residual value tracking for capitalized assets."
        icon="fas fa-recycle"
        :read-only-badge="false"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asset Financials'],
            ['label' => 'Salvage Value Records']
        ]"
    >
        <x-slot:actions>
            <a
                href="{{ route('financials.salvage.export') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition"
            >
                <i class="fas fa-file-csv text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Notifications --}}

    {{-- Residual Value Policy Notice --}}
    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-start gap-3.5">
        <div class="p-2 bg-emerald-600 text-white rounded-lg mt-0.5 shadow-sm">
            <i class="fas fa-scale-balanced text-sm"></i>
        </div>
        <div class="space-y-1">
            <h4 class="text-xs font-bold text-emerald-950 uppercase tracking-wider">Salvage Value Standard & Capitalization Threshold</h4>
            <p class="text-xs text-emerald-900 leading-relaxed">
                Equipment ₱20,000.00 and above automatically carries a standard <strong>10% salvage value</strong> at the end of its useful life, unless an explicit appraised scrap value is entered. Items below ₱20,000.00 (semi-expendable) have <strong>₱0.00 salvage value</strong>.
            </p>
        </div>
    </div>

    {{-- Salvage KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            title="Total Salvage Value"
            :value="'₱' . number_format($totalSalvageValue, 2)"
            icon="fas fa-coins"
            color="emerald"
            subtitle="Estimated value after full use"
        />

        <x-kpi-card
            title="Custom Appraised"
            :value="$customSalvageCount . ' Assets'"
            icon="fas fa-sliders"
            color="blue"
            subtitle="Assets with custom appraised value"
        />

        <x-kpi-card
            title="Standard 10% Values"
            :value="$standardSalvageCount . ' Assets'"
            icon="fas fa-calculator"
            color="purple"
            subtitle="Assets using standard 10% rule"
        />

        <x-kpi-card
            title="Fully Depreciated"
            :value="$fullyDepreciatedCount . ' Assets'"
            icon="fas fa-flag-checkered"
            color="amber"
            subtitle="Assets at end of lifespan"
        />
    </div>

    {{-- Filter Bar --}}
    <x-card>
        <form action="{{ route('financials.salvage') }}" method="GET" class="p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by asset name, code, notes..."
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
                <select name="salvage_type" class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Calculation Types</option>
                    <option value="custom" {{ request('salvage_type') == 'custom' ? 'selected' : '' }}>Custom Appraised</option>
                    <option value="standard" {{ request('salvage_type') == 'standard' ? 'selected' : '' }}>Standard (10%)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'category_id', 'salvage_type']))
                    <a href="{{ route('financials.salvage') }}" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-sm font-semibold transition shadow-xs">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    {{-- Salvage Records Table --}}
    <x-card>
        <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-list-check text-emerald-600"></i>
                Salvage Value List
            </h2>
            <span class="text-xs text-gray-500">Estimated remaining value after full use</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Asset Name & Code</th>
                        <th class="px-4 py-3 text-right">Purchase Cost</th>
                        <th class="px-4 py-3 text-center">Calculation Type</th>
                        <th class="px-4 py-3 text-right">Salvage Value</th>
                        <th class="px-4 py-3 text-left">Notes & Details</th>
                        <th class="px-4 py-3 text-right">Current Value</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($assets as $asset)
                        @php
                            $salvageDetail = [
                                'name'               => $asset->name,
                                'code'               => $asset->code,
                                'category'           => $asset->category?->name ?? 'Uncategorized',
                                'classification'     => $asset->is_capitalized_ppe ? 'PPE (Capitalized)' : 'Semi-Expendable (< ₱20k)',
                                'acquisition_cost'   => '₱' . number_format($asset->acquisition_cost, 2),
                                'calc_type'          => !$asset->is_capitalized_ppe ? 'Under ₱20k (No Salvage)' : (($asset->salvage_value !== null && $asset->salvage_value > 0) ? 'Custom Appraised' : 'Standard 10%'),
                                'salvage_value'      => '₱' . number_format($asset->calculated_salvage_value, 2),
                                'current_book_value' => '₱' . number_format($asset->current_book_value, 2),
                                'notes'              => !$asset->is_capitalized_ppe ? 'Semi-expendable item (< ₱20k); does not carry salvage value.' : ($asset->salvage_notes ?: 'Standard 10% residual value under COA rules.'),
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/40 cursor-pointer transition {{ !$asset->is_capitalized_ppe ? 'bg-amber-50/20' : '' }}"
                            onclick='if(!event.target.closest("button, a, form, input, select")) openSalvageDetailsModal(@json($salvageDetail));'
                            title="Click to view salvage value details">
                            <td class="px-4 py-3 align-middle text-left">
                                <div class="flex items-center gap-2">
                                    <div class="font-semibold text-gray-900">{{ $asset->name }}</div>
                                    @if($asset->is_capitalized_ppe)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">PPE</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Semi-Expendable (< ₱20,000) - Does not carry salvage value">Semi-Expendable</span>
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
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                @if(!$asset->is_capitalized_ppe)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-ban text-[10px]"></i> Under ₱20k (No Salvage)
                                    </span>
                                @elseif($asset->salvage_value !== null && $asset->salvage_value > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-tag text-[10px]"></i> Custom Appraised
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                        <i class="fas fa-scale-balanced text-[10px]"></i> Standard 10%
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-right font-bold whitespace-nowrap {{ $asset->is_capitalized_ppe ? 'text-emerald-700' : 'text-gray-400' }}">
                                ₱{{ number_format($asset->calculated_salvage_value, 2) }}
                                @if(!$asset->is_capitalized_ppe)
                                    <div class="text-[10px] text-amber-700 font-normal">Under ₱20k</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-left text-gray-600 max-w-xs truncate">
                                @if(!$asset->is_capitalized_ppe)
                                    <span class="italic text-gray-400">Semi-expendable item (< ₱20k); does not carry salvage value.</span>
                                @else
                                    {{ $asset->salvage_notes ?: 'Standard 10% residual value under COA rules.' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-right whitespace-nowrap">
                                <div class="font-bold text-gray-900">₱{{ number_format($asset->current_book_value, 2) }}</div>
                                @if($asset->is_capitalized_ppe && $asset->current_book_value <= $asset->calculated_salvage_value)
                                    <span class="text-[10px] text-amber-600 font-semibold uppercase tracking-wider">Fully Depreciated</span>
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
                                            salvage_value: '{{ $asset->salvage_value ?? '' }}',
                                            salvage_notes: '{{ addslashes($asset->salvage_notes ?? '') }}'
                                        }; editModal = true;"
                                        class="px-2.5 py-1 text-xs font-medium text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition"
                                    >
                                        <i class="fas fa-pen-to-square mr-1"></i> Edit Salvage Value
                                    </button>
                                @else
                                    <span class="px-2 py-1 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg inline-flex items-center gap-1 font-medium" title="Under accounting rules, items below ₱20,000 do not have a salvage value">
                                        <i class="fas fa-ban text-[10px]"></i> No Salvage
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <i class="fas fa-recycle text-3xl text-gray-300 mb-2"></i>
                                <p class="font-medium">No salvage value records found.</p>
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

    {{-- Edit Salvage Value Modal --}}
    <div
        x-show="editModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4"
        x-transition
    >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="editModal = false">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h3 class="text-base font-bold text-gray-900">
                    Edit Salvage Value
                </h3>
                <button @click="editModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'/financials/salvage/' + selectedAsset.id" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Asset</label>
                    <p class="text-sm font-bold text-gray-900" x-text="selectedAsset.code + ' - ' + selectedAsset.name"></p>
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
                        placeholder="Leave blank to use standard 10% value"
                        class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    >
                    <p class="text-[11px] text-gray-500 mt-1">If blank, standard 10% salvage value is calculated automatically.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Notes & Remarks
                    </label>
                    <textarea
                        name="salvage_notes"
                        x-model="selectedAsset.salvage_notes"
                        rows="3"
                        placeholder="e.g., Estimated scrap metal value, working parts value, or appraisal note..."
                        class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    ></textarea>
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
                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium shadow-sm transition"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Salvage Details Modal --}}
    <x-modal id="salvage-details-modal" title="Salvage Value Details">
        <div class="space-y-4 text-sm">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex items-start justify-between gap-4">
                <div>
                    <span id="sal_detail_classification" class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-1.5"></span>
                    <h4 id="sal_detail_name" class="text-base font-bold text-gray-900"></h4>
                    <p id="sal_detail_meta" class="text-xs font-mono text-gray-600 mt-0.5"></p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs font-semibold uppercase text-gray-500">Salvage Value</p>
                    <p id="sal_detail_salvage" class="text-xl font-bold text-emerald-700 font-mono"></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Purchase Cost</p>
                    <p id="sal_detail_cost" class="font-bold text-gray-900 font-mono mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Calculation</p>
                    <p id="sal_detail_calc" class="font-medium text-gray-900 mt-0.5"></p>
                </div>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500">Current Value</p>
                    <p id="sal_detail_book_value" class="font-bold text-gray-900 font-mono mt-0.5"></p>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg border border-gray-200">
                <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Appraisal Notes</p>
                <p id="sal_detail_notes" class="text-gray-700"></p>
            </div>

            <div class="flex justify-end pt-2 border-t border-gray-200">
                <button type="button" onclick="closeModal('salvage-details-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Close</button>
            </div>
        </div>
    </x-modal>

    <script>
        function openSalvageDetailsModal(s) {
            document.getElementById('sal_detail_classification').textContent = s.classification || '';
            document.getElementById('sal_detail_name').textContent = s.name || '';
            document.getElementById('sal_detail_meta').textContent = (s.code || '') + ' • ' + (s.category || '');
            document.getElementById('sal_detail_salvage').textContent = s.salvage_value || '₱0.00';
            document.getElementById('sal_detail_cost').textContent = s.acquisition_cost || '₱0.00';
            document.getElementById('sal_detail_calc').textContent = s.calc_type || '';
            document.getElementById('sal_detail_book_value').textContent = s.current_book_value || '₱0.00';
            document.getElementById('sal_detail_notes').textContent = s.notes || '';
            openModal('salvage-details-modal');
        }
    </script>
</div>
@endsection

