<!DOCTYPE html>
<html lang="en"<{if $useRtl}> dir="rtl"<{/if}>>
<head>
    <meta charset="<{$charset}>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><{$pageTitle}> (<{$currentPageNum}>/<{$totalPages}>)</title>

    <!-- Bulma CSS Framework -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js for reactive UI -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Installer UI module -->
    <script type="text/javascript" src="js/alpine-installer.js"></script>

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .installer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .installer-header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
        }
        .installer-header .subtitle {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        .installer-main {
            flex: 1;
            padding: 2rem 1.5rem;
        }
        .installer-footer {
            background-color: #f5f5f5;
            border-top: 1px solid #e0e0e0;
            padding: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: #666;
        }
        .steps-sidebar {
            background-color: #fafafa;
            border-right: 1px solid #e0e0e0;
            padding: 1.5rem;
        }
        .steps-sidebar h3 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        .steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .steps-list li {
            padding: 0.75rem 0;
            border-left: 3px solid transparent;
            padding-left: 1rem;
            transition: all 0.2s ease;
        }
        .steps-list li.current {
            border-left-color: #667eea;
            background-color: #f0f4ff;
            font-weight: 600;
            color: #667eea;
        }
        .steps-list li.disabled {
            color: #999;
        }
        .steps-list li a {
            color: #667eea;
            text-decoration: none;
        }
        .steps-list li a:hover {
            text-decoration: underline;
        }
        .page-content {
            background: white;
            border-radius: 4px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .page-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
        }
        .page-buttons button {
            min-width: 100px;
        }
    </style>
</head>
<body<{if $useRtl}> dir="rtl"<{/if}> x-data="installerUI()" @alpine:init="init()">
<!-- Header -->
<header class="installer-header">
    <div class="container">
        <div class="columns is-vcentered">
            <div class="column is-narrow">
                <img src="img/logo.png" alt="ImpressCMS" style="height: 50px;">
            </div>
            <div class="column">
                <h1><{$wizardTitle}></h1>
                <p class="subtitle"><{$stepLabel}> <{$currentPageNum}>/<{$totalPages}></p>
            </div>
        </div>
    </div>
</header>

<!-- Main Content -->
<div class="installer-main">
    <div class="container">
        <div class="columns">
            <!-- Steps Sidebar -->
            <div class="column is-3-desktop is-4-tablet">
                <div class="steps-sidebar">
                    <h3><{$stepsLabel}></h3>
                    <ul class="steps-list">
                        <{foreach from=$pages item=page key=k}>
                            <{if $k == $currentPageIndex}>
                                <li class="current"><{$pageNames[$k]}></li>
                            <{elseif $k > $currentPageIndex}>
                                <li class="disabled"><{$pageNames[$k]}></li>
                            <{else}>
                                <li><a href="<{$pageUris[$k]}>"><{$pageNames[$k]}></a></li>
                            <{/if}>
                        <{/foreach}>
                    </ul>
                </div>
            </div>

            <!-- Page Content -->
            <div class="column is-9-desktop is-8-tablet">
                <form action='<{$formAction}>' method='post'>
                    <div class="page-content" id="<{$currentPageName}>">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h2 class="title is-4"><{$pageTitle}></h2>
                            <div style="display: flex; gap: 0.5rem;">
                                <{if $pageHasHelp}>
                                    <button type="button" class="button is-small is-light" @click="toggleHelp()" id="help_button"
                                            title="<{$showHideHelpLabel}>">
                                        <span class="icon is-small">
                                            <i class="fas fa-question-circle"></i>
                                        </span>
                                    </button>
                                <{/if}>
                                <button type="button" class="button is-small is-light" @click="scrollToBottom()" id="pagedown"
                                        title="Scroll to bottom">
                                    <span class="icon is-small">
                                        <i class="fas fa-arrow-down"></i>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <{include file="page_`$currentPageName`.html.tpl"}>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="page-buttons">
                        <{if $showPrevButton}>
                            <button type="button" class="button is-info" title="<{$prevButtonLabel}>"
                                    onclick="location.href='<{$prevPageUri}>'">
                                <span class="icon is-small">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span><{$prevButtonLabel}></span>
                            </button>
                        <{/if}>
                        <{if $showFinishButton}>
                            <button type="button" class="button is-success" title="<{$showSiteButtonLabel}>"
                                    onclick="location.href='<{$finishPageUri}>'">
                                <span class="icon is-small">
                                    <i class="fas fa-home"></i>
                                </span>
                                <span><{$showSiteButtonLabel}></span>
                            </button>
                        <{/if}>
                        <{if $showNextButton}>
                            <{if $pageHasForm}>
                                <button type="submit" class="button is-primary" title="<{$nextButtonLabel}>">
                                    <span><{if $isLastModuleStep}><{$finishLabel}><{else}><{$nextButtonLabel}><{/if}></span>
                                    <span class="icon is-small">
                                        <i class="fas fa-arrow-right"></i>
                                    </span>
                                </button>
                            <{else}>
                                <button type="button" class="button is-primary" title="<{$nextButtonLabel}>" accesskey="n"
                                        onclick="location.href='<{$nextPageUri}>'">
                                    <span><{if $isLastModuleStep}><{$finishLabel}><{else}><{$nextButtonLabel}><{/if}></span>
                                    <span class="icon is-small">
                                        <i class="fas fa-arrow-right"></i>
                                    </span>
                                </button>
                            <{/if}>
                        <{/if}>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="installer-footer">
    <{$copyrightLabel}>
</footer>
</body>
</html>

