<?php

declare(strict_types=1);

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * Writes each message as an .eml file into a directory instead of sending it,
 * for the test environment (`icms+dir://default?path=...`): tests read what a
 * web request mailed.
 */
final class Directory implements TransportInterface
{
    public function __construct(private readonly string $path)
    {
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): SentMessage
    {
        $envelope ??= Envelope::create($message);
        if ($message instanceof Message && !$message->getHeaders()->has('Date')) {
            $message->getHeaders()->addDateHeader('Date', new \DateTimeImmutable());
        }
        if (!is_dir($this->path) && !mkdir($this->path, 0777, true) && !is_dir($this->path)) {
            throw new \RuntimeException(sprintf('Cannot create the mail directory "%s".', $this->path));
        }
        $file = sprintf('%s/%s-%s.eml', $this->path, (new \DateTimeImmutable())->format('Ymd-His-u'), bin2hex(random_bytes(4)));
        file_put_contents($file, $message->toString());

        return new SentMessage($message, $envelope);
    }

    public function __toString(): string
    {
        return 'icms+dir://default?path=' . $this->path;
    }
}
