<{if $error}>
    <div class="x2-note error"><{$error}></div>
<{/if}>

<!-- Load zxcvbn library for password strength estimation -->
<script type="text/javascript" src="js/zxcvbn.js"></script>
<!-- Load SHA-1 library for HIBP integration -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/js-sha1/0.6.0/sha1.min.js"></script>
<!-- Load Alpine.js password strength component -->
<script type="text/javascript" src="js/password-strength-alpine.js"></script>

<fieldset>
    <h3><{$adminAccountLegend}></h3>
    <div class="blokinit">
        <div class="dbconn_line">
            <label for="adminname"><{$adminDisplayLabel}></label>
            <div class="clear">&nbsp;</div>
            <input type="text" name="adminname" id="adminname" maxlength="25" value="<{$adminDisplayValue}>" />
        </div>
        <div class="dbconn_line">
            <label for="adminlogin_name"><{$adminLoginLabel}></label>
            <div class="clear">&nbsp;</div>
            <input class="adminlogin_name" type="text" name="adminlogin_name" id="adminlogin_name" maxlength="25" value="<{$adminLoginValue}>" />
        </div>
        <div class="dbconn_line">
            <label for="adminmail"><{$adminEmailLabel}></label>
            <div class="clear">&nbsp;</div>
            <input type="text" name="adminmail" id="adminmail" maxlength="255" value="<{$adminEmailValue}>" />
        </div>
        <div class="dbconn_line" x-data="passwordStrengthChecker()">
            <label for="adminpass"><{$adminPassLabel}></label>
            <div class="clear">&nbsp;</div>
            <input
                class="password_adv"
                type="password"
                name="adminpass"
                id="adminpass"
                maxlength="255"
                value=""
                @input="checkPasswordStrength($el.value)"
                x-ref="passwordField"
            />
            <div
                id="top_testresult"
                :class="resultClass"
                :style="{ display: showResult ? 'block' : 'none', marginTop: '8px', padding: '8px', borderRadius: '4px', fontWeight: 'bold' }"
            >
                <div x-show="resultMessage" x-text="resultMessage"></div>
                <div x-show="isCheckingBreach" style="margin-top: 8px; font-size: 0.9em; opacity: 0.7;">
                    <em>Checking for data breaches...</em>
                </div>
                <div x-show="breachWarning !== ''" style="margin-top: 8px; font-size: 0.9em; opacity: 0.9; color: #bc0000; font-weight: bold;">
                    <strong>⚠️ Security Alert:</strong> <span x-text="breachWarning"></span>
                </div>
                <div x-show="feedbackWarning" style="margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    <strong>Warning:</strong> <span x-text="feedbackWarning"></span>
                </div>
                <div x-show="feedbackSuggestions.length > 0" style="margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    <strong>Suggestions:</strong>
                    <ul style="margin: 4px 0; padding-left: 20px;">
                        <template x-for="suggestion in feedbackSuggestions" :key="suggestion">
                            <li x-text="suggestion"></li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
        <div class="dbconn_line">
            <label for="adminpass2"><{$adminConfirmPassLabel}></label>
            <div class="clear">&nbsp;</div>
            <input type="password" name="adminpass2" id="adminpass2" maxlength="255" value="" />
        </div>
    </div>
</fieldset>

