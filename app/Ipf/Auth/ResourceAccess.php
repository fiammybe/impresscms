<?php

declare(strict_types=1);

namespace App\Ipf\Auth;

use App\Admin\SystemAdminAccess;
use App\Auth\IcmsUser;
use App\Legacy\HandlerLocator;
use App\Legacy\LegacyRuntime;

/**
 * The ImpressCMS rights the generated policies ask for, read from the legacy
 * group_permission storage through the groupperm handler.
 */
final class ResourceAccess
{
    public function __construct(
        private readonly SystemAdminAccess $systemAdmin,
        private readonly HandlerLocator $handlers,
        private readonly LegacyRuntime $runtime,
    ) {
    }

    public function mayUseSystemFunction(?IcmsUser $user, string $fct): bool
    {
        return $user !== null && $this->systemAdmin->isGranted($fct, $user->getGroups());
    }

    public function administersModule(?IcmsUser $user, string $dirname): bool
    {
        if ($user === null) {
            return false;
        }

        $module = $this->moduleId($dirname);

        return $module !== null && (bool) $this->groupPermissions()->checkRight('module_admin', $module, $user->getGroups());
    }

    public function hasItemPermission(?IcmsUser $user, string $dirname, string $permission, int $item): bool
    {
        $module = $this->moduleId($dirname);
        if ($module === null) {
            return false;
        }

        $groups = $user?->getGroups() ?? [(int) ICMS_GROUP_ANONYMOUS];

        return (bool) $this->groupPermissions()->checkRight($permission, $item, $groups, $module);
    }

    private function moduleId(string $dirname): ?int
    {
        $this->runtime->bootTo('siteState');
        $module = $this->handlers->get('icms_module')->getByDirname($dirname);

        return is_object($module) ? (int) $module->getVar('mid') : null;
    }

    private function groupPermissions(): object
    {
        $this->runtime->bootTo('siteState');

        return $this->handlers->get('icms_member_groupperm');
    }
}
