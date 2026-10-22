<?php
/**
 * Taxonomy as the taxonomy provider: hierarchical terms of vocabularies, in
 * the module's own tables (Model A)
 */

use Icms\Capability\Contract\Taxonomy\CapabilityInterface;
use Icms\Capability\Contract\Taxonomy\Term;
use Icms\Capability\Contract\Taxonomy\Vocabulary;
use Icms\Capability\ObjectRef;
use Icms\Capability\Submission;
use Icms\Capability\Ui\FormContributorInterface;
use Icms\Capability\Ui\PersistenceContributorInterface;
use Icms\Capability\Ui\TableFilterContributorInterface;
use Icms\Capability\Ui\TemplateRendererInterface;
use Icms\Capability\Ui\ViewContributorInterface;

class mod_taxonomy_TaxonomyCapability implements
	CapabilityInterface,
	FormContributorInterface,
	PersistenceContributorInterface,
	ViewContributorInterface,
	TableFilterContributorInterface,
	TemplateRendererInterface
{
	/** @var array<string, list<int>> term ids of prefetched objects */
	private array $prefetched = [];

	public function capabilityName(): string {
		return self::NAME;
	}

	public function contractVersion(): string {
		return '1.0';
	}

	public function providerModule(): string {
		return 'taxonomy';
	}

	public function supportsObjectType(string $objectType): bool {
		return true;
	}

	public function adminUrl(): ?string {
		return ICMS_URL . '/modules/taxonomy/admin/vocabularies.php';
	}

	public function vocabularies(?string $objectType = null): array {
		$vocabularies = array_values($this->vocabularyHandler()->all());
		if ($objectType === null) {
			return $vocabularies;
		}
		return array_values(array_filter($vocabularies, static fn (Vocabulary $vocabulary): bool => $vocabulary->appliesTo($objectType)));
	}

	public function terms(string $vocabularyId): array {
		return $this->termHandler()->ofVocabulary((int) $vocabularyId);
	}

	public function classify(ObjectRef $ref, array $termIds): void {
		$byVocabulary = $this->groupByVocabulary($termIds);
		$this->assertCardinality($byVocabulary);
		foreach ($byVocabulary as $vocabularyId => $ids) {
			$this->replaceInVocabulary($ref, $vocabularyId, $ids);
		}
	}

	public function unclassify(ObjectRef $ref, array $termIds): void {
		$this->relationHandler()->remove($ref, array_map('intval', $termIds));
		unset($this->prefetched[$ref->toString()]);
	}

	public function termsOf(ObjectRef $ref): array {
		return $this->termsOfObjects([$ref])[$ref->toString()];
	}

	public function termsOfObjects(array $refs): array {
		$termIds = $this->relationHandler()->termIdsOfObjects($refs);
		$terms = $this->termHandler()->byIds(array_merge([], ...array_values($termIds)));
		$result = [];
		foreach ($termIds as $key => $ids) {
			$result[$key] = [];
			foreach ($ids as $id) {
				if (isset($terms[$id])) {
					$result[$key][] = $this->termHandler()->toTerm($terms[$id]);
				}
			}
		}
		return $result;
	}

	public function findObjects(string $termId, bool $includeDescendants = true, ?string $objectType = null, int $limit = 20, int $start = 0): array {
		$termIds = $includeDescendants ? $this->termHandler()->withDescendants((int) $termId) : [(int) $termId];
		return $this->relationHandler()->objectsWithTerms($termIds, $objectType, $limit, $start);
	}

	public function clear(ObjectRef $ref): void {
		$this->relationHandler()->deleteAll($this->relationHandler()->refCriteria($ref));
		unset($this->prefetched[$ref->toString()]);
	}

	public function clearType(string $objectType): void {
		$this->relationHandler()->deleteAll($this->relationHandler()->typeCriteria($objectType));
		$this->prefetched = [];
	}

	public function formElements(string $objectType, ?ObjectRef $ref, string $fieldPrefix): array {
		$current = $ref === null ? [] : array_map(static fn (Term $term): string => $term->id, $this->termsOf($ref));
		$elements = [];
		foreach ($this->vocabularies($objectType) as $vocabulary) {
			$select = new icms_form_elements_Select($vocabulary->label, $fieldPrefix . '[terms][' . $vocabulary->id . ']', $current, $vocabulary->multiple ? 5 : 1, $vocabulary->multiple);
			$select->addOptionArray($vocabulary->multiple ? $this->indentedTerms($vocabulary->id) : ['' => '-'] + $this->indentedTerms($vocabulary->id));
			$elements[] = $select;
		}
		return $elements;
	}

	public function store(ObjectRef $ref, Submission $submission): array {
		$selected = $submission->get('terms', []);
		if (!is_array($selected)) {
			return [];
		}
		$errors = [];
		foreach ($this->vocabularies($ref->objectType()) as $vocabulary) {
			$ids = array_values(array_filter(array_map('intval', (array) ($selected[$vocabulary->id] ?? []))));
			if (!$vocabulary->multiple && count($ids) > 1) {
				$errors[] = sprintf('The vocabulary "%s" allows one term per object.', $vocabulary->label);
				continue;
			}
			$this->replaceInVocabulary($ref, (int) $vocabulary->id, $ids);
		}
		return $errors;
	}

	public function prefetch(array $refs): void {
		$missing = array_values(array_filter($refs, fn (ObjectRef $ref): bool => !isset($this->prefetched[$ref->toString()])));
		if ($missing) {
			$this->prefetched = $this->relationHandler()->termIdsOfObjects($missing) + $this->prefetched;
		}
	}

	public function viewRow(ObjectRef $ref): ?array {
		$this->prefetch([$ref]);
		$html = $this->breadcrumbsHtml($this->prefetched[$ref->toString()] ?? []);
		return $html === '' ? null : ['caption' => 'Classification', 'html' => $html];
	}

	public function filterOptions(string $objectType): array {
		$options = [];
		foreach ($this->vocabularies($objectType) as $vocabulary) {
			$options += $this->indentedTerms($vocabulary->id);
		}
		return $options;
	}

	public function matchingIds(string $objectType, string $filterValue): array {
		return array_map(static fn (ObjectRef $ref): int => $ref->id, $this->findObjects($filterValue, true, $objectType, 0));
	}

	public function render(ObjectRef $ref, string $variant, array $params): string {
		$termIds = $this->relationHandler()->termIdsOfObjects([$ref])[$ref->toString()];
		if ($variant === 'breadcrumbs') {
			return $this->breadcrumbsHtml($termIds);
		}
		if ($variant === 'list') {
			$html = '';
			foreach ($this->termHandler()->byIds($termIds) as $term) {
				$html .= '<li><a href="' . ICMS_URL . '/modules/taxonomy/browse.php?term_id=' . (int) $term->getVar('term_id') . '">' . htmlspecialchars((string) $term->getVar('label', 'n'), ENT_QUOTES) . '</a></li>';
			}
			return '<ul class="taxonomy-list">' . $html . '</ul>';
		}
		return '';
	}

	/** @return array<string, string> term id => label, indented by depth */
	private function indentedTerms(string $vocabularyId): array {
		$options = [];
		$terms = $this->termHandler()->byIds(array_map(static fn (Term $term): int => (int) $term->id, $this->terms($vocabularyId)));
		foreach ($this->terms($vocabularyId) as $term) {
			$depth = isset($terms[(int) $term->id]) ? $this->termHandler()->depth($terms[(int) $term->id]) : 0;
			$options[$term->id] = str_repeat('-- ', $depth) . $term->label;
		}
		return $options;
	}

	/** @param list<int> $termIds */
	private function breadcrumbsHtml(array $termIds): string {
		$crumbs = [];
		foreach ($this->termHandler()->byIds($termIds) as $term) {
			$crumbs[] = '<span class="taxonomy-breadcrumb">' . htmlspecialchars(implode(' › ', $this->termHandler()->breadcrumb($term)), ENT_QUOTES) . '</span>';
		}
		return implode('<br />', $crumbs);
	}

	/**
	 * @param list<string|int> $termIds
	 * @return array<int, list<int>> term ids by vocabulary id
	 */
	private function groupByVocabulary(array $termIds): array {
		$byVocabulary = [];
		foreach ($this->termHandler()->byIds(array_map('intval', $termIds)) as $id => $term) {
			$byVocabulary[(int) $term->getVar('vocabulary_id')][] = (int) $id;
		}
		return $byVocabulary;
	}

	/** @param array<int, list<int>> $byVocabulary */
	private function assertCardinality(array $byVocabulary): void {
		$vocabularies = $this->vocabularyHandler()->all();
		foreach ($byVocabulary as $vocabularyId => $ids) {
			$vocabulary = $vocabularies[$vocabularyId] ?? null;
			if ($vocabulary !== null && !$vocabulary->multiple && count($ids) > 1) {
				throw new InvalidArgumentException(sprintf('The vocabulary "%s" allows one term per object.', $vocabulary->label));
			}
		}
	}

	/** @param list<int> $termIds */
	private function replaceInVocabulary(ObjectRef $ref, int $vocabularyId, array $termIds): void {
		$inVocabulary = array_map(static fn (Term $term): int => (int) $term->id, $this->terms((string) $vocabularyId));
		$current = array_values(array_intersect($this->relationHandler()->termIdsOfObjects([$ref])[$ref->toString()], $inVocabulary));
		$wanted = array_values(array_intersect($termIds, $inVocabulary));
		$this->relationHandler()->remove($ref, array_values(array_diff($current, $wanted)));
		$this->relationHandler()->add($ref, array_values(array_diff($wanted, $current)));
		unset($this->prefetched[$ref->toString()]);
	}

	private function vocabularyHandler(): mod_taxonomy_VocabularyHandler {
		return icms_getModuleHandler('vocabulary', 'taxonomy');
	}

	private function termHandler(): mod_taxonomy_TermHandler {
		return icms_getModuleHandler('term', 'taxonomy');
	}

	private function relationHandler(): mod_taxonomy_RelationHandler {
		return icms_getModuleHandler('relation', 'taxonomy');
	}
}
