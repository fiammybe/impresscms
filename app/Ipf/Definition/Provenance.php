<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * Where a definition was declared: the IPF entity and handler classes and the
 * file and line of the handler class, for compiler diagnostics and the headers
 * of generated files. The file is relative to the legacy root, so the same
 * definition gives the same output on every installation.
 */
final class Provenance
{
    public function __construct(
        public readonly string $entityClass,
        public readonly string $handlerClass,
        public readonly string $file,
        public readonly int $line,
    ) {
    }

    public function describe(): string
    {
        return "{$this->handlerClass} ({$this->file}:{$this->line})";
    }

    public function field(string $field): string
    {
        return "{$this->entityClass} variable \"{$field}\" ({$this->file})";
    }
}
