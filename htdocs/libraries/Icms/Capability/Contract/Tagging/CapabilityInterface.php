<?php

/**
 * The tagging capability contract
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Contract\Tagging;

use Icms\Capability\Contract\BaseInterface;
use Icms\Capability\ObjectRef;

/**
 * Free-form tags on IPF objects (spec capability-contracts, Tagging contract).
 * How a provider stores tags is not part of the contract.
 */
interface CapabilityInterface extends BaseInterface
{
    public const NAME = 'tagging';

    public const VERSION = '1.0';

    /**
     * Replace the tags of the object.
     *
     * @param list<string> $labels
     */
    public function setTags(ObjectRef $ref, array $labels): void;

    /** @param list<string> $labels */
    public function addTags(ObjectRef $ref, array $labels): void;

    /** @param list<string> $labels */
    public function removeTags(ObjectRef $ref, array $labels): void;

    /** @return list<Tag> */
    public function getTags(ObjectRef $ref): array;

    /**
     * @param list<ObjectRef> $refs
     * @return array<string, list<Tag>> keyed by ObjectRef::toString(); objects without tags map to []
     */
    public function getTagsForObjects(array $refs): array;

    /**
     * Objects tagged with the tag, in a stable order.
     *
     * @return list<ObjectRef>
     */
    public function findObjects(string $tagSlug, ?string $objectType = null, int $limit = 20, int $start = 0): array;

    /**
     * Tags whose label starts with the prefix, case-insensitively.
     *
     * @return list<Tag>
     */
    public function suggest(string $prefix, int $limit = 10): array;

    /** @return array<string, int> slug => usage count, most used first */
    public function cloud(?string $objectType = null, int $limit = 50): array;
}
