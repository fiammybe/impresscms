<script type="text/javascript" src="js/pathsettings.js"></script>

<div class="blokz">
    <fieldset>
        <h3><{$webLocationsLabel}></h3>
        <label for="url"><{$urlLabel}></label>
        <div class="xoform-help"><{$urlHelp}></div>
        <div class="clear">&nbsp;</div>
        <input type="text" name="URL" id="url" value="<{$url}>" />
    </fieldset>
    <br />
</div>

<div class="bloky">
    <fieldset>
        <h3><{$physicalPathLabel}></h3>
        <label for="rootpath"><{$rootPathLabel}></label>
        <div class="xoform-help"><{$rootPathHelp}></div>
        <div class="clear">&nbsp;</div>
        <input type="text" name="ROOT_PATH" id="rootpath" value="<{$rootPath}>" />
        <span id="rootpathimg"><{$rootPathCheckHtml}></span>
        <{if $rootPathValid && $permErrors|count > 0}>
            <div id="rootperms">
                <{$checkingPermissionsLabel}><br />
                <p><{$needWriteAccessLabel}></p>
                <ul class="diags">
                    <{foreach from=$permErrors item=error}>
                        <li class="<{if $error.result}>success<{else}>failure<{/if}>"><{$error.message}></li>
                    <{/foreach}>
                    <button type="button" id="permrefresh"><{$buttonRefreshLabel}></button>
                </ul>
            </div>
        <{else}>
            <div id="rootperms"><{$checkingPermissionsLabel}><br /><ul class="diags"><li class="success"><{$allPermOkLabel}></li></ul></div>
        <{/if}>
    </fieldset>
    <br />
</div>

<div class="blokx">
    <fieldset>
        <h3><{$trustPathLabel}></h3>
        <label for="trustpath"><{$trustPathLabel}></label>
        <div class="xoform-help"><{$trustPathHelp}></div>
        <div class="clear">&nbsp;</div>
        <input type="text" name="TRUST_PATH" id="trustpath" value="<{$trustPath}>" />
        <span id="trustpathimg"><{$trustPathCheckHtml}></span>
        <{if !$trustPathValid && $trustPath != ''}>
            <div id="trustperms">
                <p><{$trustPathValidateLabel}></p>
                <button type="button" id="createtrustpath"><{$buttonCreateTrustPathLabel}></button>
            </div>
        <{/if}>
    </fieldset>
</div>

