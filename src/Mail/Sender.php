<?php

declare(strict_types=1);

namespace App\Mail;

use App\Legacy\Dbal\Config;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Sends plain-text mails of the site, with the sender of the ImpressCMS mail
 * preferences: `from` (else the webmaster's address) and `fromname` (else the
 * site name).
 *
 * A mail that cannot be sent is logged, not thrown: the answer to a request for
 * a link must not depend on whether a mail went out (App\Controller\Auth\Mail).
 */
final class Sender
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Config $config,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function send(string $to, string $subject, string $text): void
    {
        $from = trim((string) $this->config->get('from', '')) ?: trim((string) $this->config->get('adminmail', ''));
        $name = trim((string) $this->config->get('fromname', '')) ?: (string) $this->config->get('sitename', '');

        try {
            $this->mailer->send(
                (new Email())
                    ->from(new Address($from, $name))
                    ->to($to)
                    ->subject($subject)
                    ->text($text),
            );
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Mail "{subject}" not sent: {message}', ['subject' => $subject, 'message' => $e->getMessage(), 'exception' => $e]);
        }
    }
}
