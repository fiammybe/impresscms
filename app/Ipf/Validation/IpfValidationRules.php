<?php

declare(strict_types=1);

namespace App\Ipf\Validation;

use App\Ipf\Resource\IpfResourceFactory;
use Illuminate\Contracts\Container\Container;

/**
 * Validator rules of the generated FormRequests, for choices only the running
 * legacy definition knows:
 *
 * - `ipf_choice:<resource>,<field>`: one of the choices of the field's option
 *   provider (a handler method, an item handler, literal options)
 * - `ipf_group`: an existing group id (handler permissions)
 */
final class IpfValidationRules
{
    /** @var array<string, list<string>> */
    private array $choices = [];

    /** @var list<string>|null */
    private ?array $groups = null;

    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @param list<string> $parameters
     */
    public function choice(string $attribute, mixed $value, array $parameters): bool
    {
        [$resource, $field] = $parameters + ['', ''];
        $key = "{$resource}.{$field}";
        $this->choices[$key] ??= array_map('strval', array_keys($this->factory()->get($resource)->legacy->options($field)));

        return is_scalar($value) && in_array((string) $value, $this->choices[$key], true);
    }

    /**
     * @param list<string> $parameters
     */
    public function group(string $attribute, mixed $value, array $parameters): bool
    {
        if ($this->groups === null) {
            $this->container->make(\App\Legacy\LegacyRuntime::class)->bootTo('module');
            $groups = (array) $this->container->make('icms.handlers')->get('icms_member')->getGroupList();
            $this->groups = array_map('strval', array_keys($groups));
        }

        return is_scalar($value) && in_array((string) $value, $this->groups, true);
    }

    private function factory(): IpfResourceFactory
    {
        return $this->container->make(IpfResourceFactory::class);
    }
}
