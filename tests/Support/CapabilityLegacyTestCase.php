<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Legacy\LegacyRuntime;
use Icms\Capability\Registry;

/**
 * In-process tests of the capability framework against the test site with
 * the capability fixture modules installed (see CapabilitySite). Legacy is
 * booted to the `online` stage as a same-site POST, so the legacy database
 * layer writes. Each concrete class declares #[RunTestsInSeparateProcesses];
 * the modules are installed once per class, in the parent process.
 */
abstract class CapabilityLegacyTestCase extends LegacyWebTestCase
{
    /** @var list<string> */
    private array $queries = [];

    public static function setUpBeforeClass(): void
    {
        if (!Isolation::inChildProcess()) {
            CapabilitySite::copyModules();
        }

        parent::setUpBeforeClass();

        if (!Isolation::inChildProcess()) {
            CapabilitySite::install(...static::modules());
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (!Isolation::inChildProcess()) {
            CapabilitySite::removeModules();
        }
    }

    /** @return list<string> the fixture modules this class installs */
    protected static function modules(): array
    {
        return CapabilitySite::MODULES;
    }

    protected function setUp(): void
    {
        parent::setUp();
        // the legacy database layer only writes during a same-site POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1/';
        self::bootKernel();
        static::getContainer()->get(LegacyRuntime::class)->bootTo('online');
        \Icms\Event::attach('icms_db_IConnection', 'execute', function ($parameters): void {
            $this->queries[] = (string) ($parameters['sql'] ?? '');
        });
    }

    /** Read the registry state again after another process changed it. */
    protected static function reload(): Registry
    {
        $registry = Registry::instance();
        $registry->reload();

        return $registry;
    }

    /**
     * The SQL statements the callback runs.
     *
     * @return list<string>
     */
    protected function queriesOf(callable $callback): array
    {
        $this->queries = [];
        $callback();

        return $this->queries;
    }

    /** Store an article through its handler and return it. */
    protected static function article(string $title): \icms_ipf_Object
    {
        $handler = icms_getModuleHandler('article', 'news');
        $article = $handler->create();
        $article->setVar('title', $title);
        self::assertTrue($handler->insert($article, true), implode(', ', $article->getErrors()));

        return $article;
    }
}
