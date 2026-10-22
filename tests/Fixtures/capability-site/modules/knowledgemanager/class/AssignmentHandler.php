<?php

use Icms\Capability\ObjectRef;

class mod_knowledgemanager_Assignment extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('assignment_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('keyword_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('module', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('item', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('object_id', XOBJ_DTYPE_INT, true);
	}
}

class mod_knowledgemanager_AssignmentHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'assignment', 'assignment_id', 'keyword_id', 'object_id', 'knowledgemanager');
	}

	public function typeCriteria(string $objectType): icms_db_criteria_Compo {
		[$module, $item] = explode('/', $objectType, 2) + ['', ''];
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('module', $this->db->escape($module)));
		$criteria->add(new icms_db_criteria_Item('item', $this->db->escape($item)));
		return $criteria;
	}

	/**
	 * @param list<ObjectRef> $refs
	 * @return array<string, list<int>> keyword ids keyed by ObjectRef::toString()
	 */
	public function keywordIdsOf(array $refs): array {
		$result = [];
		$byType = [];
		foreach ($refs as $ref) {
			$result[$ref->toString()] = [];
			$byType[$ref->objectType()][] = $ref->id;
		}
		// one query per object type
		foreach ($byType as $objectType => $ids) {
			$criteria = $this->typeCriteria($objectType);
			$criteria->add(new icms_db_criteria_Item('object_id', '(' . implode(',', array_map('intval', $ids)) . ')', 'IN'));
			$criteria->setSort('assignment_id');
			foreach ($this->getObjects($criteria) as $assignment) {
				$result[$objectType . '/' . (int) $assignment->getVar('object_id')][] = (int) $assignment->getVar('keyword_id');
			}
		}
		return $result;
	}

	/**
	 * Replace the object's keywords among $scope with $keywordIds.
	 *
	 * @param list<int> $scope
	 * @param list<int> $keywordIds
	 */
	public function replace(ObjectRef $ref, array $scope, array $keywordIds): void {
		$current = array_values(array_intersect($this->keywordIdsOf([$ref])[$ref->toString()], $scope));
		foreach (array_diff($current, $keywordIds) as $keywordId) {
			$criteria = $this->typeCriteria($ref->objectType());
			$criteria->add(new icms_db_criteria_Item('object_id', (string) $ref->id));
			$criteria->add(new icms_db_criteria_Item('keyword_id', (string) (int) $keywordId));
			$this->deleteAll($criteria);
		}
		foreach (array_unique(array_diff($keywordIds, $current)) as $keywordId) {
			$assignment = $this->create();
			$assignment->setVar('keyword_id', (int) $keywordId);
			$assignment->setVar('module', $ref->module);
			$assignment->setVar('item', $ref->item);
			$assignment->setVar('object_id', $ref->id);
			$this->insert($assignment, true);
		}
	}

	/**
	 * @param list<int> $keywordIds
	 * @return list<ObjectRef>
	 */
	public function objectsWith(array $keywordIds, ?string $objectType, int $limit, int $start): array {
		$keywordIds = array_filter(array_map('intval', $keywordIds));
		if (!$keywordIds) {
			return [];
		}
		$criteria = $objectType === null ? new icms_db_criteria_Compo() : $this->typeCriteria($objectType);
		$criteria->add(new icms_db_criteria_Item('keyword_id', '(' . implode(',', $keywordIds) . ')', 'IN'));
		$criteria->setSort('assignment_id');
		$refs = [];
		foreach ($this->getObjects($criteria) as $assignment) {
			$ref = new ObjectRef((string) $assignment->getVar('module', 'n'), (string) $assignment->getVar('item', 'n'), (int) $assignment->getVar('object_id'));
			$refs[$ref->toString()] = $ref;
		}
		return array_slice(array_values($refs), $start, $limit > 0 ? $limit : null);
	}
}
