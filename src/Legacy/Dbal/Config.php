<?php

declare(strict_types=1);

namespace App\Legacy\Dbal;

use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;

/**
 * System preferences of ImpressCMS (the `config` table, module 0) read with
 * DBAL, for host code that must not boot the legacy services: the login and
 * the account checks. Array values are unserialized as ImpressCMS does.
 */
final class Config
{
    /** @var array<string, mixed> */
    private array $values = [];

    /**
     * @param \Closure(): \Doctrine\DBAL\Connection $connection created on first use: the
     *                                                  database settings come from mainfile.php
     */
    public function __construct(
        #[AutowireServiceClosure('doctrine.dbal.default_connection')]
        private readonly \Closure $connection,
        private readonly Tables $tables,
    ) {
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (!array_key_exists($name, $this->values)) {
            $row = ($this->connection)()->fetchAssociative(
                'SELECT conf_value, conf_valuetype FROM ' . $this->tables->name('config') . ' WHERE conf_modid = 0 AND conf_name = ?',
                [$name],
            );
            $this->values[$name] = $row === false ? null : match ((string) $row['conf_valuetype']) {
                'array' => (array) @unserialize((string) $row['conf_value'], ['allowed_classes' => false]),
                'int' => (int) $row['conf_value'],
                default => (string) $row['conf_value'],
            };
        }

        return $this->values[$name] ?? $default;
    }
}
