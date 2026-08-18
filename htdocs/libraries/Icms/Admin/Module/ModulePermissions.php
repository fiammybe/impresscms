<?php

declare(strict_types=1);

namespace Icms\Admin\Module;

use icms;

/**
 * Module permission helpers for the ImpressCMS admin control panel.
 *
 * Provides a thin, type-hinted wrapper around the `icms_member_groupperm`
 * handler for reading and writing module-level admin permissions.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Module
 * @since     2.0
 */
class ModulePermissions
{
    /**
     * Permission name used for admin access checks.
     */
    private const PERM_MODULE_ADMIN = 'module_admin';

    /**
     * Retrieve the group IDs that have admin access to the given module.
     *
     * @param int $moduleId Numeric module ID (`mid`).
     *
     * @return int[] Array of group IDs that hold the permission.
     */
    public function getPermissions(int $moduleId): array
    {
        /** @var \icms_member_groupperm_Handler $handler */
        $handler  = icms::handler('icms_member_groupperm');
        $groupIds = $handler->getGroupIds(self::PERM_MODULE_ADMIN, $moduleId);

        return array_map('intval', (array) $groupIds);
    }

    /**
     * Overwrite the admin-access permissions for the given module.
     *
     * All existing `module_admin` rows for `$moduleId` are removed first, then
     * new rows are inserted for each group ID supplied.
     *
     * @param int   $moduleId Numeric module ID (`mid`).
     * @param int[] $groupIds List of group IDs that should receive admin access.
     *
     * @return bool `true` when all insert operations succeeded, `false` otherwise.
     */
    public function setPermissions(int $moduleId, array $groupIds): bool
    {
        /** @var \icms_member_groupperm_Handler $handler */
        $handler = icms::handler('icms_member_groupperm');

        // Remove all existing permissions for this module/perm combination.
        $handler->deleteByModule($moduleId, self::PERM_MODULE_ADMIN);

        $success = true;
        foreach ($groupIds as $groupId) {
            $perm = $handler->create();
            $perm->setVar('gperm_groupid',  (int) $groupId);
            $perm->setVar('gperm_itemid',   $moduleId);
            $perm->setVar('gperm_name',     self::PERM_MODULE_ADMIN);
            $perm->setVar('gperm_modid',    1); // system module owns permission records

            if (!$handler->insert($perm)) {
                $success = false;
            }
        }

        return $success;
    }
}
