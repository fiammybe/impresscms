<?php

declare(strict_types=1);

/**
 * Markdown content type handler.
 *
 * Integration notes:
 * - Preferred: league/commonmark (v2+), class League\CommonMark\MarkdownConverter
 *   Install via Composer: composer require league/commonmark
 * - Fallback: erusev/parsedown, class Parsedown (if present)
 * - If no library is present, falls back to safe plaintext rendering.
 *
 * Sanitization: As requested, skip HTML sanitization for markdown; the renderer output is returned as-is.
 */
class icms_form_content_MarkdownContentTypeHandler extends icms_form_content_AbstractContentTypeHandler
{
    public function getId(): string
    {
        return 'markdown';
    }

    public function getLabel(): string
    {
        return 'Markdown';
    }

    /**
     * Render stored Markdown to HTML.
     * If $sanitize is true, apply ImpressCMS HTMLFilter afterwards.
     */
    public function renderForOutput(string $stored, bool $sanitize = false): string
    {
        $html = '';
        // Try league/commonmark v2+
        if (class_exists('League\\CommonMark\\MarkdownConverter')) {
            /** @var object $converter */
            $converter = new \League\CommonMark\MarkdownConverter();
            $html = (string) $converter->convert($stored);
        } elseif (class_exists('League\\CommonMark\\CommonMarkConverter')) {
            // Try league/commonmark v1 (CommonMarkConverter)
            $converter = new \League\CommonMark\CommonMarkConverter();
            $html = (string) $converter->convertToHtml($stored);
        } else {
            // Try Parsedown (load from libraries if present and not already loaded)
            if (!class_exists('Parsedown')) {
                if (defined('ICMS_LIBRARIES_PATH')) {
                    $p = ICMS_LIBRARIES_PATH . '/parsedown/Parsedown.php';
                    if (file_exists($p)) {
                        require_once $p;
                    }
                }
            }
            if (class_exists('Parsedown')) {
                $parsedown = new \Parsedown();
                $html = $parsedown->text($stored);
            } else {
                // No renderer available: degrade gracefully to escaped plaintext
                $html = nl2br(htmlspecialchars($stored, ENT_QUOTES));
            }
        }
        if ($sanitize) {
            // Apply ImpressCMS HTML sanitizer to the resulting HTML
            $html = icms_core_HTMLFilter::filterHTML($html);
        }
        return $html;
    }
}

