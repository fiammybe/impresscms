<?php

/*
 * GENERATED FILE - DO NOT EDIT.
 *
 * Generator: php artisan icms:definitions:compile
 * Resource:  system.mimetype
 * Source:    SystemMimetypeHandler (modules/system/admin/mimetype/class/mimetype.php:110)
 *
 * Maps the resource abilities to ImpressCMS administration rights and item permissions.
 * Change the IPF definition and run `php artisan icms:definitions:compile`;
 * `php artisan icms:definitions:check` fails when this file differs from it.
 */

declare(strict_types=1);

namespace Modules\System\Generated\Mimetype;

final class MimetypePolicy extends \App\Ipf\Auth\IpfResourcePolicy
{
    public const RESOURCE = 'system.mimetype';

    /** who administers the resource */
    protected const ACCESS = [
        'system_function' => 'mimetype',
    ];

    /** the handler's per-item permissions (Icms\Ipf\Handler::addPermission()) */
    protected const PERMISSIONS = [
        'use_extension',
    ];
}
