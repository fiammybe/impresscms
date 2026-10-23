<?php
/**
 * ImpressCMS Asset manager
 *
 * The page is served by the framework application (App\Controller\System\AssetsController).
 * This file is only included when the framework application does not handle the request.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		System
 * @subpackage	Assets
 */

if (!is_object(icms::$user) || !is_object($icmsModule) || !icms::$user->isAdmin($icmsModule->getVar('mid'))) {
	exit("Access Denied");
}

icms_cp_header();
echo \Icms\Core\Message::warning(_CO_ICMS_ASSETS_NEEDS_HOST);
icms_cp_footer();
