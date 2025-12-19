/**
 * Main JavaScript file
 * Initializes all components and handles global functionality
 */

// Import other JS modules
import './utils.js';
import './auth.js';

class App {
    constructor() {
        this.initializeComponents();
        this.setupEventListeners();
        this.checkAuthState();
    }

    /**
     * Initialize all components
     */
    initializeComponents() {
        // Initialize tooltips
        this.initializeTooltips();

        // Initialize dropdowns
        this.initializeDropdowns();

        // Initialize modals
        this.initializeModals();

        // Initialize tabs
        this.initializeTabs();
    }

    /**
     * Set up global event listeners
     */
    setupEventListeners() {
        // Handle clicks on the document
        document.addEventListener('click', (e) => {
            // Close dropdowns when clicking outside
            if (!e.target.closest('[data-dropdown]')) {
                this.closeAllDropdowns();
            }

            // Close modals when clicking on the overlay
            if (e.target.classList.contains('modal-overlay')) {
                this.closeAllModals();
            }
        });

        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
                this.closeAllModals();
            }
        });
    }

    /**
     * Check authentication state and update UI
     */
    async checkAuthState() {
        try {
            const response = await fetch(`${window.APP_CONFIG.baseUrl}/api/auth.php`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.authenticated) {
                document.body.classList.add('user-logged-in');
                this.updateAuthUI(data.user);
            } else {
                document.body.classList.add('user-guest');
            }
        } catch (error) {
            console.error('Error checking auth state:', error);
        }
    }

    /**
     * Update UI based on authentication state
     */
    updateAuthUI(user) {
        // Update user menu or other UI elements
        const authElements = document.querySelectorAll('[data-auth]');
        const guestElements = document.querySelectorAll('[data-guest]');
        const userDisplays = document.querySelectorAll('[data-user-display]');
        const userAvatars = document.querySelectorAll('[data-user-avatar]');

        // Show/hide auth/guest elements
        authElements.forEach(el => {
            el.style.display = 'block';
        });

        guestElements.forEach(el => {
            el.style.display = 'none';
        });

        // Update user display info
        if (user) {
            userDisplays.forEach(el => {
                const attr = el.getAttribute('data-user-display');
                if (attr === 'name') {
                    el.textContent = user.display_name || 'User';
                } else if (attr === 'email') {
                    el.textContent = user.email;
                } else if (attr === 'initials') {
                    el.textContent = (user.display_name || 'U').charAt(0).toUpperCase();
                }
            });

            // Update avatars
            userAvatars.forEach(el => {
                if (user.avatar_url) {
                    el.style.backgroundImage = `url('${user.avatar_url}')`;
                    el.textContent = '';
                } else {
                    const initials = (user.display_name || 'U').split(' ')
                        .map(n => n[0])
                        .join('')
                        .toUpperCase()
                        .substring(0, 2);

                    el.textContent = initials;
                    el.style.backgroundColor = this.stringToColor(user.email || 'user@example.com');
                }
            });
        }
    }

    /**
     * Initialize tooltips
     */
    initializeTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');

        tooltips.forEach(tooltip => {
            const text = tooltip.getAttribute('data-tooltip');
            const position = tooltip.getAttribute('data-tooltip-pos') || 'top';

            // Create tooltip element
            const tooltipEl = document.createElement('div');
            tooltipEl.className = `tooltip tooltip-${position} hidden`;
            tooltipEl.textContent = text;

            // Add to DOM
            tooltip.appendChild(tooltipEl);

            // Add event listeners
            tooltip.addEventListener('mouseenter', () => {
                tooltipEl.classList.remove('hidden');
            });

            tooltip.addEventListener('mouseleave', () => {
                tooltipEl.classList.add('hidden');
            });
        });
    }

    /**
     * Initialize dropdown menus
     */
    initializeDropdowns() {
        const dropdownToggles = document.querySelectorAll('[data-dropdown-toggle]');

        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const dropdownId = toggle.getAttribute('data-dropdown-toggle');
                const dropdown = document.getElementById(dropdownId);

                if (dropdown) {
                    // Close all other dropdowns
                    this.closeAllDropdowns(dropdownId);

                    // Toggle current dropdown
                    dropdown.classList.toggle('hidden');

                    // Position dropdown
                    this.positionDropdown(toggle, dropdown);
                }
            });
        });
    }

    /**
     * Close all dropdowns except the one with the given ID
     */
    closeAllDropdowns(exceptId = null) {
        document.querySelectorAll('[data-dropdown]').forEach(dropdown => {
            if (!exceptId || dropdown.id !== exceptId) {
                dropdown.classList.add('hidden');
            }
        });
    }

    /**
     * Position dropdown relative to its toggle
     */
    positionDropdown(toggle, dropdown) {
        const toggleRect = toggle.getBoundingClientRect();
        const dropdownRect = dropdown.getBoundingClientRect();

        // Default position is below the toggle
        let top = toggleRect.bottom + window.scrollY;
        let left = toggleRect.left + window.scrollX;

        // Check if dropdown would go off the right edge of the viewport
        if (left + dropdownRect.width > window.innerWidth) {
            left = window.innerWidth - dropdownRect.width - 10; // 10px padding from edge
        }

        // Check if dropdown would go off the bottom of the viewport
        if (top + dropdownRect.height > window.innerHeight + window.scrollY) {
            // Position above the toggle instead
            top = toggleRect.top + window.scrollY - dropdownRect.height;
        }

        // Apply position
        dropdown.style.position = 'absolute';
        dropdown.style.top = `${top}px`;
        dropdown.style.left = `${left}px`;
        dropdown.style.width = 'auto';
        dropdown.style.minWidth = `${toggleRect.width}px`;
    }

    /**
     * Initialize modals
     */
    initializeModals() {
        // Open modal buttons
        const openButtons = document.querySelectorAll('[data-modal-toggle]');

        openButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-toggle');
                const modal = document.getElementById(modalId);

                if (modal) {
                    this.openModal(modal);
                }
            });
        });

        // Close modal buttons
        const closeButtons = document.querySelectorAll('[data-modal-hide]');

        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-hide');
                const modal = document.getElementById(modalId);

                if (modal) {
                    this.closeModal(modal);
                } else {
                    // If no specific modal ID, find the closest parent modal
                    const parentModal = button.closest('.modal');
                    if (parentModal) {
                        this.closeModal(parentModal);
                    }
                }
            });
        });
    }

    /**
     * Open a modal
     */
    openModal(modal) {
        // Create overlay if it doesn't exist
        let overlay = document.querySelector('.modal-overlay');

        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'modal-overlay fixed inset-0 bg-black bg-opacity-50 z-40';
            document.body.appendChild(overlay);

            // Close modal when clicking on overlay
            overlay.addEventListener('click', () => {
                this.closeAllModals();
            });
        }

        // Show modal
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Focus first focusable element in modal
        const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) {
            focusable.focus();
        }
    }

    /**
     * Close a modal
     */
    closeModal(modal) {
        modal.classList.add('hidden');

        // Remove overlay if no other modals are open
        const openModals = document.querySelectorAll('.modal:not(.hidden)');
        if (openModals.length === 0) {
            const overlay = document.querySelector('.modal-overlay');
            if (overlay) {
                overlay.remove();
            }
            document.body.classList.remove('overflow-hidden');
        }
    }

    /**
     * Close all modals
     */
    closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            this.closeModal(modal);
        });
    }

    /**
     * Initialize tabs
     */
    initializeTabs() {
        const tabGroups = document.querySelectorAll('[data-tabs]');

        tabGroups.forEach(group => {
            const tabs = group.querySelectorAll('[data-tab]');
            const tabContents = group.querySelectorAll('[data-tab-content]');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabId = tab.getAttribute('data-tab');

                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    // Show corresponding content
                    tabContents.forEach(content => {
                        if (content.getAttribute('data-tab-content') === tabId) {
                            content.classList.remove('hidden');
                        } else {
                            content.classList.add('hidden');
                        }
                    });
                });
            });

            // Activate first tab by default
            if (tabs.length > 0) {
                tabs[0].click();
            }
        });
    }

    /**
     * Generate a color from a string
     */
    stringToColor(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }

        const hue = hash % 360;
        return `hsl(${hue}, 70%, 60%)`;
    }
}

// Initialize the app when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});
