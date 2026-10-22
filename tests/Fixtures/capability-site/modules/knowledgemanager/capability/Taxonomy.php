<?php
/**
 * KnowledgeManager as a taxonomy provider: one vocabulary "Topics" of keywords of kind "topic"
 */

use Icms\Capability\Contract\Taxonomy\CapabilityInterface;
use Icms\Capability\Contract\Taxonomy\Term;
use Icms\Capability\Contract\Taxonomy\Vocabulary;
use Icms\Capability\ObjectRef;

class mod_knowledgemanager_TaxonomyCapability implements CapabilityInterface {
	private const VOCABULARY = '1';

	public function capabilityName(): string {
		return self::NAME;
	}

	public function contractVersion(): string {
		return '1.0';
	}

	public function providerModule(): string {
		return 'knowledgemanager';
	}

	public function supportsObjectType(string $objectType): bool {
		return true;
	}

	public function adminUrl(): ?string {
		return null;
	}

	public function vocabularies(?string $objectType = null): array {
		return [new Vocabulary(self::VOCABULARY, 'Topics', true)];
	}

	public function terms(string $vocabularyId): array {
		if ($vocabularyId !== self::VOCABULARY) {
			return [];
		}
		return array_values(array_map([$this, 'toTerm'], $this->keywords()->ofKind('topic')));
	}

	public function classify(ObjectRef $ref, array $termIds): void {
		$topics = array_keys($this->keywords()->ofKind('topic'));
		$this->assignments()->replace($ref, $topics, array_values(array_intersect(array_map('intval', $termIds), $topics)));
	}

	public function unclassify(ObjectRef $ref, array $termIds): void {
		$current = array_map(static fn (Term $term): int => (int) $term->id, $this->termsOf($ref));
		$this->classify($ref, array_values(array_diff($current, array_map('intval', $termIds))));
	}

	public function termsOf(ObjectRef $ref): array {
		return $this->termsOfObjects([$ref])[$ref->toString()];
	}

	public function termsOfObjects(array $refs): array {
		$topics = $this->keywords()->ofKind('topic');
		$result = [];
		foreach ($this->assignments()->keywordIdsOf($refs) as $key => $ids) {
			$result[$key] = [];
			foreach ($ids as $id) {
				if (isset($topics[$id])) {
					$result[$key][] = $this->toTerm($topics[$id]);
				}
			}
		}
		return $result;
	}

	public function findObjects(string $termId, bool $includeDescendants = true, ?string $objectType = null, int $limit = 20, int $start = 0): array {
		$ids = [(int) $termId];
		if ($includeDescendants) {
			$topics = $this->keywords()->ofKind('topic');
			for ($i = 0; $i < count($ids); $i++) {
				foreach ($topics as $id => $topic) {
					if ((int) $topic->getVar('parent_id') === $ids[$i] && !in_array($id, $ids, true)) {
						$ids[] = $id;
					}
				}
			}
		}
		return $this->assignments()->objectsWith($ids, $objectType, $limit, $start);
	}

	public function clear(ObjectRef $ref): void {
		$this->classify($ref, []);
	}

	public function clearType(string $objectType): void {
		$criteria = $this->assignments()->typeCriteria($objectType);
		$criteria->add(new icms_db_criteria_Item('keyword_id', '(' . implode(',', array_keys($this->keywords()->ofKind('topic')) ?: [0]) . ')', 'IN'));
		$this->assignments()->deleteAll($criteria);
	}

	private function toTerm(mod_knowledgemanager_Keyword $topic): Term {
		$parentId = (int) $topic->getVar('parent_id');
		return new Term((string) $topic->getVar('keyword_id'), self::VOCABULARY, (string) $topic->getVar('label', 'n'), (string) $topic->getVar('slug', 'n'), $parentId > 0 ? (string) $parentId : null);
	}

	private function keywords(): mod_knowledgemanager_KeywordHandler {
		return icms_getModuleHandler('keyword', 'knowledgemanager');
	}

	private function assignments(): mod_knowledgemanager_AssignmentHandler {
		return icms_getModuleHandler('assignment', 'knowledgemanager');
	}
}
