@extends('layouts.admin')

@section('title', 'Assets - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                <i class="fas fa-boxes-stacked text-blue-600"></i>
                Assets
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Asset Management'],
                ['label' => 'Assets']
            ]" />
        </div>

        {{-- Contextual Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2">

            @if($activeTab === 'categories')
                <button onclick="openAddCategoryModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Category
                </button>
            @elseif($activeTab === 'types')
                <button onclick="openAddTypeModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Asset Type
                </button>
            @else
                <button onclick="openAddAssetModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                    Add Asset
                </button>
            @endif
        </div>
    </div>

    {{-- Session Alerts --}}



    {{-- High-Level KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
        {{-- Total Assets --}}
        <a href="{{ route('assets.index', ['tab' => 'assets']) }}" class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:border-blue-300 transition-colors block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Total Assets</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">{{ $metrics['total_assets'] }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Registered Units</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </a>

        {{-- Available Assets --}}
        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'available']) }}" class="bg-white rounded-xl p-4 border border-emerald-200 bg-emerald-50/20 shadow-sm hover:border-emerald-300 transition-colors block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700">Available Quantity</p>
                    <p class="text-xl font-bold text-emerald-700 mt-0.5">{{ $metrics['available_assets'] }} Available</p>
                    <p class="text-[10px] text-emerald-600 mt-0.5">Ready for immediate dispatch</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </a>

        {{-- Deployed Assets --}}
        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'deployed']) }}" class="bg-white rounded-xl p-4 border border-blue-200 bg-blue-50/20 shadow-sm hover:border-blue-300 transition-colors block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-blue-700">Deployed</p>
                    <p class="text-xl font-bold text-blue-700 mt-0.5">{{ $metrics['deployed_assets'] ?? 0 }}</p>
                    <p class="text-[10px] text-blue-600 mt-0.5">In disaster field</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </a>

        {{-- Under Maintenance --}}
        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'under_maintenance']) }}" class="bg-white rounded-xl p-4 border border-amber-200 bg-amber-50/20 shadow-sm hover:border-amber-300 transition-colors block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-700">Maintenance</p>
                    <p class="text-xl font-bold text-amber-700 mt-0.5">{{ $metrics['under_maintenance_assets'] ?? 0 }}</p>
                    <p class="text-[10px] text-amber-600 mt-0.5">Active servicing</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fas fa-wrench"></i>
                </div>
            </div>
        </a>

        {{-- Out of Service / Lost --}}
        <a href="{{ route('assets.index', ['tab' => 'assets', 'status' => 'out_of_service']) }}" class="bg-white rounded-xl p-4 border border-rose-200 bg-rose-50/20 shadow-sm hover:border-rose-300 transition-colors block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-rose-700">Out of Service</p>
                    <p class="text-xl font-bold text-rose-700 mt-0.5">{{ ($metrics['out_of_service_assets'] ?? 0) + ($metrics['lost_assets'] ?? 0) }}</p>
                    <p class="text-[10px] text-rose-600 mt-0.5">Defective / Lost</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-sm">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
        </a>
    </div>

    {{-- Main Module Tabs Navigation --}}
    <div class="flex border-b border-gray-200 mb-6 space-x-1">
        <a 
            href="{{ route('assets.index', ['tab' => 'assets']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'assets' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-boxes-stacked"></i>
            <span>Asset Inventory</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'assets' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $assets->total() }}
            </span>
        </a>

        <a 
            href="{{ route('assets.index', ['tab' => 'categories']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'categories' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-folder-open"></i>
            <span>Categories</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'categories' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $categoriesList->total() }}
            </span>
        </a>

        <a 
            href="{{ route('assets.index', ['tab' => 'types']) }}"
            class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'types' ? 'border-blue-600 text-blue-600 bg-blue-50/40 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            <i class="fas fa-tags"></i>
            <span>Asset Types</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'types' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $typesList->total() }}
            </span>
        </a>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: ASSET INVENTORY                                                    --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'assets')
        {{-- Filters & Search --}}
        <form action="{{ route('assets.index') }}" method="GET" class="flex flex-col md:flex-row flex-wrap gap-3 mb-6">
            <input type="hidden" name="tab" value="assets">

            <div class="flex-1 min-w-[220px]">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search code, name, serial number, brand..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="category_id" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select name="location_id" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Locations</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>

            <select name="condition" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Conditions</option>
                @foreach(['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor'] as $key => $label)
                    <option value="{{ $key }}" {{ request('condition') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Statuses</option>
                @foreach(\App\Services\AssetLifecycleService::statuses() as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="sort_by" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="id" {{ request('sort_by') === 'id' ? 'selected' : '' }}>Sort: Default</option>
                <option value="code" {{ request('sort_by') === 'code' ? 'selected' : '' }}>Sort: Asset Code</option>
                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Sort: Name</option>
                <option value="purchase_date" {{ request('sort_by') === 'purchase_date' ? 'selected' : '' }}>Sort: Purchase Date</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                Filter
            </button>

            @if(request()->hasAny(['search', 'category_id', 'location_id', 'type_id', 'condition', 'status', 'sort_by']))
                <a href="{{ route('assets.index', ['tab' => 'assets']) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    Reset
                </a>
            @endif
        </form>

        {{-- Assets Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Asset Code</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Asset Name</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Category / Type</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Serial Number</th>
                            <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Condition</th>
                            <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Location</th>
                            <th class="px-5 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-blue-50/50 cursor-pointer transition-colors group"
                                onclick="if(!event.target.closest('button, a, form, input')) openViewAssetModal(@js($asset), @js($asset->category->name ?? 'N/A'), @js($asset->type->name ?? 'N/A'), @js($asset->location->name ?? 'N/A'));"
                                title="Click to view {{ $asset->name }} details">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <a href="{{ route('assets.show', $asset) }}" class="font-mono text-xs font-bold text-blue-700 bg-blue-50 group-hover:bg-blue-100 border border-blue-200 px-2.5 py-1 rounded-lg transition-colors">
                                        {{ $asset->code }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <a href="{{ route('assets.show', $asset) }}" class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors block">
                                        {{ $asset->name }}
                                    </a>
                                    @if($asset->manufacturer)
                                        <p class="text-[11px] text-gray-400">
                                            {{ $asset->manufacturer }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-600">
                                    <p class="font-medium text-gray-800 text-xs">{{ $asset->category->name ?? 'Uncategorized' }}</p>
                                    @if($asset->type)
                                        <p class="text-[11px] text-gray-500 flex items-center gap-1 mt-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>
                                            {{ $asset->type->name }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono text-gray-700">
                                    {{ $asset->serial_number ?: '—' }}
                                </td>
                                <td class="px-5 py-3.5 align-middle whitespace-nowrap text-center">
                                    @php
                                        $conditionColor = match($asset->condition) {
                                            'excellent', 'good' => 'success',
                                            'fair' => 'warning',
                                            'poor' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <x-badge variant="{{ $conditionColor }}" size="sm">{{ ucfirst($asset->condition) }}</x-badge>
                                </td>
                                <td class="px-5 py-3.5 align-middle whitespace-nowrap text-center">
                                    @php
                                        $statusColor = match($asset->status) {
                                            'available'         => 'success',
                                            'deployed'          => 'info',
                                            'under_maintenance' => 'warning',
                                            'out_of_service'    => 'danger',
                                            'lost'              => 'danger',
                                            'retired'           => 'secondary',
                                            default             => 'secondary'
                                        };
                                    @endphp
                                    <x-badge variant="{{ $statusColor }}" size="sm">
                                        @if($asset->status === 'available')
                                            <i class="fas fa-check mr-1 text-[10px]"></i> 1 Available
                                        @else
                                            {{ \App\Services\AssetLifecycleService::statuses()[$asset->status] ?? ucfirst(str_replace('_', ' ', $asset->status)) }}
                                        @endif
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3.5 align-middle whitespace-nowrap text-xs text-gray-600 text-left">
                                    {{ $asset->location->name ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 align-middle whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Quick View Modal --}}
                                        <button 
                                            onclick="openViewAssetModal({{ json_encode($asset) }}, '{{ $asset->category->name ?? 'N/A' }}', '{{ $asset->type->name ?? 'N/A' }}', '{{ $asset->location->name ?? 'N/A' }}')" 
                                            class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors" 
                                            title="Quick View"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        {{-- Edit Modal --}}
                                        <button 
                                            onclick="openEditAssetModal({{ json_encode($asset) }})" 
                                            class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600 hover:text-amber-800 transition-colors" 
                                            title="Edit Asset"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Safe Delete --}}
                                        <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="inline-block" onsubmit="return confirm('Remove asset [{{ $asset->code }}]? If operational records exist, it will be safely retired instead.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <x-empty-state icon="fas fa-boxes-stacked" title="No assets found" description="Start by clicking Add Asset above to register new equipment." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $assets->links() }}
            </div>
        </x-card>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: ASSET CATEGORIES                                                   --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'categories')
        {{-- Filters & Search --}}
        <form action="{{ route('assets.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="categories">

            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search category name or description..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="status" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                Filter
            </button>

            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('assets.index', ['tab' => 'categories']) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    Reset
                </a>
            @endif
        </form>

        {{-- Categories Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Assets Registered</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($categoriesList as $category)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm font-semibold text-gray-900">{{ $category->name }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                    {{ $category->description ?: 'No description provided' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $category->assets_count }} assets
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if(($category->available_assets_count ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <i class="fas fa-check-circle text-[10px] text-emerald-600"></i> {{ $category->available_assets_count }} Available for Deployment
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                            <i class="fas fa-ban text-[10px] text-gray-400"></i> 0 Available for Deployment
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            onclick="openEditCategoryModal({{ $category->id }}, @js($category->name), @js($category->description), @js($category->status))"
                                            class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                            title="Edit Category"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('asset-categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('Archive/Delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Category">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <x-empty-state icon="fas fa-folder-open" title="No categories found" description="Create your first asset category using the Add Category button above." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $categoriesList->links() }}
            </div>
        </x-card>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 3: ASSET TYPES                                                        --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'types')
        {{-- Filters & Search --}}
        <form action="{{ route('assets.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 mb-6">
            <input type="hidden" name="tab" value="types">

            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search asset type or parent category..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <select name="category_id" onchange="this.form.submit()" class="pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                Filter
            </button>

            @if(request()->hasAny(['search', 'category_id']))
                <a href="{{ route('assets.index', ['tab' => 'types']) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center">
                    Reset
                </a>
            @endif
        </form>

        {{-- Asset Types Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Assets Registered</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($typesList as $type)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="font-semibold text-gray-900">{{ $type->name }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                                    {{ $type->category->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $type->assets_count }} assets
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if(($type->available_assets_count ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <i class="fas fa-check-circle text-[10px] text-emerald-600"></i> {{ $type->available_assets_count }} Available for Deployment
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                            <i class="fas fa-ban text-[10px] text-gray-400"></i> 0 Available for Deployment
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            onclick="openEditTypeModal({{ $type->id }}, @js($type->name), {{ $type->category_id ?? 'null' }}, {{ $type->serialized ? 'true' : 'false' }}, {{ $type->borrowable ? 'true' : 'false' }}, {{ $type->requires_maintenance ? 'true' : 'false' }}, {{ $type->requires_inspection ? 'true' : 'false' }}, {{ $type->has_accessories ? 'true' : 'false' }}, {{ $type->has_attachments ? 'true' : 'false' }}, {{ $type->requires_operational_requirements ? 'true' : 'false' }})"
                                            class="p-1.5 rounded hover:bg-gray-100 text-amber-600 hover:text-amber-800 transition-colors" 
                                            title="Edit Type"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('asset-types.destroy', $type) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this asset type?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded hover:bg-gray-100 text-red-600 hover:text-red-800 transition-colors" title="Delete Type">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <x-empty-state icon="fas fa-tags" title="No asset types found" description="Create your first asset type using the Add Asset Type button above." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $typesList->links() }}
            </div>
        </x-card>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODALS: ASSETS                                                            --}}
    {{-- ========================================================================= --}}
    @include('admin.assets.partials._add_asset_modal')


    {{-- Edit Asset Modal --}}
    <x-modal id="edit-asset-modal" title="Edit Asset">
        <form id="edit-asset-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Asset Code" required>
                    <input type="text" name="code" id="edit_asset_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase" oninput="this.value = this.value.toUpperCase()">
                </x-form-group>

                <x-form-group label="Asset Name" required>
                    <input type="text" name="name" id="edit_asset_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Category" required>
                    <select name="category_id" id="edit_asset_category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Asset Type">
                    <select name="type_id" id="edit_asset_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- None --</option>
                        @foreach($types as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Serial Number">
                    <input type="text" name="serial_number" id="edit_asset_serial_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase" oninput="this.value = this.value.toUpperCase()">
                </x-form-group>

                <x-form-group label="Manufacturer">
                    <input type="text" name="manufacturer" id="edit_asset_manufacturer" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Condition" required>
                    <select name="condition" id="edit_asset_condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="excellent">Excellent</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                </x-form-group>

                <x-form-group label="Operational Status" required>
                    <select name="status" id="edit_asset_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="available">Available</option>
                        <option value="deployed">Deployed</option>
                        <option value="under_maintenance">Under Maintenance</option>
                        <option value="out_of_service">Out of Service</option>
                        <option value="lost">Lost / Missing</option>
                        <option value="retired">Retired / Decommissioned</option>
                    </select>
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form-group label="Location" required>
                    <select name="location_id" id="edit_asset_location_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>

                <x-form-group label="Acquisition Cost (PHP)">
                    <input type="number" step="0.01" min="0" inputmode="decimal" name="acquisition_cost" id="edit_asset_acquisition_cost" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>

                <x-form-group label="Purchase Date">
                    <input type="date" name="purchase_date" id="edit_asset_purchase_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Warranty Expiry">
                    <input type="date" name="warranty_expiry" id="edit_asset_warranty_expiry" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </x-form-group>
            </div>

            <x-form-group label="Notes">
                <textarea name="notes" id="edit_asset_notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </x-form-group>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-asset-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Retire Asset Modal --}}
    <x-modal id="retire-asset-modal" title="Decommission / Retire Asset">
        <form id="retire-asset-form" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs text-gray-600">
                Retiring <strong id="retire_asset_label" class="text-gray-900"></strong> will mark it as decommissioned from active disaster service. Its complete operational history, maintenance, and deployment records will remain immutably preserved.
            </p>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Reason for Decommissioning *</label>
                <textarea name="retirement_reason" required rows="3" placeholder="Provide detailed justification (e.g. beyond economic repair, damaged in typhoon, obsolete)..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
            </div>
            <div class="flex gap-3 pt-3 border-t border-gray-200">
                <x-button variant="danger" type="submit" class="flex-1">Confirm Decommissioning</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('retire-asset-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- View Asset Modal --}}
    <x-modal id="view-asset-modal" title="Equipment Details">
        <div class="space-y-5">
            {{-- Header Profile Card --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fas fa-box-archive"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 id="view_asset_name" class="text-base font-bold text-gray-900"></h3>
                            <span id="view_asset_code" class="text-xs font-mono font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded"></span>
                        </div>
                        <p id="view_asset_type_category" class="text-xs text-gray-500 mt-0.5">Category · Type</p>
                    </div>
                </div>
                <div id="view_asset_status_badge"></div>
            </div>

            {{-- 2-Column Clean Key Facts --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-2xs">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Assigned Facility / Location</span>
                    <p id="view_asset_location" class="font-semibold text-gray-800 mt-0.5 flex items-center gap-1.5 text-xs sm:text-sm">
                        <i class="fas fa-location-dot text-blue-500 text-xs"></i>
                        <span></span>
                    </p>
                </div>

                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-2xs">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Operational Condition</span>
                    <p id="view_asset_condition" class="font-semibold text-gray-800 mt-0.5 capitalize text-xs sm:text-sm"></p>
                </div>

                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-2xs">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Serial Number / Brand</span>
                    <p class="font-semibold text-gray-800 mt-0.5 text-xs sm:text-sm">
                        <span id="view_asset_serial" class="font-mono text-gray-900"></span>
                        <span id="view_asset_manufacturer" class="text-gray-500 font-normal ml-1"></span>
                    </p>
                </div>

                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-2xs">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Acquisition Cost & Date</span>
                    <p class="font-semibold text-gray-800 mt-0.5 text-xs sm:text-sm">
                        <span id="view_asset_cost" class="font-bold text-emerald-700"></span>
                        <span id="view_asset_purchase_date" class="text-gray-400 font-normal ml-1 text-xs"></span>
                    </p>
                </div>
            </div>

            {{-- Operational Notes Callout --}}
            <div id="view_asset_notes_container" class="bg-blue-50/50 p-3.5 rounded-xl border border-blue-100">
                <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block mb-1">
                    <i class="fas fa-file-lines text-blue-500 mr-1"></i> Notes & Remarks
                </span>
                <p id="view_asset_notes" class="text-xs text-gray-700 leading-relaxed"></p>
            </div>

            {{-- Modal Actions --}}
            <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <a id="view_asset_dossier_btn" href="#" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-colors w-full sm:w-auto shadow-sm">
                        <i class="fas fa-folder-open"></i>
                        <span>Open Full Dossier</span>
                    </a>
                    <a id="view_asset_deploy_btn" href="#" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition-colors hidden sm:inline-flex">
                        <i class="fas fa-truck-fast"></i>
                        <span>Deploy</span>
                    </a>
                </div>
                <button type="button" onclick="window.closeModal('view-asset-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-medium transition-colors w-full sm:w-auto">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    {{-- ========================================================================= --}}
    {{-- MODALS: CATEGORIES                                                        --}}
    {{-- ========================================================================= --}}
    {{-- Add Category Modal --}}
    <x-modal id="add-category-modal" title="Add Category">
        <form class="space-y-4" action="{{ route('asset-categories.store') }}" method="POST">
            @csrf

            <x-form-group label="Category Name" required>
                <input type="text" name="name" placeholder="e.g., Emergency Response Equipment" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <x-form-group label="Description">
                <textarea name="description" placeholder="Brief description..." rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </x-form-group>

            <x-form-group label="Status" required>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </x-form-group>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <x-button variant="primary" type="submit" class="flex-1">Create Category</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-category-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Category Modal --}}
    <x-modal id="edit-category-modal" title="Edit Category">
        <form class="space-y-4" id="edit-category-form" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Category Name" required>
                <input type="text" name="name" id="edit-category-name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </x-form-group>

            <x-form-group label="Description">
                <textarea name="description" id="edit-category-description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </x-form-group>

            <x-form-group label="Status" required>
                <select name="status" id="edit-category-status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </x-form-group>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <x-button variant="primary" type="submit" class="flex-1">Save Changes</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-category-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- ========================================================================= --}}
    {{-- MODALS: ASSET TYPES                                                       --}}
    {{-- ========================================================================= --}}
    {{-- Add Type Modal --}}
    <x-modal id="add-type-modal" title="Add Asset Type">
        <form class="space-y-4" action="{{ route('asset-types.store') }}" method="POST">
            @csrf

            <x-form-group label="Type Name" required>
                <input
                    type="text"
                    name="name"
                    placeholder="e.g., Rescue Vehicle"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value="{{ old('name') }}"
                >
            </x-form-group>

            <x-form-group label="Category" required>
                <select
                    name="category_id"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                >
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="space-y-3 border border-gray-300 rounded-lg p-4 bg-gray-50">
                <p class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Type Configuration Rules</p>

                <input type="hidden" name="serialized" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="serialized" value="1" class="w-4 h-4 text-blue-600 rounded" {{ old('serialized') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Serialized (Individually Tracked by Serial Number)</span>
                </label>

                <input type="hidden" name="borrowable" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="borrowable" value="1" class="w-4 h-4 text-blue-600 rounded" {{ old('borrowable') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Borrowable / Deployable</span>
                </label>

                <input type="hidden" name="requires_maintenance" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_maintenance" value="1" class="w-4 h-4 text-blue-600 rounded" {{ old('requires_maintenance') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Requires Maintenance</span>
                </label>

                <input type="hidden" name="requires_inspection" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_inspection" value="1" class="w-4 h-4 text-blue-600 rounded" {{ old('requires_inspection') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Requires Routine Inspection</span>
                </label>

                <input type="hidden" name="has_accessories" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_accessories" value="1" class="w-4 h-4 text-blue-600 rounded" {{ old('has_accessories') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Has Accessories</span>
                </label>

                <input type="hidden" name="has_attachments" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_attachments" value="1" class="w-4 h-4 text-blue-600 rounded" {{ old('has_attachments') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Has Attachments</span>
                </label>

                <input type="hidden" name="requires_operational_requirements" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_operational_requirements" value="1" class="w-4 h-4 text-blue-600 rounded" {{ old('requires_operational_requirements') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Requires Operational Consumables / Requirements</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <x-button variant="primary" class="flex-1" type="submit">Create Type</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('add-type-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Type Modal --}}
    <x-modal id="edit-type-modal" title="Edit Asset Type">
        <form class="space-y-4" id="edit-type-form" method="POST">
            @csrf
            @method('PUT')

            <x-form-group label="Type Name" required>
                <input
                    type="text"
                    name="name"
                    id="edit-type-name"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </x-form-group>

            <x-form-group label="Category" required>
                <select
                    name="category_id"
                    id="edit-type-category"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                >
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <div class="space-y-3 border border-gray-300 rounded-lg p-4 bg-gray-50">
                <input type="hidden" name="serialized" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="serialized" id="edit-type-serialized" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Serialized</span>
                </label>

                <input type="hidden" name="borrowable" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="borrowable" id="edit-type-borrowable" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Borrowable</span>
                </label>

                <input type="hidden" name="requires_maintenance" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_maintenance" id="edit-type-requires_maintenance" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Maintenance</span>
                </label>

                <input type="hidden" name="requires_inspection" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_inspection" id="edit-type-requires_inspection" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Inspection</span>
                </label>

                <input type="hidden" name="has_accessories" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_accessories" id="edit-type-has_accessories" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Has Accessories</span>
                </label>

                <input type="hidden" name="has_attachments" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="has_attachments" id="edit-type-has_attachments" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Has Attachments</span>
                </label>

                <input type="hidden" name="requires_operational_requirements" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="requires_operational_requirements" id="edit-type-requires_operational_requirements" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Requires Operational Requirements</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <x-button variant="primary" class="flex-1" type="submit">Save Changes</x-button>
                <x-button variant="secondary" type="button" onclick="window.closeModal('edit-type-modal')" class="flex-1">Cancel</x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            // Asset Modals
            function openAddAssetModal() {
                window.openModal('add-asset-modal');
            }

            function openEditAssetModal(asset) {
                const form = document.getElementById('edit-asset-form');
                form.action = `/assets/${asset.id}`;
                document.getElementById('edit_asset_code').value = asset.code || '';
                document.getElementById('edit_asset_name').value = asset.name || '';
                document.getElementById('edit_asset_category_id').value = asset.category_id || '';
                document.getElementById('edit_asset_type_id').value = asset.type_id || '';
                document.getElementById('edit_asset_serial_number').value = asset.serial_number || '';
                document.getElementById('edit_asset_manufacturer').value = asset.manufacturer || '';
                document.getElementById('edit_asset_condition').value = asset.condition || 'good';
                document.getElementById('edit_asset_status').value = asset.status || 'available';
                document.getElementById('edit_asset_location_id').value = asset.location_id || '';
                document.getElementById('edit_asset_acquisition_cost').value = asset.acquisition_cost || '';
                document.getElementById('edit_asset_purchase_date').value = asset.purchase_date ? asset.purchase_date.substring(0, 10) : '';
                document.getElementById('edit_asset_warranty_expiry').value = asset.warranty_expiry ? asset.warranty_expiry.substring(0, 10) : '';
                document.getElementById('edit_asset_notes').value = asset.notes || '';
                window.openModal('edit-asset-modal');
            }

            function openRetireAssetModal(id, code, name) {
                const form = document.getElementById('retire-asset-form');
                form.action = `/assets/${id}/retire`;
                document.getElementById('retire_asset_label').textContent = `${name} (${code})`;
                window.openModal('retire-asset-modal');
            }

            function openViewAssetModal(asset, categoryName, typeName, locationName) {
                document.getElementById('view_asset_name').textContent = asset.name || 'Unnamed Asset';
                document.getElementById('view_asset_code').textContent = asset.code || '';
                
                const typeCatElem = document.getElementById('view_asset_type_category');
                if (typeCatElem) {
                    typeCatElem.textContent = (categoryName || 'General') + (typeName && typeName !== 'N/A' ? ' · ' + typeName : '');
                }

                document.getElementById('view_asset_serial').textContent = asset.serial_number ? 'S/N: ' + asset.serial_number : 'Unserialized';
                document.getElementById('view_asset_manufacturer').textContent = asset.manufacturer ? '· ' + asset.manufacturer : '';
                
                const locElem = document.getElementById('view_asset_location');
                if (locElem) {
                    const locSpan = locElem.querySelector('span');
                    if (locSpan) locSpan.textContent = locationName || 'Not assigned';
                }
                
                const condElem = document.getElementById('view_asset_condition');
                const cond = asset.condition || 'good';
                condElem.textContent = cond.charAt(0).toUpperCase() + cond.slice(1);

                const costFormatted = asset.acquisition_cost ? '₱' + parseFloat(asset.acquisition_cost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '₱0.00';
                document.getElementById('view_asset_cost').textContent = costFormatted;
                
                const dateText = asset.purchase_date ? '• ' + asset.purchase_date.substring(0, 10) : '';
                document.getElementById('view_asset_purchase_date').textContent = dateText;

                const notesElem = document.getElementById('view_asset_notes');
                const notesContainer = document.getElementById('view_asset_notes_container');
                if (asset.notes && asset.notes.trim() !== '') {
                    notesElem.textContent = asset.notes;
                    notesContainer.classList.remove('hidden');
                } else {
                    notesContainer.classList.add('hidden');
                }

                const statusBadge = document.getElementById('view_asset_status_badge');
                let statusClasses = 'bg-gray-100 text-gray-700';
                let displayStatus = (asset.status || '').replace(/_/g, ' ').toUpperCase();
                if (asset.status === 'available') {
                    statusClasses = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                    displayStatus = '1 AVAILABLE (READY)';
                } else if (asset.status === 'deployed') {
                    statusClasses = 'bg-blue-100 text-blue-800 border border-blue-200';
                } else if (asset.status === 'under_maintenance') {
                    statusClasses = 'bg-amber-100 text-amber-800 border border-amber-200';
                } else if (asset.status === 'out_of_service' || asset.status === 'lost') {
                    statusClasses = 'bg-rose-100 text-rose-800 border border-rose-200';
                } else if (asset.status === 'retired') {
                    statusClasses = 'bg-gray-200 text-gray-800 border border-gray-300';
                }
                statusBadge.innerHTML = `<span class="px-2.5 py-1 text-xs font-bold rounded-full ${statusClasses}">${displayStatus}</span>`;

                const dossierBtn = document.getElementById('view_asset_dossier_btn');
                if (dossierBtn) {
                    dossierBtn.href = '/assets/' + asset.id;
                }

                const deployBtn = document.getElementById('view_asset_deploy_btn');
                if (deployBtn) {
                    if (asset.status === 'available') {
                        deployBtn.href = '/deployment/create?asset_id=' + asset.id;
                        deployBtn.classList.remove('hidden');
                    } else {
                        deployBtn.classList.add('hidden');
                    }
                }

                window.openModal('view-asset-modal');
            }

            // Category Modals
            function openAddCategoryModal() {
                window.openModal('add-category-modal');
            }

            function openEditCategoryModal(id, name, description, status) {
                const form = document.getElementById('edit-category-form');
                form.action = `/asset-categories/${id}`;
                document.getElementById('edit-category-name').value = name;
                document.getElementById('edit-category-description').value = description ?? '';
                document.getElementById('edit-category-status').value = status;
                window.openModal('edit-category-modal');
            }

            // Type Modals
            function openAddTypeModal() {
                window.openModal('add-type-modal');
            }

            function openEditTypeModal(id, name, categoryId, serialized, borrowable, requiresMaintenance, requiresInspection, hasAccessories, hasAttachments, requiresOperationalRequirements) {
                const form = document.getElementById('edit-type-form');
                form.action = `/asset-types/${id}`;
                document.getElementById('edit-type-name').value = name;
                document.getElementById('edit-type-category').value = categoryId ?? '';
                document.getElementById('edit-type-serialized').checked = Boolean(serialized);
                document.getElementById('edit-type-borrowable').checked = Boolean(borrowable);
                document.getElementById('edit-type-requires_maintenance').checked = Boolean(requiresMaintenance);
                document.getElementById('edit-type-requires_inspection').checked = Boolean(requiresInspection);
                document.getElementById('edit-type-has_accessories').checked = Boolean(hasAccessories);
                document.getElementById('edit-type-has_attachments').checked = Boolean(hasAttachments);
                document.getElementById('edit-type-requires_operational_requirements').checked = Boolean(requiresOperationalRequirements);
                window.openModal('edit-type-modal');
            }
        </script>
    @endpush
@endsection
