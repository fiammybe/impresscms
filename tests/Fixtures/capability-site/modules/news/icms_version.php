<?php
/**
 * News: the consumer module of the capability tests
 *
 * IPF items without any capability code; tagging and taxonomy reach its forms,
 * views and admin table through the capability framework only.
 */

$modversion = [
	'name' => 'News',
	'version' => 1.0,
	'description' => 'Consumer fixture of the capability tests',
	'author' => 'ImpressCMS test suite',
	'credits' => 'The ImpressCMS Project',
	'help' => '',
	'license' => 'GPL see LICENSE',
	'official' => 0,
	'dirname' => basename(__DIR__),
	'modname' => 'news',
	'image' => 'images/icon.png',
	'hasMain' => 1,
	'hasAdmin' => 1,
	'adminindex' => 'admin/article.php',
	'adminmenu' => 'admin/menu.php',
	'hasSearch' => 0,
	'hasComments' => 0,
	'object_items' => [1 => 'article', 'category'],
	'onInstall' => 'include/onupdate.php',
	'onUpdate' => 'include/onupdate.php',
	'templates' => [
		['file' => 'news_article.html', 'description' => 'One article'],
	],
	// consumer declarations (design D9): optional, no code needed to be assignable
	'capability_support' => [
		'article' => ['default' => ['tagging']],
		'category' => ['unsupported' => ['tagging']],
	],
];
