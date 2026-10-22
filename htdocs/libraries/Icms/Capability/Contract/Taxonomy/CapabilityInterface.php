<?php

/**
 * The taxonomy capability contract
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Contract\Taxonomy;

use Icms\Capability\Contract\BaseInterface;
use Icms\Capability\ObjectRef;

/**
 * Classification of IPF objects with hierarchical terms of vocabularies
 * (spec capability-contracts, Taxonomy contract).
 */
interface CapabilityInterface extends BaseInterface
{
    public const NAME = 'taxonomy';

    public const VERSION = '1.0';

    /**
     * Vocabularies, optionally only those that apply to an object type.
     *
     * @return list<Vocabulary>
     */
    public function vocabularies(?string $objectType = null): array;

    /** @return list<Term> ordered depth-first, parentId null for roots */
    public function terms(string $vocabularyId): array;

    /**
     * Replace the object's terms in the vocabularies of these terms.
     *
     * @param list<string> $termIds
     * @throws \InvalidArgumentException for several terms of a single-term vocabulary, naming the vocabulary
     */
    public function classify(ObjectRef $ref, array $termIds): void;

    /** @param list<string> $termIds */
    public function unclassify(ObjectRef $ref, array $termIds): void;

    /** @return list<Term> */
    public function termsOf(ObjectRef $ref): array;

    /**
     * @param list<ObjectRef> $refs
     * @return array<string, list<Term>> keyed by ObjectRef::toString(); unclassified objects map to []
     */
    public function termsOfObjects(array $refs): array;

    /** @return list<ObjectRef> */
    public function findObjects(string $termId, bool $includeDescendants = true, ?string $objectType = null, int $limit = 20, int $start = 0): array;
}
