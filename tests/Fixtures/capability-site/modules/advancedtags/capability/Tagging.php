<?php
/**
 * AdvancedTags as the tagging provider: translates the tagging contract to the
 * module's own IPF handlers (Model A) and contributes form, view, filter and
 * template output.
 */

use Icms\Capability\Contract\Tagging\CapabilityInterface;
use Icms\Capability\Contract\Tagging\Tag;
use Icms\Capability\ObjectRef;
use Icms\Capability\Submission;
use Icms\Capability\Ui\FormContributorInterface;
use Icms\Capability\Ui\PersistenceContributorInterface;
use Icms\Capability\Ui\TableFilterContributorInterface;
use Icms\Capability\Ui\TemplateRendererInterface;
use Icms\Capability\Ui\ViewContributorInterface;

class mod_advancedtags_TaggingCapability implements
	CapabilityInterface,
	FormContributorInterface,
	PersistenceContributorInterface,
	ViewContributorInterface,
	TableFilterContributorInterface,
	TemplateRendererInterface
{
	/** @var array<string, list<Tag>> tags of prefetched objects */
	private array $prefetched = [];

	public function capabilityName(): string {
		return self::NAME;
	}

	public function contractVersion(): string {
		return '1.0';
	}

	public function providerModule(): string {
		return 'advancedtags';
	}

	public function supportsObjectType(string $objectType): bool {
		return true;
	}

	public function adminUrl(): ?string {
		return ICMS_URL . '/modules/advancedtags/admin/tags.php';
	}

	public function setTags(ObjectRef $ref, array $labels): void {
		$this->links()->replaceFor($ref, $this->tags()->idsFor($labels));
		unset($this->prefetched[$ref->toString()]);
	}

	public function addTags(ObjectRef $ref, array $labels): void {
		$this->links()->addFor($ref, $this->tags()->idsFor($labels));
		unset($this->prefetched[$ref->toString()]);
	}

	public function removeTags(ObjectRef $ref, array $labels): void {
		$this->links()->removeFor($ref, array_keys($this->tags()->bySlugs(array_map([Tag::class, 'slugify'], $labels))));
		unset($this->prefetched[$ref->toString()]);
	}

	public function getTags(ObjectRef $ref): array {
		return $this->getTagsForObjects([$ref])[$ref->toString()];
	}

	public function getTagsForObjects(array $refs): array {
		$tagIds = $this->links()->tagIdsOfObjects($refs);
		$tags = $this->tags()->byIds(array_merge([], ...array_values($tagIds)));
		$result = [];
		foreach ($tagIds as $key => $ids) {
			$result[$key] = [];
			foreach ($ids as $id) {
				if (isset($tags[$id])) {
					$result[$key][] = $this->tags()->toTag($tags[$id]);
				}
			}
		}
		return $result;
	}

	public function findObjects(string $tagSlug, ?string $objectType = null, int $limit = 20, int $start = 0): array {
		$tag = $this->tags()->bySlugs([$tagSlug]);
		if (!$tag) {
			return [];
		}
		return $this->links()->objectsWithTag((int) array_key_first($tag), $objectType, $limit, $start);
	}

	public function suggest(string $prefix, int $limit = 10): array {
		return array_map([$this->tags(), 'toTag'], $this->tags()->startingWith($prefix, $limit));
	}

	public function cloud(?string $objectType = null, int $limit = 50): array {
		$counts = $this->links()->counts($objectType, $limit);
		$tags = $this->tags()->byIds(array_keys($counts));
		$cloud = [];
		foreach ($counts as $tagId => $count) {
			if (isset($tags[$tagId])) {
				$cloud[(string) $tags[$tagId]->getVar('slug', 'n')] = $count;
			}
		}
		return $cloud;
	}

	public function clear(ObjectRef $ref): void {
		$this->links()->deleteAll($this->links()->refCriteria($ref));
		unset($this->prefetched[$ref->toString()]);
	}

	public function clearType(string $objectType): void {
		$this->links()->deleteAll($this->links()->typeCriteria($objectType));
		$this->prefetched = [];
	}

	public function formElements(string $objectType, ?ObjectRef $ref, string $fieldPrefix): array {
		$labels = $ref === null ? [] : array_map(static fn (Tag $tag): string => $tag->label, $this->getTags($ref));
		$field = new icms_form_elements_Text('Tags', $fieldPrefix . '[tags]', 50, 255, implode(', ', $labels));
		$field->setDescription('Comma-separated tags');
		return [$field];
	}

	public function store(ObjectRef $ref, Submission $submission): array {
		if (!$submission->has('tags')) {
			return [];
		}
		$labels = array_values(array_filter(array_map('trim', explode(',', $submission->string('tags'))), 'strlen'));
		$this->setTags($ref, $labels);
		return [];
	}

	public function prefetch(array $refs): void {
		$missing = array_values(array_filter($refs, fn (ObjectRef $ref): bool => !isset($this->prefetched[$ref->toString()])));
		if ($missing) {
			$this->prefetched = $this->getTagsForObjects($missing) + $this->prefetched;
		}
	}

	public function viewRow(ObjectRef $ref): ?array {
		$this->prefetch([$ref]);
		$tags = $this->prefetched[$ref->toString()] ?? [];
		if (!$tags) {
			return null;
		}
		return ['caption' => 'Tags', 'html' => $this->links_html($tags)];
	}

	public function filterOptions(string $objectType): array {
		$options = [];
		foreach ($this->cloud($objectType, 100) as $slug => $count) {
			$options[$slug] = $slug . ' (' . $count . ')';
		}
		return $options;
	}

	public function matchingIds(string $objectType, string $filterValue): array {
		return array_map(static fn (ObjectRef $ref): int => $ref->id, $this->findObjects($filterValue, $objectType, 0));
	}

	public function render(ObjectRef $ref, string $variant, array $params): string {
		if ($variant === 'list') {
			return '<ul class="advancedtags-list">' . $this->links_html($this->getTags($ref), 'li') . '</ul>';
		}
		if ($variant === 'cloud') {
			$html = '';
			foreach ($this->cloud($ref->objectType(), (int) ($params['limit'] ?? 50)) as $slug => $count) {
				$html .= '<span class="advancedtags-cloud" data-count="' . $count . '">' . htmlspecialchars($slug, ENT_QUOTES) . '</span>';
			}
			return $html;
		}
		return '';
	}

	/** @param list<Tag> $tags */
	private function links_html(array $tags, string $wrap = ''): string {
		$html = [];
		foreach ($tags as $tag) {
			$link = '<a class="advancedtags-tag" href="' . htmlspecialchars((string) $tag->url, ENT_QUOTES) . '">' . htmlspecialchars($tag->label, ENT_QUOTES) . '</a>';
			$html[] = $wrap === '' ? $link : "<{$wrap}>{$link}</{$wrap}>";
		}
		return implode($wrap === '' ? ', ' : '', $html);
	}

	private function tags(): mod_advancedtags_TagHandler {
		return icms_getModuleHandler('tag', 'advancedtags');
	}

	private function links(): mod_advancedtags_LinkHandler {
		return icms_getModuleHandler('link', 'advancedtags');
	}
}
