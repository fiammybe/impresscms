<?php

/*
 * GENERATED FILE - DO NOT EDIT.
 *
 * Generator: php artisan icms:definitions:compile
 * Resource:  system.mimetype
 * Source:    SystemMimetypeHandler (modules/system/admin/mimetype/class/mimetype.php:110)
 *
 * Validates the posted legacy form; it neither binds nor stores.
 * Change the IPF definition and run `php artisan icms:definitions:compile`;
 * `php artisan icms:definitions:check` fails when this file differs from it.
 */

declare(strict_types=1);

namespace Modules\System\Generated\Mimetype;

final class MimetypeRequest extends \App\Ipf\Http\IpfFormRequest
{
    public const RESOURCE = 'system.mimetype';

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'extension' => [
                'sometimes',
                'required',
                'string',
            ],
            'types' => [
                'sometimes',
                'required',
                'string',
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
            ],
            'dirname' => [
                'sometimes',
                'required',
                'array',
            ],
            'dirname.*' => [
                'ipf_choice:system.mimetype,dirname',
            ],
            'use_extension' => [
                'sometimes',
                'nullable',
                'array',
            ],
            'use_extension.*' => [
                'ipf_group',
            ],
        ];
    }

    /**
     * @return array<string, list<string|int>> message key => [language constant or format, field, …arguments]
     */
    protected function messageTemplates(): array
    {
        return [
            'extension.required' => [
                '_XOBJ_ERR_REQUIRED',
                'extension',
            ],
            'extension.string' => [
                '%s: please enter text.',
                'extension',
            ],
            'types.required' => [
                '_XOBJ_ERR_REQUIRED',
                'types',
            ],
            'types.string' => [
                '%s: please enter text.',
                'types',
            ],
            'name.required' => [
                '_XOBJ_ERR_REQUIRED',
                'name',
            ],
            'name.string' => [
                '%s: please enter text.',
                'name',
            ],
            'dirname.required' => [
                '_XOBJ_ERR_REQUIRED',
                'dirname',
            ],
            'dirname.array' => [
                '%s: the selected choice is invalid.',
                'dirname',
            ],
            'dirname.*.ipf_choice' => [
                '%s: the selected choice is invalid.',
                'dirname',
            ],
            'use_extension.array' => [
                '%s: the selected choice is invalid.',
                'use_extension',
            ],
            'use_extension.*.ipf_group' => [
                '%s: the selected choice is invalid.',
                'use_extension',
            ],
        ];
    }
}
