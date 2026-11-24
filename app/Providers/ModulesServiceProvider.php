<?php

declare(strict_types=1);

namespace App\Providers;

use App\Ipf\Resource\ResourceRegistry;
use App\Module\ModuleRegistry;
use App\Takeover\TakeoverRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the Laravel side of the ImpressCMS modules found in the host's
 * `app-modules/` directory: `app-modules/<dirname>/<Dirname>ModuleServiceProvider.php`
 * (App\Module\ModuleServiceProvider). A module without one stays purely
 * legacy.
 */
final class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(ResourceRegistry::class);
        $this->app->singleton(TakeoverRegistry::class);

        foreach (self::discover($this->app->basePath('app-modules')) as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * @return list<class-string<\App\Module\ModuleServiceProvider>>
     */
    public static function discover(string $directory): array
    {
        $providers = [];
        foreach (glob("{$directory}/*/*ModuleServiceProvider.php") ?: [] as $file) {
            $dirname = basename(dirname($file));
            $class = 'Modules\\' . ucfirst($dirname) . '\\' . basename($file, '.php');
            if (class_exists($class)) {
                $providers[] = $class;
            }
        }

        sort($providers);

        return $providers;
    }
}
