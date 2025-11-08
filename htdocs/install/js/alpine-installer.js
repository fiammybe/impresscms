/**
 * ImpressCMS Installer - Alpine.js Module
 *
 * Handles installer UI interactions using Alpine.js
 * Replaces jQuery-based functionality with modern Alpine.js
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('installerUI', () => ({
        // Stylesheet management
        availableStylesheets: [],
        activeStylesheetIndex: 0,
        helpVisible: false,
        scrolling: false,

        /**
         * Initialize the installer UI
         */
        init() {
            this.initStylesheets();
        },

        /**
         * Initialize available stylesheets
         */
        initStylesheets() {
            const links = document.querySelectorAll('link[rel*=style][title]');
            links.forEach((link) => {
                this.availableStylesheets.push(link.getAttribute('title'));
            });

            // Load saved stylesheet preference
            const savedStyle = this.readCookie('style');
            if (savedStyle) {
                this.switchStylesheet(savedStyle);
            }
        },

        /**
         * Toggle between available stylesheets
         */
        toggleStylesheet() {
            this.activeStylesheetIndex++;
            this.activeStylesheetIndex %= this.availableStylesheets.length;
            this.switchStylesheet(this.availableStylesheets[this.activeStylesheetIndex]);
        },

        /**
         * Switch to a specific stylesheet by name
         * @param {string} styleName - The name of the stylesheet to activate
         */
        switchStylesheet(styleName) {
            const links = document.querySelectorAll('link[rel*=style][title]');
            links.forEach((link, index) => {
                link.disabled = true;
                if (link.getAttribute('title') === styleName) {
                    link.disabled = false;
                    this.activeStylesheetIndex = index;
                }
            });
            this.createCookie('style', styleName, 365);
        },

        /**
         * Toggle help section visibility
         */
        toggleHelp() {
            this.helpVisible = !this.helpVisible;
            const helpDiv = document.querySelector('div.xoform-help');
            if (helpDiv) {
                if (this.helpVisible) {
                    helpDiv.style.display = 'block';
                    // Smooth scroll animation
                    helpDiv.scrollIntoView({ behavior: 'smooth' });
                } else {
                    helpDiv.style.display = 'none';
                }
            }
        },

        /**
         * Scroll to bottom of page
         */
        scrollToBottom() {
            if (this.scrolling) return;
            this.scrolling = true;

            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });

            setTimeout(() => {
                this.scrolling = false;
            }, 1500);
        },

        /**
         * Create a cookie
         * @param {string} name - Cookie name
         * @param {string} value - Cookie value
         * @param {number} days - Number of days until expiration
         */
        createCookie(name, value, days) {
            let expires = '';
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            document.cookie = name + '=' + value + expires + '; path=/';
        },

        /**
         * Read a cookie value
         * @param {string} name - Cookie name
         * @returns {string|null} Cookie value or null if not found
         */
        readCookie(name) {
            const nameEQ = name + '=';
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                let cookie = cookies[i].trim();
                if (cookie.indexOf(nameEQ) === 0) {
                    return cookie.substring(nameEQ.length);
                }
            }
            return null;
        },

        /**
         * Erase a cookie
         * @param {string} name - Cookie name
         */
        eraseCookie(name) {
            this.createCookie(name, '', -1);
        },

        /**
         * Update collation field based on charset selection
         * @param {string} elementId - The ID of the element to update
         * @param {string} charset - The selected charset value
         */
        async updateCollation(elementId, charset) {
            const element = document.getElementById(elementId);
            if (!element) return;

            // Show/hide the collation field
            if (charset === '') {
                element.style.display = 'none';
                return;
            } else {
                element.style.display = 'block';
            }

            // Fetch updated collation options from server
            try {
                const params = new URLSearchParams();
                params.set('action', 'updateCollation');
                params.set('charset', charset);

                const response = await fetch('page_dbsettings.php?' + params.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'text/html'
                    }
                });

                if (response.ok) {
                    const html = await response.text();

                    // Find the select element within the container
                    const selectElement = element.querySelector('select');
                    if (selectElement) {
                        // Replace the select element with the new one
                        const parser = new DOMParser();
                        const newSelect = parser.parseFromString(html, 'text/html').body.firstChild;
                        selectElement.replaceWith(newSelect);
                    } else {
                        // If no select found, replace entire content
                        element.innerHTML = html;
                    }
                } else {
                    console.error('Failed to fetch collation options:', response.statusText);
                }
            } catch (error) {
                console.error('Error updating collation:', error);
            }
        },

        /**
         * Check password strength (for admin account setup)
         * @param {string} password - The password to check
         * @param {string} username - The username to compare against
         * @returns {Object} Strength information
         */
        checkPasswordStrength(password, username = '') {
            if (password === '') {
                return { level: 'empty', message: '' };
            }

            if (username && password === username) {
                return { level: 'error', message: 'Username and Password identical.' };
            }

            if (password.length < 6) {
                return { level: 'short', message: 'Too short' };
            }

            let strength = 0;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;
            if (password.length >= 12) strength++;

            if (strength <= 1) {
                return { level: 'bad', message: 'Weak' };
            } else if (strength <= 2) {
                return { level: 'good', message: 'Good' };
            } else {
                return { level: 'strong', message: 'Strong' };
            }
        }
    }));
});

