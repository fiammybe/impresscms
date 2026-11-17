<?php

declare(strict_types=1);

namespace App\Ipf\Metadata;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * A source of object descriptions (design D2): IPF entities, ORM-mapped
 * classes, and whatever declaration style comes later.
 */
#[AutoconfigureTag('icms.ipf.definition_provider')]
interface DefinitionProviderInterface
{
    /**
     * The description of an object, or null when this source does not know it.
     *
     * @param string $module the module directory, for example "system"
     * @param string $item   the item name, for example "mimetype"
     */
    public function definition(string $module, string $item): ?EntityDefinition;
}
