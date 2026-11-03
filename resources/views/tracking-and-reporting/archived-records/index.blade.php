@extends('layouts.admin')

@section('title', 'Archived Records - BDES')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center p-2 bg-blue-100 text-blue-700 rounded-lg">
                    <i class="fas fa-box-archive text-lg"></i>
                </span>
                <h1 class="text-2xl font-bold text-gray-900">Archived Records</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                Centralized archive of soft-deleted records across all applicable modules. Restore or permanently delete archived data.
            </p>
        </div>
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Tracking & Reporting', 'url' => route('asset-history.index')],
                ['label' => 'Archived Records']
            ]" />
        </div>
    </div>

    {{-- KPI Metric Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-box-archive"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Archived</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['total_archived']) }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Archived Assets</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['archived_assets']) }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-screwdriver-wrench"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Archived Work Orders</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['archived_work_orders']) }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Archived (30 Days)</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['archived_recent']) }}</p>
            </div>
        </div>
    </div>

    {{-- Search & Filters --}}
    <x-card>
        <form method="GET" action="{{ route('archived-records.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                {{-- Search Bar --}}
                <div class="md:col-span-4">
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Search Records</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text"
                               name="search"
                               value="{{ $filters['search'] }}"
                               placeholder="Search record name, ID, or reason..."
                               class="w-full text-xs pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>

                {{-- Module Filter --}}
                <div class="md:col-span-3">
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Source Module</label>
                    <select name="module"
                            onchange="this.form.submit()"
                            class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="all" {{ $filters['module'] === 'all' ? 'selected' : '' }}>All Modules (Unified)</option>
                        @foreach($modules as $key => $conf)
                            <option value="{{ $key }}" {{ $filters['module'] === $key ? 'selected' : '' }}>
                                {{ $conf['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Deleted By User --}}
                <div class="md:col-span-3">
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Deleted By User</label>
                    <select name="deleted_by"
                            onchange="this.form.submit()"
                            class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (string)$filters['deleted_by'] === (string)$user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Filter --}}
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Date Range</label>
                    <select name="date_filter"
                            onchange="this.form.submit()"
                            class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="all" {{ $filters['date_filter'] === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ $filters['date_filter'] === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="7days" {{ $filters['date_filter'] === '7days' ? 'selected' : '' }}>Past 7 Days</option>
                        <option value="30days" {{ $filters['date_filter'] === '30days' ? 'selected' : '' }}>Past 30 Days</option>
                    </select>
                </div>
            </div>

            {{-- Action buttons row --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs">
                <span class="text-gray-500">
                    Showing <strong class="text-gray-900">{{ $records->total() }}</strong> total archived record(s)
                </span>
                <div class="flex items-center gap-2">
                    @if(!empty($filters['search']) || $filters['module'] !== 'all' || !empty($filters['deleted_by']) || $filters['date_filter'] !== 'all')
                        <a href="{{ route('archived-records.index') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition-colors">
                            <i class="fas fa-times"></i> Reset Filters
                        </a>
                    @endif
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors shadow-xs">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </x-card>

    {{-- Centralized Archived Records Table --}}
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Record ID</th>
                        <th class="px-5 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Record Name</th>
                        <th class="px-5 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Source Module</th>
                        <th class="px-5 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Deletion Reason</th>
                        <th class="px-5 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Deleted By</th>
                        <th class="px-5 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Date Deleted</th>
                        <th class="px-5 py-3.5 text-right font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($records as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Record ID --}}
                            <td class="px-5 py-3.5 font-mono font-bold text-gray-800 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-100 border border-gray-200 text-gray-800 text-xs">
                                    {{ $item->record_id }}
                                </span>
                            </td>

                            {{-- Record Name --}}
                            <td class="px-5 py-3.5 font-semibold text-gray-900 max-w-xs">
                                <span class="truncate block" title="{{ $item->record_name }}">
                                    {{ $item->record_name }}
                                </span>
                            </td>

                            {{-- Source Module --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border bg-{{ $item->module_color }}-50 text-{{ $item->module_color }}-700 border-{{ $item->module_color }}-200">
                                    <i class="{{ $item->module_icon }} text-[10px]"></i>
                                    {{ $item->module_label }}
                                </span>
                            </td>

                            {{-- Deletion Reason --}}
                            <td class="px-5 py-3.5 text-gray-600 max-w-sm">
                                <span class="line-clamp-2 text-xs italic" title="{{ $item->deletion_reason }}">
                                    "{{ $item->deletion_reason }}"
                                </span>
                            </td>

                            {{-- Deleted By --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[10px]">
                                        {{ strtoupper(substr($item->deleted_by_name, 0, 1)) }}
                                    </span>
                                    <span class="text-gray-900 font-medium">{{ $item->deleted_by_name }}</span>
                                </div>
                            </td>

                            {{-- Date Deleted --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-gray-600">
                                @if($item->deleted_at)
                                    <div>{{ $item->deleted_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $item->deleted_at->format('h:i A') }} ({{ $item->deleted_at->diffForHumans() }})</div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- View Details --}}
                                    <button type="button"
                                            onclick="openArchiveViewModal('{{ $item->module_key }}', '{{ $item->raw_id }}')"
                                            title="View Archived Record Details"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                        <i class="fas fa-eye text-blue-600"></i> View
                                    </button>

                                    {{-- Restore Record --}}
                                    <button type="button"
                                            onclick="openRestoreModal('{{ $item->module_key }}', '{{ $item->raw_id }}', '{{ addslashes($item->record_name) }}', '{{ addslashes($item->module_label) }}')"
                                            title="Restore record to original module"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors">
                                        <i class="fas fa-rotate-left"></i> Restore
                                    </button>

                                    {{-- Permanently Delete --}}
                                    <button type="button"
                                            onclick="openForceDeleteModal('{{ $item->module_key }}', '{{ $item->raw_id }}', '{{ addslashes($item->record_name) }}', '{{ addslashes($item->module_label) }}')"
                                            title="Permanently remove record"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition-colors">
                                        <i class="fas fa-trash-can"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mb-3">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-800">No Archived Records Found</p>
                                    <p class="text-xs text-gray-400 mt-1 max-w-sm">
                                        There are currently no soft-deleted records matching your filter criteria. When records are removed from active modules, they will appear here.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $records->links() }}
            </div>
        @endif
    </x-card>
</div>

{{-- MODAL 1: VIEW DETAILS MODAL --}}
<div id="archive-view-modal"
     data-modal-name="archive-view-modal"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     role="dialog"
     aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="window.closeModal('archive-view-modal')"></div>

    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-10 transition-all">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2.5">
                    <div id="view_modal_icon_wrapper" class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
                        <i id="view_modal_icon" class="fas fa-box-archive"></i>
                    </div>
                    <div>
                        <h3 id="view_modal_title" class="text-base font-bold text-gray-900">Archived Record Details</h3>
                        <p id="view_modal_subtitle" class="text-xs text-gray-500">Source Module Information</p>
                    </div>
                </div>
                <button type="button" onclick="window.closeModal('archive-view-modal')" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                {{-- Archive Metadata Card --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/80 space-y-3">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Record Identifier</span>
                            <span id="view_record_id" class="font-mono font-bold text-gray-900">#0000</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Source Module</span>
                            <span id="view_module_label" class="font-semibold text-blue-700">Assets</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date Archived</span>
                            <span id="view_deleted_at" class="font-semibold text-gray-800">-</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Archived By</span>
                            <span id="view_deleted_by" class="font-medium text-gray-800">-</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Originally Created</span>
                            <span id="view_created_at" class="text-gray-600">-</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-200/60">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Stated Deletion Reason</span>
                        <div id="view_deletion_reason" class="text-xs text-gray-700 italic bg-white p-2.5 rounded-lg border border-gray-200">
                            "None provided"
                        </div>
                    </div>
                </div>

                {{-- Permanent Deletion Safety Status Alert --}}
                <div id="view_safety_alert" class="p-3.5 rounded-xl border text-xs"></div>

                {{-- Record Attributes Table --}}
                <div>
                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i class="fas fa-list-ul text-blue-600"></i> Original Record Attributes & Values
                    </h4>
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-xs text-left">
                            <tbody id="view_attributes_body" class="divide-y divide-gray-100">
                                <tr><td class="px-4 py-3 text-gray-400">Loading attributes...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button"
                        onclick="window.closeModal('archive-view-modal')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: RESTORE CONFIRMATION MODAL --}}
<div id="restore-confirm-modal"
     data-modal-name="restore-confirm-modal"
     class="fixed inset-0 z-50"
     style="display: none;"
     role="dialog"
     aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="window.closeModal('restore-confirm-modal')"></div>

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl border border-gray-100 sm:w-full sm:max-w-md p-6 z-10 transition-all">
        <form id="restoreRecordForm" method="POST" action="">
            @csrf
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-emerald-100 text-emerald-600 mb-4 ring-8 ring-emerald-50">
                    <i class="fas fa-rotate-left text-2xl"></i>
                </div>

                <h3 class="text-lg font-bold text-gray-900">
                    Restore Archived Record
                </h3>

                <p class="text-xs text-gray-500 mt-1.5">
                    You are about to restore this record back to its active operational module:
                </p>

                <div class="mt-3.5 px-3.5 py-3 bg-emerald-50/80 border border-emerald-200 rounded-xl text-center">
                    <p id="restore_modal_item_name" class="font-bold text-xs text-emerald-900 break-words">Selected Record</p>
                    <p id="restore_modal_module_name" class="text-[11px] text-emerald-700 mt-0.5">Assets Module</p>
                </div>

                <p class="text-xs text-gray-500 mt-3 leading-relaxed">
                    This record will be re-enabled and restored to active listings with all existing relationships intact. An entry will be recorded in the system audit logs.
                </p>
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                <button type="button"
                        onclick="window.closeModal('restore-confirm-modal')"
                        class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                    <i class="fas fa-rotate-left"></i> Yes, Restore Record
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: PERMANENT FORCE DELETE CONFIRMATION MODAL --}}
<div id="force-delete-confirm-modal"
     data-modal-name="force-delete-confirm-modal"
     class="fixed inset-0 z-50"
     style="display: none;"
     role="dialog"
     aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="window.closeModal('force-delete-confirm-modal')"></div>

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl border border-gray-100 sm:w-full sm:max-w-md p-6 z-10 transition-all">
        <form id="forceDeleteRecordForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-rose-100 text-rose-600 mb-4 ring-8 ring-rose-50">
                    <i class="fas fa-triangle-exclamation text-2xl"></i>
                </div>

                <h3 class="text-lg font-bold text-gray-900">
                    Confirm Permanent Deletion
                </h3>

                <p class="text-xs text-gray-500 mt-1.5">
                    This will permanently and irreversibly erase this record from the system database:
                </p>

                <div class="mt-3.5 px-3.5 py-3 bg-rose-50/80 border border-rose-200 rounded-xl text-center">
                    <p id="force_delete_item_name" class="font-bold text-xs text-rose-900 break-words">Selected Record</p>
                    <p id="force_delete_module_name" class="text-[11px] text-rose-700 mt-0.5">Assets Module</p>
                </div>

                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-left text-xs text-amber-800 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                        <i class="fas fa-shield-halved text-amber-600"></i> Accountability Protection Active:
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        Records with linked financial transactions, deployment history, or maintenance records cannot be permanently destroyed and must remain archived for barangay audit compliance.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                <button type="button"
                        onclick="window.closeModal('force-delete-confirm-modal')"
                        class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                    <i class="fas fa-trash-can"></i> Yes, Permanently Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Open View Details Modal
    function openArchiveViewModal(module, id) {
        const tbody = document.getElementById('view_attributes_body');
        tbody.innerHTML = '<tr><td class="px-4 py-4 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin mr-2"></i>Loading details...</td></tr>';

        window.openModal('archive-view-modal');

        fetch(`/archived-records/${module}/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                tbody.innerHTML = '<tr><td class="px-4 py-3 text-red-500">Failed to load record details.</td></tr>';
                return;
            }

            const d = res.data;
            document.getElementById('view_modal_title').textContent = d.record_name;
            document.getElementById('view_modal_subtitle').textContent = `Archived from ${d.module_label}`;
            document.getElementById('view_record_id').textContent = d.record_id;
            document.getElementById('view_module_label').textContent = d.module_label;
            document.getElementById('view_deleted_at').textContent = `${d.deleted_at} (${d.deleted_at_relative})`;
            document.getElementById('view_deleted_by').textContent = d.deleted_by_name + (d.deleted_by_email ? ` (${d.deleted_by_email})` : '');
            document.getElementById('view_created_at').textContent = d.created_at;
            document.getElementById('view_deletion_reason').textContent = `"${d.deletion_reason}"`;

            // Safety check alert
            const alertBox = document.getElementById('view_safety_alert');
            if (d.can_permanently_delete) {
                alertBox.className = 'p-3.5 rounded-xl border text-xs bg-emerald-50 border-emerald-200 text-emerald-800';
                alertBox.innerHTML = '<i class="fas fa-check-circle text-emerald-600 mr-1.5"></i> <strong>Safe for Permanent Deletion:</strong> No conflicting financial, deployment, or maintenance accountability trails depend on this record.';
            } else {
                alertBox.className = 'p-3.5 rounded-xl border text-xs bg-amber-50 border-amber-200 text-amber-800';
                alertBox.innerHTML = `<i class="fas fa-lock text-amber-600 mr-1.5"></i> <strong>Accountability Protected:</strong> ${d.permanent_delete_block_reason}`;
            }

            // Populate attributes table
            let rowsHtml = '';
            for (const [key, val] of Object.entries(d.attributes)) {
                rowsHtml += `
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-2 font-semibold text-gray-600 w-1/3 bg-gray-50/30">${key}</td>
                        <td class="px-4 py-2 text-gray-900 break-words font-mono text-[11px]">${val}</td>
                    </tr>
                `;
            }
            tbody.innerHTML = rowsHtml || '<tr><td class="px-4 py-3 text-gray-400 italic">No additional attributes available.</td></tr>';
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td class="px-4 py-3 text-red-500">Error fetching record details.</td></tr>';
        });
    }

    // Open Restore Modal
    function openRestoreModal(module, id, recordName, moduleLabel) {
        const form = document.getElementById('restoreRecordForm');
        form.action = `/archived-records/${module}/${id}/restore`;

        document.getElementById('restore_modal_item_name').textContent = recordName;
        document.getElementById('restore_modal_module_name').textContent = `${moduleLabel} Module`;

        window.openModal('restore-confirm-modal');
    }

    // Open Force Delete Modal
    function openForceDeleteModal(module, id, recordName, moduleLabel) {
        const form = document.getElementById('forceDeleteRecordForm');
        form.action = `/archived-records/${module}/${id}/force-delete`;

        document.getElementById('force_delete_item_name').textContent = recordName;
        document.getElementById('force_delete_module_name').textContent = `${moduleLabel} Module`;

        window.openModal('force-delete-confirm-modal');
    }
</script>
@endsection

