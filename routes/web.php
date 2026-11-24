<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use App\Http\Controllers\LegacyBridgeController;
use App\Http\Controllers\LegacyScriptController;
use App\Http\Controllers\ReservedPrefixController;
use App\Http\Controllers\WhoamiController;
use App\Routing\RouteOwnership;
use App\Takeover\TakeoverRegistry;
use Illuminate\Support\Facades\Route;

/*
 * Laravel routes and legacy ImpressCMS share one URL space without a prefix
 * (design L4). Every route here is an ordinary route, so the table can be
 * cached (`php artisan route:cache`):
 *
 * - a legacy script URL some takeovers claim is one route to
 *   LegacyScriptController, which asks the takeover registry whether Laravel
 *   or the legacy script answers the request (query string and POST body
 *   variants, switches, module state)
 * - module routes come from modules/<dirname>/routes.php
 * - everything no route claims falls through to the legacy bridge, last
 */

Route::get('/up', HealthController::class)->name('up');

foreach (app(TakeoverRegistry::class)->paths() as $path => $methods) {
    Route::match($methods, $path, LegacyScriptController::class)
        ->name(RouteOwnership::SCRIPT_ROUTE_PREFIX . $path);
}

if (app()->environment('local', 'test')) {
    Route::get('/whoami', WhoamiController::class)
        ->middleware(['icms', 'auth', 'can:webmaster'])
        ->name('whoami');
}

if (app()->environment('test')) {
    require __DIR__ . '/test.php';
}

foreach (config('icms.legacy.reserved_prefixes') as $prefix) {
    Route::any(trim($prefix, '/') . '/{rest?}', ReservedPrefixController::class)
        ->where('rest', '.*')
        ->name(RouteOwnership::RESERVED_ROUTE_PREFIX . $prefix);
}

Route::any('{path}', LegacyBridgeController::class)
    ->where('path', '.*')
    ->fallback()
    ->name(RouteOwnership::LEGACY_ROUTE);
