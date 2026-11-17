<?php

declare(strict_types=1);

namespace App\Ipf\Metadata;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * The description of any object, by module and item name (design D2).
 *
 * Descriptions are kept for the request. Forms keep reading the object
 * itself, because an object may change its variables per instance
 * (hideFieldFromForm(), setControl()); the kept description serves lists and
 * the API.
 */
final class DefinitionRegistry
{
    /** @var array<string, EntityDefinition> */
    private array $definitions = [];

    /**
     * @param iterable<DefinitionProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('icms.ipf.definition_provider')]
        private readonly iterable $providers,
    ) {
    }

    /**
     * @throws UnknownObjectException when no source knows the object
     */
    public function get(string $module, string $item): EntityDefinition
    {
        $definition = $this->find($module, $item);
        if ($definition === null) {
            throw new UnknownObjectException($module, $item);
        }

        return $definition;
    }

    public function find(string $module, string $item): ?EntityDefinition
    {
        $name = "{$module}/{$item}";
        if (isset($this->definitions[$name])) {
            return $this->definitions[$name];
        }

        foreach ($this->providers as $provider) {
            $definition = $provider->definition($module, $item);
            if ($definition === null) {
                continue;
            }

            return $this->definitions[$name] = $definition;
        }

        return null;
    }

    /**
     * The description of the object an IPF handler manages.
     */
    public function forHandler(object $handler): EntityDefinition
    {
        $definition = LegacyDefinitionProvider::fromHandler($handler);

        return $this->definitions[$definition->name()] ??= $definition;
    }
}
