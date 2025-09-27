<?php

declare(strict_types=1);

/**
 * Registry/factory for content type handlers.
 */
class icms_form_content_ContentTypeManager
{
    /** @var array<string, icms_form_content_ContentTypeHandlerInterface> */
    private static array $handlers = [];

    public static function initDefaults(): void
    {
        if (!empty(self::$handlers)) {
            return;
        }
        self::register(new icms_form_content_PlainTextContentTypeHandler());
        self::register(new icms_form_content_HtmlContentTypeHandler());
        // Always register Markdown; handler itself handles missing libs gracefully
        self::register(new icms_form_content_MarkdownContentTypeHandler());
    }

    public static function register(icms_form_content_ContentTypeHandlerInterface $handler): void
    {
        self::$handlers[$handler->getId()] = $handler;
    }

    /** @return array<string, icms_form_content_ContentTypeHandlerInterface> */
    public static function all(): array
    {
        self::initDefaults();
        return self::$handlers;
    }

    public static function get(string $id): icms_form_content_ContentTypeHandlerInterface
    {
        self::initDefaults();
        return self::$handlers[$id] ?? self::$handlers['plaintext'];
    }
}

