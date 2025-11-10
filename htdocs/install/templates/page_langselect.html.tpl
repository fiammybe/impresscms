<div class="content">
    <p><{$alternateLanguageMsg}></p>
    <p>
        <a href="<{$alternateLanguageLinkUrl}>" target="_blank" class="button is-light is-small">
            <{$alternateLanguageLinkMsg}>
        </a>
    </p>
</div>

<div class="columns is-multiline">
    <{foreach from=$languages item=lang}>
        <div class="column is-3-desktop is-4-tablet is-6-mobile">
            <label class="card" style="cursor: pointer; display: flex; flex-direction: column; height: 100%; transition: all 0.2s ease;">
                <div class="card-content" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                    <div class="image" style="margin-bottom: 1rem;">
                        <img src="../images/flags/<{$lang}>.gif" alt="<{$lang}>" style="max-width: 80px; height: auto;">
                    </div>
                    <p class="title is-6" style="margin-bottom: 1rem;"><{$lang}></p>
                    <div class="control">
                        <input type="radio" name="lang" value="<{$lang}>" id="lang_<{$lang}>"<{if $lang == $selectedLanguage}> checked="checked"<{/if}> style="cursor: pointer;">
                    </div>
                </div>
            </label>
        </div>
    <{/foreach}>
</div>

<style>
    label.card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    label.card input[type="radio"]:checked {
        accent-color: #667eea;
    }
</style>
