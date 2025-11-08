/**
 * Stylesheet Toggle Utility
 *
 * Vanilla JavaScript utility for toggling between stylesheets
 * Replaces jQuery-based stylesheetToggle.js
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 */

/**
 * Cookie utility functions
 */
const CookieUtil = {
    /**
     * Create a cookie
     * @param {string} name - Cookie name
     * @param {string} value - Cookie value
     * @param {number} days - Number of days until expiration
     */
    create(name, value, days) {
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
    read(name) {
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
    erase(name) {
        this.create(name, '', -1);
    }
};

/**
 * Stylesheet Toggle Manager
 */
const StylesheetToggle = {
    availableStylesheets: [],
    activeStylesheetIndex: 0,

    /**
     * Initialize stylesheet toggle
     */
    init() {
        this.discoverStylesheets();
        this.restoreSavedStyle();
    },

    /**
     * Discover all available stylesheets
     */
    discoverStylesheets() {
        const links = document.querySelectorAll('link[rel*=style][title]');
        links.forEach((link) => {
            this.availableStylesheets.push(link.getAttribute('title'));
        });
    },

    /**
     * Restore previously saved stylesheet preference
     */
    restoreSavedStyle() {
        const savedStyle = CookieUtil.read('style');
        if (savedStyle) {
            this.switchTo(savedStyle);
        }
    },

    /**
     * Toggle to the next available stylesheet
     */
    toggle() {
        this.activeStylesheetIndex++;
        this.activeStylesheetIndex %= this.availableStylesheets.length;
        this.switchTo(this.availableStylesheets[this.activeStylesheetIndex]);
    },

    /**
     * Switch to a specific stylesheet by name
     * @param {string} styleName - The name of the stylesheet to activate
     */
    switchTo(styleName) {
        const links = document.querySelectorAll('link[rel*=style][title]');
        links.forEach((link, index) => {
            link.disabled = true;
            if (link.getAttribute('title') === styleName) {
                link.disabled = false;
                this.activeStylesheetIndex = index;
            }
        });
        CookieUtil.create('style', styleName, 365);
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        StylesheetToggle.init();
    });
} else {
    StylesheetToggle.init();
}

