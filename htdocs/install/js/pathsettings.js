/**
 * Path Settings Page - Vanilla JavaScript
 *
 * Handles path validation and trust path creation
 * Replaces jQuery-based pathsettings.js
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 */

/**
 * Path Settings Manager
 */
const PathSettings = {
    /**
     * Initialize path settings handlers
     */
    init() {
        this.setupRootPathHandlers();
        this.setupTrustPathHandlers();
        this.setupPermRefreshHandler();
        this.showRootPermsSection();
    },

    /**
     * Show root permissions section with animation
     */
    showRootPermsSection() {
        const rootPerms = document.getElementById('rootperms');
        if (rootPerms) {
            rootPerms.style.display = 'none';
            // Simulate slideDown effect
            setTimeout(() => {
                rootPerms.style.display = 'block';
                rootPerms.style.opacity = '0';
                rootPerms.style.transition = 'opacity 0.5s ease-in';
                setTimeout(() => {
                    rootPerms.style.opacity = '1';
                }, 10);
            }, 100);
        }
    },

    /**
     * Setup root path input handlers
     */
    setupRootPathHandlers() {
        const rootPathInput = document.querySelector('input#rootpath');
        if (!rootPathInput) return;

        rootPathInput.addEventListener('focus', () => {
            this.fadeOut('rootpathimg');
        });

        rootPathInput.addEventListener('blur', () => {
            this.fadeIn('rootpathimg');
        });

        rootPathInput.addEventListener('change', () => {
            this.checkRootPath(rootPathInput.value);
        });
    },

    /**
     * Setup trust path input handlers
     */
    setupTrustPathHandlers() {
        const trustPathInput = document.querySelector('input#trustpath');
        if (!trustPathInput) return;

        trustPathInput.addEventListener('focus', () => {
            this.fadeOut('trustpathimg');
        });

        trustPathInput.addEventListener('blur', () => {
            this.fadeIn('trustpathimg');
        });

        trustPathInput.addEventListener('change', () => {
            this.checkTrustPath(trustPathInput.value);
        });
    },

    /**
     * Setup permission refresh button handler
     */
    setupPermRefreshHandler() {
        const permRefreshBtn = document.getElementById('permrefresh');
        if (permRefreshBtn) {
            permRefreshBtn.addEventListener('click', () => {
                permRefreshBtn.form.submit();
            });
        }
    },

    /**
     * Check root path via AJAX
     * @param {string} path - The path to check
     */
    async checkRootPath(path) {
        try {
            const params = new URLSearchParams();
            params.set('action', 'checkrootpath');
            params.set('path', path);

            const response = await fetch('page_pathsettings.php?' + params.toString());
            if (response.ok) {
                const html = await response.text();
                const rootPathImg = document.getElementById('rootpathimg');
                if (rootPathImg) {
                    rootPathImg.innerHTML = html;
                    this.fadeIn('rootpathimg');
                }
            }
        } catch (error) {
            console.error('Error checking root path:', error);
        }
    },

    /**
     * Check trust path via AJAX
     * @param {string} path - The path to check
     */
    async checkTrustPath(path) {
        try {
            const params = new URLSearchParams();
            params.set('action', 'checktrustpath');
            params.set('path', path);

            const response = await fetch('page_pathsettings.php?' + params.toString());
            if (response.ok) {
                const html = await response.text();
                const trustPathImg = document.getElementById('trustpathimg');
                if (trustPathImg) {
                    trustPathImg.innerHTML = html;
                    this.fadeIn('trustpathimg');
                }
            }
        } catch (error) {
            console.error('Error checking trust path:', error);
        }
    },

    /**
     * Create trust path via AJAX
     */
    async createTrustPath() {
        const trustPathInput = document.querySelector('input#trustpath');
        if (!trustPathInput) return;

        try {
            const params = new URLSearchParams();
            params.set('action', 'createtrustpath');
            params.set('path', trustPathInput.value);

            const response = await fetch('page_pathsettings.php?' + params.toString());
            if (response.ok) {
                const html = await response.text();

                // Update trust path image
                const trustPathImg = document.getElementById('trustpathimg');
                if (trustPathImg) {
                    trustPathImg.innerHTML = html;
                    this.fadeIn('trustpathimg');
                }

                // Update trust perms section
                const trustPerms = document.getElementById('trustperms');
                if (trustPerms) {
                    trustPerms.innerHTML = html;
                    this.fadeIn('trustperms');
                }

                // Re-check trust path
                await this.checkTrustPath(trustPathInput.value);
            }
        } catch (error) {
            console.error('Error creating trust path:', error);
        }
    },

    /**
     * Fade out an element
     * @param {string} elementId - The ID of the element to fade out
     */
    fadeOut(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.transition = 'opacity 0.3s ease-out';
            element.style.opacity = '0';
        }
    },

    /**
     * Fade in an element
     * @param {string} elementId - The ID of the element to fade in
     */
    fadeIn(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.transition = 'opacity 0.3s ease-in';
            element.style.opacity = '1';
        }
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        PathSettings.init();
        // Setup create trust path button handler
        const createTrustPathBtn = document.getElementById('createtrustpath');
        if (createTrustPathBtn) {
            createTrustPathBtn.addEventListener('click', () => {
                PathSettings.createTrustPath();
            });
        }
    });
} else {
    PathSettings.init();
    // Setup create trust path button handler
    const createTrustPathBtn = document.getElementById('createtrustpath');
    if (createTrustPathBtn) {
        createTrustPathBtn.addEventListener('click', () => {
            PathSettings.createTrustPath();
        });
    }
}

