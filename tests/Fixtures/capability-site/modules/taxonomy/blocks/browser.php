<?php
/**
 * Taxonomy browser block: the terms of a vocabulary, read through the taxonomy contract
 */

use Icms\Capability\Registry;

/**
 * @param array{0: string} $options vocabulary id
 * @return array{terms: list<array{id: string, label: string}>}
 */
function b_taxonomy_browser_show($options) {
	$taxonomy = Registry::find('taxonomy');
	$terms = [];
	if ($taxonomy !== null) {
		foreach ($taxonomy->terms((string) (int) ($options[0] ?? 0)) as $term) {
			$terms[] = ['id' => $term->id, 'label' => htmlspecialchars($term->label, ENT_QUOTES)];
		}
	}
	return ['terms' => $terms];
}

/**
 * @param array{0: string} $options
 */
function b_taxonomy_browser_edit($options) {
	return 'Vocabulary id: <input type="text" name="options[0]" value="' . (int) ($options[0] ?? 0) . '" />';
}
