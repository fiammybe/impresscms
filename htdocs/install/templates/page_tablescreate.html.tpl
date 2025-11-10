<{if $processCreate}>
    <div class="notification is-info">
        <button class="delete"></button>
        <{$readyCreateTablesLabel}>
    </div>
<{else}>
    <div class="notification is-success">
        <button class="delete"></button>
        <{$tablesFoundLabel}>
    </div>

    <{if $reportContent}>
        <div class="box" style="margin-top: 1.5rem;">
            <h3 class="title is-6">Database Report</h3>
            <div class="content" style="max-height: 400px; overflow-y: auto; background-color: #f5f5f5; padding: 1rem; border-radius: 4px; font-family: monospace; font-size: 0.875rem;">
                <{$reportContent}>
            </div>
        </div>
    <{/if}>
<{/if}>
