<?php

use Icms\Capability\ObjectRef;

include_once __DIR__ . '/Links.php';

class mod_smarttags_LinksHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'links', 'link_id', 'tag_id', 'object_id', 'smarttags');
	}

	public function typeCriteria(string $objectType): icms_db_criteria_Compo {
		[$module, $item] = explode('/', $objectType, 2) + ['', ''];
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('module', $this->db->escape($module)));
		$criteria->add(new icms_db_criteria_Item('item', $this->db->escape($item)));
		return $criteria;
	}

	public function refCriteria(ObjectRef $ref): icms_db_criteria_Compo {
		$criteria = $this->typeCriteria($ref->objectType());
		$criteria->add(new icms_db_criteria_Item('object_id', (string) $ref->id));
		return $criteria;
	}

	/** @return list<int> */
	public function tagIdsOf(ObjectRef $ref): array {
		return $this->tagIdsOfObjects([$ref])[$ref->toString()];
	}

	/** @param list<int> $tagIds */
	public function replaceFor(ObjectRef $ref, array $tagIds): void {
		$current = $this->tagIdsOf($ref);
		$this->removeFor($ref, array_values(array_diff($current, $tagIds)));
		$this->addFor($ref, array_values(array_diff($tagIds, $current)));
	}

	/** @param list<int> $tagIds */
	public function addFor(ObjectRef $ref, array $tagIds): void {
		foreach (array_unique(array_diff($tagIds, $this->tagIdsOf($ref))) as $tagId) {
			$link = $this->create();
			$link->setVar('tag_id', (int) $tagId);
			$link->setVar('module', $ref->module);
			$link->setVar('item', $ref->item);
			$link->setVar('object_id', $ref->id);
			$this->insert($link, true);
		}
	}

	/** @param list<int> $tagIds */
	public function removeFor(ObjectRef $ref, array $tagIds): void {
		$tagIds = array_filter(array_map('intval', $tagIds));
		if (!$tagIds) {
			return;
		}
		$criteria = $this->refCriteria($ref);
		$criteria->add(new icms_db_criteria_Item('tag_id', '(' . implode(',', $tagIds) . ')', 'IN'));
		$this->deleteAll($criteria);
	}

	/**
	 * The tag ids of several objects, in one query per object type.
	 *
	 * @param list<ObjectRef> $refs
	 * @return array<string, list<int>> keyed by ObjectRef::toString()
	 */
	public function tagIdsOfObjects(array $refs): array {
		$result = [];
		$byType = [];
		foreach ($refs as $ref) {
			$result[$ref->toString()] = [];
			$byType[$ref->objectType()][] = $ref->id;
		}
		foreach ($byType as $objectType => $ids) {
			$criteria = $this->typeCriteria($objectType);
			$criteria->add(new icms_db_criteria_Item('object_id', '(' . implode(',', array_map('intval', $ids)) . ')', 'IN'));
			$criteria->setSort('link_id');
			foreach ($this->getObjects($criteria) as $link) {
				$result[$objectType . '/' . (int) $link->getVar('object_id')][] = (int) $link->getVar('tag_id');
			}
		}
		return $result;
	}

	/** @return list<ObjectRef> */
	public function objectsWithTag(int $tagId, ?string $objectType, int $limit, int $start): array {
		$criteria = $objectType === null ? new icms_db_criteria_Compo() : $this->typeCriteria($objectType);
		$criteria->add(new icms_db_criteria_Item('tag_id', (string) $tagId));
		$criteria->setSort('link_id');
		$criteria->setLimit($limit);
		$criteria->setStart($start);
		$refs = [];
		foreach ($this->getObjects($criteria) as $link) {
			$refs[] = new ObjectRef((string) $link->getVar('module', 'n'), (string) $link->getVar('item', 'n'), (int) $link->getVar('object_id'));
		}
		return $refs;
	}

	/** @return array<int, int> tag id => usage count, most used first */
	public function counts(?string $objectType, int $limit): array {
		$where = $objectType === null ? '' : ' ' . $this->typeCriteria($objectType)->renderWhere();
		$result = $this->db->query('SELECT tag_id, COUNT(*) AS uses FROM ' . $this->table . $where . ' GROUP BY tag_id ORDER BY uses DESC, tag_id ASC', $limit, 0);
		$counts = [];
		while ($result && ($row = $this->db->fetchArray($result))) {
			$counts[(int) $row['tag_id']] = (int) $row['uses'];
		}
		return $counts;
	}
}
