<{foreach from=$languages item=lang}>
    <div class="langselect" style="text-decoration: none;">
        <a href="javascript:void(0);" style="text-decoration: none;">
            <img src="../images/flags/<{$lang}>.gif" alt="<{$lang}>" />
            <br /><{$lang}><br />
            <input type="radio" name="lang" value="<{$lang}>"<{if $lang == $selectedLanguage}> checked="checked"<{/if}> />
        </a>
    </div>
<{/foreach}>

<fieldset style="text-align: center;">
    <div><{$alternateLanguageMsg}></div>
    <div style="text-align: center; margin-top: 5px;">
        <a href="<{$alternateLanguageLinkUrl}>" target="_blank"><{$alternateLanguageLinkMsg}></a>
    </div>
</fieldset>

