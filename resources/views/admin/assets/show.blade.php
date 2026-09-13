@extends('layouts.admin')

@section('title', 'Asset Details: ' . $asset->name . ' - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('assets.index', ['tab' => 'assets']) }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $asset->name }}</h1>
                <span class="font-mono text-sm font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded">
                    {{ $asset->code }}
                </span>
            </div>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Assets', 'url' => route('assets.index')],
                ['label' => $asset->name]
            ]" />
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('assets.index', ['tab' => 'assets']) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                Back to Assets
            </a>
        </div>
    </div>

    {{-- Details Card --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Primary Info --}}
        <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <i class="fas fa-info-circle text-blue-600"></i>
                Asset Overview
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 text-xs font-semibold uppercase block">Category</span>
                    <span class="font-medium text-gray-900">{{ $asset->category->name ?? 'Uncategorized' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-semibold uppercase block">Asset Type</span>
                    <span class="font-medium text-gray-900">{{ $asset->type->name ?? 'None' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-semibold uppercase block">Serial Number</span>
                    <span class="font-mono text-gray-900">{{ $asset->serial_number ?: 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-semibold uppercase block">Manufacturer</span>
                    <span class="text-gray-900">{{ $asset->manufacturer ?: 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-semibold uppercase block">Location</span>
                    <span class="text-gray-900">{{ $asset->location->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-semibold uppercase block">Condition</span>
                    <span class="capitalize text-gray-900 font-medium">{{ $asset->condition }}</span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-semibold uppercase block">Purchase Date</span>
                    <span class="text-gray-900">{{ $asset->purchase_date ? $asset->purchase_date->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs font-semibold uppercase block">Warranty Expiry</span>
                    <span class="text-gray-900">{{ $asset->warranty_expiry ? $asset->warranty_expiry->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>

            @if($asset->notes)
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <span class="text-gray-500 text-xs font-semibold uppercase block mb-1">Notes & Specifications</span>
                    <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg leading-relaxed">{{ $asset->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Status Summary Card --}}
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">
                    Operational Status
                </h2>

                <div class="mb-4">
                    <span class="text-gray-500 text-xs font-semibold uppercase block mb-2">Current State</span>
                    @php
                        $statusColor = match($asset->status) {
                            'available' => 'success',
                            'deployed' => 'info',
                            'under_maintenance' => 'warning',
                            'out_of_service' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <x-badge variant="{{ $statusColor }}" size="lg">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</x-badge>
                </div>

                <div class="space-y-3 pt-4 border-t border-gray-100 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Maintenance Logs:</span>
                        <span class="font-bold text-gray-800">{{ $asset->maintenance->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Inspections:</span>
                        <span class="font-bold text-gray-800">{{ $asset->inspections->count() }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100">
                <a href="{{ route('maintenance.index', ['tab' => 'logs']) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium text-sm transition-colors">
                    <i class="fas fa-screwdriver-wrench"></i> View Maintenance Logs
                </a>
            </div>
        </div>
    </div>
@endsection

