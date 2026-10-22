<?php

declare(strict_types=1);

namespace App\Preload;

/**
 * The preload files in the preloads directory, enabled and disabled (design D5).
 *
 * Files are found the way Icms\Preload\Handler finds them. Their events are
 * read from the source with the tokenizer: the public `event*` methods of the
 * class `IcmsPreload<Name>`. No preload file is included, so inspecting a
 * disabled preload never runs its code.
 */
final class PreloadInventory
{
    public function __construct(
        private readonly string $directory,
        private readonly DisabledPreloads $disabled,
    ) {
    }

    /** @return list<PreloadInfo> */
    public function all(): array
    {
        if (!is_dir($this->directory)) {
            return [];
        }

        $disabled = $this->disabled->names();
        $preloads = [];

        foreach (new \DirectoryIterator($this->directory) as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if (preg_match('/\.php$/i', $file->getFilename()) !== 1) {
                continue;
            }

            $name = str_replace('.php', '', $file->getFilename());
            $className = self::className($name);

            $preloads[$name] = new PreloadInfo(
                $name,
                $className,
                !in_array($name, $disabled, true),
                self::events((string) file_get_contents($file->getPathname()), $className),
            );
        }

        ksort($preloads, SORT_STRING | SORT_FLAG_CASE);

        return array_values($preloads);
    }

    public function find(string $name): ?PreloadInfo
    {
        foreach ($this->all() as $preload) {
            if ($preload->name === $name) {
                return $preload;
            }
        }

        return null;
    }

    /**
     * As Icms\Preload\Handler::getClassName().
     */
    public static function className(string $name): string
    {
        return 'IcmsPreload' . ucfirst($name);
    }

    /**
     * The events a preload file handles: the `event*` methods of its preload
     * class (`eventBeforeFooter` gives "beforeFooter"; the handler attaches them
     * lower-cased), followed by the events it attaches itself with
     * `icms_Event::attach()`, as some shipped preloads do. Those are shown as
     * "sessionStart (icms_core_Session)" unless they belong to the `icms`
     * namespace.
     *
     * @return list<string>
     */
    public static function events(string $code, string $className): array
    {
        $tokens = self::significantTokens($code);

        return array_values(array_unique([
            ...self::classEvents($tokens, $className),
            ...self::attachedEvents($tokens),
        ]));
    }

    /**
     * @return list<array{0: int, 1: string, 2: int}|string>
     */
    private static function significantTokens(string $code): array
    {
        return array_values(array_filter(
            token_get_all($code),
            static fn (array|string $token): bool => !is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true),
        ));
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     *
     * @return list<string>
     */
    private static function attachedEvents(array $tokens): array
    {
        $events = [];

        foreach ($tokens as $i => $token) {
            if (!is_array($token) || !in_array(strtolower(ltrim($token[1], '\\')), ['icms_event', 'icms\\event'], true)) {
                continue;
            }

            $call = array_slice($tokens, $i + 1, 6);
            if (count($call) < 6) {
                continue;
            }

            [$doubleColon, $method, $open, $namespace, $comma, $event] = $call;
            if (!is_array($doubleColon) || $doubleColon[0] !== T_DOUBLE_COLON) {
                continue;
            }

            if (!is_array($method) || strcasecmp($method[1], 'attach') !== 0 || $open !== '(' || $comma !== ',') {
                continue;
            }

            if (!self::isStringLiteral($namespace) || !self::isStringLiteral($event)) {
                continue;
            }

            $namespaceName = self::stringValue($namespace);
            $eventName = self::stringValue($event);

            $events[] = $namespaceName === 'icms' ? $eventName : "{$eventName} ({$namespaceName})";
        }

        return $events;
    }

    private static function isStringLiteral(mixed $token): bool
    {
        return is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING;
    }

    /**
     * @param array{0: int, 1: string, 2: int} $token
     */
    private static function stringValue(array $token): string
    {
        return stripslashes(substr($token[1], 1, -1));
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     *
     * @return list<string>
     */
    private static function classEvents(array $tokens, string $className): array
    {
        $start = self::classBodyStart($tokens, $className);
        if ($start === null) {
            return [];
        }

        $events = [];
        $depth = 0;
        $visibility = T_PUBLIC;

        for ($i = $start; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if ($token === '{' || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $depth++;
                continue;
            }

            if ($token === '}') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
                continue;
            }

            if ($depth !== 1 || !is_array($token)) {
                continue;
            }

            if (in_array($token[0], [T_PUBLIC, T_PROTECTED, T_PRIVATE], true)) {
                $visibility = $token[0];
                continue;
            }

            if ($token[0] !== T_FUNCTION) {
                continue;
            }

            $method = $tokens[$i + 1] ?? null;
            $isPublic = $visibility === T_PUBLIC;
            $visibility = T_PUBLIC;

            if (!is_array($method) || $method[0] !== T_STRING) {
                continue;
            }

            if (!$isPublic || !str_starts_with($method[1], 'event') || $method[1] === 'event') {
                continue;
            }

            $events[] = lcfirst(substr($method[1], 5));
        }

        return $events;
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     */
    private static function classBodyStart(array $tokens, string $className): ?int
    {
        foreach ($tokens as $i => $token) {
            if (!is_array($token) || $token[0] !== T_CLASS) {
                continue;
            }

            $name = $tokens[$i + 1] ?? null;
            if (!is_array($name) || strcasecmp($name[1], $className) !== 0) {
                continue;
            }

            for ($j = $i + 2; $j < count($tokens); $j++) {
                if ($tokens[$j] === '{') {
                    return $j;
                }
            }
        }

        return null;
    }
}
