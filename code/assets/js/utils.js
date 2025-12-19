/**
 * Utility functions for the application
 */

class Utils {
    /**
     * Get CSRF token from meta tag
     */
    static getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Set CSRF token in all forms and AJAX requests
     */
    static setupCsrf() {
        const token = this.getCsrfToken();
        
        // Set token in all forms
        document.querySelectorAll('form').forEach(form => {
            const existingToken = form.querySelector('input[name="csrf_token"]');
            if (!existingToken) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'csrf_token';
                input.value = token;
                form.appendChild(input);
            } else {
                existingToken.value = token;
            }
        });

        // Set token in fetch headers
        const originalFetch = window.fetch;
        window.fetch = function(resource, options = {}) {
            // Only add CSRF token for same-origin requests
            const isSameOrigin = new URL(resource, window.location.origin).origin === window.location.origin;
            
            if (isSameOrigin) {
                options.headers = {
                    ...options.headers,
                    'X-CSRF-Token': token,
                    'X-Requested-With': 'XMLHttpRequest'
                };
            }
            
            return originalFetch(resource, options);
        };
    }

    /**
     * Show a flash message
     */
    static showFlashMessage(type, message, duration = 5000) {
        // Remove any existing flash messages
        this.removeFlashMessages();

        // Create flash message element
        const flash = document.createElement('div');
        flash.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg ${this.getFlashTypeClass(type)}`;
        flash.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${this.getFlashIcon(type)}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button type="button" class="inline-flex text-white focus:outline-none focus:text-white">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Add close button handler
        const closeButton = flash.querySelector('button');
        closeButton.addEventListener('click', () => {
            flash.remove();
        });

        // Add to body
        document.body.appendChild(flash);

        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                flash.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => flash.remove(), 300);
            }, duration);
        }
    }

    /**
     * Remove all flash messages
     */
    static removeFlashMessages() {
        document.querySelectorAll('.fixed.top-4.right-4').forEach(el => el.remove());
    }

    /**
     * Get CSS class for flash message type
     */
    static getFlashTypeClass(type) {
        const types = {
            success: 'bg-green-600',
            error: 'bg-red-600',
            warning: 'bg-yellow-600',
            info: 'bg-blue-600'
        };
        return types[type] || 'bg-gray-800';
    }

    /**
     * Get icon for flash message type
     */
    static getFlashIcon(type) {
        const icons = {
            success: '<svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>',
            error: '<svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>',
            warning: '<svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>',
            info: '<svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>'
        };
        return icons[type] || '';
    }

    /**
     * Debounce function to limit the rate at which a function can fire
     */
    static debounce(func, wait, immediate) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    /**
     * Throttle function to limit the rate at which a function can fire
     */
    static throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Format date to a readable string
     */
    static formatDate(date, format = 'en-US') {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Date(date).toLocaleDateString(format, options);
    }

    /**
     * Get URL parameter by name
     */
    static getParameterByName(name, url = window.location.href) {
        name = name.replace(/[\[\]]/g, '\\$&');
        const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
        const results = regex.exec(url);
        
        if (!results) return null;
        if (!results[2]) return '';
        
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }

    /**
     * Toggle element visibility
     */
    static toggleElement(selector, show = null) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            if (show === null) {
                el.classList.toggle('hidden');
            } else if (show) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });
    }

    /**
     * Copy text to clipboard
     */
    static copyToClipboard(text, element = null) {
        navigator.clipboard.writeText(text).then(() => {
            if (element) {
                const originalText = element.textContent;
                element.textContent = 'Copied!';
                setTimeout(() => {
                    element.textContent = originalText;
                }, 2000);
            }
        }).catch(err => {
            console.error('Could not copy text: ', err);
        });
    }
}

// Initialize utils when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Set up CSRF protection
    Utils.setupCsrf();
    
    // Make Utils available globally
    window.Utils = Utils;
});
