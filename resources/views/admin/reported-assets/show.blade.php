@extends('layouts.admin')

@section('title', 'Report #' . $reportedAsset->id . ' - BDES')

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Report #{{ $reportedAsset->id }}</h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Reported Assets', 'url' => route('reported-assets.index')],
                ['label' => 'Report #' . $reportedAsset->id]
            ]" />
        </div>
        <a href="{{ route('reported-assets.index') }}" class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left"></i>
            Back to List
        </a>
    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Report Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Report Info Card --}}
            <x-card>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Report Details</h2>
                        <x-badge variant="{{ \App\Services\ReportedAssetService::statusBadgeVariant($reportedAsset->status) }}" size="md">
                            @if($reportedAsset->status === 'Escalated')
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                            @endif
                            {{ $reportedAsset->status }}
                        </x-badge>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Asset</p>
                            <p class="text-sm font-medium text-gray-900">{{ $reportedAsset->asset_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Category</p>
                            <x-badge variant="info" size="sm">{{ $reportedAsset->asset_category }}</x-badge>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Report Type</p>
                            <x-badge variant="{{ \App\Services\ReportedAssetService::typeBadgeVariant($reportedAsset->reported_type) }}" size="sm">
                                {{ $reportedAsset->reported_type }}
                            </x-badge>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Location</p>
                            <p class="text-sm text-gray-700">{{ $reportedAsset->location }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Reported By</p>
                            <p class="text-sm text-gray-700">{{ $reportedAsset->reporter->name ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Reported Date</p>
                            <p class="text-sm text-gray-700">{{ $reportedAsset->reported_date->format('M d, Y') }}</p>
                        </div>
                        @if($reportedAsset->resolved_date)
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Resolved Date</p>
                            <p class="text-sm text-green-700 font-medium">{{ $reportedAsset->resolved_date->format('M d, Y') }}</p>
                        </div>
                        @endif
                        @if($reportedAsset->action)
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Action</p>
                            <p class="text-sm text-gray-700">{{ $reportedAsset->action }}</p>
                        </div>
                        @endif
                    </div>

                    @if($reportedAsset->description)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Description</p>
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $reportedAsset->description }}</p>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Status Timeline --}}
            <x-card>
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Status History</h2>

                    @if($reportedAsset->statusHistories->isNotEmpty())
                        <div class="relative">
                            {{-- Timeline line --}}
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                            <div class="space-y-6">
                                @foreach($reportedAsset->statusHistories->sortByDesc('created_at') as $history)
                                    @php
                                        $dotColor = match($history->to_status) {
                                            'Pending'    => 'bg-yellow-400',
                                            'For Review' => 'bg-sky-400',
                                            'Ongoing'    => 'bg-blue-500',
                                            'Escalated'  => 'bg-red-500',
                                            'Done'       => 'bg-green-500',
                                            'Closed'     => 'bg-gray-400',
                                            default      => 'bg-gray-300',
                                        };
                                    @endphp
                                    <div class="relative pl-10">
                                        {{-- Dot --}}
                                        <div class="absolute left-2.5 top-1 w-3 h-3 rounded-full {{ $dotColor }} ring-2 ring-white"></div>

                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="flex items-center gap-2">
                                                    @if($history->from_status)
                                                        <span class="text-sm text-gray-500">{{ $history->from_status }}</span>
                                                        <i class="fas fa-arrow-right text-xs text-gray-400"></i>
                                                    @endif
                                                    <span class="text-sm font-semibold text-gray-800">{{ $history->to_status }}</span>
                                                </div>
                                                <span class="text-xs text-gray-400">{{ $history->created_at->format('M d, Y h:i A') }}</span>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                by {{ $history->changedByUser->name ?? 'System' }}
                                            </p>
                                            @if($history->notes)
                                                <p class="text-sm text-gray-600 mt-2 italic">{{ $history->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">No status history recorded.</p>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Right Column: Quick Actions --}}
        <div class="space-y-6">
            {{-- Change Status Card --}}
            @if(count($allowedNext) > 0)
                <x-card>
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Status</h2>
                        <form action="{{ route('reported-assets.change-status', $reportedAsset) }}" method="POST" class="space-y-4">
                            @csrf

                            <x-form-group label="Transition To" required>
                                <select name="new_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($allowedNext as $nextStatus)
                                        <option value="{{ $nextStatus }}">{{ $nextStatus }}</option>
                                    @endforeach
                                </select>
                            </x-form-group>

                            <x-form-group label="Notes (Optional)">
                                <textarea name="notes" rows="3" placeholder="Reason for status change..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </x-form-group>

                            <x-button variant="primary" type="submit" class="w-full">
                                <i class="fas fa-exchange-alt mr-2"></i> Update Status
                            </x-button>
                        </form>
                    </div>
                </x-card>
            @else
                <x-card>
                    <div class="p-6 text-center">
                        <i class="fas fa-lock text-3xl text-gray-300 mb-3"></i>
                        <p class="text-sm text-gray-500 font-medium">This report is <strong>{{ $reportedAsset->status }}</strong> — no further transitions available.</p>
                    </div>
                </x-card>
            @endif

            {{-- Status Flow Reference --}}
            <x-card>
                <div class="p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Status Flow Reference</h2>
                    <div class="text-xs text-gray-500 space-y-1.5">
                        <p><span class="font-medium text-yellow-600">Pending</span> → For Review, Ongoing</p>
                        <p><span class="font-medium text-sky-600">For Review</span> → Ongoing</p>
                        <p><span class="font-medium text-blue-600">Ongoing</span> → Escalated, Done</p>
                        <p><span class="font-medium text-red-600">Escalated</span> → Ongoing, Done</p>
                        <p><span class="font-medium text-green-600">Done</span> → Closed</p>
                        <p><span class="font-medium text-gray-500">Closed</span> → (Terminal)</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
