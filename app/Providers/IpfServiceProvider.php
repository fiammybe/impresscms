<?php

declare(strict_types=1);

namespace App\Providers;

use App\Ipf\Compiler\ArtifactGenerator;
use App\Ipf\Resource\IpfResourceFactory;
use App\Ipf\Validation\IpfValidationRules;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

/**
 * IPF resources on Laravel: the compiler, the per-request resources and the
 * validator rules of the generated FormRequests.
 */
final class IpfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArtifactGenerator::class);
        $this->app->singleton(IpfResourceFactory::class);
        $this->app->singleton(IpfValidationRules::class);
    }

    public function boot(): void
    {
        // <x-ipf::text>, … and the admin page views (ipf::admin.form)
        Blade::anonymousComponentPath(resource_path('views/components/ipf'), 'ipf');
        $this->loadViewsFrom(resource_path('views/ipf'), 'ipf');

        Validator::extend('ipf_choice', fn (string $attribute, mixed $value, array $parameters): bool => $this->app->make(IpfValidationRules::class)->choice($attribute, $value, $parameters));
        Validator::extend('ipf_group', fn (string $attribute, mixed $value, array $parameters): bool => $this->app->make(IpfValidationRules::class)->group($attribute, $value, $parameters));
    }
}
