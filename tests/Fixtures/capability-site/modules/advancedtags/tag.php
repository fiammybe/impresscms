<?php
/**
 * The objects tagged with one tag (the provider's own tag page)
 */

use Icms\Capability\ObjectRef;
use Icms\Capability\Registry;

include_once '../../mainfile.php';
include_once ICMS_ROOT_PATH . '/header.php';

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$tagging = Registry::find('tagging');

echo '<ul id="tagged-objects">';
if ($tagging !== null && $tagging->providerModule() === 'advancedtags') {
	$refs = $tagging->findObjects($slug, null, 50);
	$objects = ObjectRef::resolveMany($refs);
	foreach ($refs as $ref) {
		$object = $objects[$ref->toString()] ?? null;
		if ($object !== null) {
			echo '<li>' . $object->getItemLink() . '</li>';
		}
	}
}
echo '</ul>';

include_once ICMS_ROOT_PATH . '/footer.php';
