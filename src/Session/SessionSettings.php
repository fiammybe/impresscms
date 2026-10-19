<?php

declare(strict_types=1);

namespace App\Session;

use App\Legacy\Dbal\LegacyTables;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;

/**
 * The session preferences of ImpressCMS (System > Preferences > General:
 * use_mysession, session_name, session_expire), read with one DBAL query, so
 * that the host can start the session before any legacy code runs.
 */
final class SessionSettings
{
    /** The session name ImpressCMS uses without a custom one. */
    public const DEFAULT_NAME = 'ICMSSESSION';

    /** @var array{name: string, lifetime: int}|null */
    private ?array $settings = null;

    /**
     * @param \Closure(): \Doctrine\DBAL\Connection $connection created on first use: the
     *                                                  database settings come from mainfile.php
     */
    public function __construct(
        #[AutowireServiceClosure('doctrine.dbal.default_connection')]
        private readonly \Closure $connection,
        private readonly LegacyTables $tables,
    ) {
    }

    /** The session cookie's name. */
    public function name(): string
    {
        return $this->load()['name'];
    }

    /** Seconds until an idle session expires; 0 for a browser session. */
    public function lifetime(): int
    {
        return $this->load()['lifetime'];
    }

    /**
     * @return array{name: string, lifetime: int}
     */
    private function load(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $values = ($this->connection)()->fetchAllKeyValue(
            'SELECT conf_name, conf_value FROM ' . $this->tables->name('config')
            . " WHERE conf_modid = 0 AND conf_name IN ('use_mysession', 'session_name', 'session_expire')",
        );
        $custom = !empty($values['use_mysession']) && trim((string) ($values['session_name'] ?? '')) !== '';
        $expire = (int) ($values['session_expire'] ?? 0);

        return $this->settings = [
            'name' => $custom ? trim((string) $values['session_name']) : self::DEFAULT_NAME,
            'lifetime' => $custom && $expire > 0 ? $expire * 60 : 0,
        ];
    }
}
