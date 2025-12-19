<footer class="bg-gray-900 text-white pt-12 pb-8">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <!-- About Section -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full flex items-center justify-center text-white font-bold text-xl mr-2">FR</div>
                    <span class="text-2xl font-bold">Football<span class="text-blue-400">Review</span></span>
                </div>
                <p class="text-gray-400 mb-4">
                    Your go-to platform for sharing and discovering in-depth football match reviews, 
                    analyzing team performances, and connecting with fellow football enthusiasts worldwide.
                </p>
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/" target="_blank" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                    <a href="https://www.x.com/" target="_blank" class="text-gray-400 hover:text-white transition-colors font-bold text-xl inline-flex items-center">
                        𝕏
                    </a>
                    <a href="https://www.instagram.com/" target="_blank" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a href="https://www.youtube.com/" target="_blank" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-youtube text-xl"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="matches.php" class="text-gray-400 hover:text-white transition-colors">Upcoming Matches</a></li>
                    <li><a href="leagues.php" class="text-gray-400 hover:text-white transition-colors">Leagues</a></li>
                    <li><a href="teams.php" class="text-gray-400 hover:text-white transition-colors">Teams</a></li>
                    <li><a href="latest-reviews.php" class="text-gray-400 hover:text-white transition-colors">Latest Reviews</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Legal</h3>
                <ul class="space-y-2">
                    <li><a href="privacy.php" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="terms.php" class="text-gray-400 hover:text-white transition-colors">Terms of Service</a></li>
                    <li><a href="cookie-policy.php" class="text-gray-400 hover:text-white transition-colors">Cookie Policy</a></li>
                    <li><a href="chat.php" class="text-gray-400 hover:text-white transition-colors">Chat with Us</a></li>
                </ul>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <div class="md:flex justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-xl font-semibold mb-1">Subscribe to our newsletter</h3>
                    <p class="text-gray-400">Get the latest match reviews and updates delivered to your inbox.</p>
                </div>
                <form class="flex flex-col sm:flex-row gap-2">
                    <input type="email" placeholder="Your email address" 
                           class="px-4 py-2 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent flex-grow">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-gray-800 pt-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">
                    &copy; <?= date('Y') ?> FootballReview. All rights reserved.
                </p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-500 hover:text-white text-sm">
                        <i class="fas fa-flag mr-1"></i> English
                    </a>
                    <a href="sitemap.xml" class="text-gray-500 hover:text-white text-sm">
                        <i class="fas fa-sitemap mr-1"></i> Sitemap
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="back-to-top" class="fixed bottom-6 right-6 bg-blue-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:bg-blue-700 transition-colors hidden">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Back to top button
const backToTopButton = document.getElementById('back-to-top');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        backToTopButton.classList.remove('hidden');
    } else {
        backToTopButton.classList.add('hidden');
    }
});

backToTopButton.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>
<script type="module" src="<?= BASE_URL ?>/assets/js/main.js"></script>
