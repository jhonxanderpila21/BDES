@extends('layouts.admin')

@section('title', 'Asset History & Audit Trail - BDES')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <i class="fas fa-clock-rotate-left text-blue-600"></i>
                Asset History & Audit Trail
            </h1>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Tracking & Reporting'],
                ['label' => 'Asset History & Audit Trail']
            ]" />
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('asset-history.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-medium text-sm shadow-sm transition-colors">
                <i class="fas fa-file-csv"></i>
                Export Audit Log
            </a>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Audit Events</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($metrics['total_logs']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fas fa-list-check"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Logged Today</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($metrics['today_logs']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status Transitions</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($metrics['status_changes']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                <i class="fas fa-arrows-rotate"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Active Actors</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($metrics['active_actors']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                <i class="fas fa-users-gear"></i>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form action="{{ route('asset-history.index') }}" method="GET" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row flex-wrap gap-3 items-center">
        <div class="flex-1 min-w-[240px] w-full md:w-auto">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Search description, target code, or user..." 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
        </div>

        <select name="event" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
            <option value="">All Event Types</option>
            <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
            <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
            <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
            <option value="status_changed" {{ request('event') === 'status_changed' ? 'selected' : '' }}>Status Changed</option>
        </select>

        <select name="user_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
            <option value="">All Actors / Users</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>

        <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" title="From Date">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" title="To Date">

        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>

        @if(request()->hasAny(['search', 'event', 'user_id', 'date_from', 'date_to']))
            <a href="{{ route('asset-history.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                Reset
            </a>
        @endif
    </form>

    {{-- Audit Log Timeline / Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-semibold text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">Actor / User</th>
                        <th class="px-5 py-3">Action Event</th>
                        <th class="px-5 py-3">Target Entity</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-600 font-mono">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                                <span class="text-gray-400 block text-[11px] font-sans">{{ $log->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <p class="font-bold text-gray-900 text-xs">{{ $log->user?->name ?? 'System Process' }}</p>
                                <p class="text-[11px] text-gray-400">{{ $log->user?->role ?? 'automated' }}</p>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @php
                                    $badgeStyle = match($log->event) {
                                        'created'        => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'status_changed' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'deleted'        => 'bg-rose-100 text-rose-800 border-rose-300',
                                        default          => 'bg-blue-100 text-blue-800 border-blue-300',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full border {{ $badgeStyle }}">
                                    {{ strtoupper(str_replace('_', ' ', $log->event)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                                    {{ class_basename($log->auditable_type) }}
                                    @if($log->auditable)
                                        : {{ $log->auditable->code ?? $log->auditable->name ?? ('#' . $log->auditable_id) }}
                                    @else
                                        #{{ $log->auditable_id }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-800 max-w-md">
                                {{ $log->description }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap text-xs">
                                @if($log->old_values || $log->new_values)
                                    <details class="inline-block text-left">
                                        <summary class="cursor-pointer px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded text-xs">
                                            Diff
                                        </summary>
                                        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
                                            <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-3">
                                                <div class="flex items-center justify-between border-b pb-2">
                                                    <h4 class="font-bold text-sm text-gray-900">Audit Record #{{ $log->id }} State Diff</h4>
                                                </div>
                                                <div class="text-xs font-mono space-y-2 max-h-80 overflow-y-auto bg-gray-50 p-3 rounded-lg">
                                                    @if($log->old_values)
                                                        <div><span class="text-rose-600 font-bold uppercase block text-[10px]">Previous Values:</span>
                                                            <pre class="bg-white p-2 rounded border border-gray-200 mt-1 text-[11px] whitespace-pre-wrap">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                    @endif
                                                    @if($log->new_values)
                                                        <div><span class="text-emerald-600 font-bold uppercase block text-[10px]">New Values:</span>
                                                            <pre class="bg-white p-2 rounded border border-gray-200 mt-1 text-[11px] whitespace-pre-wrap">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-right pt-2 border-t">
                                                    <button type="button" onclick="this.closest('details').removeAttribute('open')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded text-xs">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                @else
                                    <span class="text-gray-400 text-xs">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-500">
                                <i class="fas fa-fingerprint text-4xl text-gray-300 mb-2 block"></i>
                                No audit events matching the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </x-card>
</div>
@endsection
