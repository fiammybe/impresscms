<?php

/**
 * Generic contract shared by every capability
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Contract;

use Icms\Capability\ObjectRef;

/**
 * Extended by every capability-specific contract. A provider class implements
 * the specific contract; it never implements this interface on its own.
 */
interface BaseInterface
{
    public function capabilityName(): string;

    /** The contract version the provider implements ("1.0"). */
    public function contractVersion(): string;

    /** The dirname of the provider module. */
    public function providerModule(): string;

    /** Can this provider serve objects of the given type ("news/article")? */
    public function supportsObjectType(string $objectType): bool;

    /** Remove everything the provider stores for this object. Idempotent. */
    public function clear(ObjectRef $ref): void;

    /** Remove everything the provider stores for objects of this type ("news/article"). Idempotent. */
    public function clearType(string $objectType): void;

    /** The provider's own administration page, linked from the capabilities page. */
    public function adminUrl(): ?string;
}
