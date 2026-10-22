<?php

/**
 * Resolution state of a capability
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

enum State: string
{
    /** An active provider is selected and usable. */
    case Active = 'active';

    /** Providers exist, but none is active (none chosen yet, or the chosen one is gone). */
    case Unresolved = 'unresolved';

    /** No compatible provider is installed. */
    case Unavailable = 'unavailable';
}
