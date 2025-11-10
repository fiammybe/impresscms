<p class="x2-note"><{$configSaveMessage}></p>
<dl style="height: 200px; overflow: auto; border: 1px solid #D0D0D0">
    <{foreach from=$configVars item=var}>
        <dt><{$var.key}></dt>
        <dd><{$var.value}></dd>
    <{/foreach}>
</dl>

