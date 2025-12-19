<header class="bg-white shadow-md sticky top-0 z-50">
    <nav class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full flex items-center justify-center text-white font-bold text-xl">
                    FR
                </div>
                <span class="text-2xl font-bold text-gray-900">Football<span class="text-blue-600">Review</span></span>
            </div>

            <div class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="matches.php" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
                    <i class="fas fa-futbol mr-2"></i>Matches
                </a>
                <a href="leagues.php" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
                    <i class="fas fa-trophy mr-2"></i>Leagues
                </a>
                <a href="teams.php" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
                    <i class="fas fa-shield-alt mr-2"></i>Teams
                </a>
                <a href="messages.php" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
                    <i class="fas fa-comments mr-2"></i>Messages
                </a>
                
                <div class="border-l border-gray-300 h-6"></div>
                
                <a href="profile.php" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 flex items-center">
                    <div class="w-8 h-8 rounded-full border-2 border-blue-500 bg-yellow-200 flex items-center justify-center text-gray-900 font-bold mr-2">
                        A
                    </div>
                    <span class="hidden md:inline font-medium"><?= htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username'] ?? 'Admin') ?></span>
                </a>
                <a href="../logout.php" class="ml-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
            </div>

            <!-- Mobile menu button -->
            <button id="mobile-menu-button" class="md:hidden text-gray-700">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-200 pt-4">
            <div class="flex flex-col space-y-3">
                <a href="index.php" class="text-gray-700 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="matches.php" class="text-gray-700 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-futbol mr-2"></i>Matches
                </a>
                <a href="leagues.php" class="text-gray-700 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-trophy mr-2"></i>Leagues
                </a>
                <a href="teams.php" class="text-gray-700 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-shield-alt mr-2"></i>Teams
                </a>
                <a href="messages.php" class="text-gray-700 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-comments mr-2"></i>Messages
                </a>
                <a href="profile.php" class="text-gray-700 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors border-t border-gray-200 pt-3 mt-3">
                    <i class="fas fa-user mr-2"></i>My Profile
                </a>
                <a href="../logout.php" class="text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
