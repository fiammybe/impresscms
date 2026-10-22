<?php

/**
 * A capability was requested that has no usable provider
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Exception;

use Icms\Capability\State;
use RuntimeException;

final class UnavailableException extends RuntimeException
{
    public function __construct(
        public readonly string $capability,
        public readonly State $state,
    ) {
        parent::__construct("The capability \"{$capability}\" is not available (state: {$state->value}).");
    }
}
