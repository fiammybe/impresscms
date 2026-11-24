<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Http\Controllers\Test\ProbeTakeoverController;
use App\Http\Controllers\Test\TakeoverNotSuppressed;
use App\Takeover\Takeover;
use App\Takeover\TakeoverRegistry;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Group;

/**
 * The ownership decision for legacy script URLs, without Laravel's route
 * matcher: parameters of the query string or the POST body, methods,
 * conditions, the switch and the module state.
 */
#[Group('spec:request-ownership')]
final class TakeoverRegistryTest extends TestCase
{
    public function testQueryParametersMustBeInTheQueryString(): void
    {
        $registry = $this->registry(new Takeover('t.query', '/admin.php', ['GET', 'POST'], ProbeTakeoverController::class, query: ['fct' => 'a']));

        self::assertSame('t.query', $registry->match(Request::create('/admin.php?fct=a'))?->key);
        self::assertSame('t.query', $registry->match(Request::create('/admin.php?fct=a', 'HEAD'))?->key, 'HEAD follows GET');
        self::assertNull($registry->match(Request::create('/admin.php', 'POST', ['fct' => 'a'])), 'not from the body');
        self::assertNull($registry->match(Request::create('/admin.php?fct=b')));
        self::assertNull($registry->match(Request::create('/other.php?fct=a')));
    }

    public function testInputParametersPreferTheBody(): void
    {
        $registry = $this->registry(new Takeover('t.input', '/admin.php', ['GET', 'POST'], ProbeTakeoverController::class, input: ['fct' => 'a']));

        self::assertNotNull($registry->match(Request::create('/admin.php?fct=a')));
        self::assertNotNull($registry->match(Request::create('/admin.php', 'POST', ['fct' => 'a'])));
        self::assertNotNull($registry->match(Request::create('/admin.php?fct=b', 'POST', ['fct' => 'a'])));
        self::assertNull($registry->match(Request::create('/admin.php?fct=a', 'POST', ['fct' => 'b'])), 'the body replaces the query string');
        self::assertNull($registry->match(Request::create('/admin.php', 'POST', ['fct' => ['a']])), 'arrays never match');
    }

    public function testMethodsConditionsAndOrder(): void
    {
        $registry = $this->registry(
            new Takeover('t.first', '/admin.php', ['GET'], ProbeTakeoverController::class, query: ['fct' => 'a'], conditions: [TakeoverNotSuppressed::class]),
            new Takeover('t.second', '/admin.php', ['GET', 'POST'], ProbeTakeoverController::class, query: ['fct' => 'a']),
        );

        self::assertSame('t.first', $registry->match(Request::create('/admin.php?fct=a'))?->key);
        $suppressed = Request::create('/admin.php?fct=a');
        $suppressed->headers->set('X-Icms-Test-Takeover', 'off');
        self::assertSame('t.second', $registry->match($suppressed)?->key, 'a failing condition passes to the next takeover');
        self::assertSame('t.second', $registry->match(Request::create('/admin.php?fct=a', 'POST'))?->key);
        self::assertNull($registry->match(Request::create('/admin.php?fct=a', 'PUT')));
        self::assertSame(['/admin.php' => ['GET', 'POST']], $registry->paths());
    }

    public function testSwitchedOffTakeoversAndModulesFallThrough(): void
    {
        $registry = $this->registry(
            new Takeover('t.off', '/admin.php', ['GET'], ProbeTakeoverController::class, query: ['fct' => 'a']),
            new Takeover('t.module', '/admin.php', ['GET'], ProbeTakeoverController::class, query: ['fct' => 'b'], module: 'somemodule'),
        );

        config(['icms.takeovers.disabled' => ['t.off'], 'icms.modules.laravel_disabled' => ['somemodule']]);

        self::assertNull($registry->match(Request::create('/admin.php?fct=a')));
        self::assertSame('t.off', $registry->matchIgnoringSwitches(Request::create('/admin.php?fct=a'))?->key);
        self::assertNull($registry->match(Request::create('/admin.php?fct=b')), 'a switched-off module owns nothing');
    }

    private function registry(Takeover ...$takeovers): TakeoverRegistry
    {
        $registry = $this->app->make(TakeoverRegistry::class);
        $fresh = new TakeoverRegistry($this->app, $this->app->make(\App\Module\ModuleRegistry::class));
        foreach ($takeovers as $takeover) {
            $fresh->add($takeover);
        }

        self::assertNotSame($registry, $fresh);

        return $fresh;
    }
}
