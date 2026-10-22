<?php
/**
 * AdvancedTags: the second tagging provider of the capability tests
 * (tables advancedtags_tag, advancedtags_link), with an admin page, a suggest
 * endpoint and a tag cloud block
 */

$modversion = [
	'name' => 'AdvancedTags',
	'version' => 1.2,
	'description' => 'Tagging provider fixture of the capability tests',
	'author' => 'ImpressCMS test suite',
	'credits' => 'The ImpressCMS Project',
	'help' => '',
	'license' => 'GPL see LICENSE',
	'official' => 0,
	'dirname' => basename(__DIR__),
	'modname' => 'advancedtags',
	'image' => 'images/icon.png',
	'hasMain' => 1,
	'hasAdmin' => 1,
	'adminindex' => 'admin/tags.php',
	'adminmenu' => 'admin/menu.php',
	'object_items' => [1 => 'tag', 'link'],
	'onInstall' => 'include/onupdate.php',
	'onUpdate' => 'include/onupdate.php',
	'blocks' => [
		1 => [
			'file' => 'cloud.php',
			'name' => 'Tag cloud',
			'description' => 'The most used tags of one object type',
			'show_func' => 'b_advancedtags_cloud_show',
			'edit_func' => 'b_advancedtags_cloud_edit',
			// object type, number of tags
			'options' => '|20',
			'template' => 'advancedtags_block_cloud.html',
		],
	],
];
