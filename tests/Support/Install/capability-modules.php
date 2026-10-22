<?php
/**
 * Install, update, uninstall, activate or deactivate modules of the test site
 * through the module administration's own functions (modulesadmin.php), as
 * the capability tests need them.
 *
 * Usage: php capability-modules.php <mainfile> <install|update|uninstall|activate|deactivate> <dirname>...
 * Prints the administration's result messages; exits 1 when a module is unknown.
 */

[$mainfile, $action] = [$argv[1], $argv[2]];
$dirnames = array_slice($argv, 3);

$_SERVER += [
	'HTTP_HOST' => '127.0.0.1',
	'SERVER_NAME' => '127.0.0.1',
	'REQUEST_URI' => '/modules/system/admin.php',
	'SCRIPT_NAME' => '/modules/system/admin.php',
	'QUERY_STRING' => '',
	'REMOTE_ADDR' => '127.0.0.1',
	// The legacy database layer only allows writes during POST requests
	// with a same-site referer (Icms\Core\Security::service()).
	'REQUEST_METHOD' => 'POST',
	'HTTP_REFERER' => 'http://127.0.0.1/',
];

require $mainfile;
include_once ICMS_ROOT_PATH . '/include/cp_functions.php';
include_once ICMS_ROOT_PATH . '/modules/system/constants.php';

// modulesadmin.php only runs for an administrator of the system module
$moduleHandler = icms::handler('icms_module');
icms::$user = icms::handler('icms_member')->getUser(1);
$icmsModule = icms::$module = $moduleHandler->getByDirname('system');
include_once ICMS_ROOT_PATH . '/modules/system/admin/modulesadmin/modulesadmin.php';
icms_loadLanguageFile('system', 'modulesadmin', true);
icms_loadLanguageFile('system', 'blocksadmin', true);

$GLOBALS['icmsAdminTpl'] = new icms_view_Tpl();

foreach ($dirnames as $dirname) {
	if ($action === 'install') {
		echo strip_tags(xoops_module_install($dirname)), "\n";
		continue;
	}

	$module = $moduleHandler->getByDirname($dirname);
	if (!is_object($module)) {
		fwrite(STDERR, "Module {$dirname} is not installed.\n");
		exit(1);
	}

	$result = match ($action) {
		'update' => icms_module_update($dirname),
		'uninstall' => xoops_module_uninstall($dirname),
		'activate' => xoops_module_activate((int) $module->getVar('mid')),
		'deactivate' => xoops_module_deactivate((int) $module->getVar('mid')),
	};
	echo strip_tags((string) $result), "\n";
}
