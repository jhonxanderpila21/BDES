<!-- Top Navigation Bar -->
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        
        <div class="flex items-center gap-4">
            
            <button 
                @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-600"
            >
                <i class="fas fa-bars text-lg"></i>
            </button>

            
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 text-white p-2 rounded-lg">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="hidden sm:block">
                    <h2 class="text-lg font-bold text-gray-900">BDES</h2>
                    <p class="text-xs text-gray-600">Disaster Assets</p>
                </div>
            </div>
        </div>

        
        <div class="flex items-center gap-6">
            
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button 
                    @click="open = !open"
                    class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-600"
                >
                    <i class="fas fa-bell text-lg"></i>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                
                <div
                    x-show="open"
                    x-cloak
                    class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                    x-transition
                >
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900">Notifications</h3>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        <div class="p-4 hover:bg-gray-50 cursor-pointer">
                            <p class="text-sm font-medium text-gray-900">Equipment Maintenance Due</p>
                            <p class="text-xs text-gray-600 mt-1">Fire extinguisher needs inspection</p>
                            <p class="text-xs text-gray-500 mt-2">2 hours ago</p>
                        </div>
                        <div class="p-4 hover:bg-gray-50 cursor-pointer">
                            <p class="text-sm font-medium text-gray-900">Deployment Reminder</p>
                            <p class="text-xs text-gray-600 mt-1">Team needs to return borrowed equipment</p>
                            <p class="text-xs text-gray-500 mt-2">5 hours ago</p>
                        </div>
                        <div class="p-4 hover:bg-gray-50 cursor-pointer">
                            <p class="text-sm font-medium text-gray-900">Incident Report Filed</p>
                            <p class="text-xs text-gray-600 mt-1">New incident report requires review</p>
                            <p class="text-xs text-gray-500 mt-2">1 day ago</p>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-200 text-center">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All Notifications</a>
                    </div>
                </div>
            </div>

            
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button 
                    @click="open = !open"
                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                        JD
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-gray-900">John Doe</p>
                        <p class="text-xs text-gray-600">Administrator</p>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-600 hidden sm:block"></i>
                </button>

                
                <div
                    x-show="open"
                    x-cloak
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 divide-y divide-gray-200"
                    x-transition
                >
                    <div class="p-4">
                        <p class="text-sm font-semibold text-gray-900">Logged in as</p>
                        <p class="text-sm text-gray-600">john.doe@barangay.gov</p>
                    </div>
                    <div class="py-2">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-user-circle mr-2"></i> My Profile
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                    </div>
                    <div class="py-2">
                        <form action="#" method="POST" class="px-4 py-2">
                            <?php echo csrf_field(); ?>
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
<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/admin/partials/navbar.blade.php ENDPATH**/ ?>