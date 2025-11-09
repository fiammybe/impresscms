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
                const hash = await this.sha1Hash(password);
				console.log('HIBP: hashed value:', password);
				console.log('HIBP: Full SHA-1 hash:', hash);

                const prefix = hash.substring(0, 5).toUpperCase();
                const suffix = hash.substring(5).toUpperCase();

                console.log('HIBP: Prefix (first 5 chars):', prefix);
                console.log('HIBP: Suffix (remaining chars):', suffix);
                console.log('HIBP: Checking password with prefix:', prefix);

                // Fetch from HIBP API using k-Anonymity model
                // Note: No custom headers to avoid CORS preflight requests
                const response = await fetch(`https://api.pwnedpasswords.com/range/${prefix}`);

                if (!response.ok) {
                    throw new Error(`HIBP API error: ${response.status}`);
                }

                const text = await response.text();
                console.log('HIBP API response length:', text.length);
                console.log('First 200 chars of response:', text.substring(0, 200));

                // Split by either \r\n or \n
                const lines = text.split(/\r?\n/);
                console.log('Number of lines in response:', lines.length);
                console.log('Looking for suffix:', suffix);

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
                console.warn('HIBP API check failed:', error);
                // Don't block the form if API is unavailable
                this.isCheckingBreach = false;
                this.showResult = true;
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
         * SHA-1 hash function with multiple fallback implementations
         * @param {string} password - The password to hash
         * @returns {Promise<string>} - The SHA-1 hash in hexadecimal
         */
        async sha1Hash(password) {
            // Try js-sha1 library first (loaded from CDN)
            if (typeof sha1 !== 'undefined') {
                try {
                    console.log('Using js-sha1 library');
                    const result = sha1(password);
                    console.log('js-sha1 result:', result);
                    return result;
                } catch (error) {
                    console.warn('js-sha1 failed:', error);
                }
            }

            // Try SubtleCrypto second (modern browsers)
            if (typeof crypto !== 'undefined' && crypto.subtle && crypto.subtle.digest) {
                try {
                    console.log('Using SubtleCrypto for SHA-1');
                    const encoder = new TextEncoder();
                    const data = encoder.encode(password);
                    const hashBuffer = await crypto.subtle.digest('SHA-1', data);
                    const hashArray = Array.from(new Uint8Array(hashBuffer));
                    const result = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                    console.log('SubtleCrypto result:', result);
                    return result;
                } catch (error) {
                    console.warn('SubtleCrypto SHA-1 failed, using fallback:', error);
                }
            }

            // Fallback: Use simple SHA-1 implementation
            console.log('Using SHA-1 fallback implementation');
            const result = this.sha1Fallback(password);
            console.log('SHA-1 fallback result:', result);
            return result;
        },

        /**
         * Fallback SHA-1 implementation (pure JavaScript)
         * Simple and proven implementation
         * @param {string} msg - The message to hash
         * @returns {string} - The SHA-1 hash in hexadecimal
         */
        sha1Fallback(msg) {
            // Convert string to UTF-8 bytes
            const encoder = new TextEncoder();
            const msgBytes = encoder.encode(msg);

            // Initialize hash values
            let h0 = 0x67452301;
            let h1 = 0xEFCDAB89;
            let h2 = 0x98BADCFE;
            let h3 = 0x10325476;
            let h4 = 0xC3D2E1F0;

            // Pre-processing: adding padding bits
            const ml = msgBytes.length * 8; // message length in bits
            const msg2 = new Uint8Array(msgBytes.length + 1 + 8);
            msg2.set(msgBytes);
            msg2[msgBytes.length] = 0x80;

            // Append length in bits as 64-bit big-endian
            for (let i = 0; i < 8; i++) {
                msg2[msgBytes.length + 1 + 7 - i] = (ml >>> (i * 8)) & 0xFF;
            }

            // Process message in 512-bit chunks
            for (let offset = 0; offset < msg2.length; offset += 64) {
                const w = new Array(80);

                // Break chunk into sixteen 32-bit big-endian words
                for (let i = 0; i < 16; i++) {
                    w[i] = (msg2[offset + i * 4] << 24) |
                           (msg2[offset + i * 4 + 1] << 16) |
                           (msg2[offset + i * 4 + 2] << 8) |
                           msg2[offset + i * 4 + 3];
                }

                // Extend the sixteen 32-bit words into eighty 32-bit words
                for (let i = 16; i < 80; i++) {
                    w[i] = this.rol32(w[i - 3] ^ w[i - 8] ^ w[i - 14] ^ w[i - 16], 1);
                }

                // Initialize working variables
                let a = h0;
                let b = h1;
                let c = h2;
                let d = h3;
                let e = h4;

                // Main loop
                for (let i = 0; i < 80; i++) {
                    let f, k;
                    if (i < 20) {
                        f = (b & c) | (~b & d);
                        k = 0x5A827999;
                    } else if (i < 40) {
                        f = b ^ c ^ d;
                        k = 0x6ED9EBA1;
                    } else if (i < 60) {
                        f = (b & c) | (b & d) | (c & d);
                        k = 0x8F1BBCDC;
                    } else {
                        f = b ^ c ^ d;
                        k = 0xCA62C1D6;
                    }

                    const temp = (this.rol32(a, 5) + f + e + k + w[i]) >>> 0;
                    e = d;
                    d = c;
                    c = this.rol32(b, 30);
                    b = a;
                    a = temp;
                }

                // Add this chunk's hash to result so far
                h0 = (h0 + a) >>> 0;
                h1 = (h1 + b) >>> 0;
                h2 = (h2 + c) >>> 0;
                h3 = (h3 + d) >>> 0;
                h4 = (h4 + e) >>> 0;
            }

            // Produce the final hash value as a 160-bit hex string
            const result = this.toHex(h0) + this.toHex(h1) + this.toHex(h2) + this.toHex(h3) + this.toHex(h4);
            console.log('SHA-1 fallback final result:', result);
            return result;
        },

        /**
         * Rotate left (circular left shift) operation
         * @param {number} n - The number to rotate
         * @param {number} b - The number of bits to rotate
         * @returns {number} - The rotated number
         */
        rol32(n, b) {
            return ((n << b) | (n >>> (32 - b))) >>> 0;
        },

        /**
         * Convert 32-bit number to hex string
         * @param {number} n - The number to convert
         * @returns {string} - The hex string
         */
        toHex(n) {
            let s = '';
            for (let i = 7; i >= 0; i--) {
                s += ((n >>> (i * 4)) & 0xf).toString(16);
            }
            return s;
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

