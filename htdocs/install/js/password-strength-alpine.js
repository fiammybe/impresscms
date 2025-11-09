/**
 * Password Strength Checker using zxcvbn, Alpine.js, and HIBP API
 *
 * Provides real-time password strength estimation using the zxcvbn library
 * integrated with Alpine.js for reactive UI updates and HIBP Pwned Passwords
 * API for breach detection.
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 */

/**
 * Alpine.js component for password strength checking
 * Usage: x-data="passwordStrengthChecker()"
 */
function passwordStrengthChecker() {
    return {
        password: '',
        username: '',
        score: -1,
        showResult: false,
        resultMessage: '',
        resultClass: 'top_testresult',
        feedbackWarning: '',
        feedbackSuggestions: [],
        breachCount: 0,
        breachWarning: '',
        isCheckingBreach: false,
        debounceTimer: null,
        passwordCache: {},
        hibpDebounceDelay: 500,

        /**
         * Check password strength using zxcvbn and HIBP API
         * @param {string} newPassword - The password to check
         */
        checkPasswordStrength(newPassword) {
            this.password = newPassword;

            // Clear previous zxcvbn results (but NOT HIBP results)
            this.clearResults();

            // If password is empty, hide results
            if (!newPassword) {
                return;
            }

            // Get username from the admin login field
            const usernameField = document.getElementById('adminlogin_name');
            this.username = usernameField ? usernameField.value : '';

            // Check if password matches username
            if (this.username && newPassword === this.username) {
                this.showResult = true;
                this.resultMessage = 'Username and Password identical.';
                this.resultClass = 'top_testresult strength-error';
                return;
            }

            // Use zxcvbn to analyze password strength
            if (typeof zxcvbn !== 'undefined') {
                try {
                    const result = zxcvbn(newPassword, this.username ? [this.username] : []);
                    this.displayZxcvbnResult(result);
                } catch (error) {
                    console.error('Error analyzing password strength:', error);
                    this.showResult = false;
                }
            } else {
                console.warn('zxcvbn library not loaded');
                this.showResult = false;
            }

            // Check HIBP API with debouncing (only for passwords 6+ characters)
            if (newPassword.length >= 6) {
                console.log('Password length >= 6, calling checkHIBPWithDebounce');
                this.checkHIBPWithDebounce(newPassword);
            } else {
                console.log('Password length < 6, skipping HIBP check');
            }
        },

        /**
         * Debounced HIBP breach check
         * @param {string} password - The password to check
         */
        checkHIBPWithDebounce(password) {
            console.log('checkHIBPWithDebounce called');

            // Clear previous timer
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            // Clear HIBP results and show "checking" message
            // This happens AFTER debounce delay, not immediately
            this.debounceTimer = setTimeout(async () => {
                console.log('Debounce timer expired, clearing HIBP results and starting check');

                // Clear old HIBP results and show checking message
                this.breachWarning = '';
                this.breachCount = 0;
                this.isCheckingBreach = true;
                this.showResult = true;

                console.log('After setting flags - isCheckingBreach:', this.isCheckingBreach, 'showResult:', this.showResult);

                // Now perform the HIBP check
                await this.checkHIBPBreach(password);
            }, this.hibpDebounceDelay);
        },

        /**
         * Check if password has been compromised using HIBP API
         * @param {string} password - The password to check
         */
        async checkHIBPBreach(password) {
            console.log('checkHIBPBreach called for password length:', password.length);

            // Check cache first
            if (this.passwordCache[password] !== undefined) {
                const cachedResult = this.passwordCache[password];
                console.log('Using cached result:', cachedResult);
                this.displayHIBPResult(cachedResult);
                return;
            }

            this.isCheckingBreach = true;
            this.showResult = true;

            try {
                // Hash the password using SHA-1
                // If js-sha1 library is not available, this will throw an error
                const hash = await this.sha1Hash(password);

                const prefix = hash.substring(0, 5).toUpperCase();
                const suffix = hash.substring(5).toUpperCase();

                // Fetch from HIBP API using k-Anonymity model
                // Note: No custom headers to avoid CORS preflight requests
                const response = await fetch(`https://api.pwnedpasswords.com/range/${prefix}`);

                if (!response.ok) {
                    throw new Error(`HIBP API error: ${response.status}`);
                }

                const text = await response.text();

                // Split by either \r\n or \n
                const lines = text.split(/\r?\n/);

                // Search for the suffix in the response
                let breachCount = 0;
                for (const line of lines) {
                    const [hashSuffix, count] = line.split(':');
                    if (hashSuffix === suffix) {
                        breachCount = parseInt(count, 10);
                        console.log('HIBP: Password found in', breachCount, 'breaches');
                        break;
                    }
                }

                if (breachCount === 0) {
                    console.log('HIBP: Password not found in any breaches (checked', lines.length, 'entries)');
                }

                // Cache the result
                this.passwordCache[password] = breachCount;

                // Display the result
                console.log('Calling displayHIBPResult with breachCount:', breachCount);
                this.displayHIBPResult(breachCount);
            } catch (error) {
                console.warn('HIBP API check failed or unavailable:', error.message);
                // Gracefully degrade: disable HIBP checking if library or API is unavailable
                // Don't block the form - user can still proceed with password strength from zxcvbn
                this.isCheckingBreach = false;
                this.showResult = true;
                // Don't display breach warning if check failed
                this.breachWarning = '';
            }
        },

        /**
         * Display HIBP breach result
         * @param {number} breachCount - Number of times password appears in breaches
         */
        displayHIBPResult(breachCount) {
            console.log('displayHIBPResult called with breachCount:', breachCount);
            this.breachCount = breachCount;
            this.isCheckingBreach = false;
            this.showResult = true;

            if (breachCount > 0) {
                this.breachWarning = `This password has been exposed in ${breachCount} data breach${breachCount !== 1 ? 'es' : ''}. Consider using a different password.`;
                console.log('breachWarning set to:', this.breachWarning);

                // Downgrade strength if password is compromised
                if (this.score >= 3) {
                    this.resultMessage = 'Compromised';
                    this.resultClass = 'top_testresult strength-error';
                }
            } else {
                this.breachWarning = '';
                console.log('breachWarning cleared (no breaches found)');
            }
        },

        /**
         * SHA-1 hash function using js-sha1 library from CDN
         * @param {string} password - The password to hash
         * @returns {Promise<string>} - The SHA-1 hash in hexadecimal
         * @throws {Error} If js-sha1 library is not available
         */
        async sha1Hash(password) {
            // Check if js-sha1 library is available
            if (typeof sha1 === 'undefined') {
                throw new Error('js-sha1 library not available. HIBP breach checking is unavailable.');
            }

            try {
                console.log('Using js-sha1 library');
                const result = sha1(password);
                console.log('js-sha1 result:', result);
                return result;
            } catch (error) {
                console.error('js-sha1 failed:', error);
                throw new Error('Failed to hash password with js-sha1 library');
            }
        },

        /**
         * Display zxcvbn result with appropriate styling and feedback
         * @param {Object} result - The zxcvbn result object
         */
        displayZxcvbnResult(result) {
            this.score = result.score;
            this.showResult = true;

            // Map zxcvbn score (0-4) to strength levels and CSS classes
            let strengthLevel = 'bad';
            let strengthMessage = 'Weak';

            switch (result.score) {
                case 0:
                case 1:
                    strengthLevel = 'bad';
                    strengthMessage = 'Weak';
                    break;
                case 2:
                    strengthLevel = 'good';
                    strengthMessage = 'Good';
                    break;
                case 3:
                case 4:
                    strengthLevel = 'strong';
                    strengthMessage = 'Strong';
                    break;
            }

            // Set the result message
            this.resultMessage = strengthMessage;

            // Set CSS class for styling
            this.resultClass = `top_testresult strength-${strengthLevel}`;

            // Extract and display feedback
            if (result.feedback) {
                this.feedbackWarning = result.feedback.warning || '';
                this.feedbackSuggestions = result.feedback.suggestions || [];
            } else {
                this.feedbackWarning = '';
                this.feedbackSuggestions = [];
            }
        },

        /**
         * Clear all results (but NOT HIBP results - those are cleared separately)
         */
        clearResults() {
            this.showResult = false;
            this.resultMessage = '';
            this.resultClass = 'top_testresult';
            this.feedbackWarning = '';
            this.feedbackSuggestions = [];
            this.score = -1;
            // Note: breachWarning and breachCount are NOT cleared here
            // They are cleared separately in clearHIBPResults() when needed
        },

        /**
         * Clear HIBP results (called when user types a new password)
         */
        clearHIBPResults() {
            this.breachWarning = '';
            this.breachCount = 0;
            this.isCheckingBreach = false;
        }
    };
}

