<?php

use Icms\Capability\Contract\Taxonomy\Vocabulary;

include_once __DIR__ . '/Vocabulary.php';

class mod_taxonomy_VocabularyHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'vocabulary', 'vocabulary_id', 'label', 'label', 'taxonomy');
	}

	/** @return array<int, Vocabulary> by id */
	public function all(): array {
		$vocabularies = [];
		foreach ($this->getObjects(null, true) as $id => $row) {
			$types = array_values(array_filter(array_map('trim', explode(',', (string) $row->getVar('object_types', 'n'))), 'strlen'));
			$vocabularies[$id] = new Vocabulary((string) $id, (string) $row->getVar('label', 'n'), (int) $row->getVar('multiple') === 1, $types);
		}
		return $vocabularies;
	}
}
