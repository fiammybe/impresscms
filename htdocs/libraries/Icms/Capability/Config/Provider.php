<?php

/**
 * The selected provider of a capability
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Config;

use Icms\Ipf\Entity;

/**
 * One row of system_capabilityprovider (design D7): which module is the
 * active provider of a capability. The row is kept when that module goes
 * away, so that reactivating it restores the selection (design D8).
 *
 * Loaded as the system module item "capabilityprovider"
 * (mod_system_Capabilityprovider).
 */
class Provider extends Entity
{
    public function __construct(ProviderHandler &$handler)
    {
        parent::__construct($handler);

        $this->quickInitVar('capabilityproviderid', XOBJ_DTYPE_INT, true);
        $this->quickInitVar('capability', XOBJ_DTYPE_TXTBOX, true);
        $this->quickInitVar('provider', XOBJ_DTYPE_TXTBOX, true);
        $this->quickInitVar('activated', XOBJ_DTYPE_INT, false, '', '', 0);
        $this->quickInitVar('activated_by', XOBJ_DTYPE_INT, false, '', '', 0);
    }

    public function capability(): string
    {
        return (string) $this->getVar('capability', 'n');
    }

    public function provider(): string
    {
        return (string) $this->getVar('provider', 'n');
    }
}
