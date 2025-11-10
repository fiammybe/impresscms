<script type="text/javascript" src="js/pathsettings.js"></script>

<div class="box" style="margin-bottom: 2rem;">
    <h3 class="title is-5"><{$webLocationsLabel}></h3>
    <div class="field">
        <label class="label" for="url"><{$urlLabel}></label>
        <p class="help"><{$urlHelp}></p>
        <div class="control">
            <input class="input" type="text" name="URL" id="url" value="<{$url}>" />
        </div>
    </div>
</div>

<div class="box" style="margin-bottom: 2rem;">
    <h3 class="title is-5"><{$physicalPathLabel}></h3>
    <div class="field">
        <label class="label" for="rootpath"><{$rootPathLabel}></label>
        <p class="help"><{$rootPathHelp}></p>
        <div class="control has-icons-right">
            <input class="input" type="text" name="ROOT_PATH" id="rootpath" value="<{$rootPath}>" />
            <span class="icon is-right" id="rootpathimg"><{$rootPathCheckHtml}></span>
        </div>
    </div>

    <{if $rootPathValid && $permErrors|count > 0}>
        <div class="notification is-warning" id="rootperms">
            <button class="delete"></button>
            <p><strong><{$checkingPermissionsLabel}></strong></p>
            <p><{$needWriteAccessLabel}></p>
            <div style="margin-top: 1rem;">
                <{foreach from=$permErrors item=error}>
                    <div class="tags has-addons" style="margin-bottom: 0.5rem;">
                        <span class="tag is-light"><{$error.message}></span>
                        <span class="tag <{if $error.result}>is-success<{else}>is-danger<{/if}>">
                            <{if $error.result}>✓<{else}>✗<{/if}>
                        </span>
                    </div>
                <{/foreach}>
            </div>
            <button type="button" class="button is-small is-warning" id="permrefresh" style="margin-top: 1rem;">
                <{$buttonRefreshLabel}>
            </button>
        </div>
    <{else}>
        <div class="notification is-success" id="rootperms">
            <button class="delete"></button>
            <p><{$checkingPermissionsLabel}></p>
            <p><{$allPermOkLabel}></p>
        </div>
    <{/if}>
</div>

<div class="box">
    <h3 class="title is-5"><{$trustPathLabel}></h3>
    <div class="field">
        <label class="label" for="trustpath"><{$trustPathLabel}></label>
        <p class="help"><{$trustPathHelp}></p>
        <div class="control has-icons-right">
            <input class="input" type="text" name="TRUST_PATH" id="trustpath" value="<{$trustPath}>" />
            <span class="icon is-right" id="trustpathimg"><{$trustPathCheckHtml}></span>
        </div>
    </div>

    <{if !$trustPathValid && $trustPath != ''}>
        <div class="notification is-info" id="trustperms">
            <button class="delete"></button>
            <p><{$trustPathValidateLabel}></p>
            <button type="button" class="button is-small is-info" id="createtrustpath" style="margin-top: 1rem;">
                <{$buttonCreateTrustPathLabel}>
            </button>
        </div>
    <{/if}>
</div>
