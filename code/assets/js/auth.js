/**
 * Authentication JavaScript
 * Handles login, registration, and session management
 */

class Auth {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.initEventListeners();
        this.checkSession();
    }

    /**
     * Initialize event listeners for auth forms
     */
    initEventListeners() {
        // Login form submission
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Registration form submission
        const registerForm = document.getElementById('register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.handleRegister(e));

            // Password strength meter
            const passwordInput = registerForm.querySelector('input[name="password"]');
            if (passwordInput) {
                passwordInput.addEventListener('input', (e) => this.updatePasswordStrength(e.target.value));
            }
        }

        // Logout button
        const logoutButtons = document.querySelectorAll('.logout-btn');
        logoutButtons.forEach(button => {
            button.addEventListener('click', (e) => this.handleLogout(e));
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', (e) => this.togglePasswordVisibility(e));
        });
    }

    /**
     * Handle login form submission
     */
    async handleLogin(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const email = form.querySelector('input[name="email"]').value.trim();
        const password = form.querySelector('input[name="password"]').value;
        const remember = form.querySelector('input[name="remember"]')?.checked || false;

        // Basic validation
        if (!this.validateEmail(email)) {
            this.showError('Please enter a valid email address');
            return;
        }

        if (!password) {
            this.showError('Please enter your password');
            return;
        }

        // Disable submit button and show loading state
        this.setLoading(submitBtn, true);

        try {
            const response = await fetch(`${window.APP_CONFIG.baseUrl}/api/auth.php?action=login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify({
                    email,
                    password,
                    remember
                })
            });

            const data = await response.json();

            if (data.success) {
                // Redirect to dashboard or intended URL
                const redirectUrl = this.getParameterByName('redirect') || '/';
                window.location.href = redirectUrl;
            } else {
                this.showError(data.message || 'Login failed. Please try again.');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showError('An error occurred. Please try again.');
        } finally {
            this.setLoading(submitBtn, false);
        }
    }

    /**
     * Handle registration form submission
     */
    async handleRegister(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const email = form.querySelector('input[name="email"]').value.trim();
        const displayName = form.querySelector('input[name="display_name"]').value.trim();
        const password = form.querySelector('input[name="password"]').value;
        const confirmPassword = form.querySelector('input[name="confirm_password"]').value;
        const terms = form.querySelector('input[name="terms"]')?.checked || false;

        // Validation
        if (!this.validateEmail(email)) {
            this.showError('Please enter a valid email address');
            return;
        }

        if (displayName.length < 3) {
            this.showError('Display name must be at least 3 characters long');
            return;
        }

        if (password.length < 8) {
            this.showError('Password must be at least 8 characters long');
            return;
        }

        if (password !== confirmPassword) {
            this.showError('Passwords do not match');
            return;
        }

        if (!terms) {
            this.showError('You must accept the terms and conditions');
            return;
        }

        // Disable submit button and show loading state
        this.setLoading(submitBtn, true);

        try {
            const response = await fetch(`${window.APP_CONFIG.baseUrl}/api/auth.php?action=register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify({
                    email,
                    display_name: displayName,
                    password,
                    terms: true
                })
            });

            const data = await response.json();

            if (data.success) {
                // Auto-login after successful registration
                const loginResponse = await fetch(`${window.APP_CONFIG.baseUrl}/api/auth.php?action=login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({
                        email,
                        password,
                        remember: true
                    })
                });

                const loginData = await loginResponse.json();

                if (loginData.success) {
                    // Redirect to dashboard or intended URL
                    const redirectUrl = this.getParameterByName('redirect') || '/';
                    window.location.href = redirectUrl;
                } else {
                    // If auto-login fails, redirect to login page
                    window.location.href = '/login.php?registered=true';
                }
            } else {
                this.showError(data.message || 'Registration failed. Please try again.');
            }
        } catch (error) {
            console.error('Registration error:', error);
            this.showError('An error occurred. Please try again.');
        } finally {
            this.setLoading(submitBtn, false);
        }
    }

    /**
     * Handle logout
     */
    async handleLogout(e) {
        e.preventDefault();

        try {
            const response = await fetch(`${window.APP_CONFIG.baseUrl}/api/auth.php?action=logout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = '/login.php';
            } else {
                this.showError('Logout failed. Please try again.');
            }
        } catch (error) {
            console.error('Logout error:', error);
            this.showError('An error occurred during logout.');
        }
    }

    /**
     * Check user session
     */
    async checkSession() {
        try {
            const response = await fetch(`${window.APP_CONFIG.baseUrl}/api/auth.php`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const data = await response.json();

            // Update UI based on authentication status
            if (data.authenticated) {
                document.body.classList.add('user-logged-in');
                this.updateUserUI(data.user);
            } else {
                document.body.classList.add('user-guest');
            }

            return data.authenticated;
        } catch (error) {
            console.error('Session check error:', error);
            return false;
        }
    }

    /**
     * Update UI elements based on user data
     */
    updateUserUI(user) {
        // Update user menu or other UI elements
        const userMenus = document.querySelectorAll('.user-menu');
        const authLinks = document.querySelectorAll('.auth-links');
        const userDisplayNames = document.querySelectorAll('.user-display-name');
        const userEmails = document.querySelectorAll('.user-email');
        const userAvatars = document.querySelectorAll('.user-avatar');

        userDisplayNames.forEach(el => {
            el.textContent = user.display_name || 'User';
        });

        userEmails.forEach(el => {
            el.textContent = user.email;
        });

        userAvatars.forEach(el => {
            // You can add avatar logic here
            const initials = (user.display_name || 'U').charAt(0).toUpperCase();
            el.textContent = initials;
            el.style.backgroundColor = this.stringToColor(user.email);
        });

        // Show/hide auth elements
        userMenus.forEach(menu => menu.style.display = 'block');
        authLinks.forEach(link => link.style.display = 'none');
    }

    /**
     * Toggle password visibility
     */
    togglePasswordVisibility(e) {
        const button = e.currentTarget;
        const input = button.closest('.input-group').querySelector('input');
        const icon = button.querySelector('svg');

        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);

        // Toggle icon
        if (type === 'password') {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
        } else {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
        }
    }

    /**
     * Update password strength meter
     */
    updatePasswordStrength(password) {
        let strength = 0;

        // Length check
        if (password.length >= 8) strength += 1;
        // Contains lowercase
        if (/[a-z]/.test(password)) strength += 1;
        // Contains uppercase
        if (/[A-Z]/.test(password)) strength += 1;
        // Contains number
        if (/[0-9]/.test(password)) strength += 1;
        // Contains special character
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;

        // Update UI
        const strengthMeter = document.querySelector('.password-strength-meter');
        if (strengthMeter) {
            const colors = ['bg-red-500', 'bg-yellow-500', 'bg-yellow-400', 'bg-blue-500', 'bg-green-500'];
            const width = (strength / 5) * 100;

            strengthMeter.style.width = `${width}%`;
            strengthMeter.className = `password-strength-meter h-full transition-all duration-300 ${colors[strength - 1] || 'bg-gray-200'}`;
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        // Remove any existing error messages
        this.clearErrors();

        // Create error element
        const errorEl = document.createElement('div');
        errorEl.className = 'bg-red-50 border-l-4 border-red-500 p-4 mb-4';
        errorEl.innerHTML = `
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">${message}</p>
                </div>
            </div>
        `;

        // Insert error message at the top of the form
        const form = document.querySelector('form');
        if (form) {
            form.insertBefore(errorEl, form.firstChild);

            // Scroll to error message
            errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    /**
     * Clear all error messages
     */
    clearErrors() {
        const errorMessages = document.querySelectorAll('.bg-red-50');
        errorMessages.forEach(el => {
            if (el.textContent.includes('Error:') || el.textContent.includes('failed')) {
                el.remove();
            }
        });
    }

    /**
     * Set loading state for a button
     */
    setLoading(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.classList.add('btn-loading');
        } else {
            button.disabled = false;
            button.classList.remove('btn-loading');
        }
    }

    /**
     * Validate email format
     */
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    /**
     * Generate a color from a string (for avatars)
     */
    stringToColor(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }

        const hue = hash % 360;
        return `hsl(${hue}, 70%, 60%)`;
    }

    /**
     * Get URL parameter by name
     */
    getParameterByName(name, url = window.location.href) {
        name = name.replace(/[\[\]]/g, '\\$&');
        const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
        const results = regex.exec(url);

        if (!results) return null;
        if (!results[2]) return '';

        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }
}

// Initialize auth when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.auth = new Auth();
});
