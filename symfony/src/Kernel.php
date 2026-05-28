<?php

declare(strict_types=1);

namespace App;

use App\Bridge\LegacyBridge;
use App\Bridge\RequestOwnershipDecider;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        $this->boot();
        $container = $this->getContainer();

        $decider = $container->get(RequestOwnershipDecider::class);
        $ownership = $decider->decide($request);

        error_log(json_encode([
            'component' => 'migration_bridge',
            'classification' => $ownership,
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
        ], JSON_UNESCAPED_SLASHES));

        if ($ownership === RequestOwnershipDecider::OWNERSHIP_LEGACY) {
            $bridge = $container->get(LegacyBridge::class);
            return $bridge->handle($request, $this->isDebug());
        }

        return parent::handle($request, $type, $catch);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir() . '/config/packages/*.yaml', 'glob');
        $loader->load($this->getProjectDir() . '/config/services.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getProjectDir() . '/config/routes.yaml');
    }
}
