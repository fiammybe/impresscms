<?php

declare(strict_types=1);

namespace App\Tests\Support;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * The web server for the Http suite.
 *
 * Normally a `php -S` server on a free local port that serves the web root
 * `htdocs/`, with the test site's mainfile (ICMS_MAINFILE) in its environment.
 * Every request goes to index.php (htdocs/index.php as router), as with the
 * configurations of docs/server/ (routing level 3, which ImpressCMS requires);
 * index.php hands it to the host in the test site's trust path.
 * notRouted() is a server that runs existing scripts itself instead.
 *
 * With ICMS_TEST_BASE_URL the suite tests an already running server instead
 * (Apache, nginx or FrankenPHP with the configuration from docs/server/).
 */
final class ServerProcess
{
    private ?Process $process = null;
    private int $port = 0;

    /**
     * @param array<string, string> $env
     * @param array<string, string> $ini php.ini settings that override the defaults
     */
    public function __construct(
        private readonly string $documentRoot,
        private readonly ?string $router = null,
        private readonly array $env = [],
        private readonly ?string $externalBaseUri = null,
        private readonly array $ini = [],
    ) {
    }

    /**
     * The test site: the server at ICMS_TEST_BASE_URL, or `php -S` on htdocs/
     * with every request routed to index.php.
     *
     * @param array<string, string> $env
     */
    public static function forSite(array $env = []): self
    {
        $root = TestSite::repoRoot();
        if (null !== $external = self::externalBaseUri()) {
            return new self($root . '/htdocs', null, [], $external);
        }

        return new self($root . '/htdocs', $root . '/htdocs/index.php', $env + ['APP_ENV' => 'test', 'APP_DEBUG' => '0']);
    }

    /**
     * The test site on a web server that is not configured for ImpressCMS:
     * `php -S` without router executes existing scripts itself and sends only
     * unknown paths to index.php.
     *
     * @param array<string, string> $env
     */
    public static function notRouted(array $env = []): self
    {
        return new self(TestSite::repoRoot() . '/htdocs', null, $env + ['APP_ENV' => 'test', 'APP_DEBUG' => '0']);
    }

    /**
     * The host with another web root (the bridge fixtures), every request
     * routed to it.
     *
     * @param array<string, string> $env for example APP_DEBUG
     */
    public static function forLegacyRoot(string $legacyRoot, array $env = []): self
    {
        return new self(
            $legacyRoot,
            TestSite::repoRoot() . '/tests/Support/legacy-root-router.php',
            ['ICMS_LEGACY_ROOT' => $legacyRoot] + $env + ['APP_ENV' => 'test', 'APP_DEBUG' => '0'],
        );
    }

    /** The external server under test (ICMS_TEST_BASE_URL), if any. */
    public static function externalBaseUri(): ?string
    {
        $url = getenv('ICMS_TEST_BASE_URL');

        return is_string($url) && $url !== '' ? rtrim($url, '/') : null;
    }

    public function start(float $timeout = 15.0): void
    {
        if ($this->externalBaseUri !== null || $this->process?->isRunning()) {
            return;
        }

        $this->port = self::freePort();
        // Production-like error handling: PHP errors go to the server log
        // (stderr), not into pages, independent of the local php.ini.
        // php -S only exposes its environment (APP_ENV, APP_DEBUG, …) to
        // Symfony through $_ENV, which php.ini-production's "GPCS" leaves empty.
        $command = [
            PHP_BINARY,
            '-d', 'variables_order=EGPCS',
            '-d', 'display_errors=0',
            '-d', 'log_errors=1',
            '-d', 'error_reporting=' . E_ALL,
        ];
        foreach ($this->ini as $name => $value) {
            array_push($command, '-d', $name . '=' . $value);
        }
        array_push($command, '-S', '127.0.0.1:' . $this->port, '-t', $this->documentRoot);
        if ($this->router !== null) {
            $command[] = $this->router;
        }

        $this->process = new Process(
            $command,
            $this->documentRoot,
            $this->env + ['ICMS_MAINFILE' => TestSite::mainfile()],
            null,
            null,
        );
        $this->process->start();

        $deadline = microtime(true) + $timeout;
        while (microtime(true) < $deadline) {
            if (!$this->process->isRunning()) {
                throw new RuntimeException("php -S exited early:\n" . $this->process->getErrorOutput() . $this->process->getOutput());
            }
            $socket = @fsockopen('127.0.0.1', $this->port, $errno, $errstr, 0.2);
            if ($socket !== false) {
                fclose($socket);

                return;
            }
            usleep(50_000);
        }

        $this->stop();
        throw new RuntimeException("php -S did not accept connections on port {$this->port} within {$timeout}s.");
    }

    public function stop(): void
    {
        if ($this->process !== null && $this->process->isRunning()) {
            $this->process->stop(2);
        }
        $this->process = null;
    }

    public function baseUri(): string
    {
        return $this->externalBaseUri ?? 'http://127.0.0.1:' . $this->port;
    }

    public function isExternal(): bool
    {
        return $this->externalBaseUri !== null;
    }

    public function documentRoot(): string
    {
        return $this->documentRoot;
    }

    /**
     * Everything the server wrote so far (access log, PHP errors).
     */
    public function log(): string
    {
        return $this->process === null ? '' : $this->process->getErrorOutput() . $this->process->getOutput();
    }

    public function __destruct()
    {
        $this->stop();
    }

    private static function freePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            throw new RuntimeException("Cannot allocate a free port: {$errstr}");
        }
        $name = (string) stream_socket_get_name($socket, false);
        fclose($socket);

        return (int) substr($name, strrpos($name, ':') + 1);
    }
}
