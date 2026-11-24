<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Http\Controllers\LegacyScriptController;
use App\Http\Controllers\Test\ProbeTakeoverController;
use App\LegacyBridge\LegacyExecutionResponse;
use App\Routing\RouteOwnership;
use App\Takeover\Takeover;
use App\Takeover\TakeoverRegistry;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;

/**
 * Takeovers of legacy URLs are switched off by ICMS_TAKEOVERS_DISABLED, read
 * through config('icms.takeovers.disabled') (design L26), and a takeover can
 * match a parameter that only the POST body carries (Takeover `input`).
 */
#[Group('spec:request-ownership')]
final class TakeoverSwitchTest extends TestCase
{
    private const URL = '/modules/system/admin.php?fct=users';

    /**
     * @scenario Takeover enabled
     */
    public function testEnabledTakeoverIsServedByLaravel(): void
    {
        $response = $this->get(self::URL);

        self::assertNotInstanceOf(LegacyExecutionResponse::class, $response->baseResponse);
        self::assertSame('host: system users', $response->getContent());
    }

    /**
     * @scenario Takeover disabled
     */
    public function testDisabledTakeoverFallsBackToLegacy(): void
    {
        config(['icms.takeovers.disabled' => ['other.page', 'test.users']]);

        $response = $this->get(self::URL)->baseResponse;

        self::assertInstanceOf(LegacyExecutionResponse::class, $response);
        self::assertSame('/modules/system/admin.php', $response->resolution->scriptName);
    }

    public function testOwnershipCommandNamesTheDisabledTakeover(): void
    {
        config(['icms.takeovers.disabled' => ['test.users']]);

        self::assertSame(0, Artisan::call('icms:routes:ownership', ['url' => self::URL]));
        self::assertStringContainsString('takeover test.users disabled', Artisan::output());
    }

    /**
     * admin.php extracts `fct` from the POST body over the query string.
     */
    public function testInputParameterMatchesTheBodyOrElseTheQuery(): void
    {
        $this->takeover(new Takeover('test.input', '/_test/input.php', ['POST'], ProbeTakeoverController::class, input: ['fct' => 'mimetype']));

        self::assertSame('host: probe', $this->post('/_test/input.php', ['fct' => 'mimetype'])->getContent());
        self::assertSame('host: probe', $this->post('/_test/input.php?fct=mimetype')->getContent());
        self::assertSame('host: probe', $this->post('/_test/input.php?fct=other', ['fct' => 'mimetype'])->getContent());
        self::assertInstanceOf(LegacyExecutionResponse::class, $this->post('/_test/input.php?fct=mimetype', ['fct' => 'other'])->baseResponse);
        self::assertInstanceOf(LegacyExecutionResponse::class, $this->post('/_test/input.php')->baseResponse);
    }

    /**
     * Several takeovers of one URL are one route; the registry picks the variant.
     */
    public function testTakeoversOfTheSameUrlAreAllKept(): void
    {
        $this->takeover(new Takeover('test.probe', '/modules/system/admin.php', ['GET'], ProbeTakeoverController::class, query: ['fct' => 'probe']));

        self::assertSame('host: probe', $this->get('/modules/system/admin.php?fct=probe')->getContent());
        self::assertSame('host: system users', $this->get(self::URL)->getContent());
        self::assertInstanceOf(LegacyExecutionResponse::class, $this->get('/modules/system/admin.php?fct=preferences')->baseResponse);
    }

    /**
     * A takeover added after boot needs its path routed, as routes/web.php does.
     */
    private function takeover(Takeover $takeover): void
    {
        $this->app->make(TakeoverRegistry::class)->add($takeover);
        Route::match($takeover->methods, $takeover->path, LegacyScriptController::class)
            ->name(RouteOwnership::SCRIPT_ROUTE_PREFIX . $takeover->path);
        $this->app->make('router')->getRoutes()->refreshNameLookups();
    }

    public function testDisabledKeysAreTrimmed(): void
    {
        $config = require dirname(__DIR__, 2) . '/config/icms.php';

        self::assertIsArray($config['takeovers']['disabled']);
        putenv('ICMS_TAKEOVERS_DISABLED= a , b ,');

        try {
            $reloaded = require dirname(__DIR__, 2) . '/config/icms.php';
        } finally {
            putenv('ICMS_TAKEOVERS_DISABLED');
        }

        self::assertSame(['a', 'b'], $reloaded['takeovers']['disabled']);
    }
}
