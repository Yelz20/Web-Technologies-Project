            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-section">
                        <h3>About Football Review</h3>
                        <p>Your ultimate destination for football match reviews, team analysis, and player statistics.</p>
                    </div>
                    <div class="footer-section">
                        <h3>Quick Links</h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo $base_url; ?>/about.php">About Us</a></li>
                            <li><a href="<?php echo $base_url; ?>/contact.php">Contact</a></li>
                            <li><a href="<?php echo $base_url; ?>/privacy.php">Privacy Policy</a></li>
                            <li><a href="<?php echo $base_url; ?>/terms.php">Terms of Service</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h3>Connect With Us</h3>
                        <div class="social-links">
                            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
                            <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                            <a href="#" target="_blank"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?php echo date('Y'); ?> Football Review. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>/assets/js/main.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom Scripts -->
    <script>
        // Mobile menu toggle
        document.querySelector('.navbar-toggler').addEventListener('click', function() {
            document.querySelector('.navbar-menu').classList.toggle('active');
        });

        // Dropdown menu
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown-toggle');
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.nextElementSibling.classList.toggle('show');
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.matches('.dropdown-toggle')) {
                    const dropdowns = document.querySelectorAll('.dropdown-menu');
                    dropdowns.forEach(dropdown => {
                        if (dropdown.classList.contains('show')) {
                            dropdown.classList.remove('show');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
