@extends('layouts.admin')

@section('title', 'Dashboard - BDES')

@section('content')
    <!-- DEBUG: begin dashboard content -->
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
        <p class="text-gray-600">Welcome back! Here's your asset inventory overview</p>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8 auto-rows-fr items-stretch">
        <x-statistic-card
            title="Total Assets"
            value="{{ number_format($stats['total_assets']) }}"
            icon="fas fa-box"
            color="blue"
            trend="Real-time"
            trendDirection="up"
            :href="route('assets.index')"
        />
        <x-statistic-card
            title="Available Assets"
            value="{{ number_format($stats['available']) }}"
            icon="fas fa-check-circle"
            color="green"
            trend="Ready for use"
            trendDirection="up"
            :href="route('assets.index', ['status' => 'available'])"
        />
        <x-statistic-card
            title="Deployed Assets"
            value="{{ number_format($stats['deployed']) }}"
            icon="fas fa-truck"
            color="purple"
            trend="In field"
            trendDirection="up"
            :href="route('deployment.index')"
        />
        <x-statistic-card
            title="Under Maintenance"
            value="{{ number_format($stats['under_maintenance']) }}"
            icon="fas fa-wrench"
            color="yellow"
            trend="Needs repair"
            trendDirection="up"
            :href="route('maintenance.index')"
        />
        <x-statistic-card
            title="Due for Inspection"
            value="{{ number_format($stats['due_for_inspection']) }}"
            icon="fas fa-clipboard-check"
            color="red"
            trend="Next 7 days"
            trendDirection="down"
            :href="route('inspection.index')"
        />
    </div>

    {{-- Charts and Reminders --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Charts Section --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Assets by Category Chart --}}
            <x-card>
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Assets by Category</h2>
                </div>
                <div class="p-6">
                    @if(!empty($assetsByCategory['labels']))
                        <div class="w-full h-80">
                            <canvas data-chart="assets-by-category"></canvas>
                        </div>
                        <script type="application/json" id="assets-by-category-data">{!! json_encode($assetsByCategory, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

                        {{-- Non-color view of the same data, for screen readers and print. --}}
                        <details class="mt-4">
                            <summary class="text-sm text-blue-600 hover:text-blue-800 cursor-pointer">View as table</summary>
                            <table class="w-full mt-3 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-600 border-b border-gray-200">
                                        <th class="py-2 font-medium">Category</th>
                                        <th class="py-2 font-medium text-right">Assets</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($assetsByCategory['labels'] as $index => $label)
                                        <tr>
                                            <td class="py-2 text-gray-900">{{ $label }}</td>
                                            <td class="py-2 text-right text-gray-900 tabular-nums">{{ number_format($assetsByCategory['data'][$index]) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </details>
                    @else
                        <div class="w-full h-80 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-chart-pie text-5xl text-gray-300 mb-4 block"></i>
                                <p class="text-gray-500">No category data yet</p>
                                <p class="text-xs text-gray-400 mt-2">Add assets to an active category to see this chart</p>
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Right Sidebar --}}
        <div class="space-y-6">
            {{-- Upcoming Inspections --}}
            <x-card>
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Upcoming Inspections</h2>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($upcomingInspections as $inspection)
                        <a href="{{ route('inspection.show', $inspection) }}" class="flex items-start gap-3 rounded-md transition-colors hover:bg-gray-50 {{ !$loop->last ? 'pb-4 border-b border-gray-200' : '' }}">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $inspection->asset->name ?? 'Unknown Asset' }}</p>
                                <p class="text-xs text-gray-600">Code: {{ $inspection->asset->code ?? 'N/A' }}</p>
                                <p class="text-xs text-red-600 font-medium mt-1">Due: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('M d, Y') }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500 italic">No upcoming inspections.</p>
                    @endforelse
                </div>
            </x-card>

            {{-- Maintenance Reminders --}}
            <x-card>
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Maintenance Reminders</h2>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($maintenanceReminders as $reminder)
                        <a href="{{ route('maintenance.show', $reminder) }}" class="flex items-start gap-3 rounded-md transition-colors hover:bg-gray-50">
                            <i class="fas fa-circle text-{{ $reminder->status == 'pending' ? 'red' : 'yellow' }}-500 text-xs mt-1.5 flex-shrink-0"></i>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">{{ $reminder->asset->name ?? 'Unknown Asset' }} - {{ ucfirst($reminder->type) }}</p>
                                <p class="text-xs text-gray-600">Scheduled: {{ $reminder->scheduled_date ? \Carbon\Carbon::parse($reminder->scheduled_date)->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500 italic">No maintenance reminders.</p>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="mb-8">
        {{-- Recent Activities Table --}}
        <x-card>
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Recent Activities</h2>
                <a href="{{ route('asset-history.index') }}" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recentActivities as $activity)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-{{ $activity['color'] }}-100 text-{{ $activity['color'] }}-600">
                                                <i class="{{ $activity['icon'] }} text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                                            <p class="text-xs text-gray-600">{{ $activity['description'] }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500 italic text-center">No recent activities found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    @push('scripts')
        @vite('resources/js/dashboard-charts.js')
    @endpush
@endsection
