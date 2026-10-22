<?php
/**
 * Tag management of the provider: every tag with its usage, and renaming a tag
 */

include_once '../../../include/cp_header.php';

$tags = icms_getModuleHandler('tag', 'advancedtags');
$links = icms_getModuleHandler('link', 'advancedtags');

if (($_POST['op'] ?? '') === 'rename') {
	if (!icms::$security->check()) {
		redirect_header('tags.php', 3, implode('<br />', icms::$security->getErrors()));
	}
	$tag = $tags->get((int) ($_POST['tag_id'] ?? 0));
	$label = trim((string) ($_POST['label'] ?? ''));
	if (!$tag->isNew() && $label !== '') {
		$tag->setVar('label', $label);
		$tag->setVar('slug', \Icms\Capability\Contract\Tagging\Tag::slugify($label));
		$tags->insert($tag);
	}
	redirect_header('tags.php', 2, 'Tag renamed');
}

icms_cp_header();
$counts = $links->counts(null, 0);
echo '<h1>Tags</h1><table id="advancedtags-admin" class="outer"><tr><th>Tag</th><th>Uses</th><th></th></tr>';
foreach ($tags->getObjects(null, true) as $id => $tag) {
	echo '<tr><td>' . $tag->getVar('label') . '</td><td>' . ($counts[$id] ?? 0) . '</td><td>'
		. '<form method="post" action="tags.php"><input type="hidden" name="op" value="rename" /><input type="hidden" name="tag_id" value="' . $id . '" />'
		. '<input type="text" name="label" value="' . $tag->getVar('label', 'e') . '" />' . icms::$security->getTokenHTML() . '<button type="submit">Rename</button></form>'
		. '</td></tr>';
}
echo '</table>';
icms_cp_footer();
