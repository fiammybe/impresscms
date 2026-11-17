<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Keeps the ORM from opening the database while the container is built.
 *
 * The ORM's cache warmers and its validator loader create the entity manager,
 * and with it the DBAL connection, whose settings come from mainfile.php: that
 * boots ImpressCMS in whatever request happens to rebuild the container, and
 * fails outright where no site is installed (the installer, a build). Proxies
 * and metadata are built on first use instead.
 */
final class DoctrineWarmupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach (array_keys($container->findTaggedServiceIds('kernel.cache_warmer')) as $id) {
            if (!str_starts_with($id, 'doctrine.orm.')) {
                continue;
            }

            $container->getDefinition($id)->clearTag('kernel.cache_warmer');
        }

        // the validator is warmed up at boot with every auto-mapping loader,
        // and the ORM's takes the entity manager: object rules come from the
        // object's description, not from column definitions
        foreach (array_keys($container->findTaggedServiceIds('validator.auto_mapper')) as $id) {
            if (!str_starts_with($id, 'doctrine.orm.')) {
                continue;
            }

            $container->getDefinition($id)->clearTag('validator.auto_mapper');
        }
    }
}
