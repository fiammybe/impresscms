<{if $success}>
    <div class="notification is-success">
        <button class="delete"></button>
        <h3 class="title is-5"><{$finishLabel}></h3>
        <p><{$successMessage}></p>
    </div>
<{/if}>

<{if $errors}>
    <div class="notification is-danger">
        <button class="delete"></button>
        <h3 class="title is-5">Errors</h3>
        <ul>
            <{foreach from=$errors item=error}>
                <li><{$error}></li>
            <{/foreach}>
        </ul>
    </div>
<{/if}>

<div class="content" style="margin-top: 1.5rem;">
    <p><{$finishMessage}></p>
</div>

<{if $finishContent}>
    <div class="box" style="margin-top: 1.5rem;">
        <{$finishContent}>
    </div>
<{/if}>
