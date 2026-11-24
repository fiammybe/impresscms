<?php

declare(strict_types=1);

namespace App\Ipf\Http;

use App\Ipf\Resource\IpfResource;
use App\Ipf\Resource\IpfResourceFactory;
use App\Ipf\Resource\ResourceRegistration;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base of the generated <Item>Request classes: validates the payload of the
 * legacy IPF form of a resource. It validates and normalizes only; storing is
 * the repository's.
 *
 * - Input is the top level of the POST body under the legacy field names
 *   (`extension`, `dirname[]`, the permission names); the query string and
 *   other keys (`op`, `fct`, the token) are ignored, as
 *   Icms\Ipf\Controller::postDataToObject() reads $_POST.
 * - Rules come from the generated rules(); messages and attribute names name
 *   the field by its caption in the current language, with the legacy wording
 *   (_XOBJ_ERR_REQUIRED, _XOBJ_ERR_SHORTERTHAN).
 * - A failure throws LegacyFormRejected, which answers as the legacy save does.
 * - With the resource's validation switched to legacy, only the submitted
 *   keys are kept, unvalidated: the handler's cleanVars() checks them.
 */
abstract class IpfFormRequest extends FormRequest
{
    public const RESOURCE = '';

    /**
     * @return array<string, list<string>>
     */
    abstract public function rules(): array;

    /**
     * @return array<string, list<string|int>> message key => [language constant or sprintf format, field, …arguments]
     */
    abstract protected function messageTemplates(): array;

    public function authorize(): bool
    {
        // the controller authorizes with the resource's policy
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        $fields = array_filter(array_keys($this->rules()), static fn (string $key): bool => ! str_contains($key, '.'));

        return array_intersect_key($this->request->all(), array_flip($fields));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $resource = $this->resource();
        $messages = [];
        foreach ($this->messageTemplates() as $key => [$template, $field]) {
            $arguments = array_slice($this->messageTemplates()[$key], 2);
            $format = str_starts_with((string) $template, '_') ? $resource->text((string) $template) : (string) $template;
            $messages[$key] = sprintf($format, $resource->caption((string) $field), ...$arguments);
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $resource = $this->resource();
        $attributes = [];
        foreach (array_keys($this->rules()) as $key) {
            $attributes[$key] = $resource->caption(explode('.', $key)[0]);
        }

        return $attributes;
    }

    /**
     * The validated values of the entity's fields, permissions excluded.
     *
     * @return array<string, mixed>
     */
    public function values(): array
    {
        $descriptor = $this->resource()->descriptor;

        return array_filter($this->validated(), static fn (string $key): bool => ! $descriptor::isPermission($key), ARRAY_FILTER_USE_KEY);
    }

    /**
     * The selected groups of every permission of the handler; none for a
     * permission the browser did not send, as in legacy.
     *
     * @return array<string, list<int>>
     */
    public function permissionGroups(): array
    {
        $validated = $this->validated();
        $groups = [];
        foreach ($this->resource()->descriptor::permissions() as $permission) {
            $value = $validated[$permission] ?? [];
            $groups[$permission] = array_values(array_map('intval', is_array($value) ? $value : [$value]));
        }

        return $groups;
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationRules(): array
    {
        $rules = $this->rules();
        if ($this->resource()->registration->validation() !== ResourceRegistration::VALIDATION_LEGACY) {
            return $rules;
        }

        $kept = [];
        foreach (array_keys($rules) as $key) {
            if (! str_contains($key, '.')) {
                $kept[$key] = ['sometimes'];
            }
        }

        return $kept;
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new LegacyFormRejected(array_values(array_unique($validator->errors()->all())));
    }

    protected function resource(): IpfResource
    {
        return $this->container->make(IpfResourceFactory::class)->get(static::RESOURCE);
    }
}
