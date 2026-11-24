<?php

declare(strict_types=1);

namespace App\Providers;

use App\LegacyBridge\LegacyBridge;
use App\LegacyBridge\LegacyRequestHydrator;
use App\LegacyBridge\LegacyScriptResolver;
use App\Testing\TestServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * The legacy bridge (design L4, L5). Routing needs no extension: takeovers of
 * legacy URLs are decided by App\Takeover\TakeoverRegistry behind ordinary,
 * cacheable routes (routes/web.php).
 */
final class HostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LegacyScriptResolver::class, static fn (Application $app): LegacyScriptResolver => new LegacyScriptResolver(
            (string) config('icms.legacy.root'),
            (string) config('icms.legacy.front_controller'),
            config('icms.legacy.asset_extensions'),
            config('icms.legacy.non_public_paths'),
            config('icms.legacy.script_free_paths'),
        ));

        $this->app->singleton(LegacyBridge::class, static fn (Application $app): LegacyBridge => new LegacyBridge(
            $app->make(LegacyScriptResolver::class),
            new LegacyRequestHydrator(),
            config('icms.legacy.mime_types'),
            (bool) config('app.debug'),
            $app->make(LoggerInterface::class),
            $app->basePath('host.php'),
        ));

        if ($this->app->environment('test')) {
            $this->app->register(TestServiceProvider::class);
        }
    }
}
