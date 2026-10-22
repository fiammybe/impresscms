<?php

/**
 * A cell of the capability assignment matrix
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Config;

use Icms\Ipf\Entity;

/**
 * One row of system_capabilityassignment (design D7, D9): whether a capability
 * is enabled for an IPF object type ("news/article"), and whether the
 * administrator or a consumer default set it.
 *
 * Loaded as the system module item "capabilityassignment"
 * (mod_system_Capabilityassignment).
 */
class Assignment extends Entity
{
    public const SOURCE_ADMIN = 'admin';
    public const SOURCE_DEFAULT = 'default';

    public function __construct(AssignmentHandler &$handler)
    {
        parent::__construct($handler);

        $this->quickInitVar('capabilityassignmentid', XOBJ_DTYPE_INT, true);
        $this->quickInitVar('object_type', XOBJ_DTYPE_TXTBOX, true);
        $this->quickInitVar('capability', XOBJ_DTYPE_TXTBOX, true);
        $this->quickInitVar('enabled', XOBJ_DTYPE_INT, false, '', '', 0);
        $this->quickInitVar('source', XOBJ_DTYPE_TXTBOX, false, '', '', self::SOURCE_ADMIN);
    }

    public function objectType(): string
    {
        return (string) $this->getVar('object_type', 'n');
    }

    public function capability(): string
    {
        return (string) $this->getVar('capability', 'n');
    }

    public function isEnabled(): bool
    {
        return (int) $this->getVar('enabled', 'n') === 1;
    }

    public function source(): string
    {
        return (string) $this->getVar('source', 'n');
    }
}
