<?php
/**
 * KnowledgeManager: provides tagging and taxonomy from one module (tables
 * knowledgemanager_keyword, knowledgemanager_assignment); contributes no UI
 */

$modversion = [
	'name' => 'KnowledgeManager',
	'version' => 1.0,
	'description' => 'Tagging and taxonomy provider fixture of the capability tests',
	'author' => 'ImpressCMS test suite',
	'credits' => 'The ImpressCMS Project',
	'help' => '',
	'license' => 'GPL see LICENSE',
	'official' => 0,
	'dirname' => basename(__DIR__),
	'modname' => 'knowledgemanager',
	'image' => 'images/icon.png',
	'hasMain' => 0,
	'hasAdmin' => 0,
	'object_items' => [1 => 'keyword', 'assignment'],
	'onInstall' => 'include/onupdate.php',
	'onUpdate' => 'include/onupdate.php',
];
