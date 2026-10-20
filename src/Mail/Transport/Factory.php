<?php

declare(strict_types=1);

namespace App\Mail\Transport;

use App\Legacy\Dbal\Config;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Mailer transports of ImpressCMS (OpenSpec change remember-me-and-email-links,
 * design D5):
 * - `icms://default`: the transport of the ImpressCMS mail preferences
 *   (App\Mail\Transport\Legacy), so there is one mail configuration;
 * - `icms+dir://default?path=...`: each message as an .eml file in a
 *   directory (App\Mail\Transport\Directory), for the test environment.
 */
final class Factory implements TransportFactoryInterface
{
    public function __construct(private readonly Config $config)
    {
    }

    public function create(Dsn $dsn): TransportInterface
    {
        return match ($dsn->getScheme()) {
            'icms' => new Legacy($this->config),
            'icms+dir' => new Directory((string) $dsn->getOption('path')),
            default => throw new UnsupportedSchemeException($dsn, 'icms', ['icms', 'icms+dir']),
        };
    }

    public function supports(Dsn $dsn): bool
    {
        return in_array($dsn->getScheme(), ['icms', 'icms+dir'], true);
    }
}
