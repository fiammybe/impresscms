<?php

/*
 * GENERATED FILE - DO NOT EDIT.
 *
 * Generator: php artisan icms:definitions:compile
 * Resource:  system.mimetype
 * Source:    SystemMimetypeHandler (modules/system/admin/mimetype/class/mimetype.php:110)
 *
 * Reads the legacy table; writes go through the IPF handler.
 * Change the IPF definition and run `php artisan icms:definitions:compile`;
 * `php artisan icms:definitions:check` fails when this file differs from it.
 */

declare(strict_types=1);

namespace Modules\System\Generated\Mimetype;

final class Mimetype extends \App\Ipf\Eloquent\IpfModel
{
    public const RESOURCE = 'system.mimetype';

    /** without the table prefix: the `icms` connection adds it */
    protected $table = 'system_mimetype';

    protected $primaryKey = 'mimetypeid';

    /** @var array<string, string> */
    protected $casts = [
        'mimetypeid' => 'integer',
        'dirname' => 'App\\Ipf\\Eloquent\\Casts\\PipeList',
    ];
}
