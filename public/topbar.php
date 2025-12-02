<?php
// Get current user info for display
$username = $_SESSION['username'] ?? 'User';
$role = ucfirst($_SESSION['role'] ?? 'Guest');
?>

<!-- Top Header Bar -->
<div class="fixed top-0 left-64 right-0 h-16 bg-white shadow-md z-40 flex items-center justify-between px-8">
    <!-- Page Title / Breadcrumb -->
    <div>
        <h1 class="text-xl font-semibold text-gray-800"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
    </div>
    
    <!-- Right Side: Notifications, Settings, Profile -->
    <div class="flex items-center gap-6">
        <!-- Notifications -->
        <div class="relative">
            <button class="relative p-2 text-gray-600 hover:text-secondary transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <!-- Notification Badge -->
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">3</span>
            </button>
        </div>
        
        <!-- Settings Dropdown -->
        <div class="relative group">
            <button class="p-2 text-gray-600 hover:text-secondary transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>
            <!-- Settings Dropdown Menu -->
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200">
                <a href="change_password.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Change Password</a>
                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Account Settings</a>
            </div>
        </div>
        
        <!-- Profile Dropdown -->
        <div class="relative group">
            <button class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-secondary to-accent flex items-center justify-center text-white font-semibold">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($username); ?></p>
                    <p class="text-xs text-gray-500"><?php echo $role; ?></p>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <!-- Profile Dropdown Menu -->
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200">
                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        My Profile
                    </div>
                </a>
                <div class="border-t my-1"></div>
                <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
