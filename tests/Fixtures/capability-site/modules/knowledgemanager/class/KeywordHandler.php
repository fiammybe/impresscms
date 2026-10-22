<?php

use Icms\Capability\Contract\Tagging\Tag;

class mod_knowledgemanager_Keyword extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('keyword_id', XOBJ_DTYPE_INT, true);
		// "tag" for tagging, "topic" for the taxonomy
		$this->quickInitVar('kind', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('label', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('slug', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('parent_id', XOBJ_DTYPE_INT, false, '', '', 0);
	}
}

class mod_knowledgemanager_KeywordHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'keyword', 'keyword_id', 'label', 'slug', 'knowledgemanager');
	}

	/** @return array<int, mod_knowledgemanager_Keyword> */
	public function ofKind(string $kind): array {
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('kind', $this->db->escape($kind)));
		$criteria->setSort('keyword_id');
		return $this->getObjects($criteria, true);
	}

	/** The id of the keyword with this label, created when missing. */
	public function idFor(string $kind, string $label, int $parentId = 0): int {
		foreach ($this->ofKind($kind) as $id => $keyword) {
			if ($keyword->getVar('slug', 'n') === Tag::slugify($label)) {
				return $id;
			}
		}
		$keyword = $this->create();
		$keyword->setVar('kind', $kind);
		$keyword->setVar('label', $label);
		$keyword->setVar('slug', Tag::slugify($label));
		$keyword->setVar('parent_id', $parentId);
		$this->insert($keyword, true);
		return (int) $keyword->getVar('keyword_id');
	}
}
