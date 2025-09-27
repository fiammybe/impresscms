<?php

declare(strict_types=1);

/**
 * Content type handler interface for textarea-like content.
 * Implementations transform raw input for storage and for output/display.
 *
 * Backwards compatible: if not used, legacy flow remains unchanged.
 */
interface icms_form_content_ContentTypeHandlerInterface
{
    /** Identifier used in forms/config (e.g. 'plaintext', 'html', 'markdown'). */
    public function getId(): string;

    /** Human-readable label. */
    public function getLabel(): string;

    /**
     * Prepare user-provided input for storage. Default is passthrough.
     * Allows normalization per type.
     */
    public function processForStorage(string $input): string;

    /**
     * Render stored content for output as HTML string.
     * Implementations are responsible for escaping/sanitization as needed for the type.
     */
    public function renderForOutput(string $stored): string;
}

