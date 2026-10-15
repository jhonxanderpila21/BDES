@extends('layouts.admin')

@section('title', 'Salvage Value Records - BDES')

@section('content')
<div class="space-y-6" x-data="{ editModal: false, selectedAsset: {} }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-emerald-100 text-emerald-700 rounded-lg"><i class="fas fa-recycle"></i></span>
                Salvage Value Records & Appraisal
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Financials'],
                ['label' => 'Salvage Value Records']
            ]" />
        </div>
        <div class="flex items-center gap-3">
            <a
                href="{{ route('financials.salvage.export') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition"
            >
                <i class="fas fa-file-csv text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <a
                href="{{ route('financials.depreciation') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium shadow-sm transition"
            >
                <i class="fas fa-arrow-trend-down"></i>
                <span>Depreciation Schedule</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i class="fas fa-circle-check text-emerald-600 text-lg"></i>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3">
            <i class="fas fa-circle-exclamation text-rose-600 text-lg"></i>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Philippine Government Accounting Standard Statutory Notice (COA Circular No. 2022-004) --}}
    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-start gap-3.5">
        <div class="p-2 bg-emerald-600 text-white rounded-lg mt-0.5 shadow-sm">
            <i class="fas fa-scale-balanced text-sm"></i>
        </div>
        <div class="space-y-1">
            <h4 class="text-xs font-bold text-emerald-950 uppercase tracking-wider">Philippine Government Residual Valuation Standard (COA Circular No. 2003-007 & GAM for LGUs)</h4>
            <p class="text-xs text-emerald-900 leading-relaxed">
                Under COA guidelines, capitalized PPE items (₱50,000.00 and above) automatically carry a statutory <strong>10% residual value</strong> floor unless an explicit appraised scrap valuation is justified. Tangible equipment below ₱50,000.00 (Semi-Expendable Property) carries a mandatory <strong>₱0.00 salvage value</strong> and is expensed upon issuance.
            </p>
        </div>
    </div>

    {{-- Salvage KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Total Residual Value</span>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg"><i class="fas fa-coins"></i></span>
            </div>
            <p class="text-2xl font-bold text-emerald-700 mt-2">₱{{ number_format($totalSalvageValue, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Guaranteed salvage recovery floor</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Custom Valuations</span>
                <span class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i class="fas fa-sliders"></i></span>
            </div>
            <p class="text-2xl font-bold text-blue-700 mt-2">{{ $customSalvageCount }} Assets</p>
            <p class="text-xs text-gray-500 mt-1">Directly appraised residual value</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Standard Residual</span>
                <span class="p-2 bg-purple-50 text-purple-600 rounded-lg"><i class="fas fa-calculator"></i></span>
            </div>
            <p class="text-2xl font-bold text-purple-700 mt-2">{{ $standardSalvageCount }} Assets</p>
            <p class="text-xs text-gray-500 mt-1">COA statutory 10% acquisition cost</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">At Salvage Floor</span>
                <span class="p-2 bg-amber-50 text-amber-600 rounded-lg"><i class="fas fa-flag-checkered"></i></span>
            </div>
            <p class="text-2xl font-bold text-amber-700 mt-2">{{ $fullyDepreciatedCount }} Assets</p>
            <p class="text-xs text-gray-500 mt-1">Fully depreciated (end of life)</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <x-card>
        <form action="{{ route('financials.salvage') }}" method="GET" class="p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search asset, code, notes..."
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
                    <option value="">All Valuation Types</option>
                    <option value="custom" {{ request('salvage_type') == 'custom' ? 'selected' : '' }}>Custom Valuation</option>
                    <option value="standard" {{ request('salvage_type') == 'standard' ? 'selected' : '' }}>Standard (10%)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'salvage_type']))
                    <a href="{{ route('financials.salvage') }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">
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
                Asset Salvage & Residual Value Records
            </h2>
            <span class="text-xs text-gray-500">End-of-life residual valuation ledger</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Asset</th>
                        <th class="px-4 py-3">Acquisition Cost</th>
                        <th class="px-4 py-3">Method</th>
                        <th class="px-4 py-3">Salvage Value</th>
                        <th class="px-4 py-3">Residual Notes / Justification</th>
                        <th class="px-4 py-3">Current Book Value</th>
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
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Semi-Expendable (< ₱50,000) - Mandatory ₱0.00 salvage value under COA Circular No. 2022-004">Semi-Expendable</span>
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
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if(!$asset->is_capitalized_ppe)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-ban text-[10px]"></i> COA Semi-Expendable
                                    </span>
                                @elseif($asset->salvage_value !== null && $asset->salvage_value > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-tag text-[10px]"></i> Custom Appraised
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                        <i class="fas fa-scale-balanced text-[10px]"></i> COA 10% Standard
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold whitespace-nowrap {{ $asset->is_capitalized_ppe ? 'text-emerald-700' : 'text-gray-400' }}">
                                ₱{{ number_format($asset->calculated_salvage_value, 2) }}
                                @if(!$asset->is_capitalized_ppe)
                                    <div class="text-[10px] text-amber-700 font-normal">Non-Salvageable (< ₱50k)</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-xs truncate">
                                @if(!$asset->is_capitalized_ppe)
                                    <span class="italic text-gray-400">Semi-expendable property; legally non-salvageable under COA Circular No. 2022-004.</span>
                                @else
                                    {{ $asset->salvage_notes ?: 'Standard 10% statutory residual allowance under LGU guidelines.' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-bold text-gray-900">₱{{ number_format($asset->current_book_value, 2) }}</div>
                                @if($asset->is_capitalized_ppe && $asset->current_book_value <= $asset->calculated_salvage_value)
                                    <span class="text-[10px] text-amber-600 font-semibold uppercase tracking-wider">At Salvage Floor</span>
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
                                            salvage_value: '{{ $asset->salvage_value ?? '' }}',
                                            salvage_notes: '{{ addslashes($asset->salvage_notes ?? '') }}'
                                        }; editModal = true;"
                                        class="px-2.5 py-1 text-xs font-medium text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition"
                                    >
                                        <i class="fas fa-pen-to-square mr-1"></i> Update Record
                                    </button>
                                @else
                                    <span class="px-2 py-1 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg inline-flex items-center gap-1 font-medium" title="Under COA Circular No. 2022-004, semi-expendable equipment (< ₱50,000) cannot have a salvage value">
                                        <i class="fas fa-ban text-[10px]"></i> Non-Salvageable
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
                    Update Salvage Value Record
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
                        Specific Salvage Value (PHP)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        inputmode="decimal"
                        name="salvage_value"
                        x-model="selectedAsset.salvage_value"
                        placeholder="Leave blank to use default 10% residual"
                        class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    >
                    <p class="text-[11px] text-gray-500 mt-1">If blank, standard 10% residual value is applied automatically.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Appraisal Notes & Justification
                    </label>
                    <textarea
                        name="salvage_notes"
                        x-model="selectedAsset.salvage_notes"
                        rows="3"
                        placeholder="e.g., Estimated scrap metal recovery, secondary parts salvage appraisal, or disposal minimum..."
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
                        Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

