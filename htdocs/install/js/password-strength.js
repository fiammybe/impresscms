/**
 * Password Strength Checker
 *
 * Vanilla JavaScript password strength validation
 * Replaces jQuery password_strength_plugin.js
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 */

/**
 * Password Strength Checker
 */
const PasswordStrength = {
    // Configuration
    config: {
        shortPass: 'Too short',
        badPass: 'Weak',
        goodPass: 'Good',
        strongPass: 'Strong',
        samePassword: 'Username and Password identical.',
        resultStyle: 'top_testresult',
        shortPassId: 'top_shortPass',
        badPassId: 'top_badPass',
        goodPassId: 'top_goodPass',
        strongPassId: 'top_strongPass',
        userIdSelector: '#adminlogin_name'
    },

    /**
     * Initialize password strength checker
     * @param {Object} options - Configuration options
     */
    init(options = {}) {
        this.config = { ...this.config, ...options };
        this.attachListeners();
    },

    /**
     * Attach event listeners to password fields
     */
    attachListeners() {
        const passwordFields = document.querySelectorAll('.password_adv');
        passwordFields.forEach((field) => {
            field.addEventListener('keyup', () => this.checkStrength(field));
            field.addEventListener('change', () => this.checkStrength(field));
        });
    },

    /**
     * Check password strength
     * @param {HTMLElement} field - The password input field
     */
    checkStrength(field) {
        const password = field.value;
        const username = document.querySelector(this.config.userIdSelector)?.value || '';

        // Clear previous result
        this.clearResult();

        // Check if password is empty
        if (password === '') {
            return;
        }

        // Check if username and password are the same
        if (username && password === username) {
            this.showResult(this.config.samePassword, 'error');
            return;
        }

        // Calculate strength
        const strength = this.calculateStrength(password);

        // Show result based on strength
        switch (strength) {
            case 'short':
                this.showResult(this.config.shortPass, 'short');
                break;
            case 'bad':
                this.showResult(this.config.badPass, 'bad');
                break;
            case 'good':
                this.showResult(this.config.goodPass, 'good');
                break;
            case 'strong':
                this.showResult(this.config.strongPass, 'strong');
                break;
        }
    },

    /**
     * Calculate password strength
     * @param {string} password - The password to check
     * @returns {string} Strength level: 'short', 'bad', 'good', or 'strong'
     */
    calculateStrength(password) {
        // Check minimum length
        if (password.length < 6) {
            return 'short';
        }

        let strength = 0;

        // Check for lowercase letters
        if (/[a-z]/.test(password)) {
            strength++;
        }

        // Check for uppercase letters
        if (/[A-Z]/.test(password)) {
            strength++;
        }

        // Check for numbers
        if (/[0-9]/.test(password)) {
            strength++;
        }

        // Check for special characters
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            strength++;
        }

        // Check length bonus
        if (password.length >= 12) {
            strength++;
        }

        // Determine strength level
        if (strength <= 1) {
            return 'bad';
        } else if (strength <= 2) {
            return 'good';
        } else {
            return 'strong';
        }
    },

    /**
     * Show password strength result
     * @param {string} message - The message to display
     * @param {string} level - The strength level
     */
    showResult(message, level) {
        const resultDiv = document.getElementById(this.config.resultStyle);
        if (!resultDiv) {
            return;
        }

        // Clear previous classes
        resultDiv.className = this.config.resultStyle;

        // Add level-specific class
        resultDiv.classList.add(`strength-${level}`);

        // Set message
        resultDiv.textContent = message;
        resultDiv.style.display = 'block';
    },

    /**
     * Clear password strength result
     */
    clearResult() {
        const resultDiv = document.getElementById(this.config.resultStyle);
        if (resultDiv) {
            resultDiv.textContent = '';
            resultDiv.style.display = 'none';
        }
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        PasswordStrength.init();
    });
} else {
    PasswordStrength.init();
}

