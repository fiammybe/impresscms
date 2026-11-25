<?php

declare(strict_types=1);

use App\Http\Controllers\Test\IdentityTestController;
use App\Http\Controllers\Test\LegacyConfigurationController;
use App\Http\Controllers\Test\LegacyHandlerController;
use App\Http\Controllers\Test\SystemAdminAccessController;
use Illuminate\Support\Facades\Route;

/*
 * Test-only routes, registered when APP_ENV is `test`. The test takeover of
 * /modules/system/admin.php?fct=users is App\Testing\TestServiceProvider's.
 */

Route::get('/_test/legacy/sitename', LegacyConfigurationController::class)
    ->middleware('icms.legacy:siteState')
    ->name('test.legacy.sitename');

Route::get('/_test/legacy/handler', LegacyHandlerController::class)
    ->middleware('icms.legacy:module')
    ->name('test.legacy.handler');

Route::get('/_test/system-admin/{fct}', SystemAdminAccessController::class)
    ->middleware('icms.legacy:siteState')
    ->name('test.system_admin_access');

Route::middleware('icms')->controller(IdentityTestController::class)->group(static function (): void {
    Route::get('/_test/identity', 'identity')->name('test.identity');
    Route::get('/_test/members-only', 'membersOnly')->middleware('auth')->name('test.members_only');
    Route::get('/_test/module-admin', 'moduleAdmin')->middleware('can:icms_perm:module_admin')->name('test.module_admin');
    Route::get('/_test/module-read', 'moduleRead')->middleware('can:icms_perm:module_read')->name('test.module_read');
    Route::post('/_test/token', 'token')->middleware('icms.token')->name('test.token');
});
