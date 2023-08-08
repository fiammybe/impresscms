<?php
/**
 * Installer database configuration page
 *
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright	http://www.xoops.org/ The XOOPS Project
 * @copyright	XOOPS_copyrights.txt
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		installer
 * @since		XOOPS
 * @author		http://www.xoops.org/ The XOOPS Project
 * @author	   Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
 * @version		$Id: page_dbconnection.php 12426 2014-02-24 16:19:49Z fiammy $
 */
/**
 *
 */
require_once 'common.inc.php';
if (!defined( 'XOOPS_INSTALL' ) )    exit();

$wizard->setPage( 'dbconnection' );
$pageHasForm = true;
$pageHasHelp = true;

$vars =& $_SESSION['settings'];

// Load config values from mainfile.php constants if 1st invocation, or reload has been asked
if (!isset( $vars['DB_HOST'] ) || false !== @strpos( $_SERVER['HTTP_CACHE_CONTROL'], 'max-age=0' )) {
	$keys = array( 'DB_TYPE', 'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_PCONNECT' );
	foreach ( $keys as $k) {
		$vars[ $k ] = defined( "XOOPS_$k" ) ? constant( "XOOPS_$k" ) : '';
	}
	$vars['DB_PASS'] = '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$params = array( 'DB_TYPE', 'DB_HOST', 'DB_USER', 'DB_PASS' );
	foreach ( $params as $name) {
		$vars[$name] = $_POST[$name];
	}
	$vars['DB_PCONNECT'] = @$_POST['DB_PCONNECT'] ? 1 : 0;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($vars['DB_HOST']) && !empty($vars['DB_USER'])) {
	switch ($vars['DB_TYPE']) {
		case 'mysql':
			$func_connect = empty($vars['DB_PCONNECT'])?"mysql_connect":"mysql_pconnect";
			if (!($link = @$func_connect($vars['DB_HOST'], $vars['DB_USER'], $vars['DB_PASS'], true))) {
				$error = ERR_NO_DBCONNECTION;
			}
			break;
		case 'pdo.mysql':
			try {
				$dbh = new PDO('mysql:host=' . $vars['DB_HOST'],
					$vars['DB_USER'],
					$vars['DB_PASS'],
					array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_PERSISTENT => !empty($vars['DB_PCONNECT'])
					));
			} catch (PDOException $ex) {
				$error = ERR_NO_DBCONNECTION;
			}
			break;
	}
	if (empty($error)) {
		$wizard->redirectToPage('+1');
		exit();
	}
}

//so far, mysql extension has to exist and be loaded
$connections = [];
if (function_exists('mysql_connect') || function_exists('mysql_pconnect')) {
	$connections['mysql'] = array('type' => 'mysql', 'name' => 'MySQL', 'selected' => 'selected');
	$db_connection = $connections['mysql'];
}
// Fill with default values
// check for PDO MySQL and select it, if it is available
if (class_exists("PDO", false)) {
	$db_connection = array('type' => 'pdo.mysql', 'name' => 'PDO MySQL', 'selected' => 'selected');
	if (isset($connections['mysql'])) {
		$connections['mysql']['selected'] = '';
	}
	$connections['pdo'] = $db_connection;
}

if (@empty($vars['DB_HOST'])) {
	$vars = array_merge($vars, array(
		'DB_TYPE'        => $db_connection['type'],
		'DB_HOST'        => 'localhost',
		'DB_USER'        => '',
		'DB_PASS'        => '',
		'DB_PCONNECT'    => 0,
	));

}


ob_start();
?>
<?php if (!empty( $error ) ) echo '<div class="alert alert-danger">' . $error . "</div>\n"; ?>
	<h3><?php echo LEGEND_CONNECTION; ?></h3>
	<div class="blokSQL">
		<div class="mb-3 d-none">
			<label class="form-label"> <?php echo LEGEND_DATABASE; ?></label>
			<select size="2" name="DB_TYPE" class="db_select form-control">
					<?php
					foreach ($connections as $option) {
						$selected = "";
						if (!empty($option['selected'])) $selected = " selected='selected'";
						echo "<option value='" . $option['type'] . "'" . $selected . ">" . $option['name'] . "</option>";
					}
					?>
			</select>
		</div>
		<?php echo xoFormField( 'DB_HOST',    $vars['DB_HOST'],        DB_HOST_LABEL, DB_HOST_HELP ); ?>
		<?php echo xoFormField( 'DB_USER',    $vars['DB_USER'],        DB_USER_LABEL, DB_USER_HELP ); ?>
		<?php echo xoFormField( 'DB_PASS',	$vars['DB_PASS'],		DB_PASS_LABEL, DB_PASS_HELP, 'password' ); ?>
	</div>

	<div class="form-check">
		<label class="form-check-label"> <?php echo htmlspecialchars( DB_PCONNECT_LABEL ); ?></label>
		<input
			class="form-check-input" type="checkbox" name="DB_PCONNECT" value="1"
			onclick="alert('<?php echo htmlspecialchars( DB_PCONNECT_HELPS ); ?>');"
			<?php echo $vars['DB_PCONNECT'] ? "'checked'" : ""; ?> />
		<div class="xoform-help"><?php echo htmlspecialchars( DB_PCONNECT_HELP ); ?></div>

	</div>
<?php
$content = ob_get_contents();
ob_end_clean();
include 'install_tpl.php';