<?php
/**
 * Taxonomy: the taxonomy provider of the capability tests (tables
 * taxonomy_vocabulary, taxonomy_term, taxonomy_relation), with vocabulary
 * management, a browser page and a browser block
 */

$modversion = [
	'name' => 'Taxonomy',
	'version' => 1.0,
	'description' => 'Taxonomy provider fixture of the capability tests',
	'author' => 'ImpressCMS test suite',
	'credits' => 'The ImpressCMS Project',
	'help' => '',
	'license' => 'GPL see LICENSE',
	'official' => 0,
	'dirname' => basename(__DIR__),
	'modname' => 'taxonomy',
	'image' => 'images/icon.png',
	'hasMain' => 1,
	'hasAdmin' => 1,
	'adminindex' => 'admin/vocabularies.php',
	'adminmenu' => 'admin/menu.php',
	'object_items' => [1 => 'vocabulary', 'term', 'relation'],
	'onInstall' => 'include/onupdate.php',
	'onUpdate' => 'include/onupdate.php',
	'blocks' => [
		1 => [
			'file' => 'browser.php',
			'name' => 'Taxonomy browser',
			'description' => 'The terms of a vocabulary, linking to the objects classified under them',
			'show_func' => 'b_taxonomy_browser_show',
			'edit_func' => 'b_taxonomy_browser_edit',
			// vocabulary id
			'options' => '1',
			'template' => 'taxonomy_block_browser.html',
		],
	],
];
