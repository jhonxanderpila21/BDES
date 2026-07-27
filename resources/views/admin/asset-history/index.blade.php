@extends('layouts.admin')

@section('title', 'Asset History - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Asset History</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Monitoring'],
                ['label' => 'Asset History']
            ]" />
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <x-search-bar placeholder="Search by asset code or name..." />
        </div>
        <x-filter-dropdown 
            label="Filter by Event Type"
            :options="['creation' => 'Asset Created', 'deployment' => 'Deployment', 'return' => 'Return', 'maintenance' => 'Maintenance', 'inspection' => 'Inspection', 'incident' => 'Incident']"
        />
    </div>

    {{-- Timeline Container --}}
    <div class="space-y-6">
        {{-- Asset History Entry --}}
        @php
            $historyEvents = [
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Asset Created', 'date' => '2024-01-15', 'time' => '10:30 AM', 'details' => 'Initial asset entry into system', 'user' => 'Admin User', 'status' => 'Completed'],
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Deployed', 'date' => '2024-06-20', 'time' => '14:15 PM', 'details' => 'Deployed to Purok A Fire Station', 'user' => 'Carlos Rodriguez', 'status' => 'Completed'],
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Maintenance', 'date' => '2024-07-01', 'time' => '09:00 AM', 'details' => 'Refill and pressure check performed', 'user' => 'Maintenance Team', 'status' => 'Completed'],
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Returned', 'date' => '2024-07-12', 'time' => '16:45 PM', 'details' => 'Returned from deployment - Good condition', 'user' => 'Juan Santos', 'status' => 'Completed'],
                ['asset' => 'Fire Extinguisher #001', 'event' => 'Inspection', 'date' => '2024-07-15', 'time' => '11:20 AM', 'details' => 'Quarterly inspection passed', 'user' => 'Ana Luna', 'status' => 'Completed'],
            ];
        @endphp

        @forelse($historyEvents as $event)
            <x-card>
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        {{-- Timeline Dot --}}
                        <div class="flex-shrink-0 relative">
                            @php
                                $eventColor = match($event['event']) {
                                    'Asset Created' => 'bg-blue-100 text-blue-600',
                                    'Deployed' => 'bg-purple-100 text-purple-600',
                                    'Returned' => 'bg-green-100 text-green-600',
                                    'Maintenance' => 'bg-yellow-100 text-yellow-600',
                                    'Inspection' => 'bg-cyan-100 text-cyan-600',
                                    'Incident' => 'bg-red-100 text-red-600',
                                    default => 'bg-gray-100 text-gray-600'
                                };

                                $eventIcon = match($event['event']) {
                                    'Asset Created' => 'fas fa-plus',
                                    'Deployed' => 'fas fa-truck',
                                    'Returned' => 'fas fa-undo',
                                    'Maintenance' => 'fas fa-wrench',
                                    'Inspection' => 'fas fa-clipboard-check',
                                    'Incident' => 'fas fa-exclamation-triangle',
                                    default => 'fas fa-circle'
                                };
                            @endphp
                            <div class="flex items-center justify-center h-12 w-12 rounded-full {{ $eventColor }}">
                                <i class="{{ $eventIcon }}"></i>
                            </div>
                        </div>

                        {{-- Event Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $event['event'] }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">Asset: <span class="font-medium">{{ $event['asset'] }}</span></p>
                                </div>
                                <div class="flex-shrink-0">
                                    <x-badge variant="success" size="sm">{{ $event['status'] }}</x-badge>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">
                                        <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                        {{ $event['date'] }} at {{ $event['time'] }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-600">
                                        <i class="fas fa-user mr-2 text-gray-400"></i>
                                        {{ $event['user'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-700">{{ $event['details'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <x-card>
                <div class="px-6 py-8">
                    <x-empty-state icon="fas fa-history" title="No history found" message="This asset has no historical records yet" />
                </div>
            </x-card>
        @endforelse
    </div>
@endsection
