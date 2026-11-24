<?php

declare(strict_types=1);

namespace App\Ipf\Resource;

use App\Legacy\HandlerLocator;
use App\Legacy\LegacyRuntime;

/**
 * The live legacy side of an IPF resource: its handler, a fresh entity, and
 * what only the running legacy definition knows — captions and descriptions
 * in the current language, the choices of dynamic option providers, the
 * groups and the module's default permissions.
 *
 * Adapter between the Laravel side and legacy code; controllers use it only
 * through App\Ipf\Resource\IpfResource.
 */
final class LegacyResource
{
    private ?object $template = null;

    /**
     * @param class-string<ResourceDescriptor> $descriptor
     * @param object|null $handler the handler to use instead of the registered one (tests)
     */
    public function __construct(
        public readonly ResourceRegistration $registration,
        public readonly string $descriptor,
        private readonly HandlerLocator $handlers,
        private readonly LegacyRuntime $runtime,
        private readonly ?object $handler = null,
    ) {
    }

    public function handler(): object
    {
        $this->runtime->bootTo('module');

        return $this->handler ?? $this->handlers->get($this->registration->handler);
    }

    /**
     * An entity the handler creates, to read the definition from; never stored.
     */
    public function template(): object
    {
        return $this->template ??= $this->handler()->create();
    }

    /**
     * The caption of a field or permission, as plain text; the name when it has none.
     */
    public function caption(string $name): string
    {
        $caption = $this->descriptor::isPermission($name)
            ? (string) ($this->permission($name)['caption'] ?? '')
            : (string) ($this->template()->vars[$name]['form_caption'] ?? '');
        $caption = trim(html_entity_decode(strip_tags($caption), ENT_QUOTES, 'UTF-8'));

        return $caption !== '' ? $caption : $name;
    }

    public function description(string $name): ?string
    {
        $description = $this->descriptor::isPermission($name)
            ? (string) ($this->permission($name)['description'] ?? '')
            : (string) ($this->template()->vars[$name]['form_dsc'] ?? '');

        return $description !== '' ? $description : null;
    }

    /**
     * The choices of a field's control, in the order of the legacy select
     * (Icms\Ipf\Form\Elements\Select), from the provider in the descriptor.
     *
     * @return array<int|string, string> value => label
     */
    public function options(string $field): array
    {
        $provider = $this->descriptor::field($field)['options'] ?? null;
        if (! is_array($provider)) {
            return [];
        }

        $options = match ($provider['kind']) {
            'literal' => (array) ($provider['options'] ?? []),
            'object' => $this->call($this->template()->controls[$field]['object'] ?? null, (string) ($provider['method'] ?? ''), []),
            'handler' => $this->call($this->handler(), (string) ($provider['method'] ?? ''), (array) ($provider['params'] ?? [])),
            'itemHandler' => $this->call($this->itemHandler($provider), (string) ($provider['method'] ?? ''), (array) ($provider['params'] ?? [])),
            default => throw new \LogicException("Unknown option provider \"{$provider['kind']}\"."),
        };

        return array_map(static fn (mixed $label): string => is_scalar($label) ? (string) $label : '', $options);
    }

    /**
     * A stored value as the legacy form shows it for editing (getVar($key, 'e')):
     * text areas without the marks of the HTML input filter, which saving adds
     * again. Plain text: the view escapes it.
     */
    public function editValue(string $field, mixed $value): mixed
    {
        $type = $this->descriptor::field($field)['type'];
        if (! is_string($value) || ! in_array($type, ['TXTAREA', 'SOURCE'], true)) {
            return $value;
        }

        $this->template();
        $edit = (string) \Icms\Core\DataFilter::checkVar($value, 'html', 'edit');

        return html_entity_decode($edit, ENT_QUOTES, 'UTF-8');
    }

    /**
     * All groups by id, sorted by name (Icms\Ipf\Form\Base::createPermissionControls()).
     *
     * @return array<int, string>
     */
    public function groups(): array
    {
        $this->runtime->bootTo('module');
        $groups = (array) $this->handlers->get('icms_member')->getGroupList();
        asort($groups);

        return $groups;
    }

    /**
     * The groups a new item gets for a permission: the module setting
     * def_perm_<name>, as the legacy form preselects them.
     *
     * @return list<int>
     */
    public function defaultGroups(string $permission): array
    {
        $selected = $this->handler()->getModuleConfig()["def_perm_{$permission}"] ?? [];

        return array_values(array_map('intval', (array) ($selected ?: [])));
    }

    /**
     * A language constant of the core or the module; its name when undefined.
     */
    public function text(string $constant): string
    {
        $this->template();

        return defined($constant) ? (string) constant($constant) : $constant;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function permission(string $name): ?array
    {
        foreach ((array) $this->handler()->getPermissions() as $permission) {
            if ($permission['perm_name'] === $name) {
                return $permission;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $provider
     */
    private function itemHandler(array $provider): object
    {
        $this->runtime->bootTo('module');
        $name = (string) $provider['handler'];

        return isset($provider['module'])
            ? $this->handlers->get("module:{$provider['module']}/{$name}")
            : $this->handlers->get($name);
    }

    /**
     * @param list<mixed> $params
     * @return array<int|string, mixed>
     */
    private function call(mixed $target, string $method, array $params): array
    {
        if (! is_object($target) || $method === '' || ! method_exists($target, $method)) {
            return [];
        }

        $options = $target->{$method}(...$params);

        return is_array($options) ? $options : [];
    }
}
