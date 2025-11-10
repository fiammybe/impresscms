<{if $error}>
    <div class="notification is-danger">
        <button class="delete"></button>
        <{$error}>
    </div>
<{/if}>

<div class="box">
    <h3 class="title is-5"><{$connectionLegend}></h3>

    <div class="field">
        <label class="label"><{$databaseLegend}></label>
        <div class="control">
            <div class="select is-fullwidth">
                <select name="DB_TYPE">
                    <{foreach from=$connections item=option}>
                        <option value="<{$option.type}>"<{if $option.selected}> selected="selected"<{/if}>><{$option.name}></option>
                    <{/foreach}>
                </select>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="label" for='DB_HOST'><{$dbHostLabel}></label>
        <{if $dbHostHelp}>
            <p class="help"><{$dbHostHelp}></p>
        <{/if}>
        <div class="control">
            <input class="input" type='text' name='DB_HOST' id='DB_HOST' value='<{$dbHost}>' />
        </div>
    </div>

    <div class="field">
        <label class="label" for='DB_USER'><{$dbUserLabel}></label>
        <{if $dbUserHelp}>
            <p class="help"><{$dbUserHelp}></p>
        <{/if}>
        <div class="control">
            <input class="input" type='text' name='DB_USER' id='DB_USER' value='<{$dbUser}>' />
        </div>
    </div>

    <div class="field">
        <label class="label" for='DB_PASS'><{$dbPassLabel}></label>
        <{if $dbPassHelp}>
            <p class="help"><{$dbPassHelp}></p>
        <{/if}>
        <div class="control">
            <input class="input" type='password' name='DB_PASS' id='DB_PASS' value='<{$dbPass}>' />
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="checkbox">
                <input type="checkbox" name="DB_PCONNECT" value="1"
                       onclick="alert('<{$dbPconnectHelps}>');"<{if $dbPconnect}> checked="checked"<{/if}> />
                <{$dbPconnectLabel}>
            </label>
        </div>
        <{if $dbPconnectHelp}>
            <p class="help"><{$dbPconnectHelp}></p>
        <{/if}>
    </div>
</div>
