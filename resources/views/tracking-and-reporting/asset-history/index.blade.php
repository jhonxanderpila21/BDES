@extends('layouts.admin')

@section('title', 'Asset History & Audit Trail - BDES')

@section('content')
@php
    $isReadOnlyStaff = auth()->user()?->isViewer() || auth()->user()?->hasRole('viewer', 'staff');
@endphp
<div class="space-y-6">

    {{-- Page Header --}}
    <x-page-header
        title="Asset History & Audit Trail"
        :subtitle="auth()->user()->isAdmin()
            ? 'Full Administrative Access • All System Accounts'
            : ($isReadOnlyStaff
                ? 'Read-Only Staff Access • All System Records'
                : 'Account-Specific Access • Logs for ' . auth()->user()->name)"
        icon="fas fa-clock-rotate-left"
        :is-read-only-staff="$isReadOnlyStaff"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Tracking & Reporting'],
            ['label' => 'Asset History & Audit Trail']
        ]"
    >
        @if(!$isReadOnlyStaff)
            <x-slot:actions>
                <a href="{{ route('asset-history.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-medium text-sm shadow-sm transition-colors">
                    <i class="fas fa-file-csv"></i>
                    Export Audit Log
                </a>
            </x-slot:actions>
        @endif
    </x-page-header>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            :title="(auth()->user()->isAdmin() || $isReadOnlyStaff) ? 'Total Audit Events' : 'My Audit Events'"
            :value="number_format($metrics['total_logs'])"
            icon="fas fa-list-check"
            color="blue"
        />

        <x-kpi-card
            title="Logged Today"
            :value="number_format($metrics['today_logs'])"
            icon="fas fa-calendar-day"
            color="emerald"
        />

        <x-kpi-card
            title="Status Transitions"
            :value="number_format($metrics['status_changes'])"
            icon="fas fa-arrows-rotate"
            color="amber"
        />

        <x-kpi-card
            :title="(auth()->user()->isAdmin() || $isReadOnlyStaff) ? 'Active Actors' : 'Account Role'"
            :value="(auth()->user()->isAdmin() || $isReadOnlyStaff) ? number_format($metrics['active_actors']) : strtoupper(str_replace('_', ' ', auth()->user()->role))"
            icon="fas fa-users-gear"
            color="purple"
        />
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
            <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
            <option value="location_changed" {{ request('event') === 'location_changed' ? 'selected' : '' }}>Location Changed</option>
            <option value="status_changed" {{ request('event') === 'status_changed' ? 'selected' : '' }}>Status Changed</option>
            <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
        </select>

        @if(auth()->user()->isAdmin() || $isReadOnlyStaff)
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

        @if(request()->hasAny(['search', 'event', 'user_id', 'date_from', 'date_to']))
            <a href="{{ route('asset-history.index') }}" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-sm font-semibold transition-colors shadow-xs">
                Reset
            </a>
        @endif
    </form>

    {{-- Audit Log Timeline / Table --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-bold text-gray-700">
                    <tr>
                        <th class="px-5 py-3.5 text-left tracking-wider">Timestamp</th>
                        <th class="px-5 py-3.5 text-left tracking-wider">Actor / User</th>
                        <th class="px-5 py-3.5 text-center tracking-wider">Action Event</th>
                        <th class="px-5 py-3.5 text-left tracking-wider">Target Entity</th>
                        <th class="px-5 py-3.5 text-left tracking-wider">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                        @php
                            $historyDetail = [
                                'timestamp'   => $log->created_at->format('M d, Y h:i:s A'),
                                'relative'    => $log->created_at->diffForHumans(),
                                'actor'       => $log->user?->name ?? 'System Process',
                                'role'        => ucwords(str_replace('_', ' ', $log->user?->role ?? 'automated')),
                                'event'       => strtoupper(str_replace('_', ' ', $log->event)),
                                'entity'      => class_basename($log->auditable_type) . ($log->auditable ? ': ' . ($log->auditable->code ?? $log->auditable->name ?? ('#' . $log->auditable_id)) : ' #' . $log->auditable_id),
                                'description' => $log->description,
                                'old_values'  => $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT) : null,
                                'new_values'  => $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT) : null,
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/40 cursor-pointer transition-colors"
                            onclick='openHistoryDetailsModal(@json($historyDetail))'
                            title="Click to view audit event details">
                            <td class="px-5 py-3.5 align-middle whitespace-nowrap text-xs text-gray-600 font-mono text-left">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                                <span class="text-gray-400 block text-[11px] font-sans">{{ $log->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-5 py-3.5 align-middle whitespace-nowrap text-left">
                                <p class="font-bold text-gray-900 text-xs">{{ $log->user?->name ?? 'System Process' }}</p>
                                <p class="text-[11px] text-gray-400">{{ $log->user?->role ?? 'automated' }}</p>
                            </td>
                            <td class="px-5 py-3.5 align-middle whitespace-nowrap text-center">
                                @php
                                    $badgeStyle = match($log->event) {
                                        'created'          => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'location_changed' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                                        'status_changed'   => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'deleted'          => 'bg-rose-100 text-rose-800 border-rose-300',
                                        default            => 'bg-blue-100 text-blue-800 border-blue-300',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full border {{ $badgeStyle }}">
                                    {{ strtoupper(str_replace('_', ' ', $log->event)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 align-middle whitespace-nowrap text-left">
                                <span class="font-mono text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                                    {{ class_basename($log->auditable_type) }}
                                    @if($log->auditable)
                                        : {{ $log->auditable->code ?? $log->auditable->name ?? ('#' . $log->auditable_id) }}
                                    @else
                                        #{{ $log->auditable_id }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-5 py-3.5 align-middle text-xs text-gray-800 max-w-md text-left">
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

    {{-- Audit Event Details Modal --}}
    <x-modal id="history-details-modal" title="Audit Event Details">
        <div class="space-y-4 text-sm">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex items-start justify-between gap-4">
                <div>
                    <span id="hist_detail_event" class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-800 mb-1.5"></span>
                    <h4 id="hist_detail_entity" class="text-base font-bold text-gray-900 font-mono"></h4>
                    <p id="hist_detail_timestamp" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs font-semibold uppercase text-gray-500">Actor</p>
                    <p id="hist_detail_actor" class="font-bold text-gray-900"></p>
                    <p id="hist_detail_role" class="text-xs text-gray-500"></p>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg border border-gray-200">
                <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Description</p>
                <p id="hist_detail_description" class="text-gray-800"></p>
            </div>

            <div id="hist_detail_changes_wrap" class="grid grid-cols-1 md:grid-cols-2 gap-3 hidden">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Previous Values</p>
                    <pre id="hist_detail_old" class="text-xs font-mono text-gray-700 whitespace-pre-wrap break-all"></pre>
                </div>
                <div class="p-3 bg-blue-50/50 rounded-lg border border-blue-200">
                    <p class="text-xs font-semibold uppercase text-blue-700 mb-1">Updated Values</p>
                    <pre id="hist_detail_new" class="text-xs font-mono text-gray-800 whitespace-pre-wrap break-all"></pre>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-gray-200">
                <button type="button" onclick="closeModal('history-details-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Close</button>
            </div>
        </div>
    </x-modal>

    <script>
        function openHistoryDetailsModal(item) {
            document.getElementById('hist_detail_event').textContent = item.event || 'EVENT';
            document.getElementById('hist_detail_entity').textContent = item.entity || '';
            document.getElementById('hist_detail_timestamp').textContent = (item.timestamp || '') + (item.relative ? ' (' + item.relative + ')' : '');
            document.getElementById('hist_detail_actor').textContent = item.actor || 'System Process';
            document.getElementById('hist_detail_role').textContent = item.role || '';
            document.getElementById('hist_detail_description').textContent = item.description || '';

            const wrap = document.getElementById('hist_detail_changes_wrap');
            if (item.old_values || item.new_values) {
                document.getElementById('hist_detail_old').textContent = item.old_values || '—';
                document.getElementById('hist_detail_new').textContent = item.new_values || '—';
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }
            openModal('history-details-modal');
        }
    </script>
</div>
@endsection
