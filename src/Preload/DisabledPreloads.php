<?php

declare(strict_types=1);

namespace App\Preload;

/**
 * The preloads the site has disabled (design D3): `<trust>/var/preloads.php`,
 * returning ['disabled' => [name, ...]]. Icms\Preload\Handler reads it before
 * the database exists and skips those preloads; this class is the writing side.
 *
 * Writes lock a file next to the store, write a temporary file in the same
 * directory and rename it over the store, so that a request never includes a
 * half-written file.
 */
final class DisabledPreloads
{
    private const NAME_PATTERN = '/^[A-Za-z0-9_-]+$/';

    public function __construct(private readonly string $file)
    {
    }

    public function file(): string
    {
        return $this->file;
    }

    /** @return list<string> */
    public function names(): array
    {
        return \Icms\Preload\Handler::disabledPreloads($this->file);
    }

    public function isDisabled(string $name): bool
    {
        return in_array($name, $this->names(), true);
    }

    public static function isValidName(string $name): bool
    {
        return preg_match(self::NAME_PATTERN, $name) === 1;
    }

    /**
     * @throws \InvalidArgumentException for a name that cannot be a preload
     * @throws PreloadStoreException     when the store cannot be written
     */
    public function disable(string $name): void
    {
        $this->update($name, static fn (array $names): array => [...$names, $name]);
    }

    /**
     * @throws \InvalidArgumentException for a name that cannot be a preload
     * @throws PreloadStoreException     when the store cannot be written
     */
    public function enable(string $name): void
    {
        $this->update($name, static fn (array $names): array => array_diff($names, [$name]));
    }

    /**
     * @param \Closure(list<string>): array<string> $change
     */
    private function update(string $name, \Closure $change): void
    {
        if (!self::isValidName($name)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a preload name.', $name));
        }

        if ($this->file === '') {
            throw PreloadStoreException::notWritable('ICMS_TRUST_PATH');
        }

        $directory = dirname($this->file);
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw PreloadStoreException::notWritable($directory);
        }

        $lock = @fopen("{$this->file}.lock", 'c');
        if ($lock === false) {
            throw PreloadStoreException::notWritable($directory);
        }

        try {
            flock($lock, LOCK_EX);

            $names = array_values(array_unique($change($this->names())));
            sort($names);
            $this->write($directory, $names);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * @param list<string> $names
     */
    private function write(string $directory, array $names): void
    {
        $code = "<?php\n// Preloads disabled in the administration (admin/preloads). Delete this file to enable every preload.\nreturn "
            . var_export(['disabled' => $names], true) . ";\n";

        $temporary = @tempnam($directory, 'preloads');
        // tempnam() falls back to the system temporary directory when $directory is not writable
        if ($temporary === false || realpath(dirname($temporary)) !== realpath($directory)) {
            if ($temporary !== false) {
                @unlink($temporary);
            }

            throw PreloadStoreException::notWritable($directory);
        }

        if (@file_put_contents($temporary, $code) === false || !@rename($temporary, $this->file)) {
            @unlink($temporary);

            throw PreloadStoreException::notWritable($directory);
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->file, true);
        }
    }
}
