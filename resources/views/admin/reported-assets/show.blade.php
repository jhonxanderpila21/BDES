@extends('layouts.admin')

@section('title', 'Report #' . $reportedAsset->id . ' - BDES')

@section('content')
@php
    $isReadOnlyStaff = auth()->user()?->isViewer() || auth()->user()?->hasRole('viewer', 'staff');
@endphp
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm print:shadow-none print:border-none">
        <div class="flex items-start gap-4">
            <a href="{{ route('reported-assets.index') }}" class="mt-1 p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors print:hidden" title="Back to List">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Incident Report #{{ $reportedAsset->id }}</h1>
                    <x-badge variant="{{ \App\Services\ReportedAssetService::statusBadgeVariant($reportedAsset->status) }}" size="md">
                        @if($reportedAsset->status === 'Escalated')
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                        @endif
                        {{ $reportedAsset->status }}
                    </x-badge>
                </div>
                <x-breadcrumb :items="[
                    ['label' => 'Dashboard', 'url' => route('dashboard')],
                    ['label' => 'Reported Assets', 'url' => route('reported-assets.index')],
                    ['label' => 'Report #' . $reportedAsset->id]
                ]" />
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex flex-wrap items-center gap-2 print:hidden">
            @if($isReadOnlyStaff)
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                    <i class="fas fa-shield-alt text-amber-600"></i>
                    Read-Only Staff Access
                </span>
            @elseif($reportedAsset->asset)
                <a href="{{ route('assets.show', $reportedAsset->asset) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl font-medium text-sm transition-colors">
                    <i class="fas fa-box"></i>
                    <span>View Asset Dossier</span>
                </a>
            @endif
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium text-sm transition-colors">
                <i class="fas fa-print"></i>
                <span class="hidden sm:inline">Print Report</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Report Details & Timeline --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Report Info Card --}}
            <x-card>
                <div class="p-6">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <h2 class="text-lg font-bold text-gray-900">Incident Overview</h2>
                        </div>
                        <span class="text-xs text-gray-400 font-medium">
                            Filed {{ $reportedAsset->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Asset Information</p>
                            <p class="text-sm font-semibold text-gray-900">
                                @if($reportedAsset->asset && !$isReadOnlyStaff)
                                    <a href="{{ route('assets.show', $reportedAsset->asset) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $reportedAsset->asset_name }}
                                    </a>
                                @else
                                    {{ $reportedAsset->asset_name }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Category</p>
                            <x-badge variant="info" size="sm">{{ $reportedAsset->asset_category }}</x-badge>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Incident Type</p>
                            <x-badge variant="{{ \App\Services\ReportedAssetService::typeBadgeVariant($reportedAsset->reported_type) }}" size="sm">
                                {{ $reportedAsset->reported_type }}
                            </x-badge>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Location Reported</p>
                            <p class="text-sm text-gray-700 flex items-center gap-1.5">
                                <i class="fas fa-map-marker-alt text-gray-400"></i>
                                <span>{{ $reportedAsset->location }}</span>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Filed By</p>
                            <p class="text-sm text-gray-700 flex items-center gap-1.5">
                                <i class="fas fa-user text-gray-400"></i>
                                <span>{{ $reportedAsset->reporter->name ?? 'System / Anonymous' }}</span>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Incident Date & Time</p>
                            <p class="text-sm text-gray-700 flex items-center gap-1.5">
                                <i class="far fa-clock text-gray-400"></i>
                                <span>{{ $reportedAsset->reported_date->format('M d, Y') }} at {{ $reportedAsset->created_at->format('h:i A') }}</span>
                            </p>
                        </div>

                        @if($reportedAsset->resolved_date)
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Resolved Date</p>
                                <p class="text-sm text-emerald-700 font-semibold flex items-center gap-1.5">
                                    <i class="fas fa-check-circle text-emerald-500"></i>
                                    <span>{{ $reportedAsset->resolved_date->format('M d, Y') }}</span>
                                </p>
                            </div>
                        @endif

                        @if($reportedAsset->action)
                            <div class="sm:col-span-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Action Undertaken / Recommended</p>
                                <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-200">{{ $reportedAsset->action }}</p>
                            </div>
                        @endif
                    </div>

                    @if($reportedAsset->description)
                        <div class="mt-6 pt-5 border-t border-gray-100">
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Description & Notes</p>
                            <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-800 leading-relaxed whitespace-pre-line border border-gray-100">
                                {{ $reportedAsset->description }}
                            </div>
                        </div>
                    @endif

                    {{-- Attached Photo Evidence / Documents --}}
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Attached Evidence / Documentation</p>
                        @if($reportedAsset->receipts && $reportedAsset->receipts->isNotEmpty())
                            <x-receipt-list :receipts="$reportedAsset->receipts" />
                        @else
                            <p class="text-xs text-gray-400 italic">No attachments or photo evidence uploaded with this report.</p>
                        @endif
                    </div>
                </div>
            </x-card>

            {{-- Status Timeline --}}
            <x-card>
                <div class="p-6">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <h2 class="text-lg font-bold text-gray-900">Status History</h2>
                        </div>
                        <span class="text-xs text-gray-400 font-medium">
                            {{ $reportedAsset->statusHistories->count() }} event{{ $reportedAsset->statusHistories->count() === 1 ? '' : 's' }}
                        </span>
                    </div>

                    @if($reportedAsset->statusHistories->isNotEmpty())
                        <div class="relative">
                            {{-- Timeline line --}}
                            <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-gray-200"></div>

                            <div class="space-y-6">
                                @foreach($reportedAsset->statusHistories->sortByDesc('created_at') as $history)
                                    @php
                                        $dotColor = match($history->to_status) {
                                            'Pending'    => 'bg-amber-400',
                                            'For Review' => 'bg-sky-400',
                                            'Ongoing'    => 'bg-blue-500',
                                            'Escalated'  => 'bg-rose-500',
                                            'Done'       => 'bg-emerald-500',
                                            'Closed'     => 'bg-gray-400',
                                            default      => 'bg-gray-300',
                                        };
                                    @endphp
                                    <div class="relative pl-10">
                                        {{-- Dot --}}
                                        <div class="absolute left-2.5 top-2 w-3 h-3 rounded-full {{ $dotColor }} ring-4 ring-white shadow-sm"></div>

                                        <div class="bg-gray-50 hover:bg-gray-100/70 transition-colors rounded-xl p-4 border border-gray-100">
                                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                                <div class="flex items-center gap-2">
                                                    @if($history->from_status)
                                                        <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-0.5 rounded border border-gray-200">{{ $history->from_status }}</span>
                                                        <i class="fas fa-arrow-right text-[10px] text-gray-400"></i>
                                                    @endif
                                                    <span class="text-xs font-bold text-gray-900 bg-white px-2 py-0.5 rounded border border-gray-200">{{ $history->to_status }}</span>
                                                </div>
                                                <span class="text-xs text-gray-400">{{ $history->created_at->format('M d, Y h:i A') }}</span>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                Updated by <strong class="text-gray-700 font-medium">{{ $history->changedByUser->name ?? 'System' }}</strong>
                                            </p>
                                            @if($history->notes)
                                                <p class="text-sm text-gray-700 mt-2 p-2.5 bg-white rounded-lg border border-gray-100 italic">{{ $history->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-6">No status history recorded yet.</p>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Right Column: Quick Actions & Reference --}}
        <div class="space-y-6">
            {{-- Change Status Card --}}
            @if($isReadOnlyStaff)
                <x-card>
                    <div class="p-6 text-center">
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3 border border-amber-200">
                            <i class="fas fa-shield-alt text-xl"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Read-Only Staff Access</h3>
                        <p class="text-xs text-gray-500">Your account has staff read-only permissions. Status updates, report edits, and deletions are restricted.</p>
                    </div>
                </x-card>
            @elseif(count($allowedNext) > 0)
                <x-card>
                    <div class="p-6">
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-gray-100">
                            <i class="fas fa-exchange-alt text-blue-600"></i>
                            <h2 class="text-base font-bold text-gray-900">Update Incident Status</h2>
                        </div>

                        <form action="{{ route('reported-assets.change-status', $reportedAsset) }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label for="new_status" class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Next Lifecycle Stage <span class="text-red-500">*</span>
                                </label>
                                <select id="new_status" name="new_status" class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    @foreach($allowedNext as $nextStatus)
                                        <option value="{{ $nextStatus }}">{{ $nextStatus }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="status_notes" class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Status Notes / Justification (Optional)
                                </label>
                                <textarea id="status_notes" name="notes" rows="3" placeholder="Provide context, findings, or assigned technician..." class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>

                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium text-sm rounded-xl shadow-sm transition-colors">
                                <i class="fas fa-check"></i>
                                <span>Transition Status</span>
                            </button>
                        </form>
                    </div>
                </x-card>
            @else
                <x-card>
                    <div class="p-6 text-center">
                        <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-lock text-xl"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Workflow Terminal State</h3>
                        <p class="text-xs text-gray-500">This report is marked <strong>{{ $reportedAsset->status }}</strong>. No further status transitions are available.</p>
                    </div>
                </x-card>
            @endif

            {{-- Status Flow Reference Card --}}
            <x-card>
                <div class="p-6">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-2">
                        <i class="fas fa-sitemap text-gray-400"></i>
                        <span>Workflow Transitions</span>
                    </h2>
                    <div class="text-xs text-gray-600 space-y-2">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                            <span class="font-semibold text-amber-700">Pending</span>
                            <span class="text-gray-400">→ For Review, Ongoing</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                            <span class="font-semibold text-sky-700">For Review</span>
                            <span class="text-gray-400">→ Ongoing</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                            <span class="font-semibold text-blue-700">Ongoing</span>
                            <span class="text-gray-400">→ Escalated, Done</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                            <span class="font-semibold text-rose-700">Escalated</span>
                            <span class="text-gray-400">→ Ongoing, Done</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                            <span class="font-semibold text-emerald-700">Done</span>
                            <span class="text-gray-400">→ Closed</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                            <span class="font-semibold text-gray-600">Closed</span>
                            <span class="text-gray-400">(Terminal State)</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
