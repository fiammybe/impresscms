<?php

/*
 * GENERATED FILE - DO NOT EDIT.
 *
 * Generator: php artisan icms:definitions:compile
 * Resource:  system.mimetype
 * Source:    SystemMimetypeHandler (modules/system/admin/mimetype/class/mimetype.php:110)
 *
 * The definition of the resource as data: fields, form, list, legacy URL and support.
 * Change the IPF definition and run `php artisan icms:definitions:compile`;
 * `php artisan icms:definitions:check` fails when this file differs from it.
 */

declare(strict_types=1);

namespace Modules\System\Generated\Mimetype;

final class MimetypeDescriptor extends \App\Ipf\Resource\ResourceDescriptor
{
    public const DATA = [
        'resource' => 'system.mimetype',
        'module' => 'system',
        'item' => 'mimetype',
        'entity' => 'SystemMimetype',
        'handler' => 'module:system/mimetype',
        'table' => 'system_mimetype',
        'primaryKey' => 'mimetypeid',
        'identifier' => 'mimetypeid',
        'summary' => 'name',
        'fields' => [
            'mimetypeid' => [
                'type' => 'INT',
                'control' => 'hidden',
                'multiple' => false,
                'options' => null,
                'settings' => [],
                'required' => true,
                'maxLength' => null,
                'default' => null,
                'persistent' => true,
                'readonly' => false,
                'hidden' => true,
                'key' => true,
                'sortable' => false,
                'multilingual' => false,
                'valueOptions' => null,
            ],
            'extension' => [
                'type' => 'TXTBOX',
                'control' => 'text',
                'multiple' => false,
                'options' => null,
                'settings' => [],
                'required' => true,
                'maxLength' => null,
                'default' => null,
                'persistent' => true,
                'readonly' => false,
                'hidden' => false,
                'key' => false,
                'sortable' => false,
                'multilingual' => false,
                'valueOptions' => null,
            ],
            'types' => [
                'type' => 'TXTAREA',
                'control' => 'textarea',
                'multiple' => false,
                'options' => null,
                'settings' => [],
                'required' => true,
                'maxLength' => null,
                'default' => null,
                'persistent' => true,
                'readonly' => false,
                'hidden' => false,
                'key' => false,
                'sortable' => false,
                'multilingual' => false,
                'valueOptions' => null,
            ],
            'name' => [
                'type' => 'TXTBOX',
                'control' => 'text',
                'multiple' => false,
                'options' => null,
                'settings' => [],
                'required' => true,
                'maxLength' => null,
                'default' => null,
                'persistent' => true,
                'readonly' => false,
                'hidden' => false,
                'key' => false,
                'sortable' => false,
                'multilingual' => false,
                'valueOptions' => null,
            ],
            'dirname' => [
                'type' => 'SIMPLE_ARRAY',
                'control' => 'selectmulti',
                'multiple' => true,
                'options' => [
                    'kind' => 'itemHandler',
                    'handler' => 'icms_module',
                    'method' => 'getActive',
                ],
                'settings' => [],
                'required' => true,
                'maxLength' => null,
                'default' => null,
                'persistent' => true,
                'readonly' => false,
                'hidden' => false,
                'key' => false,
                'sortable' => false,
                'multilingual' => false,
                'valueOptions' => null,
            ],
        ],
        'form' => [
            'mimetypeid',
            'extension',
            'types',
            'name',
            'dirname',
            'use_extension',
        ],
        'permissions' => [
            'use_extension',
        ],
        'list' => [
            'columns' => [
                [
                    'field' => 'name',
                    'align' => 'left',
                    'width' => 150,
                    'sortable' => true,
                ],
                [
                    'field' => 'extension',
                    'align' => 'left',
                    'width' => 150,
                    'sortable' => true,
                ],
                [
                    'field' => 'types',
                    'align' => 'left',
                    'width' => null,
                    'sortable' => true,
                ],
            ],
            'quickSearch' => [
                'name',
                'extension',
                'types',
            ],
            'filters' => [],
            'actions' => [
                'edit',
                'delete',
            ],
            'createButton' => true,
            'sortBy' => 'mimetypeid',
            'order' => 'ASC',
            'limit' => 15,
        ],
        'legacy' => [
            'script' => '/modules/system/admin.php',
            'match' => [
                'fct' => 'mimetype',
            ],
            'form' => 'mod',
            'save' => 'addmimetype',
            'delete' => 'del',
            'confirm' => 'confirm',
        ],
        'relations' => [],
        'notifications' => [],
        'support' => [
            'validation' => true,
            'blade' => true,
            'unsupported' => [
                'validation' => [],
                'blade' => [],
            ],
        ],
    ];
}
