<!-- Top Navigation Bar -->
<nav class="bg-[#003366] border-b border-[#002244] text-white shadow-sm">
    <div class="px-4 sm:px-6 lg:px-8 h-[68px] flex items-center justify-between">
        {{-- Left Section --}}
        <div class="flex items-center gap-4">
            {{-- Sidebar Hamburger Toggle (Toggles Icon-Only Rail on Desktop, Drawer on Mobile) --}}
            <button 
                type="button"
                @click="toggleSidebar()"
                class="p-2 rounded-lg hover:bg-white/10 transition-colors text-white focus:outline-none focus:ring-2 focus:ring-blue-400"
                title="Toggle Navigation Sidebar"
            >
                <i class="fas fa-bars text-lg"></i>
            </button>
        </div>

        {{-- Right Section --}}
        <div class="flex items-center gap-6 pr-3 sm:pr-5">
            {{-- User Profile Info (No dropdown arrow) & Logout Modal Handler --}}
            <div class="relative" x-data="{ showLogoutModal: false }" @bdes-open-logout.window="showLogoutModal = true">
                <a 
                    href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3.5 px-3 py-2 rounded-lg hover:bg-white/10 transition-colors"
                    title="Account Settings"
                >
                    <div class="w-9 h-9 bg-[#2563EB] rounded-full flex items-center justify-center text-white text-sm font-semibold shadow-sm shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left space-y-0.5">
                        <p class="text-sm font-semibold text-white leading-tight">{{ Auth::user()->name ?? 'User' }}</p>
                        <p class="text-xs text-[#60A5FA] leading-tight">{{ Auth::user()?->role_label ?? 'User' }}</p>
                    </div>
                </a>

                {{-- Logout Confirmation Modal --}}
                <div
                    x-show="showLogoutModal"
                    x-cloak
                    class="fixed inset-0 z-[100] overflow-y-auto"
                    aria-labelledby="modal-title"
                    role="dialog"
                    aria-modal="true"
                    @keydown.escape.window="showLogoutModal = false"
                >
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        {{-- Backdrop --}}
                        <div
                            x-show="showLogoutModal"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                            @click="showLogoutModal = false"
                            aria-hidden="true"
                        ></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        {{-- Modal Dialog Panel --}}
                        <div
                            x-show="showLogoutModal"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-[#E5E7EB]"
                        >
                            <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start gap-4">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0">
                                        <i class="fas fa-sign-out-alt text-[#DC2626] text-lg"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:text-left flex-1">
                                        <h3 class="text-lg leading-6 font-bold text-[#1F2937]" id="modal-title">
                                            Log Out Confirmation
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-[#6B7280]">
                                                Are you sure you want to log out?
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-[#FAFAF9] px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3 border-t border-[#E5E7EB]">
                                <form action="{{ route('logout') }}" method="POST" class="inline">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-[#DC2626] text-sm font-semibold text-white hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors cursor-pointer"
                                    >
                                        <i class="fas fa-sign-out-alt text-xs"></i>
                                        Log Out
                                    </button>
                                </form>
                                <button
                                    type="button"
                                    @click="showLogoutModal = false"
                                    class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-[#E5E7EB] shadow-sm px-4 py-2.5 bg-white text-sm font-medium text-[#1F2937] hover:bg-[#FAFAF9] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors cursor-pointer"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
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

            openItem(item) {
                if (!item.is_read) {
                    this.markAsRead(item);
                }
                if (item.url) {
                    window.location.href = item.url;
                }
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
