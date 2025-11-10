<{if $error}>
    <div class="notification is-danger">
        <button class="delete"></button>
        <strong>Error:</strong> <{$message}>
    </div>
<{else}>
    <div class="notification is-info">
        <button class="delete"></button>
        <{$message}>
    </div>
    <{if $content}>
        <div class="box" style="margin-top: 1.5rem;">
            <{$content}>
        </div>
    <{/if}>
<{/if}>
