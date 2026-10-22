<?php
/**
 * Taxonomy browser: the objects classified under a term or below it
 */

use Icms\Capability\ObjectRef;
use Icms\Capability\Registry;

include_once '../../mainfile.php';
include_once ICMS_ROOT_PATH . '/header.php';

$taxonomy = Registry::find('taxonomy');

echo '<ul id="classified-objects">';
if ($taxonomy !== null && $taxonomy->providerModule() === 'taxonomy') {
	$refs = $taxonomy->findObjects((string) (int) ($_GET['term_id'] ?? 0), true, null, 50);
	$objects = ObjectRef::resolveMany($refs);
	foreach ($refs as $ref) {
		if (isset($objects[$ref->toString()])) {
			echo '<li>' . $objects[$ref->toString()]->getItemLink() . '</li>';
		}
	}
}
echo '</ul>';

include_once ICMS_ROOT_PATH . '/footer.php';
