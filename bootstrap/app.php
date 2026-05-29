<?php

declare(strict_types=1);

use App\LegacyBridge\LegacyBridge;
use App\LegacyBridge\LegacyRequestHydrator;
use App\LegacyBridge\LegacyScriptResolver;
use App\LegacyBridge\RequestOwnershipResolver;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Routing\Router;

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8', true, 500);
    }
    echo "Laravel bridge dependencies are missing. Run composer install at repository root.\n";
    exit(1);
}

require_once $autoloadPath;

$container = new Container();
Container::setInstance($container);

$config = [
    'app' => [
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    ],
    'legacy-bridge' => require dirname(__DIR__) . '/config/legacy-bridge.php',
];

$container->instance('config', $config);

$events = new Dispatcher($container);
$container->instance(Dispatcher::class, $events);
$container->instance('events', $events);

$router = new Router($events, $container);
$routeRegistrar = require dirname(__DIR__) . '/routes/web.php';
$routeRegistrar($router);
$container->instance(Router::class, $router);
$container->instance('router', $router);
$container->singleton(CallableDispatcherContract::class, static fn (Container $container): CallableDispatcher => new CallableDispatcher($container));
$container->singleton(ControllerDispatcherContract::class, static fn (Container $container): ControllerDispatcher => new ControllerDispatcher($container));

$container->singleton(LegacyScriptResolver::class, static function (Container $container): LegacyScriptResolver {
    $bridgeConfig = $container->make('config')['legacy-bridge'];

    return new LegacyScriptResolver(
        legacyRoot: $bridgeConfig['legacy_root'],
        frontController: $bridgeConfig['front_controller'],
        assetExtensions: $bridgeConfig['asset_extensions'],
    );
});

$container->singleton(LegacyRequestHydrator::class, static fn (): LegacyRequestHydrator => new LegacyRequestHydrator());

$container->singleton(RequestOwnershipResolver::class, static function (Container $container): RequestOwnershipResolver {
    $bridgeConfig = $container->make('config')['legacy-bridge'];

    return new RequestOwnershipResolver(
        router: $container->make(Router::class),
        reservedPaths: $bridgeConfig['laravel_paths'],
    );
});

$container->singleton(LegacyBridge::class, static function (Container $container): LegacyBridge {
    $bridgeConfig = $container->make('config')['legacy-bridge'];
    $appConfig = $container->make('config')['app'];

    return new LegacyBridge(
        resolver: $container->make(LegacyScriptResolver::class),
        hydrator: $container->make(LegacyRequestHydrator::class),
        legacyRoot: $bridgeConfig['legacy_root'],
        mimeTypes: $bridgeConfig['mime_types'],
        debug: (bool) ($appConfig['debug'] ?? false),
    );
});

return $container;
