<?php

declare(strict_types=1);

namespace App\Module;

use App\Ipf\Http\LegacyOperationsController;
use App\Ipf\Resource\ResourceDescriptor;
use App\Ipf\Resource\ResourceRegistration;
use App\Ipf\Resource\ResourceRegistry;
use App\Takeover\Takeover;
use App\Takeover\TakeoverRegistry;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * The Laravel side of one ImpressCMS module, in the host's trust path:
 * `app-modules/<dirname>/`, namespace `Modules\<Dirname>\`, next to the
 * module's legacy code in the web root, which it does not change. (The trust
 * path's `modules/` belongs to legacy: Protector and duplicatable modules.)
 *
 * A module directory may hold:
 *
 * - `<Dirname>ModuleServiceProvider.php`: a subclass of this provider
 * - `resources.php`: the IPF resources compiled into Laravel artifacts,
 *   `item => ['handler' => 'module:<dirname>/<item>', 'middleware' => [...]]`
 * - `config.php`: the module's switches, merged as `icms_modules.<dirname>`
 * - `Generated/<Item>/`: the compiler's output, never edited
 * - `routes.php`, `views/`, `migrations/`: loaded when present
 *
 * Everything is registered whether or not the module is active: routes stay
 * cacheable. Requests check the module's state (App\Module\ModuleRegistry):
 * takeovers of an inactive module fall through to legacy, and its routes,
 * behind the `icms.module:<dirname>` middleware, answer 404.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    abstract public function dirname(): string;

    public function register(): void
    {
        $config = "{$this->path()}/config.php";
        if (is_file($config)) {
            $this->mergeConfigFrom($config, "icms_modules.{$this->dirname()}");
        }

        $this->app->afterResolving(ResourceRegistry::class, function (ResourceRegistry $registry): void {
            foreach ($this->registrations() as $registration) {
                $registry->add($registration);
            }
        });

        $this->app->afterResolving(TakeoverRegistry::class, function (TakeoverRegistry $takeovers): void {
            foreach ($this->takeovers() as $takeover) {
                $takeovers->add($takeover);
            }
        });
    }

    public function boot(): void
    {
        $path = $this->path();
        if (is_dir("{$path}/views")) {
            $this->loadViewsFrom("{$path}/views", $this->dirname());
        }

        if (is_file("{$path}/routes.php")) {
            $this->loadRoutesFrom("{$path}/routes.php");
        }

        if (is_dir("{$path}/migrations")) {
            $this->loadMigrationsFrom("{$path}/migrations");
        }

        foreach ($this->registrations() as $registration) {
            $model = $registration->generatedClass();
            $policy = $registration->generatedClass('Policy');
            if (class_exists($model) && class_exists($policy)) {
                Gate::policy($model, $policy);
            }
        }
    }

    /**
     * The module's IPF resources, with the switches of its configuration.
     *
     * @return list<ResourceRegistration>
     */
    public function registrations(): array
    {
        $file = "{$this->path()}/resources.php";
        if (! is_file($file)) {
            return [];
        }

        $registrations = [];
        foreach ((array) require $file as $item => $resource) {
            $registration = new ResourceRegistration(
                $this->dirname(),
                (string) $item,
                (string) $resource['handler'],
                $this->moduleNamespace(),
                $this->path(),
                controller: $resource['controller'] ?? null,
                middleware: array_values((array) ($resource['middleware'] ?? [])),
            );
            $switches = (array) config("icms_modules.{$this->dirname()}.resources.{$item}", []);
            $registrations[] = $registration->withSwitches(array_map('strval', $switches));
        }

        return $registrations;
    }

    /**
     * A takeover of the legacy admin URL of each resource that declares one
     * (Icms\Ipf\Metadata\LegacyOperations), served by the resource's
     * controller through LegacyOperationsController.
     *
     * @return list<Takeover>
     */
    protected function takeovers(): array
    {
        $takeovers = [];
        foreach ($this->registrations() as $registration) {
            /** @var class-string<ResourceDescriptor> $descriptor */
            $descriptor = $registration->generatedClass('Descriptor');
            if ($registration->controller === null || ! class_exists($descriptor) || $descriptor::legacy() === null) {
                continue;
            }

            $legacy = $descriptor::legacy();
            $takeovers[] = new Takeover(
                key: $registration->key(),
                path: $legacy['script'],
                methods: ['GET', 'POST'],
                input: $legacy['match'],
                action: [LegacyOperationsController::class, 'handle'],
                parameters: ['resource' => $registration->key()],
                middleware: ["icms.module:{$this->dirname()}", ...$registration->middleware],
                module: $this->dirname(),
            );
        }

        return $takeovers;
    }

    protected function path(): string
    {
        return dirname((string) (new ReflectionClass($this))->getFileName());
    }

    protected function moduleNamespace(): string
    {
        return (new ReflectionClass($this))->getNamespaceName();
    }
}
