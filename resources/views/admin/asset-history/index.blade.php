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
            <p class="text-xs text-gray-500 mt-1">
                @if(auth()->user()->isAdmin())
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-200">
                        <i class="fas fa-shield-halved"></i> Full Administrative Access &bull; All System Accounts
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-semibold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-200">
                        <i class="fas fa-user-shield"></i> Account-Specific Access &bull; Logs for {{ auth()->user()->name }}
                    </span>
                @endif
            </p>
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
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    {{ auth()->user()->isAdmin() ? 'Total Audit Events' : 'My Audit Events' }}
                </p>
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
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    {{ auth()->user()->isAdmin() ? 'Active Actors' : 'Account Role' }}
                </p>
                @if(auth()->user()->isAdmin())
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($metrics['active_actors']) }}</p>
                @else
                    <p class="text-sm font-bold text-purple-600 mt-2 uppercase tracking-wide">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                @endif
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
                placeholder="Search description, target code, or action..." 
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

        @if(auth()->user()->isAdmin())
            <select name="user_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                <option value="">All Actors / Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        @else
            <span class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 flex items-center gap-1.5" title="You are viewing logs recorded for your own account">
                <i class="fas fa-user-circle text-blue-600"></i> {{ auth()->user()->name }} (My Account)
            </span>
        @endif

        <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" title="From Date">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" title="To Date">

        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
            Filter
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-500">
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
