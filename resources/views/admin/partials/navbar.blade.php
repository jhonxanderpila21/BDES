<!-- Top Navigation Bar -->
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        {{-- Left Section --}}
        <div class="flex items-center gap-4">
            {{-- Sidebar Toggle --}}
            <button 
                @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-600"
            >
                <i class="fas fa-bars text-lg"></i>
            </button>

            {{-- Logo --}}
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 text-white p-2 rounded-lg">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="hidden sm:block">
                    <h2 class="text-lg font-bold text-gray-900">BDES</h2>
                    <p class="text-xs text-gray-600">Barangay Disaster Equipment System</p>
                </div>
            </div>
        </div>

        {{-- Right Section --}}
        <div class="flex items-center gap-6">
            {{-- Notifications Dropdown with Alpine.js Reactive Badge & Real-Time Data --}}
            @php
                $notifItems = $navbarNotifications['items'] ?? [];
            @endphp
            <div 
                class="relative" 
                x-data="bdesNotificationComponent({
                    items: {{ Js::from($notifItems) }},
                    userId: '{{ Auth::id() }}',
                    markReadUrl: '{{ route('notifications.mark-read') }}',
                    markAllUrl: '{{ route('notifications.mark-all-read') }}',
                    csrfToken: '{{ csrf_token() }}'
                })"
                @click.outside="open = false"
            >
                <button 
                    type="button"
                    @click="open = !open"
                    class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    title="Notifications"
                >
                    <i class="fas fa-bell text-lg"></i>
                    
                    {{-- Reactive Unread Count Badge (Requirement 1, 2, 3) --}}
                    <span 
                        x-show="unreadCount > 0"
                        x-cloak
                        x-text="unreadCount > 99 ? '99+' : unreadCount"
                        class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full shadow border-2 border-white min-w-[18px] transition-transform duration-200"
                    ></span>
                </button>

                {{-- Notifications Dropdown Menu --}}
                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                    class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-gray-200/90 z-50 overflow-hidden"
                >
                    {{-- Header with Filter Tabs & Mark All As Read --}}
                    <div class="p-3 sm:p-4 bg-gray-50/90 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-gray-900 text-sm">Notifications</h3>
                                <span 
                                    x-show="unreadCount > 0"
                                    x-text="unreadCount + ' new'"
                                    class="text-[10px] font-semibold bg-red-100 text-red-700 px-2 py-0.5 rounded-full"
                                ></span>
                            </div>
                            <button 
                                type="button"
                                x-show="unreadCount > 0"
                                @click="markAllAsRead()"
                                class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors hover:underline"
                            >
                                Mark all as read
                            </button>
                        </div>

                        {{-- All / Unread Filter Tabs --}}
                        <div class="flex items-center gap-1 bg-gray-200/70 p-0.5 rounded-lg text-xs">
                            <button 
                                type="button"
                                @click="tab = 'all'"
                                :class="tab === 'all' ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                class="flex-1 py-1 rounded-md text-center transition-all"
                            >
                                All (<span x-text="items.length"></span>)
                            </button>
                            <button 
                                type="button"
                                @click="tab = 'unread'"
                                :class="tab === 'unread' ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                class="flex-1 py-1 rounded-md text-center transition-all"
                            >
                                Unread (<span x-text="unreadCount"></span>)
                            </button>
                        </div>
                    </div>

                    {{-- Scrollable List with Smooth Animations --}}
                    <div class="divide-y divide-gray-100 max-h-80 sm:max-h-96 overflow-y-auto">
                        <template x-if="filteredItems.length === 0">
                            <div class="py-10 text-center px-4">
                                <div class="w-10 h-10 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-2">
                                    <i class="fas fa-bell-slash text-base"></i>
                                </div>
                                <p class="text-xs font-semibold text-gray-600">No notifications to display</p>
                                <p class="text-[11px] text-gray-400 mt-0.5" x-text="tab === 'unread' ? 'You are all caught up!' : 'No system alerts found'"></p>
                            </div>
                        </template>

                        <template x-for="item in filteredItems" :key="item.id">
                            <div 
                                class="p-3 transition-colors duration-150 flex items-start gap-3 relative group"
                                :class="item.is_read ? 'bg-white opacity-85 hover:bg-gray-50' : 'bg-blue-50/40 hover:bg-blue-50/70 font-medium'"
                            >
                                {{-- Severity / Category Icon --}}
                                <div 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-xs shadow-xs"
                                    :class="item.color || 'bg-blue-100 text-blue-600'"
                                >
                                    <i :class="item.icon || 'fas fa-info-circle'"></i>
                                </div>

                                {{-- Notification Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-1">
                                        <p class="text-xs font-semibold text-gray-900 truncate" x-text="item.title"></p>
                                        <span class="text-[10px] text-gray-400 whitespace-nowrap ml-1" x-text="item.timestamp"></span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-0.5 line-clamp-2 leading-relaxed" x-text="item.message"></p>

                                    {{-- Status Tag & Action Row --}}
                                    <div class="flex items-center justify-between mt-2 pt-1">
                                        <div class="flex items-center gap-1.5">
                                            <span 
                                                class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded"
                                                :class="{
                                                    'bg-red-100 text-red-700': item.severity === 'critical',
                                                    'bg-amber-100 text-amber-700': item.severity === 'warning',
                                                    'bg-blue-100 text-blue-700': item.severity === 'info' || !item.severity
                                                }"
                                                x-text="item.category || 'General'"
                                            ></span>
                                            <span x-show="!item.is_read" class="w-1.5 h-1.5 rounded-full bg-blue-600 inline-block"></span>
                                        </div>

                                        {{-- Actions: View Record (automatically marks as read on view) --}}
                                        <div class="flex items-center gap-1">
                                            <a 
                                                :href="item.url" 
                                                @click.stop="markAsRead(item)"
                                                title="Open record"
                                                class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-100/60 rounded-md transition-colors"
                                            >
                                                <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Footer --}}
                    <div class="p-2.5 sm:p-3 bg-gray-50 border-t border-gray-100 text-center">
                        <a 
                            href="{{ route('notifications.index') }}" 
                            class="w-full inline-flex items-center justify-center gap-1.5 py-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 hover:bg-blue-50/80 rounded-lg transition-colors"
                        >
                            <span>View All Notifications</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- User Profile Dropdown --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button 
                    @click="open = !open"
                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'User' }}</p>
                        <p class="text-xs text-gray-600">{{ ucwords(str_replace('_', ' ', Auth::user()->role ?? 'User')) }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-600 hidden sm:block"></i>
                </button>

                {{-- User Dropdown Menu --}}
                <div
                    x-show="open"
                    x-cloak
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 divide-y divide-gray-200"
                    x-transition
                >
                    <div class="p-4">
                        <p class="text-sm font-semibold text-gray-900">Logged in as</p>
                        <p class="text-sm text-gray-600 truncate">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                    <div class="py-2">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-user-circle mr-2"></i> My Profile
                        </a>
                    </div>
                    <div class="py-2">
                        <form action="{{ route('logout') }}" method="POST" class="px-4 py-2">
                            @csrf
                            <button type="submit" class="w-full text-left text-sm text-red-600 hover:bg-red-50 rounded py-2 px-2 transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    function bdesNotificationComponent(config) {
        return {
            open: false,
            tab: 'all',
            items: [],
            unreadCount: 0,
            readIds: [],
            storageKey: 'bdes_read_notifs_' + (config.userId || 'guest'),

            init() {
                let localRead = [];
                try {
                    const stored = JSON.parse(localStorage.getItem(this.storageKey) || '[]');
                    if (Array.isArray(stored)) {
                        localRead = stored;
                    }
                } catch (e) {
                    localRead = [];
                }

                // Collect server marked read items
                const serverRead = (config.items || []).filter(i => i.is_read).map(i => i.id);

                // Merge unique read IDs
                this.readIds = Array.from(new Set([...localRead, ...serverRead]));
                this.syncStorage();

                // Map items ensuring each has a reactive is_read property
                this.items = (config.items || []).map(item => ({
                    ...item,
                    message: item.message || item.description || '',
                    color: item.color || item.icon_bg || 'bg-blue-100 text-blue-600',
                    is_read: this.readIds.includes(item.id) || !!item.is_read
                }));

                // Calculate reactive unread count
                this.unreadCount = this.items.filter(i => !i.is_read).length;

                // Listen for cross-component read events from Notification Center
                window.addEventListener('bdes:notification-read', (e) => {
                    const id = e.detail?.id;
                    if (!id) return;

                    const item = this.items.find(i => i.id === id);
                    if (item) {
                        if (!item.is_read) {
                            item.is_read = true;
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                    } else if (!this.readIds.includes(id)) {
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }

                    if (!this.readIds.includes(id)) {
                        this.readIds = [...this.readIds, id];
                        this.syncStorage();
                    }
                });

                window.addEventListener('bdes:all-notifications-read', () => {
                    this.items.forEach(i => { i.is_read = true; });
                    this.unreadCount = 0;
                    const allIds = this.items.map(i => i.id);
                    this.readIds = Array.from(new Set([...this.readIds, ...allIds]));
                    this.syncStorage();
                });
            },

            syncStorage() {
                try {
                    localStorage.setItem(this.storageKey, JSON.stringify(this.readIds));
                } catch (e) {}
            },

            isRead(id) {
                return this.readIds.includes(id);
            },

            get filteredItems() {
                if (this.tab === 'unread') {
                    return this.items.filter(item => !item.is_read);
                }
                return this.items;
            },

            markAsRead(item, event) {
                if (event) {
                    event.stopPropagation();
                }

                // Prevent duplicate count reductions
                if (item.is_read || this.readIds.includes(item.id)) {
                    return;
                }

                // 1. Mark item as read immediately on the reactive object
                item.is_read = true;

                // 2. Decrement unread count by 1
                this.unreadCount = Math.max(0, this.unreadCount - 1);

                // 3. Update readIds and persist to localStorage
                if (!this.readIds.includes(item.id)) {
                    this.readIds = [...this.readIds, item.id];
                    this.syncStorage();
                }

                // 4. Dispatch global event to sync Notification Center page
                window.dispatchEvent(new CustomEvent('bdes:notification-read', {
                    detail: { id: item.id }
                }));

                // 5. Persist read status to backend with keepalive
                fetch(config.markReadUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: item.id }),
                    keepalive: true
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    // Synchronized successfully with server
                })
                .catch(err => {
                    console.warn('Backend markAsRead warning:', err);
                });
            },

            markAllAsRead() {
                if (this.unreadCount === 0) return;

                const unreadItems = this.items.filter(i => !i.is_read);
                const unreadIds = unreadItems.map(i => i.id);

                // 1. Optimistic UI update: mark all items as read and set unread count to 0
                this.items.forEach(i => { i.is_read = true; });
                this.unreadCount = 0;
                this.readIds = Array.from(new Set([...this.readIds, ...unreadIds]));
                this.syncStorage();

                // 2. Dispatch global event to sync Notification Center page
                window.dispatchEvent(new CustomEvent('bdes:all-notifications-read'));

                // 3. Persist to backend with keepalive
                fetch(config.markAllUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: unreadIds }),
                    keepalive: true
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }
                    return res.json();
                })
                .catch(err => {
                    console.warn('Backend markAllAsRead warning:', err);
                });
            }
        };
    }
</script>
