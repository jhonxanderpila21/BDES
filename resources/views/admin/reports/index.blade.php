@extends('layouts.admin')

@section('title', 'Reports - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Reports</h1>
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Monitoring'],
            ['label' => 'Reports']
        ]" />
    </div>

    {{-- Report Filters --}}
    <x-card class="mb-8">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Report Filters</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-form-group label="From Date">
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="To Date">
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </x-form-group>

                <x-form-group label="Category">
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>All Categories</option>
                        <option>Emergency Equipment</option>
                        <option>Medical Supplies</option>
                        <option>Rescue Equipment</option>
                    </select>
                </x-form-group>

                <x-form-group label="Location">
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>All Locations</option>
                        <option>Main Storage</option>
                        <option>Purok A</option>
                        <option>Medical Center</option>
                    </select>
                </x-form-group>
            </div>

            <div class="flex gap-3 mt-6">
                <button class="flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-filter"></i>
                    Apply Filters
                </button>
                <button class="flex items-center gap-2 px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    <i class="fas fa-redo"></i>
                    Reset
                </button>
            </div>
        </div>
    </x-card>

    {{-- Report Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {{-- Inventory Report --}}
        <x-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="generateReport('inventory')">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Inventory Report</h3>
                        <p class="text-sm text-gray-600">Complete asset inventory with location and status</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100 text-blue-600">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <p class="text-xs text-gray-600 mb-3">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Last generated: 2024-07-15
                    </p>
                    <div class="flex gap-2">
                        <button class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                        <button class="flex-1 px-3 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Deployment Report --}}
        <x-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="generateReport('deployment')">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Deployment Report</h3>
                        <p class="text-sm text-gray-600">Asset deployment history and current assignments</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100 text-purple-600">
                            <i class="fas fa-truck"></i>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <p class="text-xs text-gray-600 mb-3">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Last generated: 2024-07-14
                    </p>
                    <div class="flex gap-2">
                        <button class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                        <button class="flex-1 px-3 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Maintenance Report --}}
        <x-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="generateReport('maintenance')">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Maintenance Report</h3>
                        <p class="text-sm text-gray-600">Maintenance history and scheduled maintenance</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-yellow-100 text-yellow-600">
                            <i class="fas fa-wrench"></i>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <p class="text-xs text-gray-600 mb-3">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Last generated: 2024-07-13
                    </p>
                    <div class="flex gap-2">
                        <button class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                        <button class="flex-1 px-3 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Inspection Report --}}
        <x-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="generateReport('inspection')">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Inspection Report</h3>
                        <p class="text-sm text-gray-600">Inspection results and compliance status</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-cyan-100 text-cyan-600">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <p class="text-xs text-gray-600 mb-3">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Last generated: 2024-07-12
                    </p>
                    <div class="flex gap-2">
                        <button class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                        <button class="flex-1 px-3 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Incident Report --}}
        <x-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="generateReport('incident')">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Incident Report</h3>
                        <p class="text-sm text-gray-600">All incidents and resolution status</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-red-100 text-red-600">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <p class="text-xs text-gray-600 mb-3">
                        <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                        Unresolved: 2 incidents
                    </p>
                    <div class="flex gap-2">
                        <button class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                        <button class="flex-1 px-3 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Asset Lifecycle Report --}}
        <x-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="generateReport('lifecycle')">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Asset Lifecycle</h3>
                        <p class="text-sm text-gray-600">Complete asset lifecycle and condition analysis</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-green-100 text-green-600">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <p class="text-xs text-gray-600 mb-3">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Last generated: 2024-07-10
                    </p>
                    <div class="flex gap-2">
                        <button class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                        <button class="flex-1 px-3 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    @push('scripts')
        <script>
            function generateReport(type) {
                alert(`Generating ${type} report...`);
            }
        </script>
    @endpush
@endsection
