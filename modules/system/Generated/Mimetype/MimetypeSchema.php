<?php

/*
 * GENERATED FILE - DO NOT EDIT.
 *
 * Generator: php artisan icms:definitions:compile
 * Resource:  system.mimetype
 * Source:    SystemMimetypeHandler (modules/system/admin/mimetype/class/mimetype.php:110)
 *
 * The columns the IPF definition needs, as Icms\Db\Legacy\Updater types them.
 * Change the IPF definition and run `php artisan icms:definitions:compile`;
 * `php artisan icms:definitions:check` fails when this file differs from it.
 */

declare(strict_types=1);

namespace Modules\System\Generated\Mimetype;

final class MimetypeSchema extends \App\Ipf\Schema\SchemaContract
{
    public const DATA = [
        'table' => 'system_mimetype',
        'columns' => [
            'mimetypeid' => [
                'family' => 'int',
                'nullable' => false,
                'default' => null,
                'autoIncrement' => true,
            ],
            'extension' => [
                'family' => 'varchar',
                'nullable' => false,
                'default' => '',
                'autoIncrement' => false,
            ],
            'types' => [
                'family' => 'text',
                'nullable' => false,
                'default' => null,
                'autoIncrement' => false,
            ],
            'name' => [
                'family' => 'varchar',
                'nullable' => false,
                'default' => '',
                'autoIncrement' => false,
            ],
            'dirname' => [
                'family' => 'text',
                'nullable' => false,
                'default' => null,
                'autoIncrement' => false,
            ],
        ],
        'primaryKey' => [
            'mimetypeid',
        ],
        'relations' => [],
    ];
}
