<?php

declare(strict_types=1);

namespace App\Testing;

use App\Http\Controllers\Test\TakeoverNotSuppressed;
use App\Http\Controllers\Test\UsersTakeoverController;
use App\Takeover\Takeover;
use App\Takeover\TakeoverRegistry;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Takeovers and listeners that exist only in the `test` environment;
 * registered by HostServiceProvider.
 */
final class TestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // one variant of admin.php, switched off by the header X-Icms-Test-Takeover: off
        $this->app->afterResolving(TakeoverRegistry::class, static function (TakeoverRegistry $takeovers): void {
            $takeovers->add(new Takeover(
                key: 'test.users',
                path: '/modules/system/admin.php',
                methods: ['GET'],
                action: UsersTakeoverController::class,
                query: ['fct' => 'users'],
                conditions: [TakeoverNotSuppressed::class],
            ));
        });
    }

    public function boot(): void
    {
        Event::listen('icms.icms.loadService-db', [LoadServiceDbRecorder::class, 'handle']);
        Event::listen(RequestHandled::class, [HostMarker::class, 'handle']);
    }
}
