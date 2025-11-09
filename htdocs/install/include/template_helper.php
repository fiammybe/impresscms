<?php

/**
 * Template helper functions for ImpressCMS installer
 *
 * Provides utility functions for template rendering and data preparation
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package installer
 * @since 1.5
 */

/**
 * Get the global template engine instance
 *
 * @return InstallerTemplateEngine
 */
function getInstallerTemplate()
{
    global $installerTemplate;

    if (!isset($installerTemplate)) {
        require_once dirname(__FILE__) . '/../class/TemplateEngine.php';
        $installerTemplate = new InstallerTemplateEngine();
    }

    return $installerTemplate;
}

/**
 * Render the installer layout with page content
 *
 * @param IcmsInstallWizard $wizard The installer wizard instance
 * @param array $pageVariables Variables to pass to the page template
 * @param bool $pageHasForm Whether the page has a form
 * @param bool $pageHasHelp Whether the page has help content
 * @return void
 */
function renderInstallerLayout($wizard, $pageVariables = [], $pageHasForm = false, $pageHasHelp = false)
{
    $template = getInstallerTemplate();

    // Prepare page URIs
    $pageUris = [];
    foreach ($wizard->pages as $page) {
        $pageUris[] = $wizard->pageURI($page);
    }

    // Determine button visibility
    $showPrevButton = ($wizard->currentPage != 0 && $wizard->currentPage != 11);
    $showFinishButton = ($wizard->currentPage == 11);
    $showNextButton = ($wizard->pages[$wizard->currentPage] != $wizard->lastpage);

    // Determine if this is the last module step
    $isLastModuleStep = ($wizard->pages[$wizard->currentPage] == $wizard->secondlastpage && isset($_POST['mod']) && $_POST['mod'] == 1);

    // Assign page variables first
    if (!empty($pageVariables)) {
        $template->assign($pageVariables);
    }

    // Assign layout template variables
    $template->assign([
        'pageTitle' => sprintf(XOOPS_INSTALL_WIZARD, XOOPS_VERSION),
        'currentPageNum' => $wizard->currentPage + 1,
        'totalPages' => count($wizard->pages),
        'charset' => _INSTALL_CHARSET,
        'useRtl' => defined('_ADM_USE_RTL') && _ADM_USE_RTL,
        'wizardTitle' => sprintf(XOOPS_INSTALL_WIZARD, XOOPS_VERSION),
        'stepLabel' => INSTALL_STEP,
        'outOfLabel' => INSTALL_OUTOF,
        'stepsLabel' => INSTALL_H3_STEPS,
        'pages' => $wizard->pages,
        'pageNames' => $wizard->pagesNames,
        'pageUris' => $pageUris,
        'currentPageIndex' => $wizard->currentPage,
        'currentPageName' => $wizard->currentPageName,
        'formAction' => htmlentities($_SERVER['PHP_SELF']),
        'pageHasHelp' => $pageHasHelp,
        'pageHasForm' => $pageHasForm,
        'showPrevButton' => $showPrevButton,
        'showFinishButton' => $showFinishButton,
        'showNextButton' => $showNextButton,
        'isLastModuleStep' => $isLastModuleStep,
        'prevButtonLabel' => BUTTON_PREVIOUS,
        'nextButtonLabel' => BUTTON_NEXT,
        'finishButtonLabel' => BUTTON_FINISH,
        'showSiteButtonLabel' => BUTTON_SHOW_SITE,
        'finishLabel' => BUTTON_FINISH,
        'showHideHelpLabel' => SHOW_HIDE_HELP,
        'copyrightLabel' => INSTALL_COPYRIGHT,
        'prevPageUri' => $wizard->pageURI('-1'),
        'nextPageUri' => $wizard->pageURI('+1'),
        'finishPageUri' => $wizard->pageURI('11') . '?success=true',
    ]);

    // Display the layout
    $template->display('layout');
}

/**
 * Prepare page variables for rendering
 *
 * This function is now a pass-through for backward compatibility.
 * Variables are assigned directly in renderInstallerLayout.
 *
 * @param string $pageName Page template name (without .html extension)
 * @param array $variables Template variables
 * @return array The variables array
 * @deprecated Use renderInstallerLayout() directly with variables
 */
function renderInstallerPage($pageName, $variables = [])
{
    // This function is kept for backward compatibility
    // The actual rendering now happens in renderInstallerLayout
    return $variables;
}

