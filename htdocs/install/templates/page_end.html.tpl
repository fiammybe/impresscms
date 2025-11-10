<h3><{$finishLabel}></h3>

<{if $success}>
    <div class="x2-note success">
        <{$successMessage}>
    </div>
<{/if}>

<{if $errors}>
    <div class="x2-note error">
        <{foreach from=$errors item=error}>
            <p><{$error}></p>
        <{/foreach}>
    </div>
<{/if}>

<p><{$finishMessage}></p>

<{if $finishContent}>
    <{$finishContent}>
<{/if}>

