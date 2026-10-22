<?php
/**
 * KnowledgeManager as a tagging provider: keywords of kind "tag"
 */

use Icms\Capability\Contract\Tagging\CapabilityInterface;
use Icms\Capability\Contract\Tagging\Tag;
use Icms\Capability\ObjectRef;

class mod_knowledgemanager_TaggingCapability implements CapabilityInterface {
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

	public function setTags(ObjectRef $ref, array $labels): void {
		$ids = array_map(fn (string $label): int => $this->keywords()->idFor('tag', $label), array_filter(array_map('trim', $labels), 'strlen'));
		$this->assignments()->replace($ref, array_keys($this->keywords()->ofKind('tag')), $ids);
	}

	public function addTags(ObjectRef $ref, array $labels): void {
		$this->setTags($ref, array_merge(array_map(static fn (Tag $tag): string => $tag->label, $this->getTags($ref)), $labels));
	}

	public function removeTags(ObjectRef $ref, array $labels): void {
		$remove = array_map([Tag::class, 'slugify'], $labels);
		$this->setTags($ref, array_map(static fn (Tag $tag): string => $tag->label, array_filter($this->getTags($ref), static fn (Tag $tag): bool => !in_array($tag->slug, $remove, true))));
	}

	public function getTags(ObjectRef $ref): array {
		return $this->getTagsForObjects([$ref])[$ref->toString()];
	}

	public function getTagsForObjects(array $refs): array {
		$tags = $this->keywords()->ofKind('tag');
		$result = [];
		foreach ($this->assignments()->keywordIdsOf($refs) as $key => $ids) {
			$result[$key] = [];
			foreach ($ids as $id) {
				if (isset($tags[$id])) {
					$result[$key][] = new Tag((string) $id, (string) $tags[$id]->getVar('label', 'n'), (string) $tags[$id]->getVar('slug', 'n'));
				}
			}
		}
		return $result;
	}

	public function findObjects(string $tagSlug, ?string $objectType = null, int $limit = 20, int $start = 0): array {
		$ids = [];
		foreach ($this->keywords()->ofKind('tag') as $id => $tag) {
			if ($tag->getVar('slug', 'n') === $tagSlug) {
				$ids[] = $id;
			}
		}
		return $this->assignments()->objectsWith($ids, $objectType, $limit, $start);
	}

	public function suggest(string $prefix, int $limit = 10): array {
		$tags = [];
		foreach ($this->keywords()->ofKind('tag') as $id => $tag) {
			if (stripos((string) $tag->getVar('label', 'n'), $prefix) === 0) {
				$tags[] = new Tag((string) $id, (string) $tag->getVar('label', 'n'), (string) $tag->getVar('slug', 'n'));
			}
		}
		return array_slice($tags, 0, $limit);
	}

	public function cloud(?string $objectType = null, int $limit = 50): array {
		return [];
	}

	public function clear(ObjectRef $ref): void {
		$this->assignments()->replace($ref, array_keys($this->keywords()->ofKind('tag')), []);
	}

	public function clearType(string $objectType): void {
		$criteria = $this->assignments()->typeCriteria($objectType);
		$criteria->add(new icms_db_criteria_Item('keyword_id', '(' . implode(',', array_keys($this->keywords()->ofKind('tag')) ?: [0]) . ')', 'IN'));
		$this->assignments()->deleteAll($criteria);
	}

	private function keywords(): mod_knowledgemanager_KeywordHandler {
		return icms_getModuleHandler('keyword', 'knowledgemanager');
	}

	private function assignments(): mod_knowledgemanager_AssignmentHandler {
		return icms_getModuleHandler('assignment', 'knowledgemanager');
	}
}
