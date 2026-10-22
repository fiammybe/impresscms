<?php
/**
 * Autocomplete for the tag field: tags starting with ?q=, as JSON
 *
 * The endpoint belongs to the provider and checks the provider module's own
 * access right.
 */

use Icms\Capability\Contract\Tagging\Tag;
use Icms\Capability\Registry;

include_once '../../../mainfile.php';

header('Content-Type: application/json; charset=utf-8');

$module = icms::handler('icms_module')->getByDirname('advancedtags');
$groups = is_object(icms::$user) ? icms::$user->getGroups() : [ICMS_GROUP_ANONYMOUS];
if (!is_object($module) || !icms::handler('icms_member_groupperm')->checkRight('module_read', $module->getVar('mid'), $groups)) {
	http_response_code(403);
	echo json_encode([]);
	exit;
}

$tagging = Registry::find('tagging');
$labels = [];
if ($tagging !== null && $tagging->providerModule() === 'advancedtags') {
	$labels = array_map(static fn (Tag $tag): string => $tag->label, $tagging->suggest(isset($_GET['q']) ? (string) $_GET['q'] : '', 10));
}
echo json_encode($labels);
exit;
