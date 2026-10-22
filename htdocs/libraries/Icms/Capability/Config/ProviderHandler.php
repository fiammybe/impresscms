<?php

/**
 * Handler of the selected capability providers
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Config;

use Icms\Ipf\Handler;
use Icms\Ipf\Registry\Handler as ObjectCache;

/**
 * Handler of system_capabilityprovider, loaded as the system module item
 * "capabilityprovider" (mod_system_CapabilityproviderHandler).
 */
class ProviderHandler extends Handler
{
    /** Whether this request has loaded the table into the IPF object cache. */
    private bool $loaded = false;

    public function __construct($db)
    {
        parent::__construct($db, 'capabilityprovider', 'capabilityproviderid', 'capability', 'provider', 'system');
    }

    /**
     * The selection of every capability, loaded once per request through the
     * IPF object cache (design D7).
     *
     * @return array<string, Provider> by capability
     */
    public function selections(bool $refresh = false): array
    {
        $cache = ObjectCache::getInstance();
        $rows = $this->loaded && ! $refresh
            ? $cache->getObjects($this->_itemname, $this->_moduleName)
            : $cache->addObjectsFromHandler($this);
        $this->loaded = true;

        $selections = [];

        foreach (is_array($rows) ? $rows : [] as $row) {
            $selections[$row->capability()] = $row;
        }

        return $selections;
    }

    /** Make the module the active provider of the capability, replacing the previous selection. */
    public function select(string $capability, string $module, int $uid): bool
    {
        $row = $this->selections()[$capability] ?? $this->create();
        $row->setVar('capability', $capability);
        $row->setVar('provider', $module);
        $row->setVar('activated', time());
        $row->setVar('activated_by', $uid);

        $stored = $this->insert($row, true);
        $this->selections(true);

        return $stored;
    }
}
