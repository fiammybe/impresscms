<?php

declare(strict_types=1);

/**
 * HTML content type handler.
 * Keeps behavior consistent with legacy: return as-is (sanitization happens elsewhere if enabled).
 */
class icms_form_content_HtmlContentTypeHandler extends icms_form_content_AbstractContentTypeHandler
{
    public function getId(): string
    {
        return 'html';
    }

    public function getLabel(): string
    {
        return _HTML ?? 'HTML';
    }

    public function renderForOutput(string $stored): string
    {
        // Return as-is. HTML purifier may run elsewhere in the display pipeline.
        return $stored;
    }
}

