<?php

use Icms\Capability\Contract\Tagging\Tag;

include_once __DIR__ . '/Tags.php';

class mod_smarttags_TagsHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'tags', 'tag_id', 'label', 'slug', 'smarttags');
	}

	/**
	 * The ids of the tags with these labels, creating the missing ones.
	 *
	 * @param list<string> $labels
	 * @return list<int>
	 */
	public function idsFor(array $labels): array {
		$ids = [];
		foreach ($labels as $label) {
			$label = trim($label);
			if ($label === '') {
				continue;
			}
			$existing = $this->bySlugs([Tag::slugify($label)]);
			if ($existing) {
				$ids[] = (int) reset($existing)->getVar('tag_id');
				continue;
			}
			$tag = $this->create();
			$tag->setVar('label', $label);
			$tag->setVar('slug', Tag::slugify($label));
			if ($this->insert($tag, true)) {
				$ids[] = (int) $tag->getVar('tag_id');
			}
		}
		return array_values(array_unique($ids));
	}

	/**
	 * @param list<string> $slugs
	 * @return array<int, mod_smarttags_Tags>
	 */
	public function bySlugs(array $slugs): array {
		if (!$slugs) {
			return [];
		}
		$quoted = array_map(fn ($slug) => $this->db->quoteString(Tag::slugify($slug)), $slugs);
		return $this->getObjects(new icms_db_criteria_Item('slug', '(' . implode(',', $quoted) . ')', 'IN'), true);
	}

	/**
	 * @param list<int> $ids
	 * @return array<int, mod_smarttags_Tags>
	 */
	public function byIds(array $ids): array {
		$ids = array_filter(array_map('intval', $ids));
		if (!$ids) {
			return [];
		}
		return $this->getObjects(new icms_db_criteria_Item('tag_id', '(' . implode(',', $ids) . ')', 'IN'), true);
	}

	/** @return list<mod_smarttags_Tags> tags whose label starts with the prefix */
	public function startingWith(string $prefix, int $limit): array {
		$escaped = addcslashes($this->db->escape($prefix), '%_');
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('label', $escaped . '%', 'LIKE'));
		$criteria->setSort('label');
		$criteria->setLimit($limit);
		return array_values($this->getObjects($criteria));
	}

	public function toTag(mod_smarttags_Tags $tag): Tag {
		$slug = (string) $tag->getVar('slug', 'n');
		return new Tag((string) $tag->getVar('tag_id'), (string) $tag->getVar('label', 'n'), $slug, ICMS_URL . '/modules/smarttags/tag.php?slug=' . rawurlencode($slug));
	}
}
