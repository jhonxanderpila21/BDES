@extends('layouts.admin')

@section('title', 'Notifications Center - BDES')

@section('content')
<div 
    class="space-y-6"
    x-data="bdesNotificationCenter({
        items: {{ Js::from($notifications->values()->all()) }},
        initialUnreadCount: {{ (int) $stats['unread'] }},
        currentFilter: '{{ $currentFilter }}',
        markReadUrl: '{{ route('notifications.mark-read') }}',
        markAllUrl: '{{ route('notifications.mark-all-read') }}',
        csrfToken: '{{ csrf_token() }}'
    })"
>

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <i class="fas fa-bell text-blue-600"></i>
                <span>Operational Alerts</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                Notifications Center
            </h1>
            <p class="text-gray-600 text-sm mt-1">
                Real-time operational alerts for maintenance work orders, return deadlines, preventive schedules, and incidents.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" @submit.prevent="markAllAsRead()">
                @csrf
                <button 
                    type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="unreadCount === 0"
                    {{ $stats['unread'] === 0 ? 'disabled' : '' }}
                >
                    <i class="fas fa-check-double"></i>
                    <span>Mark All as Read</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Success Alert if redirected --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="bg-white p-2 rounded-2xl border border-gray-200 shadow-sm flex flex-wrap gap-1.5 text-xs font-medium">
        <a 
            href="{{ route('notifications.index', ['filter' => 'all']) }}" 
            class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 {{ $currentFilter === 'all' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}"
        >
            <i class="fas fa-layer-group"></i>
            <span>All Alerts</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $currentFilter === 'all' ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                {{ $stats['total'] }}
            </span>
        </a>

        <a 
            href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
            class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 {{ $currentFilter === 'unread' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}"
        >
            <i class="fas fa-envelope"></i>
            <span>Unread</span>
            <span 
                x-text="unreadCount"
                class="px-1.5 py-0.5 text-[10px] rounded-full transition-colors {{ $currentFilter === 'unread' ? 'bg-blue-700 text-white' : 'bg-rose-100 text-rose-700 font-bold' }}"
            >
                {{ $stats['unread'] }}
            </span>
        </a>

        <a 
            href="{{ route('notifications.index', ['filter' => 'maintenance']) }}" 
            class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 {{ $currentFilter === 'maintenance' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}"
        >
            <i class="fas fa-wrench"></i>
            <span>Maintenance & Schedules</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $currentFilter === 'maintenance' ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                {{ $stats['maintenance'] }}
            </span>
        </a>

        <a 
            href="{{ route('notifications.index', ['filter' => 'deployment']) }}" 
            class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 {{ $currentFilter === 'deployment' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}"
        >
            <i class="fas fa-truck-fast"></i>
            <span>Deployments & Returns</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $currentFilter === 'deployment' ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                {{ $stats['deployment'] }}
            </span>
        </a>

        <a 
            href="{{ route('notifications.index', ['filter' => 'inspection']) }}" 
            class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 {{ $currentFilter === 'inspection' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}"
        >
            <i class="fas fa-clipboard-check"></i>
            <span>Inspections</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $currentFilter === 'inspection' ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                {{ $stats['inspection'] }}
            </span>
        </a>

        <a 
            href="{{ route('notifications.index', ['filter' => 'reported_asset']) }}" 
            class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 {{ $currentFilter === 'reported_asset' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}"
        >
            <i class="fas fa-triangle-exclamation"></i>
            <span>Incident Reports</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $currentFilter === 'reported_asset' ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                {{ $stats['reported_asset'] }}
            </span>
        </a>
    </div>

    {{-- Notifications List --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        @if($notifications->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-14 h-14 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center mx-auto text-2xl">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">All caught up!</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto">
                    @if($currentFilter === 'unread')
                        You have no unread notifications. All alerts have been reviewed.
                    @else
                        No operational alerts matching this filter. Equipment schedules and deployments are currently running on time.
                    @endif
                </p>
                <div class="pt-2">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Return to Dashboard</span>
                    </a>
                </div>
            </div>
        @else
            {{-- Dynamic Empty State when all unread items are marked as read in-page --}}
            <div 
                x-show="currentFilter === 'unread' && unreadCount === 0"
                x-cloak
                class="p-12 text-center space-y-3"
            >
                <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center mx-auto text-2xl">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">All caught up!</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto">
                    You have reviewed all notifications in this view. All alerts are marked as read.
                </p>
                <div class="pt-2">
                    <a href="{{ route('notifications.index', ['filter' => 'all']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-semibold transition-colors">
                        <i class="fas fa-layer-group"></i>
                        <span>View All Alerts</span>
                    </a>
                </div>
            </div>

            <div 
                class="divide-y divide-gray-100"
                x-show="!(currentFilter === 'unread' && unreadCount === 0)"
            >
                @foreach($notifications as $item)
                    <div 
                        id="notification-{{ $item['id'] }}"
                        @click="openNotification('{{ $item['id'] }}')"
                        class="p-4 sm:p-5 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer group"
                        :class="isItemRead('{{ $item['id'] }}') ? 'bg-white hover:bg-gray-50/80 border-l-4 border-transparent' : 'bg-blue-50/30 hover:bg-blue-50/60 border-l-4 border-blue-600'"
                    >
                        
                        {{-- Left Details --}}
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            {{-- Category Icon Container --}}
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-base {{ $item['icon_bg'] }} shadow-xs">
                                <i class="{{ $item['icon'] }}"></i>
                            </div>

                            <div class="space-y-1 flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm sm:text-base font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                                        {{ $item['title'] }}
                                    </h4>

                                    {{-- Priority Badge --}}
                                    @if($item['priority'] === 'high')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            <i class="fas fa-triangle-exclamation text-[9px]"></i>
                                            <span>{{ $item['badge'] }}</span>
                                        </span>
                                    @elseif($item['priority'] === 'medium')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="fas fa-clock text-[9px]"></i>
                                            <span>{{ $item['badge'] }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                            <span>{{ $item['badge'] }}</span>
                                        </span>
                                    @endif

                                    {{-- Unread Badge (disappears immediately when marked read) --}}
                                    <span 
                                        x-show="!isItemRead('{{ $item['id'] }}')"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 transition-all duration-200"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                        <span>Unread</span>
                                    </span>
                                </div>

                                <p class="text-xs sm:text-sm text-gray-600 max-w-3xl leading-relaxed">
                                    {{ $item['description'] }}
                                </p>

                                <div class="flex items-center gap-3 text-xs text-gray-400 pt-1">
                                    <span class="flex items-center gap-1">
                                        <i class="far fa-clock"></i>
                                        <span>{{ $item['timestamp'] }}</span>
                                    </span>
                                    <span>•</span>
                                    <span class="font-medium text-gray-500 uppercase tracking-wider text-[10px]">
                                        {{ $item['category_label'] }}
                                    </span>
                                    <span>•</span>
                                    <span class="text-blue-600 font-medium text-[11px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                        <i class="fas fa-expand-alt text-[9px]"></i>
                                        <span>Click to view details</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Right Actions --}}
                        <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                            {{-- Mark Read button is removed --}}

                            <a 
                                href="{{ route('notifications.view-record', $item['id']) }}" 
                                @click.stop="handleViewRecord('{{ $item['id'] }}', '{{ $item['url'] }}', $event)"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors shadow-xs"
                                title="View Record"
                            >
                                <span>View Record</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Dedicated Notification Details Modal (Marks as read upon viewing) --}}
    <div 
        x-show="detailsModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="closeDetails()"
    >
        {{-- Backdrop --}}
        <div 
            x-show="detailsModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
            @click="closeDetails()"
        ></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="detailsModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100"
                @click.stop
            >
                <template x-if="selectedItem">
                    <div>
                        {{-- Modal Header --}}
                        <div class="p-5 sm:p-6 border-b border-gray-100 flex items-start justify-between gap-4 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div 
                                    class="w-12 h-12 rounded-xl flex items-center justify-center text-lg flex-shrink-0 shadow-xs"
                                    :class="selectedItem.icon_bg"
                                >
                                    <i :class="selectedItem.icon"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span 
                                            class="text-xs font-bold uppercase tracking-wider text-gray-500"
                                            x-text="selectedItem.category_label"
                                        ></span>
                                        <span class="text-gray-300">•</span>
                                        <span class="text-xs text-gray-500" x-text="selectedItem.timestamp"></span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mt-0.5 leading-snug" x-text="selectedItem.title"></h3>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="closeDetails()"
                                class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition-colors"
                            >
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="p-5 sm:p-6 space-y-4">
                            {{-- Badges Row --}}
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Priority Badge --}}
                                <template x-if="selectedItem.priority === 'high'">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        <i class="fas fa-triangle-exclamation text-[10px]"></i>
                                        <span x-text="selectedItem.badge"></span>
                                    </span>
                                </template>
                                <template x-if="selectedItem.priority === 'medium'">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-clock text-[10px]"></i>
                                        <span x-text="selectedItem.badge"></span>
                                    </span>
                                </template>
                                <template x-if="selectedItem.priority !== 'high' && selectedItem.priority !== 'medium'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                        <span x-text="selectedItem.badge"></span>
                                    </span>
                                </template>

                                {{-- Confirmation Read Status Badge --}}
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-check-circle text-[10px] text-emerald-600"></i>
                                    <span>Marked as Read</span>
                                </span>
                            </div>

                            {{-- Description Card --}}
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/80">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Alert Details</h4>
                                <p class="text-sm text-gray-800 leading-relaxed" x-text="selectedItem.description"></p>
                            </div>

                            {{-- Context Info Metadata --}}
                            <div class="grid grid-cols-2 gap-3 pt-1 text-xs">
                                <div class="bg-white p-3 rounded-xl border border-gray-100">
                                    <span class="text-gray-400 block text-[10px] font-semibold uppercase tracking-wider">Alert Type</span>
                                    <span class="font-bold text-gray-800 mt-0.5 block" x-text="selectedItem.category_label"></span>
                                </div>
                                <div class="bg-white p-3 rounded-xl border border-gray-100">
                                    <span class="text-gray-400 block text-[10px] font-semibold uppercase tracking-wider">Priority Level</span>
                                    <span class="font-bold text-gray-800 mt-0.5 block capitalize" x-text="selectedItem.priority + ' Priority'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="p-4 sm:p-5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between gap-3">
                            <button 
                                type="button" 
                                @click="closeDetails()"
                                class="px-4 py-2 text-xs font-semibold text-gray-700 hover:text-gray-900 bg-white hover:bg-gray-100 border border-gray-300 rounded-xl transition-colors"
                            >
                                Close
                            </button>

                            <a 
                                :href="selectedItem.url"
                                @click="handleViewRecord(selectedItem.id, selectedItem.url, $event)"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-colors shadow-sm"
                            >
                                <span>View Record</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function bdesNotificationCenter(config) {
        return {
            items: config.items || [],
            readIds: (config.items || []).filter(i => i.is_read).map(i => i.id),
            unreadCount: config.initialUnreadCount || 0,
            currentFilter: config.currentFilter || 'all',
            detailsModalOpen: false,
            selectedItem: null,

            init() {
                // Synchronize reactive unread count
                this.unreadCount = this.items.filter(i => !this.isItemRead(i.id)).length;

                // Listen for cross-component read events from navbar
                window.addEventListener('bdes:notification-read', (e) => {
                    const id = e.detail?.id;
                    if (!id) return;
                    if (!this.readIds.includes(id)) {
                        this.readIds.push(id);
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                    const item = this.items.find(i => i.id === id);
                    if (item) {
                        item.is_read = true;
                    }
                });

                window.addEventListener('bdes:all-notifications-read', () => {
                    this.items.forEach(i => { i.is_read = true; });
                    this.readIds = this.items.map(i => i.id);
                    this.unreadCount = 0;
                });

                // Support direct opening via ?open=ID query parameter
                const urlParams = new URLSearchParams(window.location.search);
                const openId = urlParams.get('open');
                if (openId) {
                    this.openNotification(openId);
                }
            },

            isItemRead(id) {
                return this.readIds.includes(id);
            },

            openNotification(id) {
                const item = this.items.find(i => i.id === id);
                if (!item) return;

                this.selectedItem = item;
                this.detailsModalOpen = true;

                // Automatically mark as read when viewed
                if (!this.isItemRead(id)) {
                    this.markAsRead(item);
                }
            },

            closeDetails() {
                this.detailsModalOpen = false;
                this.selectedItem = null;
            },

            markAsRead(item) {
                if (this.readIds.includes(item.id)) return;

                // 1. Immediately update reactive state
                this.readIds.push(item.id);
                item.is_read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);

                // 2. Dispatch cross-component event so navbar bell count updates immediately
                window.dispatchEvent(new CustomEvent('bdes:notification-read', {
                    detail: { id: item.id }
                }));

                // 3. Persist to server and database with keepalive
                fetch(config.markReadUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: item.id }),
                    keepalive: true
                }).catch(err => {
                    console.warn('Backend markAsRead warning:', err);
                });
            },

            handleViewRecord(id, targetUrl, event) {
                const item = this.items.find(i => i.id === id);
                if (item && !this.isItemRead(id)) {
                    this.markAsRead(item);
                }
                // Regular navigation proceeds to destination
            },

            markAllAsRead() {
                if (this.unreadCount === 0) return;

                const unreadIds = this.items.filter(i => !this.isItemRead(i.id)).map(i => i.id);

                this.items.forEach(i => { i.is_read = true; });
                this.readIds = Array.from(new Set([...this.readIds, ...unreadIds]));
                this.unreadCount = 0;

                window.dispatchEvent(new CustomEvent('bdes:all-notifications-read'));

                fetch(config.markAllUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: unreadIds }),
                    keepalive: true
                }).catch(err => {
                    console.warn('Backend markAllAsRead warning:', err);
                });
            }
        };
    }
</script>
@endsection
