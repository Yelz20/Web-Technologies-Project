<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$is_home = ($current_page === 'index');
?>
<script>
    window.APP_CONFIG = {
        baseUrl: '<?= BASE_URL ?>'
    };
</script>
<header class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <!-- Logo and Brand -->
            <div class="flex items-center mb-4 md:mb-0">
                <a href="index.php" class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full flex items-center justify-center text-white font-bold text-xl mr-2">FR</div>
                    <span class="text-2xl font-bold text-gray-800">Football<span class="text-blue-600">Review</span></span>
                </a>
            </div>

            <!-- Search Bar -->
            <div class="w-full md:w-1/3 mb-4 md:mb-0 px-4">
                <form action="matches.php" method="GET" class="relative">
                    <input type="text" name="search" placeholder="Search matches, teams, or reviews..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="submit" class="absolute right-3 top-2 text-gray-500 hover:text-blue-600">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Navigation -->
            <nav class="flex items-center space-x-1">
                <a href="index.php" class="px-3 py-2 rounded-lg <?= $is_home ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <i class="fas fa-home mr-1"></i> Home
                </a>
                <a href="matches.php" class="px-3 py-2 rounded-lg <?= ($current_page === 'matches') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <i class="fas fa-futbol mr-1"></i> Matches
                </a>
                <a href="leagues.php" class="px-3 py-2 rounded-lg <?= ($current_page === 'leagues') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <i class="fas fa-trophy mr-1"></i> Leagues
                </a>
                
                <?php if (is_logged_in()): ?>
                    <a href="profile.php" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 flex items-center">
                        <img src="<?= get_avatar_url($_SESSION['avatar_url'] ?? '', 32, $_SESSION['display_name'] ?? $_SESSION['username'] ?? 'User') ?>" 
                             alt="<?= htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username'] ?? 'User') ?>" 
                             class="w-8 h-8 rounded-full border-2 border-blue-500 mr-2">
                        <span class="hidden md:inline font-medium"><?= htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username'] ?? 'User') ?></span>
                    </a>
                    <a href="logout.php" class="ml-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="ml-2 px-4 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="register.php" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    
    <!-- Mobile Menu Button -->
    <div class="md:hidden px-4 py-2">
        <button id="mobile-menu-button" class="text-gray-700">
            <i class="fas fa-bars text-2xl"></i>
        </button>
    </div>
    
    <!-- Mobile Menu (hidden by default) -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
        <a href="index.php" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-100">
            <i class="fas fa-home mr-2"></i> Home
        </a>
        <a href="matches.php" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-100">
            <i class="fas fa-futbol mr-2"></i> Matches
        </a>
        <a href="leagues.php" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-100">
            <i class="fas fa-trophy mr-2"></i> Leagues
        </a>
        <?php if (is_logged_in()): ?>
            <a href="profile.php" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-100">
                <i class="fas fa-user-circle mr-2"></i> My Profile
            </a>
            <?php if (is_admin()): ?>
                <a href="admin/dashboard.php" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-100">
                    <i class="fas fa-tachometer-alt mr-2"></i> Admin Dashboard
                </a>
            <?php endif; ?>
            <a href="logout.php" class="block px-4 py-3 text-red-600 hover:bg-red-50 border-b border-gray-100">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        <?php else: ?>
            <a href="login.php" class="block px-4 py-3 text-blue-600 hover:bg-blue-50 border-b border-gray-100">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </a>
            <a href="register.php" class="block px-4 py-3 bg-blue-600 text-white hover:bg-blue-700">
                <i class="fas fa-user-plus mr-2"></i> Register
            </a>
        <?php endif; ?>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    }
});
</script>
