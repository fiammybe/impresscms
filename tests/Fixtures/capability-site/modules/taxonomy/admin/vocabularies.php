<?php
/**
 * Classification management of the provider: vocabularies and their terms
 */

include_once '../../../include/cp_header.php';

$vocabularies = icms_getModuleHandler('vocabulary', 'taxonomy');
$terms = icms_getModuleHandler('term', 'taxonomy');
$op = (string) ($_POST['op'] ?? '');

if ($op === 'addvocabulary' || $op === 'addterm') {
	if (!icms::$security->check()) {
		redirect_header('vocabularies.php', 3, implode('<br />', icms::$security->getErrors()));
	}
	if ($op === 'addvocabulary' && trim((string) ($_POST['label'] ?? '')) !== '') {
		$vocabulary = $vocabularies->create();
		$vocabulary->setVar('label', trim((string) $_POST['label']));
		$vocabulary->setVar('multiple', empty($_POST['multiple']) ? 0 : 1);
		$vocabulary->setVar('object_types', trim((string) ($_POST['object_types'] ?? '')));
		$vocabularies->insert($vocabulary);
	}
	if ($op === 'addterm' && trim((string) ($_POST['label'] ?? '')) !== '') {
		$terms->add((int) ($_POST['vocabulary_id'] ?? 0), trim((string) $_POST['label']), (int) ($_POST['parent_id'] ?? 0));
	}
	redirect_header('vocabularies.php', 2, 'Saved');
}

icms_cp_header();
echo '<h1>Vocabularies</h1>';
foreach ($vocabularies->all() as $vocabulary) {
	echo '<h2>' . htmlspecialchars($vocabulary->label, ENT_QUOTES) . ($vocabulary->multiple ? ' (several terms)' : ' (one term)') . '</h2><ul class="taxonomy-terms">';
	foreach ($terms->ofVocabulary((int) $vocabulary->id) as $term) {
		echo '<li data-term="' . (int) $term->id . '">' . htmlspecialchars($term->label, ENT_QUOTES) . '</li>';
	}
	echo '</ul><form method="post" action="vocabularies.php"><input type="hidden" name="op" value="addterm" /><input type="hidden" name="vocabulary_id" value="' . (int) $vocabulary->id . '" />'
		. 'Term: <input type="text" name="label" /> under term id <input type="text" name="parent_id" value="0" size="4" />' . icms::$security->getTokenHTML() . '<button type="submit">Add</button></form>';
}
echo '<form method="post" action="vocabularies.php"><input type="hidden" name="op" value="addvocabulary" />Vocabulary: <input type="text" name="label" />'
	. ' <label><input type="checkbox" name="multiple" value="1" /> several terms</label> object types: <input type="text" name="object_types" />'
	. icms::$security->getTokenHTML() . '<button type="submit">Add</button></form>';
icms_cp_footer();
