<?php

declare(strict_types=1);

namespace App\Ipf\Auth;

use App\Auth\IcmsUser;

/**
 * Base of the generated <Item>Policy classes: Laravel abilities of an IPF
 * resource, decided by the ImpressCMS rights that guard its legacy page.
 *
 * - viewAny, view, create, update, delete: who administers the resource
 *   (ACCESS): a system admin function (`system_function`), granted to
 *   webmasters and to system module administrators with the function's
 *   category, as modules/system/admin.php checks it; or the module_admin
 *   right on a module (`module_admin`)
 * - permission: a per-item permission of the handler (PERMISSIONS), from
 *   group_permission, with anonymous visitors in the anonymous group
 *
 * The legacy permission storage stays authoritative: the policy reads it,
 * never keeps its own.
 */
abstract class IpfResourcePolicy
{
    public const RESOURCE = '';

    /** @var array<string, string> */
    protected const ACCESS = [];

    /** @var list<string> */
    protected const PERMISSIONS = [];

    public function __construct(private readonly ResourceAccess $access)
    {
    }

    public function viewAny(?IcmsUser $user): bool
    {
        return $this->administers($user);
    }

    public function view(?IcmsUser $user, mixed $item = null): bool
    {
        return $this->administers($user);
    }

    public function create(?IcmsUser $user): bool
    {
        return $this->administers($user);
    }

    public function update(?IcmsUser $user, mixed $item = null): bool
    {
        return $this->administers($user);
    }

    public function delete(?IcmsUser $user, mixed $item = null): bool
    {
        return $this->administers($user);
    }

    /**
     * Whether the user holds a permission of the handler on an item.
     */
    public function permission(?IcmsUser $user, string $permission, int $item): bool
    {
        if (! in_array($permission, static::PERMISSIONS, true)) {
            return false;
        }

        return $this->access->hasItemPermission($user, explode('.', static::RESOURCE)[0], $permission, $item);
    }

    private function administers(?IcmsUser $user): bool
    {
        if (isset(static::ACCESS['system_function'])) {
            return $this->access->mayUseSystemFunction($user, static::ACCESS['system_function']);
        }

        if (isset(static::ACCESS['module_admin'])) {
            return $this->access->administersModule($user, static::ACCESS['module_admin']);
        }

        return false;
    }
}
