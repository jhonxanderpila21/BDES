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
            subtitle="All recorded activity"
            :href="route('asset-history.index')"
            :active="!request()->anyFilled(['event', 'date_from', 'date_to', 'user_id', 'search', 'filter'])"
            tooltip="Show all audit events"
        />

        <x-kpi-card
            title="Logged Today"
            :value="number_format($metrics['today_logs'])"
            icon="fas fa-calendar-day"
            color="emerald"
            subtitle="Events logged today"
            :href="route('asset-history.index', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()])"
            :active="request('date_from') === now()->toDateString() && request('date_to') === now()->toDateString()"
            tooltip="Filter by today's events"
        />

        <x-kpi-card
            title="Status Transitions"
            :value="number_format($metrics['status_changes'])"
            icon="fas fa-arrows-rotate"
            color="amber"
            subtitle="Asset status updates"
            :href="route('asset-history.index', ['event' => 'status_changed'])"
            :active="request('event') === 'status_changed'"
            tooltip="Filter by status transitions"
        />

        <x-kpi-card
            :title="(auth()->user()->isAdmin() || $isReadOnlyStaff) ? 'Active Actors' : 'Account Role'"
            :value="(auth()->user()->isAdmin() || $isReadOnlyStaff) ? number_format($metrics['active_actors']) : strtoupper(str_replace('_', ' ', auth()->user()->role))"
            icon="fas fa-users-gear"
            color="purple"
            :subtitle="(auth()->user()->isAdmin() || $isReadOnlyStaff) ? 'Users with recorded activity' : 'Your access level'"
            :href="request('filter') === 'active_actors' ? route('asset-history.index') : route('asset-history.index', ['filter' => 'active_actors'])"
            :active="request('filter') === 'active_actors'"
            tooltip="Show each active actor's latest activity"
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

        @if(request()->hasAny(['search', 'event', 'user_id', 'date_from', 'date_to', 'filter']))
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
                    @php
                        $formatAuditVal = function ($val) use (&$formatAuditVal) {
                            if ($val === null || $val === '') return '—';
                            if (is_bool($val)) return $val ? 'Yes' : 'No';
                            if (is_array($val)) {
                                if (empty($val)) return '—';
                                $parts = [];
                                foreach ($val as $k => $v) {
                                    $formatted = $formatAuditVal($v);
                                    $parts[] = is_string($k) ? (ucwords(str_replace('_', ' ', $k)) . ': ' . $formatted) : $formatted;
                                }
                                return implode(', ', $parts);
                            }
                            if (is_object($val)) {
                                return json_encode($val);
                            }
                            $str = (string) $val;
                            if (preg_match('/^[a-z0-9_]+$/', $str) && str_contains($str, '_')) {
                                return ucwords(str_replace('_', ' ', $str));
                            }
                            return $str;
                        };
                    @endphp
                    @forelse($logs as $log)
                        @php
                            $oldArr = is_array($log->old_values) ? $log->old_values : [];
                            $newArr = is_array($log->new_values) ? $log->new_values : [];
                            $ignoredKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'];
                            $allKeys = array_values(array_diff(array_unique(array_merge(array_keys($oldArr), array_keys($newArr))), $ignoredKeys));
                            $changesList = [];
                            foreach ($allKeys as $k) {
                                $changesList[] = [
                                    'field' => ucwords(str_replace('_', ' ', preg_replace('/_id$/', '', (string) $k))),
                                    'old'   => $formatAuditVal($oldArr[$k] ?? null),
                                    'new'   => $formatAuditVal($newArr[$k] ?? null),
                                ];
                            }
                            $baseType = class_basename($log->auditable_type);
                            $entityLabel = $log->auditable
                                ? ($log->auditable->name ?? $log->auditable->code ?? $log->auditable->requirement_type ?? ($baseType . ' #' . $log->auditable_id))
                                : ($baseType . ' #' . $log->auditable_id);
                            $entityUrl = match ($baseType) {
                                'Asset'                  => $log->auditable ? route('assets.show', $log->auditable_id) : null,
                                'ReportedAsset'          => $log->auditable ? route('reported-assets.show', $log->auditable_id) : null,
                                'Deployment'             => route('deployment.index'),
                                'Maintenance'            => route('maintenance.index'),
                                'OperationalRequirement' => route('operational-requirements.index'),
                                'Location'               => route('locations.index'),
                                default                  => null,
                            };
                            $historyDetail = [
                                'timestamp'   => $log->created_at->format('M d, Y h:i:s A'),
                                'relative'    => $log->created_at->diffForHumans(),
                                'actor'       => $log->user?->name ?? 'System Process',
                                'role'        => ucwords(str_replace('_', ' ', $log->user?->role ?? 'automated')),
                                'event'       => strtoupper(str_replace('_', ' ', $log->event)),
                                'entity_type' => $baseType,
                                'entity'      => $entityLabel,
                                'entity_url'  => $entityUrl,
                                'description' => $log->description,
                                'changes'     => $changesList,
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
                    <div class="flex items-center gap-2 mb-1.5">
                        <span id="hist_detail_event" class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-800"></span>
                        <span id="hist_detail_entity_type" class="text-xs font-semibold text-gray-500 uppercase"></span>
                    </div>
                    <h4 id="hist_detail_entity" class="text-base font-bold text-gray-900"></h4>
                    <p id="hist_detail_timestamp" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs font-semibold uppercase text-gray-500">Recorded By</p>
                    <p id="hist_detail_actor" class="font-bold text-gray-900"></p>
                    <p id="hist_detail_role" class="text-xs text-gray-500"></p>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg border border-gray-200">
                <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Summary</p>
                <p id="hist_detail_description" class="text-gray-800"></p>
            </div>

            <div id="hist_detail_changes_wrap" class="hidden">
                <p class="text-xs font-semibold uppercase text-gray-500 mb-1.5">Recorded Changes</p>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase">
                            <tr>
                                <th class="px-3 py-2 font-semibold">Field</th>
                                <th class="px-3 py-2 font-semibold">Previous</th>
                                <th class="px-3 py-2 font-semibold">Updated</th>
                            </tr>
                        </thead>
                        <tbody id="hist_detail_changes_body" class="divide-y divide-gray-200 bg-white"></tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                <a id="hist_detail_link" href="#" class="hidden inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold transition-colors">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    <span>View Related Record</span>
                </a>
                <button type="button" onclick="closeModal('history-details-modal')" class="ml-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Close</button>
            </div>
        </div>
    </x-modal>

    <script>
        function openHistoryDetailsModal(item) {
            document.getElementById('hist_detail_event').textContent = item.event || 'EVENT';
            document.getElementById('hist_detail_entity_type').textContent = item.entity_type || '';
            document.getElementById('hist_detail_entity').textContent = item.entity || '';
            document.getElementById('hist_detail_timestamp').textContent = (item.timestamp || '') + (item.relative ? ' (' + item.relative + ')' : '');
            document.getElementById('hist_detail_actor').textContent = item.actor || 'System Process';
            document.getElementById('hist_detail_role').textContent = item.role || '';
            document.getElementById('hist_detail_description').textContent = item.description || '';

            const wrap = document.getElementById('hist_detail_changes_wrap');
            const tbody = document.getElementById('hist_detail_changes_body');
            tbody.innerHTML = '';

            if (Array.isArray(item.changes) && item.changes.length > 0) {
                item.changes.forEach(function (c) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td class="px-3 py-2 font-semibold text-gray-700"></td><td class="px-3 py-2 text-gray-500"></td><td class="px-3 py-2 font-medium text-blue-700"></td>';
                    tr.children[0].textContent = c.field;
                    tr.children[1].textContent = c.old;
                    tr.children[2].textContent = c.new;
                    tbody.appendChild(tr);
                });
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }

            const linkEl = document.getElementById('hist_detail_link');
            if (item.entity_url) {
                linkEl.href = item.entity_url;
                linkEl.classList.remove('hidden');
            } else {
                linkEl.classList.add('hidden');
            }

            openModal('history-details-modal');
        }
    </script>
</div>
@endsection
