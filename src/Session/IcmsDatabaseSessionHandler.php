<?php

declare(strict_types=1);

namespace App\Session;

use App\Legacy\Dbal\LegacyTables;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

/**
 * Stores sessions in the ImpressCMS `session` table (sess_id, sess_updated,
 * sess_ip, sess_data), as Icms\Core\Session did, so sessions survive the move
 * to the host. A session is only read from the network it was created from:
 * the first three IPv4 blocks, or the first seven IPv6 groups. No locking, as
 * before.
 */
final class IcmsDatabaseSessionHandler extends AbstractSessionHandler
{
    private const IPV4_BLOCKS = 3;
    private const IPV6_GROUPS = 7;

    private ?string $table = null;

    /** @var array<string, true> ids read in this request that have no row */
    private array $unstored = [];

    /** @var array<string, true> ids read in this request whose data lacks Symfony's metadata */
    private array $beforeTheHost = [];

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

    /**
     * Whether the session was stored by ImpressCMS' own save handler, before the
     * host owned the session: its data has no Symfony metadata (`_sf2_meta`).
     * Known once the session has been read in this request.
     */
    public function storedBeforeTheHost(string $sessionId): bool
    {
        return isset($this->beforeTheHost[$sessionId]);
    }

    /**
     * Whether a session created from $storedIp may be read from $remoteIp.
     */
    public static function sameNetwork(string $storedIp, string $remoteIp): bool
    {
        if (filter_var($storedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $length = self::IPV4_BLOCKS;
            $separator = '.';
        } elseif (filter_var($storedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $length = self::IPV6_GROUPS;
            $separator = ':';
            $storedIp = self::expandIpv6($storedIp);
            $remoteIp = filter_var($remoteIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false ? self::expandIpv6($remoteIp) : $remoteIp;
        } else {
            // not bound to an address (for example a session written by a command)
            return true;
        }

        return $remoteIp !== ''
            && array_slice(explode($separator, $storedIp), 0, $length) === array_slice(explode($separator, $remoteIp), 0, $length);
    }

    protected function doRead(string $sessionId): string
    {
        $row = ($this->connection)()->fetchAssociative(
            'SELECT sess_data, sess_ip FROM ' . $this->table() . ' WHERE sess_id = ?',
            [$sessionId],
        );
        if ($row === false) {
            $this->unstored[$sessionId] = true;

            return '';
        }
        if (!self::sameNetwork((string) $row['sess_ip'], self::remoteIp())) {
            return '';
        }
        $data = (string) $row['sess_data'];
        if ($data !== '' && !str_contains($data, '_sf2_meta|')) {
            $this->beforeTheHost[$sessionId] = true;
        }

        return $data;
    }

    protected function doWrite(string $sessionId, string $data): bool
    {
        $updated = ($this->connection)()->executeStatement(
            'UPDATE ' . $this->table() . ' SET sess_updated = ?, sess_data = ? WHERE sess_id = ?',
            [time(), $data, $sessionId],
        );
        if ($updated === 0) {
            // also when the row exists with the same data and time: MySQL reports 0
            ($this->connection)()->executeStatement(
                'INSERT INTO ' . $this->table() . ' (sess_id, sess_updated, sess_ip, sess_data) VALUES (?, ?, ?, ?)'
                . ' ON DUPLICATE KEY UPDATE sess_updated = VALUES(sess_updated), sess_data = VALUES(sess_data)',
                [$sessionId, time(), self::remoteIp(), $data],
            );
        }

        return true;
    }

    /**
     * Symfony destroys a session that ends up empty. One that was never stored
     * and has no cookie (an anonymous visitor that stored nothing) needs no
     * invalidation cookie: it simply does not exist.
     */
    public function destroy(#[\SensitiveParameter] string $sessionId): bool
    {
        if (isset($this->unstored[$sessionId]) && !isset($_COOKIE[session_name()])) {
            return true;
        }

        return parent::destroy($sessionId);
    }

    protected function doDestroy(string $sessionId): bool
    {
        ($this->connection)()->executeStatement('DELETE FROM ' . $this->table() . ' WHERE sess_id = ?', [$sessionId]);

        return true;
    }

    public function updateTimestamp(string $sessionId, string $data): bool
    {
        ($this->connection)()->executeStatement(
            'UPDATE ' . $this->table() . ' SET sess_updated = ? WHERE sess_id = ?',
            [time(), $sessionId],
        );

        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function gc(int $maxlifetime): int
    {
        return (int) ($this->connection)()->executeStatement(
            'DELETE FROM ' . $this->table() . ' WHERE sess_updated < ?',
            [time() - $maxlifetime],
        );
    }

    private function table(): string
    {
        return $this->table ??= $this->tables->name('session');
    }

    private static function remoteIp(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    private static function expandIpv6(string $ip): string
    {
        $packed = inet_pton($ip);

        return $packed === false ? $ip : implode(':', str_split(bin2hex($packed), 4));
    }
}
