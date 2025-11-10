<{if $error}>
    <div class="x2-note error"><{$error}></div>
<{/if}>

<div class="blokSQL">
    <fieldset>
        <h3><{$databaseLegend}></h3>

        <div class='dbconn_line'>
            <label for='DB_NAME'><{$dbNameLabel}></label>
            <{if $dbNameHelp}>
                <div class="xoform-help"><{$dbNameHelp}></div>
                <div class='clear'>&nbsp;</div>
            <{/if}>
            <input type='text' name='DB_NAME' id='DB_NAME' value='<{$dbName}>' />
        </div>

        <div class='dbconn_line'>
            <label for='DB_PREFIX'><{$dbPrefixLabel}></label>
            <{if $dbPrefixHelp}>
                <div class="xoform-help"><{$dbPrefixHelp}></div>
                <div class='clear'>&nbsp;</div>
            <{/if}>
            <input type='text' name='DB_PREFIX' id='DB_PREFIX' value='<{$dbPrefix}>' />
        </div>

        <div class='dbconn_line'>
            <label for='DB_SALT'><{$dbSaltLabel}></label>
            <{if $dbSaltHelp}>
                <div class="xoform-help"><{$dbSaltHelp}></div>
                <div class='clear'>&nbsp;</div>
            <{/if}>
            <input type='text' name='DB_SALT' id='DB_SALT' value='<{$dbSalt}>' />
        </div>

        <div class='dbconn_line'>
            <label for='DB_CHARSET'><{$dbCharsetLabel}></label>
            <{if $dbCharsetHelp}>
                <div class="xoform-help"><{$dbCharsetHelp}></div>
                <div class='clear'>&nbsp;</div>
            <{/if}>
            <select name='DB_CHARSET' id='DB_CHARSET' @change="updateCollation('DB_COLLATION_div', $event.target.value)">
                <option value=''>None</option>
                <{foreach from=$charsetOptions item=charset}>
                    <option value='<{$charset.value}>'<{if $charset.selected}> selected='selected'<{/if}>><{$charset.value}> - <{$charset.description}></option>
                <{/foreach}>
            </select>
        </div>

        <div class='dbconn_line' id='DB_COLLATION_div'>
            <label for='DB_COLLATION'><{$dbCollationLabel}></label>
            <{if $dbCollationHelp}>
                <div class="xoform-help"><{$dbCollationHelp}></div>
                <div class='clear'>&nbsp;</div>
            <{/if}>
            <select name='DB_COLLATION' id='DB_COLLATION'>
                <{foreach from=$collationOptions item=collation}>
                    <option value='<{$collation.value}>'<{if $collation.selected}> selected='selected'<{/if}>><{$collation.label}></option>
                <{/foreach}>
            </select>
        </div>
    </fieldset>
</div>

