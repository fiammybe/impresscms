<?php
/**
 * Canonical form of an HTML fragment, for "semantically the same" assertions.
 *
 * Equal for markup that a browser reads the same way: attribute order, quote
 * style, entity encoding of attribute values and text, runs of whitespace and
 * comments do not count; elements, attributes, their decoded values and the
 * text do.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Support;

final class HtmlCanonicalizer
{
    public static function canonical(string $html): string
    {
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?><html><body><div id="icms-canonical-root">' . $html . '</div></body></html>', LIBXML_NOBLANKS | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('icms-canonical-root');
        if ($root === null) {
            return trim((string) preg_replace('/\s+/', ' ', $html));
        }
        $out = '';
        foreach ($root->childNodes as $node) {
            $out .= self::node($node);
        }

        return trim((string) preg_replace('/\s+/', ' ', $out));
    }

    private static function node(\DOMNode $node): string
    {
        if ($node instanceof \DOMText) {
            return htmlspecialchars((string) preg_replace('/\s+/', ' ', $node->nodeValue ?? ''), ENT_NOQUOTES);
        }
        if (!$node instanceof \DOMElement) {
            // comments, processing instructions
            return '';
        }
        $attributes = [];
        foreach ($node->attributes ?? [] as $attribute) {
            $attributes[$attribute->name] = (string) preg_replace('/\s+/', ' ', trim($attribute->value));
        }
        ksort($attributes);
        $out = '<' . $node->tagName;
        foreach ($attributes as $name => $value) {
            $out .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }
        $out .= '>';
        foreach ($node->childNodes as $child) {
            $out .= self::node($child);
        }

        return $out . '</' . $node->tagName . '>';
    }
}
