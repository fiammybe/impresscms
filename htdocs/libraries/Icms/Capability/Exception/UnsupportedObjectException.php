<?php

/**
 * An object that capabilities cannot reference
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Exception;

use InvalidArgumentException;

final class UnsupportedObjectException extends InvalidArgumentException
{
    public static function forObject(object $object): self
    {
        $class = $object::class;

        return new self("Only IPF objects (icms_ipf_Object) are supported by capabilities, got {$class}.");
    }

    public static function compositeKey(object $object): self
    {
        $class = $object::class;

        return new self("The IPF object {$class} has a composite key; capabilities reference objects by a single id.");
    }

    public static function notStored(object $object): self
    {
        $class = $object::class;

        return new self("The IPF object {$class} has no key value yet; store it before referencing it.");
    }
}
