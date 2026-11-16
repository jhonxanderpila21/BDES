@extends('layouts.admin')

@section('title', 'Asset Locations - BDES')

@section('content')
@php
    $isReadOnlyStaff = auth()->user()?->isViewer() || auth()->user()?->hasRole('viewer', 'staff');
@endphp
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-sm">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Asset Locations & Facilities</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage warehouses, depots, stations, and storage facilities holding emergency response equipment</p>
                </div>
            </div>
            <div class="mt-2">
                <x-breadcrumb :items="[
                    ['label' => 'Dashboard', 'url' => route('dashboard')],
                    ['label' => 'Administration'],
                    ['label' => 'Asset Locations']
                ]" />
            </div>
        </div>
        @if(auth()->user()->isAdmin())
            <button onclick="openAddLocationModal()" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 active:bg-blue-800 transition-colors font-medium text-sm shadow-sm">
                <i class="fas fa-plus"></i>
                <span>Add Asset Location</span>
            </button>
        @endif
    </div>

    {{-- KPI Summary Deck --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('locations.index') }}" class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all flex items-center gap-4 group block">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 group-hover:text-blue-600 transition-colors">Total Facilities</p>
                <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $stats['total_locations'] ?? $locations->total() }}</p>
            </div>
        </a>

        <a href="{{ route('assets.index', ['tab' => 'assets']) }}" class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-indigo-400 hover:shadow-md transition-all flex items-center gap-4 group block" title="View all assets in registry">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 group-hover:text-indigo-600 transition-colors">Assets Kept</p>
                <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $stats['total_assets'] ?? 0 }}</p>
            </div>
        </a>

        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'available']) }}" class="bg-white p-5 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-sm hover:border-emerald-400 hover:shadow-md transition-all flex items-center gap-4 group block" title="View ready assets">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Ready for Deployment</p>
                <p class="text-2xl font-bold text-emerald-700 mt-0.5">{{ $stats['available_assets'] ?? 0 }}</p>
            </div>
        </a>

        <a href="{{ route('locations.index') }}" class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-amber-400 hover:shadow-md transition-all flex items-center gap-4 group block">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 group-hover:text-amber-600 transition-colors">Storage Capacity</p>
                <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($stats['total_capacity'] ?? 0) }} <span class="text-xs font-normal text-gray-400">slots</span></p>
            </div>
        </a>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <form action="{{ route('locations.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search facilities by name, type, address..." class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                </div>
            </div>

            <div class="w-full sm:w-48">
                <select name="type" onchange="this.form.submit()" class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">All Facility Types</option>
                    <option value="storage" {{ request('type') === 'storage' ? 'selected' : '' }}>Storage</option>
                    <option value="office" {{ request('type') === 'office' ? 'selected' : '' }}>Office</option>
                    <option value="depot" {{ request('type') === 'depot' ? 'selected' : '' }}>Depot</option>
                    <option value="field" {{ request('type') === 'field' ? 'selected' : '' }}>Field Station</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium transition-colors shadow-sm">
                    <i class="fas fa-filter mr-1.5"></i>Filter
                </button>
                @if(request()->anyFilled(['search', 'type']))
                    <a href="{{ route('locations.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition-colors">
                        <i class="fas fa-undo mr-1.5"></i>Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Locations Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Location / Facility</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Physical Address</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Assets Kept</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Capacity Usage</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($locations as $location)
                        <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer group" onclick="openLocationAssetsModal(@js($location))">
                            {{-- Location Name --}}
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold group-hover:bg-blue-600 group-hover:text-white transition-colors shrink-0">
                                        <i class="fas fa-map-pin"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors flex items-center gap-1.5">
                                            <span>{{ $location->name }}</span>
                                            <i class="fas fa-external-link-alt text-[10px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </p>
                                        <p class="text-xs text-gray-400">Click to view stored assets</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Type --}}
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                @php
                                    $typeVariant = match(strtolower($location->type ?? '')) {
                                        'storage' => 'info',
                                        'office'  => 'primary',
                                        'depot'   => 'warning',
                                        'field'   => 'success',
                                        default   => 'secondary',
                                    };
                                @endphp
                                <x-badge variant="{{ $typeVariant }}" size="sm">{{ ucfirst($location->type ?: 'Storage') }}</x-badge>
                            </td>

                            {{-- Address --}}
                            <td class="px-6 py-4 align-middle text-sm text-gray-600">
                                @if($location->address)
                                    <span class="flex items-center gap-1.5">
                                        <i class="fas fa-location-dot text-gray-400 text-xs"></i>
                                        <span>{{ $location->address }}</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">No physical address specified</span>
                                @endif
                            </td>

                            {{-- Assets Count --}}
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-check-circle text-[10px]"></i> {{ $location->available_assets_count ?? 0 }} Available
                                    </span>
                                    <span class="text-xs font-medium text-gray-500 mt-1">
                                        <strong>{{ $location->assets_count }}</strong> item{{ $location->assets_count === 1 ? '' : 's' }} stored
                                    </span>
                                </div>
                            </td>

                            {{-- Capacity Usage --}}
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center">
                                @php 
                                    $capacity = $location->storage_capacity > 0 ? $location->storage_capacity : 100;
                                    $percentage = min(100, round(($location->assets_count / $capacity) * 100));
                                    $barColor = $percentage >= 90 ? 'bg-rose-500' : ($percentage >= 70 ? 'bg-amber-500' : 'bg-blue-500');
                                @endphp
                                <div class="max-w-[130px] mx-auto space-y-1">
                                    <div class="flex items-center justify-between text-xs text-gray-600">
                                        <span class="font-bold">{{ $percentage }}%</span>
                                        <span class="text-[11px] text-gray-400">{{ $location->assets_count }}/{{ $location->storage_capacity ?: '∞' }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-1.5" onclick="event.stopPropagation();">
                                    {{-- Quick view assets button --}}
                                    <button type="button" 
                                            onclick="openLocationAssetsModal(@js($location))" 
                                            class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" 
                                            title="View Stored Assets">
                                        <i class="fas fa-boxes-stacked"></i>
                                    </button>

                                    @if(auth()->user()->isAdmin())
                                        {{-- Edit Button --}}
                                        <button type="button" 
                                                onclick="editLocation({{ $location->id }}, @js($location->name), @js($location->type), @js($location->address), {{ $location->storage_capacity ?? 'null' }})" 
                                                class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors" 
                                                title="Edit Location">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Delete Button with Deletion Protection --}}
                                        @if($location->assets_count > 0)
                                            <button type="button" 
                                                    onclick="window.bdesToast('Cannot delete facility [{{ addslashes($location->name) }}] because {{ $location->assets_count }} asset(s) are currently kept here. Please relocate or reassign all assets to another facility first.', 'warning', 5000, 'Deletion Blocked');" 
                                                    class="p-2 rounded-lg text-gray-300 hover:text-gray-400 cursor-not-allowed transition-colors" 
                                                    title="Deletion blocked: {{ $location->assets_count }} asset(s) currently stored">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('locations.destroy', $location) }}" method="POST" class="inline-block" onsubmit="return confirmSecureDelete(event, this, 'Facility: {{ addslashes($location->name) }}', 'This action will permanently delete this location from the facility registry. Ensure no active operations rely on it.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors" title="Delete Location">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-empty-state icon="fas fa-warehouse" title="No asset locations found" description="No storage facilities match your current search criteria." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $locations->links() }}
        </div>
    </x-card>

    {{-- Location Stored Assets Details Modal --}}
    <x-modal id="location-assets-modal" title="Facility Stored Assets" maxWidth="5xl">
        <div class="space-y-6">
            {{-- Facility Header Card --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 id="modal_location_name" class="text-lg font-bold text-gray-900">Facility Name</h3>
                            <span id="modal_location_type_badge" class="px-2 py-0.5 rounded text-xs font-bold uppercase bg-blue-100 text-blue-800">Storage</span>
                        </div>
                        <p id="modal_location_address" class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                            <i class="fas fa-location-dot text-gray-400"></i>
                            <span>Address</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3 bg-white px-3.5 py-2 rounded-lg border border-gray-200 shadow-xs shrink-0">
                        <div class="text-center">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Total Stored</span>
                            <span id="modal_total_assets" class="text-sm font-bold text-gray-900">0</span>
                        </div>
                        <div class="w-px h-6 bg-gray-200"></div>
                        <div class="text-center">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Available</span>
                            <span id="modal_available_assets" class="text-sm font-bold text-emerald-600">0</span>
                        </div>
                        <div class="w-px h-6 bg-gray-200"></div>
                        <div class="text-center">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Capacity</span>
                            <span id="modal_capacity_text" class="text-sm font-bold text-blue-600">0%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assets Table / List --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                        <i class="fas fa-boxes-stacked text-blue-600"></i>
                        <span>Assets Kept in this Facility</span>
                    </h4>
                    <div class="flex items-center gap-2">
                        @if(!$isReadOnlyStaff)
                            <button type="button" 
                                    id="batch_relocate_btn" 
                                    onclick="openBatchRelocateModal()" 
                                    class="hidden inline-flex items-center gap-1.5 px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-colors shadow-xs">
                                <i class="fas fa-dolly text-[11px]"></i>
                                <span>Relocate Selected (<span id="batch_count">0</span>)</span>
                            </button>
                        @endif
                        <span id="modal_assets_count_badge" class="text-xs text-gray-400 font-medium">0 assets</span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-xl overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-gray-100/75 border-b border-gray-200 sticky top-0 z-10">
                            <tr>
                                @if(!$isReadOnlyStaff)
                                    <th class="w-10 px-3 py-2.5 text-center">
                                        <input type="checkbox" id="modal_select_all_assets" onchange="toggleSelectAllAssets(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" title="Select all assets for relocation">
                                    </th>
                                @endif
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Asset Code & Name</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Category</th>
                                <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Cost</th>
                                <th class="px-4 py-2.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="modal_assets_table_body" class="divide-y divide-gray-100 bg-white">
                            {{-- Injected dynamically --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                <a id="modal_registry_filter_btn" href="#" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl font-medium text-xs border border-blue-200 transition-colors">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Open in Asset Registry (Filtered)</span>
                </a>
                <button type="button" onclick="window.closeModal('location-assets-modal')" class="w-full sm:w-auto px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium text-xs transition-colors">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    @if(!$isReadOnlyStaff)
        {{-- Relocate Asset Modal --}}
        <x-modal id="relocate-asset-modal" title="Relocate Equipment to Another Facility">
            <form id="relocate-asset-form" action="{{ route('locations.relocate-asset') }}" method="POST" class="space-y-4">
                @csrf
                <div id="relocate_hidden_asset_ids_container">
                    <input type="hidden" name="asset_ids[]" id="relocate_single_asset_id">
                </div>
                <input type="hidden" name="current_location_id" id="relocate_current_location_id">

                {{-- Equipment Summary Card --}}
                <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 text-base shadow-sm">
                            <i class="fas fa-truck-ramp-box"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div id="relocate_single_asset_details" class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span id="relocate_asset_code" class="font-mono text-xs font-bold text-blue-700 bg-white border border-blue-200 px-2 py-0.5 rounded shadow-2xs">AST-0000</span>
                                    <h4 id="relocate_asset_name" class="font-bold text-gray-900 text-sm truncate">Asset Name</h4>
                                </div>
                                <div class="text-xs text-gray-600 flex items-center gap-2 mt-0.5">
                                    <span>Status:</span>
                                    <span id="relocate_asset_status_badge" class="font-semibold text-gray-800 capitalize">Available</span>
                                </div>
                            </div>
                            <div id="relocate_batch_assets_details" class="hidden">
                                <h4 class="font-bold text-gray-900 text-sm">Transferring <span id="relocate_batch_count_text">0</span> Selected Assets</h4>
                                <p id="relocate_batch_codes_text" class="text-xs text-gray-600 font-mono mt-1 break-words"></p>
                            </div>
                            <div class="mt-2 text-xs text-gray-500 pt-2 border-t border-blue-200/60 flex items-center gap-1.5">
                                <i class="fas fa-warehouse text-gray-400"></i>
                                <span>Current Facility: <strong id="relocate_current_location_name" class="text-gray-800 font-semibold">—</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Destination Facility --}}
                <x-form-group label="Destination Facility / Location" required>
                    <select name="location_id" id="relocate_destination_select" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="">Select destination storage facility...</option>
                        @foreach($allLocations as $loc)
                            <option value="{{ $loc->id }}" data-name="{{ $loc->name }}">
                                {{ $loc->name }} ({{ ucfirst($loc->type ?? 'storage') }}) &bull; {{ $loc->assets_count }} {{ $loc->storage_capacity ? '/ ' . $loc->storage_capacity . ' capacity' : 'assets' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Specify which storage facility, warehouse, or station will hold this equipment.</p>
                </x-form-group>

                {{-- Transfer Reason & Notes --}}
                <x-form-group label="Relocation Reason & Notes">
                    <textarea name="notes" rows="3" placeholder="State reason for transfer (e.g., Seasonal prepositioning, station reassignment, repair staging)..." class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Recorded in the asset's permanent Audit Trail.</p>
                </x-form-group>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="relocate_return_to_source" name="return_to_source" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="relocate_return_to_source" class="text-xs text-gray-600 cursor-pointer">
                        Stay in the source facility view after relocation
                    </label>
                </div>

                <div class="flex gap-3 pt-3 border-t border-gray-100">
                    <x-button variant="primary" type="submit" class="flex-1">
                        <i class="fas fa-arrows-turn-to-dots mr-1.5"></i> Confirm Relocation
                    </x-button>
                    <x-button variant="secondary" type="button" onclick="closeRelocateModal()" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>
    @endif

    @if(auth()->user()->isAdmin())
        {{-- Add Location Modal --}}
        <x-modal id="add-location-modal" title="Add Asset Location">
            <form class="space-y-4" action="{{ route('locations.store') }}" method="POST">
                @csrf
                <x-form-group label="Location Name" required>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Main Storage Facility" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Location Type" required>
                    <select name="type" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="storage">Storage</option>
                        <option value="office">Office</option>
                        <option value="depot">Depot</option>
                        <option value="field">Field Station</option>
                    </select>
                </x-form-group>

                <x-form-group label="Physical Address">
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Street address or building coordinates" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Storage Capacity (Slots)">
                    <input type="number" min="0" name="storage_capacity" value="{{ old('storage_capacity') }}" placeholder="Maximum number of items" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <div class="flex gap-3 mt-6">
                    <x-button variant="primary" type="submit" class="flex-1">Create Facility</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('add-location-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>

        {{-- Edit Location Modal --}}
        <x-modal id="edit-location-modal" title="Edit Asset Location">
            <form class="space-y-4" id="edit-location-form" method="POST">
                @csrf
                @method('PUT')
                <x-form-group label="Location Name" required>
                    <input type="text" name="name" id="edit_location_name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Location Type" required>
                    <select name="type" id="edit_location_type" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="storage">Storage</option>
                        <option value="office">Office</option>
                        <option value="depot">Depot</option>
                        <option value="field">Field Station</option>
                    </select>
                </x-form-group>

                <x-form-group label="Physical Address">
                    <input type="text" name="address" id="edit_location_address" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <x-form-group label="Storage Capacity (Slots)">
                    <input type="number" min="0" name="storage_capacity" id="edit_location_capacity" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </x-form-group>

                <div class="flex gap-3 mt-6">
                    <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                    <x-button variant="secondary" type="button" onclick="window.closeModal('edit-location-modal')" class="flex-1">Cancel</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</div>

@push('scripts')
<script>
    const canRelocate = !@json($isReadOnlyStaff);
    let currentOpenLocation = null;

    function openAddLocationModal() {
        window.openModal('add-location-modal');
    }

    function editLocation(id, name, type, address, capacity) {
        const form = document.getElementById('edit-location-form');
        if (!form) return;
        form.action = '/locations/' + id;
        document.getElementById('edit_location_name').value = name;
        document.getElementById('edit_location_type').value = type;
        document.getElementById('edit_location_address').value = address || '';
        document.getElementById('edit_location_capacity').value = capacity !== null ? capacity : '';
        window.openModal('edit-location-modal');
    }

    function openLocationAssetsModal(location) {
        currentOpenLocation = location;

        const masterCb = document.getElementById('modal_select_all_assets');
        if (masterCb) {
            masterCb.checked = false;
            masterCb.indeterminate = false;
        }
        const batchBtn = document.getElementById('batch_relocate_btn');
        if (batchBtn) {
            batchBtn.classList.add('hidden');
        }

        document.getElementById('modal_location_name').textContent = location.name;
        document.getElementById('modal_location_address').querySelector('span').textContent = location.address || 'No physical address specified';
        
        const typeBadge = document.getElementById('modal_location_type_badge');
        typeBadge.textContent = (location.type || 'storage').toUpperCase();

        const assets = location.assets || [];
        const totalCount = location.assets_count !== undefined ? location.assets_count : assets.length;
        const availableCount = location.available_assets_count !== undefined 
            ? location.available_assets_count 
            : assets.filter(a => a.status === 'available').length;

        document.getElementById('modal_total_assets').textContent = totalCount;
        document.getElementById('modal_available_assets').textContent = availableCount;

        const capacity = location.storage_capacity > 0 ? location.storage_capacity : 100;
        const percentage = Math.min(100, Math.round((totalCount / capacity) * 100));
        document.getElementById('modal_capacity_text').textContent = percentage + '%';

        document.getElementById('modal_assets_count_badge').textContent = totalCount + ' asset' + (totalCount === 1 ? '' : 's');

        // Link to registry with filter
        const filterBtn = document.getElementById('modal_registry_filter_btn');
        if (filterBtn) {
            filterBtn.href = '/assets?location_id=' + location.id;
        }

        const tbody = document.getElementById('modal_assets_table_body');
        if (assets.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${canRelocate ? 6 : 5}" class="px-4 py-8 text-center text-gray-400 italic text-xs">
                        <i class="fas fa-boxes-stacked text-2xl text-gray-300 mb-2 block"></i>
                        No assets are currently stored in this facility.
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = assets.map(asset => {
                let statusBadge = '';
                if (asset.status === 'available') {
                    statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-check-circle text-[9px]"></i> Available</span>';
                } else if (asset.status === 'deployed') {
                    statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Deployed</span>';
                } else if (asset.status === 'under_maintenance') {
                    statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Maintenance</span>';
                } else {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">${escapeHtml(asset.status)}</span>`;
                }

                const costFormatted = asset.acquisition_cost ? '₱' + parseFloat(asset.acquisition_cost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '—';
                const categoryName = asset.category ? asset.category.name : 'Uncategorized';

                return `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        ${canRelocate ? `
                            <td class="w-10 px-3 py-3 text-center align-middle">
                                <input type="checkbox" value="${asset.id}" data-code="${escapeHtml(asset.code)}" data-name="${escapeHtml(asset.name)}" onchange="updateBatchRelocateState()" class="asset-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                        ` : ''}
                        <td class="px-4 py-3 align-middle">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded whitespace-nowrap shrink-0">${escapeHtml(asset.code)}</span>
                                <div>
                                    <a href="/assets/${asset.id}" class="font-semibold text-gray-900 hover:text-blue-600 hover:underline block">${escapeHtml(asset.name)}</a>
                                    ${asset.serial_number ? `<span class="text-[10px] text-gray-400 font-mono block">S/N: ${escapeHtml(asset.serial_number)}</span>` : ''}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle text-xs text-gray-600">
                            ${escapeHtml(categoryName)}
                        </td>
                        <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                            ${statusBadge}
                        </td>
                        <td class="px-4 py-3 align-middle text-right font-mono text-xs text-gray-700 whitespace-nowrap">
                            ${costFormatted}
                        </td>
                        <td class="px-4 py-3 align-middle text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                ${canRelocate ? `
                                    <button type="button" 
                                            onclick="openSingleRelocateModal(${asset.id}, '${escapeJs(asset.code)}', '${escapeJs(asset.name)}', '${escapeJs(asset.status || '')}')" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition-colors"
                                            title="Relocate this equipment to another facility">
                                        <i class="fas fa-truck-ramp-box text-[10px]"></i>
                                        <span>Relocate</span>
                                    </button>
                                ` : ''}
                                <a href="/assets/${asset.id}" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline px-2 py-1">
                                    <span>Details</span>
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        window.openModal('location-assets-modal');
    }

    function openSingleRelocateModal(assetId, assetCode, assetName, status) {
        if (!currentOpenLocation) return;

        const container = document.getElementById('relocate_hidden_asset_ids_container');
        container.innerHTML = `<input type="hidden" name="asset_ids[]" value="${assetId}">`;

        document.getElementById('relocate_current_location_id').value = currentOpenLocation.id;
        document.getElementById('relocate_current_location_name').textContent = currentOpenLocation.name;

        document.getElementById('relocate_single_asset_details').classList.remove('hidden');
        document.getElementById('relocate_batch_assets_details').classList.add('hidden');

        document.getElementById('relocate_asset_code').textContent = assetCode;
        document.getElementById('relocate_asset_name').textContent = assetName;
        document.getElementById('relocate_asset_status_badge').textContent = (status || 'Available').replace('_', ' ');

        setupDestinationSelect(currentOpenLocation.id);

        window.closeModal('location-assets-modal');
        window.openModal('relocate-asset-modal');
    }

    function openBatchRelocateModal() {
        if (!currentOpenLocation) return;

        const checkedBoxes = Array.from(document.querySelectorAll('.asset-checkbox:checked'));
        if (checkedBoxes.length === 0) return;

        const container = document.getElementById('relocate_hidden_asset_ids_container');
        container.innerHTML = checkedBoxes.map(cb => `<input type="hidden" name="asset_ids[]" value="${cb.value}">`).join('');

        document.getElementById('relocate_current_location_id').value = currentOpenLocation.id;
        document.getElementById('relocate_current_location_name').textContent = currentOpenLocation.name;

        document.getElementById('relocate_single_asset_details').classList.add('hidden');
        document.getElementById('relocate_batch_assets_details').classList.remove('hidden');

        document.getElementById('relocate_batch_count_text').textContent = checkedBoxes.length;
        const codes = checkedBoxes.map(cb => cb.dataset.code).join(', ');
        document.getElementById('relocate_batch_codes_text').textContent = codes;

        setupDestinationSelect(currentOpenLocation.id);

        window.closeModal('location-assets-modal');
        window.openModal('relocate-asset-modal');
    }

    function setupDestinationSelect(currentLocId) {
        const select = document.getElementById('relocate_destination_select');
        if (!select) return;
        select.value = '';
        Array.from(select.options).forEach(opt => {
            if (opt.value == currentLocId) {
                opt.disabled = true;
                if (!opt.textContent.includes('(Current)')) {
                    opt.textContent = opt.textContent + ' (Current)';
                }
            } else {
                opt.disabled = false;
                opt.textContent = opt.textContent.replace(' (Current)', '');
            }
        });
    }

    function closeRelocateModal() {
        window.closeModal('relocate-asset-modal');
        if (currentOpenLocation) {
            window.openModal('location-assets-modal');
        }
    }

    function toggleSelectAllAssets(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.asset-checkbox');
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        updateBatchRelocateState();
    }

    function updateBatchRelocateState() {
        const checkedBoxes = document.querySelectorAll('.asset-checkbox:checked');
        const batchBtn = document.getElementById('batch_relocate_btn');
        const batchCount = document.getElementById('batch_count');
        const masterCb = document.getElementById('modal_select_all_assets');

        if (batchBtn && batchCount) {
            batchCount.textContent = checkedBoxes.length;
            if (checkedBoxes.length > 0) {
                batchBtn.classList.remove('hidden');
            } else {
                batchBtn.classList.add('hidden');
            }
        }

        const allBoxes = document.querySelectorAll('.asset-checkbox');
        if (masterCb && allBoxes.length > 0) {
            masterCb.checked = checkedBoxes.length === allBoxes.length;
            masterCb.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < allBoxes.length;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function escapeJs(text) {
        if (!text) return '';
        return String(text)
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'")
            .replace(/"/g, '\\"');
    }

    // Auto-open if query param view_location is present
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const viewId = urlParams.get('view_location');
        if (viewId) {
            const locations = @js($locations->items());
            const target = locations.find(l => l.id == viewId);
            if (target) {
                openLocationAssetsModal(target);
            } else {
                fetch('/locations/' + viewId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (data) openLocationAssetsModal(data);
                })
                .catch(() => {});
            }
        }
    });
</script>
@endpush
@endsection
