<?php
/**
 * SmartTags: a tagging provider of the capability tests (tables smarttags_tags, smarttags_links)
 */

$modversion = [
	'name' => 'SmartTags',
	'version' => 2.0,
	'description' => 'Tagging provider fixture of the capability tests',
	'author' => 'ImpressCMS test suite',
	'credits' => 'The ImpressCMS Project',
	'help' => '',
	'license' => 'GPL see LICENSE',
	'official' => 0,
	'dirname' => basename(__DIR__),
	'modname' => 'smarttags',
	'image' => 'images/icon.png',
	'hasMain' => 1,
	'hasAdmin' => 0,
	'object_items' => [1 => 'tags', 'links'],
	'onInstall' => 'include/onupdate.php',
	'onUpdate' => 'include/onupdate.php',
];
