<?php

declare(strict_types=1);

namespace App\Preload;

final class PreloadStoreException extends \RuntimeException
{
    public function __construct(public readonly string $directory)
    {
        parent::__construct(sprintf('The disabled preloads cannot be stored: "%s" must be writable.', $directory));
    }

    public static function notWritable(string $directory): self
    {
        return new self($directory);
    }
}
