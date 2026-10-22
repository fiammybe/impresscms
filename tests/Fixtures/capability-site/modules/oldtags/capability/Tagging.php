<?php
/**
 * OldTags reports contract version 0.3: listed, but never selectable
 */

use Icms\Capability\Contract\Tagging\CapabilityInterface;
use Icms\Capability\ObjectRef;

class mod_oldtags_TaggingCapability implements CapabilityInterface {
	public function capabilityName(): string {
		return self::NAME;
	}

	public function contractVersion(): string {
		return '0.3';
	}

	public function providerModule(): string {
		return 'oldtags';
	}

	public function supportsObjectType(string $objectType): bool {
		return true;
	}

	public function adminUrl(): ?string {
		return null;
	}

	public function setTags(ObjectRef $ref, array $labels): void {
	}

	public function addTags(ObjectRef $ref, array $labels): void {
	}

	public function removeTags(ObjectRef $ref, array $labels): void {
	}

	public function getTags(ObjectRef $ref): array {
		return [];
	}

	public function getTagsForObjects(array $refs): array {
		return [];
	}

	public function findObjects(string $tagSlug, ?string $objectType = null, int $limit = 20, int $start = 0): array {
		return [];
	}

	public function suggest(string $prefix, int $limit = 10): array {
		return [];
	}

	public function cloud(?string $objectType = null, int $limit = 50): array {
		return [];
	}

	public function clear(ObjectRef $ref): void {
	}

	public function clearType(string $objectType): void {
	}
}
