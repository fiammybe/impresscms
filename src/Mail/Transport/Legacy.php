<?php

declare(strict_types=1);

namespace App\Mail\Transport;

use App\Legacy\Dbal\Config;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * The transport of the ImpressCMS mail preferences (System > Preferences >
 * Mail setup), built when the first message is sent:
 * - `mail`: PHP's mail function (`native://default`);
 * - `sendmail`: the configured sendmail path;
 * - `smtp`: the first SMTP host (`host` or `host:port`), without login;
 * - `smtpauth`: the SMTP host with user and password; `smtpsecure` = ssl uses
 *   implicit TLS on `smtpauthport`, otherwise STARTTLS when offered.
 */
final class Legacy implements TransportInterface
{
    private ?TransportInterface $transport = null;

    public function __construct(private readonly Config $config)
    {
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        return ($this->transport ??= Transport::fromDsn($this->dsn()))->send($message, $envelope);
    }

    /**
     * The DSN of the preferences, for Symfony Mailer.
     */
    public function dsn(): string
    {
        $hosts = (array) $this->config->get('smtphost', []);
        $host = trim((string) ($hosts[0] ?? ''));

        switch ((string) $this->config->get('mailmethod', 'mail')) {
            case 'sendmail':
                $path = trim((string) $this->config->get('sendmailpath', '')) ?: '/usr/sbin/sendmail';

                return 'sendmail://default?command=' . rawurlencode($path . ' -bs');
            case 'smtp':
                return 'smtp://' . ($host !== '' ? $host : 'localhost');
            case 'smtpauth':
                $implicitTls = strtolower((string) $this->config->get('smtpsecure', '')) === 'ssl';
                $port = (int) $this->config->get('smtpauthport', 0);
                if (!str_contains($host, ':') && $port > 0) {
                    $host .= ':' . $port;
                }

                return ($implicitTls ? 'smtps://' : 'smtp://')
                    . rawurlencode((string) $this->config->get('smtpuser', '')) . ':'
                    . rawurlencode((string) $this->config->get('smtppass', '')) . '@'
                    . ($host !== '' ? $host : 'localhost');
            default:
                return 'native://default';
        }
    }

    public function __toString(): string
    {
        return 'icms://default';
    }
}
