<?php

declare(strict_types=1);

namespace App\Security\RememberMe;

use App\Legacy\Dbal\Tables;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentToken;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

/**
 * Remember-me tokens in the ImpressCMS table <prefix>_rememberme_token (OpenSpec
 * change remember-me-and-email-links, design D1), as Symfony's
 * DoctrineTokenProvider keeps them, which only knows an unprefixed table.
 */
final class TokenProvider implements TokenProviderInterface
{
    private ?string $table = null;

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

    public function loadTokenBySeries(string $series): PersistentTokenInterface
    {
        $row = ($this->connection)()->fetchAssociative(
            'SELECT username, value, lastUsed FROM ' . $this->table() . ' WHERE series = ?',
            [$series],
        );
        if ($row === false) {
            throw new TokenNotFoundException('No remember-me token for this series.');
        }

        return new PersistentToken((string) $row['username'], $series, (string) $row['value'], new \DateTimeImmutable((string) $row['lastUsed']));
    }

    public function deleteTokenBySeries(string $series): void
    {
        ($this->connection)()->executeStatement('DELETE FROM ' . $this->table() . ' WHERE series = ?', [$series]);
    }

    public function updateToken(string $series, #[\SensitiveParameter] string $tokenValue, \DateTimeInterface $lastUsed): void
    {
        $updated = ($this->connection)()->executeStatement(
            'UPDATE ' . $this->table() . ' SET value = ?, lastUsed = ? WHERE series = ?',
            [$tokenValue, $lastUsed->format('Y-m-d H:i:s'), $series],
        );
        if ($updated < 1) {
            throw new TokenNotFoundException('No remember-me token for this series.');
        }
    }

    public function createNewToken(PersistentTokenInterface $token): void
    {
        ($this->connection)()->executeStatement(
            'INSERT INTO ' . $this->table() . ' (class, username, series, value, lastUsed) VALUES (?, ?, ?, ?, ?)',
            ['', $token->getUserIdentifier(), $token->getSeries(), $token->getTokenValue(), $token->getLastUsed()->format('Y-m-d H:i:s')],
        );
    }

    /**
     * Forgets every remembered device of a user (after a new password).
     */
    public function deleteTokensOf(string $userIdentifier): void
    {
        ($this->connection)()->executeStatement('DELETE FROM ' . $this->table() . ' WHERE username = ?', [$userIdentifier]);
    }

    private function table(): string
    {
        return $this->table ??= $this->tables->name('rememberme_token');
    }
}
