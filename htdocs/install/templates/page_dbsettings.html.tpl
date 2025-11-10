<{if $error}>
    <div class="notification is-danger">
        <button class="delete"></button>
        <{$error}>
    </div>
<{/if}>

<div class="box">
    <h3 class="title is-5"><{$databaseLegend}></h3>

    <div class="field">
        <label class="label" for='DB_NAME'><{$dbNameLabel}></label>
        <{if $dbNameHelp}>
            <p class="help"><{$dbNameHelp}></p>
        <{/if}>
        <div class="control">
            <input class="input" type='text' name='DB_NAME' id='DB_NAME' value='<{$dbName}>' />
        </div>
    </div>

    <div class="field">
        <label class="label" for='DB_PREFIX'><{$dbPrefixLabel}></label>
        <{if $dbPrefixHelp}>
            <p class="help"><{$dbPrefixHelp}></p>
        <{/if}>
        <div class="control">
            <input class="input" type='text' name='DB_PREFIX' id='DB_PREFIX' value='<{$dbPrefix}>' />
        </div>
    </div>

    <div class="field">
        <label class="label" for='DB_SALT'><{$dbSaltLabel}></label>
        <{if $dbSaltHelp}>
            <p class="help"><{$dbSaltHelp}></p>
        <{/if}>
        <div class="control">
            <input class="input" type='text' name='DB_SALT' id='DB_SALT' value='<{$dbSalt}>' />
        </div>
    </div>

    <div class="field">
        <label class="label" for='DB_CHARSET'><{$dbCharsetLabel}></label>
        <{if $dbCharsetHelp}>
            <p class="help"><{$dbCharsetHelp}></p>
        <{/if}>
        <div class="control">
            <div class="select is-fullwidth">
                <select name='DB_CHARSET' id='DB_CHARSET' @change="updateCollation('DB_COLLATION_div', $event.target.value)">
                    <option value=''>None</option>
                    <{foreach from=$charsetOptions item=charset}>
                        <option value='<{$charset.value}>'<{if $charset.selected}> selected='selected'<{/if}>><{$charset.value}> - <{$charset.description}></option>
                    <{/foreach}>
                </select>
            </div>
        </div>
    </div>

    <div class="field" id='DB_COLLATION_div'>
        <label class="label" for='DB_COLLATION'><{$dbCollationLabel}></label>
        <{if $dbCollationHelp}>
            <p class="help"><{$dbCollationHelp}></p>
        <{/if}>
        <div class="control">
            <div class="select is-fullwidth">
                <select name='DB_COLLATION' id='DB_COLLATION'>
                    <{foreach from=$collationOptions item=collation}>
                        <option value='<{$collation.value}>'<{if $collation.selected}> selected='selected'<{/if}>><{$collation.label}></option>
                    <{/foreach}>
                </select>
            </div>
        </div>
    </div>
</div>
