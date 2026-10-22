<?php
/**
 * ImpressCMS Preloads
 *
 * The preloads administration is served by the Symfony host; admin.php?fct=preloads
 * leads there. index.php/<route> reaches the host at every routing level.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Administration
 * @subpackage	Preloads
 * @since		2.0
 */
if (!is_object(icms::$user) || !is_object($icmsModule) || !icms::$user->isAdmin($icmsModule->getVar('mid'))) {
	exit("Access Denied");
}

header('Location: ' . ICMS_URL . '/index.php/admin/preloads', true, 302);
exit();
