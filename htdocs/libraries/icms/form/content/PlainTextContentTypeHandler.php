<?php

declare(strict_types=1);

/**
 * Plain text content type handler.
 */
class icms_form_content_PlainTextContentTypeHandler extends icms_form_content_AbstractContentTypeHandler
{
    public function getId(): string
    {
        return 'plaintext';
    }

    public function getLabel(): string
    {
        return _PLAIN_TEXT ?? 'Plain text';
    }

    public function renderForOutput(string $stored): string
    {
        // Escape and convert newlines to <br>
        return nl2br(htmlspecialchars($stored, ENT_QUOTES));
    }
}

