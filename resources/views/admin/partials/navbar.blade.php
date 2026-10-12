<!-- Top Navigation Bar -->
<nav class="bg-[#003366] border-b border-[#002244] text-white shadow-sm">
    <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        {{-- Left Section --}}
        <div class="flex items-center gap-4">
            {{-- Sidebar Toggle --}}
            <button 
                @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-lg hover:bg-white/10 transition-colors text-white"
            >
                <i class="fas fa-bars text-lg"></i>
            </button>

            {{-- Logo --}}
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/bdes-logo.png') }}" alt="BDES Logo" class="w-9 h-9 object-contain rounded-full shadow-sm">
                <div class="hidden sm:block">
                    <h2 class="text-base font-bold text-white leading-tight">BDES</h2>
                    <p class="text-[11px] font-medium text-[#60A5FA] leading-none">Disaster Assets</p>
                </div>
            </div>
        </div>

        {{-- Right Section --}}
        <div class="flex items-center gap-6">
            {{-- Notifications --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button 
                    @click="open = !open"
                    class="relative p-2 rounded-lg hover:bg-white/10 transition-colors text-blue-100 hover:text-white"
                >
                    <i class="fas fa-bell text-lg"></i>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-[#DC2626] rounded-full"></span>
                </button>

                {{-- Notifications Dropdown --}}
                <div
                    x-show="open"
                    x-cloak
                    class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-[#E5E7EB] z-50 text-[#1F2937]"
                    x-transition
                >
                    <div class="p-4 border-b border-[#E5E7EB]">
                        <h3 class="font-semibold text-[#1F2937]">Notifications</h3>
                    </div>
                    <div class="divide-y divide-[#E5E7EB] max-h-96 overflow-y-auto">
                        <div class="p-4 hover:bg-[#FAFAF9] cursor-pointer">
                            <p class="text-sm font-medium text-[#1F2937]">Equipment Maintenance Due</p>
                            <p class="text-xs text-[#6B7280] mt-1">Fire extinguisher needs inspection</p>
                            <p class="text-xs text-[#6B7280] mt-2">2 hours ago</p>
                        </div>
                        <div class="p-4 hover:bg-[#FAFAF9] cursor-pointer">
                            <p class="text-sm font-medium text-[#1F2937]">Deployment Reminder</p>
                            <p class="text-xs text-[#6B7280] mt-1">Team needs to return borrowed equipment</p>
                            <p class="text-xs text-[#6B7280] mt-2">5 hours ago</p>
                        </div>
                        <div class="p-4 hover:bg-[#FAFAF9] cursor-pointer">
                            <p class="text-sm font-medium text-[#1F2937]">Asset Inspection Due</p>
                            <p class="text-xs text-[#6B7280] mt-1">Scheduled physical inspection requires review</p>
                            <p class="text-xs text-[#6B7280] mt-2">1 day ago</p>
                        </div>
                    </div>
                    <div class="p-4 border-t border-[#E5E7EB] text-center">
                        <a href="#" class="text-sm text-[#2563EB] hover:underline font-medium">View All Notifications</a>
                    </div>
                </div>
            </div>

            {{-- User Profile Dropdown --}}
            <div class="relative" x-data="{ open: false, showLogoutModal: false }" @click.outside="open = false">
                <button 
                    @click="open = !open"
                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/10 transition-colors cursor-pointer"
                >
                    <div class="w-8 h-8 bg-[#2563EB] rounded-full flex items-center justify-center text-white text-sm font-semibold shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-white">{{ Auth::user()->name ?? 'User' }}</p>
                        <p class="text-xs text-[#60A5FA]">{{ ucwords(str_replace('_', ' ', Auth::user()->role ?? 'User')) }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-blue-200 hidden sm:block"></i>
                </button>

                {{-- User Dropdown Menu --}}
                <div
                    x-show="open"
                    x-cloak
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-[#E5E7EB] z-50 divide-y divide-[#E5E7EB] text-[#1F2937]"
                    x-transition
                >
                    <div class="p-4">
                        <p class="text-sm font-semibold text-[#1F2937]">Logged in as</p>
                        <p class="text-sm text-[#6B7280] truncate">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                    <div class="py-2">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-[#1F2937] hover:bg-[#FAFAF9] transition-colors">
                            <i class="fas fa-user-circle mr-2 text-[#2563EB]"></i> My Profile
                        </a>
                    </div>
                    <div class="py-2">
                        <button 
                            type="button"
                            @click="open = false; showLogoutModal = true"
                            class="w-full text-left text-sm text-[#DC2626] hover:bg-red-50 rounded py-2 px-4 transition-colors flex items-center cursor-pointer"
                        >
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </div>
                </div>

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
