<div class="section">
    <h3 class="title is-5"><{$requirementsLabel}></h3>
    <{foreach from=$requirements item=requirement}>
        <div class="box" style="margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p class="heading"><{$requirement.description}></p>
                    <p class="title is-6" style="margin-top: 0.5rem;">
                        <{if $requirement.status == 1}>
                            <span class="tag is-success"><{$requirement.result}></span>
                        <{elseif $requirement.status == 0}>
                            <span class="tag is-warning"><{$requirement.result}></span>
                        <{else}>
                            <span class="tag is-danger"><{$requirement.result}></span>
                        <{/if}>
                    </p>
                </div>
                <div>
                    <{if $requirement.status == 1}>
                        <span class="icon has-text-success is-large">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    <{elseif $requirement.status == 0}>
                        <span class="icon has-text-warning is-large">
                            <i class="fas fa-exclamation-circle"></i>
                        </span>
                    <{else}>
                        <span class="icon has-text-danger is-large">
                            <i class="fas fa-times-circle"></i>
                        </span>
                    <{/if}>
                </div>
            </div>
        </div>
    <{/foreach}>
</div>

<div class="section">
    <h3 class="title is-5"><{$recommendedExtensionsLabel}></h3>
    <div class="content">
        <p><{$recommendedExtensionsMsg}></p>
    </div>

    <div class="box" style="margin-bottom: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p class="heading"><{$charEncodingLabel}></p>
                <p class="title is-6" style="margin-top: 0.5rem;">
                    <{if $charEncodingExtensions}>
                        <span class="tag is-success"><{$charEncodingExtensions}></span>
                    <{else}>
                        <span class="tag is-warning"><{$noneLabel}></span>
                    <{/if}>
                </p>
            </div>
            <div>
                <{if $charEncodingExtensions}>
                    <span class="icon has-text-success is-large">
                        <i class="fas fa-check-circle"></i>
                    </span>
                <{else}>
                    <span class="icon has-text-warning is-large">
                        <i class="fas fa-exclamation-circle"></i>
                    </span>
                <{/if}>
            </div>
        </div>
    </div>

    <div class="box">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p class="heading"><{$xmlParsingLabel}></p>
                <p class="title is-6" style="margin-top: 0.5rem;">
                    <{if $xmlExtensions}>
                        <span class="tag is-success"><{$xmlExtensions}></span>
                    <{else}>
                        <span class="tag is-warning"><{$noneLabel}></span>
                    <{/if}>
                </p>
            </div>
            <div>
                <{if $xmlExtensions}>
                    <span class="icon has-text-success is-large">
                        <i class="fas fa-check-circle"></i>
                    </span>
                <{else}>
                    <span class="icon has-text-warning is-large">
                        <i class="fas fa-exclamation-circle"></i>
                    </span>
                <{/if}>
            </div>
        </div>
    </div>
</div>
