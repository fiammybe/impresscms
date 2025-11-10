<{if $hasError}>
    <div class="notification is-danger">
        <button class="delete"></button>
        <strong>Error:</strong> <{$errorMessage}>
    </div>
<{else}>
    <div class="notification is-info">
        <button class="delete"></button>
        <{$configSaveMessage}>
    </div>

    <div class="box" style="max-height: 400px; overflow-y: auto;">
        <table class="table is-fullwidth is-striped">
            <tbody>
                <{foreach from=$configVars item=var}>
                    <tr>
                        <td style="font-weight: 600; width: 30%;"><{$var.key}></td>
                        <td><code><{$var.value}></code></td>
                    </tr>
                <{/foreach}>
            </tbody>
        </table>
    </div>
<{/if}>
