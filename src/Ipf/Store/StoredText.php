<?php

declare(strict_types=1);

namespace App\Ipf\Store;

/**
 * Text as ImpressCMS stores it.
 */
final class StoredText
{
    /** bookkeeping the legacy HTML input filter stores with the text it has filtered */
    private const FILTER_MARKERS = ['<!-- filtered with htmlpurifier -->', '<!-- input filtered -->'];

    /**
     * The text without the input filter's markers, which are not part of the value.
     */
    public static function withoutFilterMarkers(string $text): string
    {
        return str_replace(self::FILTER_MARKERS, '', $text);
    }
}
