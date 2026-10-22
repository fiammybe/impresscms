<?php

use Icms\Capability\Contract\Tagging\Tag;
use Icms\Capability\Contract\Taxonomy\Term;

include_once __DIR__ . '/Term.php';

class mod_taxonomy_TermHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'term', 'term_id', 'label', 'slug', 'taxonomy');
	}

	/** Add a term under a parent (0 for a root), keeping the materialized path. */
	public function add(int $vocabularyId, string $label, int $parentId = 0): int {
		$parentPath = '/';
		if ($parentId > 0) {
			$parent = $this->get($parentId);
			$parentPath = $parent->isNew() ? '/' : (string) $parent->getVar('path', 'n');
		}
		$term = $this->create();
		$term->setVar('vocabulary_id', $vocabularyId);
		$term->setVar('parent_id', $parentId);
		$term->setVar('label', $label);
		$term->setVar('slug', Tag::slugify($label));
		$this->insert($term, true);
		$id = (int) $term->getVar('term_id');
		$term->setVar('path', $parentPath . str_pad((string) $id, 6, '0', STR_PAD_LEFT) . '/');
		$this->insert($term, true);
		return $id;
	}

	/** @return list<Term> depth-first */
	public function ofVocabulary(int $vocabularyId): array {
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('vocabulary_id', (string) $vocabularyId));
		$criteria->setSort('path');
		return array_map([$this, 'toTerm'], array_values($this->getObjects($criteria)));
	}

	/**
	 * @param list<int> $ids
	 * @return array<int, mod_taxonomy_Term>
	 */
	public function byIds(array $ids): array {
		$ids = array_filter(array_map('intval', $ids));
		if (!$ids) {
			return [];
		}
		return $this->getObjects(new icms_db_criteria_Item('term_id', '(' . implode(',', $ids) . ')', 'IN'), true);
	}

	/** @return list<int> the term and every term below it */
	public function withDescendants(int $termId): array {
		$term = $this->get($termId);
		if ($term->isNew()) {
			return [];
		}
		$path = addcslashes($this->db->escape((string) $term->getVar('path', 'n')), '%_');
		return array_map('intval', array_keys($this->getObjects(new icms_db_criteria_Item('path', $path . '%', 'LIKE'), true)));
	}

	/** @return list<string> the labels from the root down to the term */
	public function breadcrumb(mod_taxonomy_Term $term): array {
		$ids = array_map('intval', array_filter(explode('/', (string) $term->getVar('path', 'n')), 'strlen'));
		$terms = $this->byIds($ids);
		$labels = [];
		foreach ($ids as $id) {
			if (isset($terms[$id])) {
				$labels[] = (string) $terms[$id]->getVar('label', 'n');
			}
		}
		return $labels;
	}

	public function depth(mod_taxonomy_Term $term): int {
		return max(0, substr_count((string) $term->getVar('path', 'n'), '/') - 2);
	}

	public function toTerm(mod_taxonomy_Term $term): Term {
		$parentId = (int) $term->getVar('parent_id');
		return new Term((string) $term->getVar('term_id'), (string) $term->getVar('vocabulary_id'), (string) $term->getVar('label', 'n'), (string) $term->getVar('slug', 'n'), $parentId > 0 ? (string) $parentId : null);
	}
}
