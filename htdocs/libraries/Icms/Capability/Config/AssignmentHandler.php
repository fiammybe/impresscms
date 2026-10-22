<?php

/**
 * Handler of the capability assignment matrix
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Config;

use Icms\Db\Criteria\Item;
use Icms\Ipf\Handler;
use Icms\Ipf\Registry\Handler as ObjectCache;

/**
 * Handler of system_capabilityassignment, loaded as the system module item
 * "capabilityassignment" (mod_system_CapabilityassignmentHandler).
 */
class AssignmentHandler extends Handler
{
    /** Whether this request has loaded the table into the IPF object cache. */
    private bool $loaded = false;

    public function __construct($db)
    {
        parent::__construct($db, 'capabilityassignment', 'capabilityassignmentid', 'object_type', 'capability', 'system');
    }

    /**
     * Every cell, loaded once per request through the IPF object cache (design D7).
     *
     * @return array<string, array<string, Assignment>> object type => capability => cell
     */
    public function matrix(bool $refresh = false): array
    {
        $cache = ObjectCache::getInstance();
        $rows = $this->loaded && ! $refresh
            ? $cache->getObjects($this->_itemname, $this->_moduleName)
            : $cache->addObjectsFromHandler($this);
        $this->loaded = true;

        $matrix = [];

        foreach (is_array($rows) ? $rows : [] as $row) {
            $matrix[$row->objectType()][$row->capability()] = $row;
        }

        return $matrix;
    }

    public function cell(string $objectType, string $capability): ?Assignment
    {
        return $this->matrix()[$objectType][$capability] ?? null;
    }

    /** Enable or disable a capability for an object type; does not refresh the cached matrix. */
    public function setCell(string $objectType, string $capability, bool $enabled, string $source = Assignment::SOURCE_ADMIN): bool
    {
        $cell = $this->cell($objectType, $capability) ?? $this->create();
        $cell->setVar('object_type', $objectType);
        $cell->setVar('capability', $capability);
        $cell->setVar('enabled', $enabled ? 1 : 0);
        $cell->setVar('source', $source);

        return $this->insert($cell, true);
    }

    /** Remove the cells of every object type of a module (the consumer was uninstalled). */
    public function deleteForModule(string $dirname): bool
    {
        $deleted = $this->deleteAll(new Item('object_type', "{$dirname}/%", 'LIKE'));
        $this->matrix(true);

        return (bool) $deleted;
    }
}
