<?php
/**
 * Tag cloud block: tag counts of one object type, read through the tagging
 * contract, so it works without any code in the consumer module
 */

use Icms\Capability\Registry;

/**
 * @param array{0: string, 1: string} $options object type ("news/article", empty for all), number of tags
 * @return array{tags: list<array{slug: string, count: int}>, objectType: string}
 */
function b_advancedtags_cloud_show($options) {
	$objectType = trim((string) ($options[0] ?? ''));
	$tagging = Registry::find('tagging');
	$tags = [];
	if ($tagging !== null) {
		foreach ($tagging->cloud($objectType === '' ? null : $objectType, max(1, (int) ($options[1] ?? 20))) as $slug => $count) {
			$tags[] = ['slug' => htmlspecialchars($slug, ENT_QUOTES), 'count' => $count];
		}
	}
	return ['tags' => $tags, 'objectType' => htmlspecialchars($objectType, ENT_QUOTES)];
}

/**
 * @param array{0: string, 1: string} $options
 */
function b_advancedtags_cloud_edit($options) {
	$form = 'Object type (module/item, empty for all): <input type="text" name="options[0]" value="' . htmlspecialchars((string) ($options[0] ?? ''), ENT_QUOTES) . '" /><br />';
	$form .= 'Number of tags: <input type="text" name="options[1]" value="' . (int) ($options[1] ?? 20) . '" />';
	return $form;
}
