<?php

use Icms\Capability\ObjectRef;

include_once __DIR__ . '/Relation.php';

class mod_taxonomy_RelationHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'relation', 'relation_id', 'term_id', 'object_id', 'taxonomy');
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

	/**
	 * The term ids of several objects, in one query per object type.
	 *
	 * @param list<ObjectRef> $refs
	 * @return array<string, list<int>> keyed by ObjectRef::toString()
	 */
	public function termIdsOfObjects(array $refs): array {
		$result = [];
		$byType = [];
		foreach ($refs as $ref) {
			$result[$ref->toString()] = [];
			$byType[$ref->objectType()][] = $ref->id;
		}
		foreach ($byType as $objectType => $ids) {
			$criteria = $this->typeCriteria($objectType);
			$criteria->add(new icms_db_criteria_Item('object_id', '(' . implode(',', array_map('intval', $ids)) . ')', 'IN'));
			$criteria->setSort('relation_id');
			foreach ($this->getObjects($criteria) as $relation) {
				$result[$objectType . '/' . (int) $relation->getVar('object_id')][] = (int) $relation->getVar('term_id');
			}
		}
		return $result;
	}

	/** @param list<int> $termIds */
	public function remove(ObjectRef $ref, array $termIds): void {
		$termIds = array_filter(array_map('intval', $termIds));
		if (!$termIds) {
			return;
		}
		$criteria = $this->refCriteria($ref);
		$criteria->add(new icms_db_criteria_Item('term_id', '(' . implode(',', $termIds) . ')', 'IN'));
		$this->deleteAll($criteria);
	}

	/** @param list<int> $termIds */
	public function add(ObjectRef $ref, array $termIds): void {
		foreach (array_unique(array_map('intval', $termIds)) as $termId) {
			$relation = $this->create();
			$relation->setVar('term_id', $termId);
			$relation->setVar('module', $ref->module);
			$relation->setVar('item', $ref->item);
			$relation->setVar('object_id', $ref->id);
			$this->insert($relation, true);
		}
	}

	/**
	 * The objects classified under any of the terms, each once, in a stable order.
	 *
	 * @param list<int> $termIds
	 * @return list<ObjectRef>
	 */
	public function objectsWithTerms(array $termIds, ?string $objectType, int $limit, int $start): array {
		$termIds = array_filter(array_map('intval', $termIds));
		if (!$termIds) {
			return [];
		}
		$criteria = $objectType === null ? new icms_db_criteria_Compo() : $this->typeCriteria($objectType);
		$criteria->add(new icms_db_criteria_Item('term_id', '(' . implode(',', $termIds) . ')', 'IN'));
		$sql = 'SELECT module, item, object_id FROM ' . $this->table . ' ' . $criteria->renderWhere()
			. ' GROUP BY module, item, object_id ORDER BY MIN(relation_id)';
		$result = $this->db->query($sql, $limit, $start);
		$refs = [];
		while ($result && ($row = $this->db->fetchArray($result))) {
			$refs[] = new ObjectRef((string) $row['module'], (string) $row['item'], (int) $row['object_id']);
		}
		return $refs;
	}
}
