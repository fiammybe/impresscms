<?php
/**
 * Installer cleanup confirmation page
 *
 * Ask user whether to remove the installer directory. Only remove when confirmed.
 */
require_once 'common.inc.php';
if (!defined('XOOPS_INSTALL')) exit();
include_once '../mainfile.php';

$wizard->setPage('cleanup');
$pageHasForm = false;
$pageHasHelp = false;

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'remove') {
        if (defined('ICMS_ROOT_PATH') && is_dir(ICMS_ROOT_PATH . '/install')) {
            icms_core_Filesystem::deleteRecursive(ICMS_ROOT_PATH . '/install', true);
        }
        // Clear installer session and go to site
        $_SESSION = array();
        header('Location: ' . ICMS_URL . '/');
        exit();
    } elseif ($action === 'keep') {
        // Keep installer, just redirect to site
        $_SESSION = array();
        header('Location: ' . ICMS_URL . '/');
        exit();
    }
}

// Render content
ob_start();
?>
    <div class="x2-note">
        <p><?php echo CLEANUP_PROMPT; ?></p>
    </div>
    <div class="xoform-buttons" style="margin-top: 1em; display: flex; gap: 10px; align-items: center;">
        <button type="submit" name="action" value="remove" class="finish">
            <img src="img/delete.png" alt="" width="16" /> <?php echo BUTTON_REMOVE_INSTALLER; ?>
        </button>
        <button type="submit" name="action" value="keep" class="prev">
            <img src="img/left-arr.png" alt="" width="16" /> <?php echo BUTTON_KEEP_INSTALLER; ?>
        </button>
        <a href="<?php echo ICMS_URL; ?>/" class="next" style="margin-left: auto;">
            <img src="img/Home.png" alt="" width="16" /> <?php echo BUTTON_SHOW_SITE; ?>
        </a>
    </div>
<?php
$content = ob_get_clean();

include 'install_tpl.php';

