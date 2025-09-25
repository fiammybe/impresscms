<?php
/**
 * Smarty plugin: {picture file="banner.jpg" alt="Hero" sizes="(max-width: 768px) 100vw, 1200px"}
 * 
 * Automatisch <picture> met AVIF, WebP en fallback + responsive srcset.
 * Verwacht dat je build pipeline varianten maakt zoals:
 *   banner-512.webp, banner-1024.webp, banner-2048.webp
 *   banner-512.avif, banner-1024.avif, banner-2048.avif
 */

function smarty_function_picture($params, &$smarty)
{
    if (empty($params['file'])) {
        return '<!-- picture: no file specified -->';
    }

    $file = $params['file'];
    $alt  = isset($params['alt']) ? htmlspecialchars($params['alt'], ENT_QUOTES) : '';
    $class = isset($params['class']) ? ' class="'.htmlspecialchars($params['class']).'"' : '';
    $loading = isset($params['loading']) ? $params['loading'] : 'lazy';
    $sizes = isset($params['sizes']) ? $params['sizes'] : '100vw';

    // ImpressCMS convention: use icms_imageurl for theme assets
    $baseUrl = '<{$icms_imageurl}>';

    // Strip extensie
    $basename = preg_replace('/\.[^.]+$/', '', $file);
    $ext = pathinfo($file, PATHINFO_EXTENSION);

    // Stel responsive varianten in (afhankelijk van je build pipeline)
    $widths = [512, 1024, 2048];

    // Bouw srcset strings
    $srcset_avif = [];
    $srcset_webp = [];
    foreach ($widths as $w) {
        $srcset_avif[] = "{$baseUrl}{$basename}-{$w}.avif {$w}w";
        $srcset_webp[] = "{$baseUrl}{$basename}-{$w}.webp {$w}w";
    }

    // Bouw de picture tag
    $html  = "<picture>\n";
    $html .= "  <source type=\"image/avif\" srcset=\"".implode(', ', $srcset_avif)."\" sizes=\"{$sizes}\">\n";
    $html .= "  <source type=\"image/webp\" srcset=\"".implode(', ', $srcset_webp)."\" sizes=\"{$sizes}\">\n";
    $html .= "  <img src=\"{$baseUrl}{$file}\" alt=\"{$alt}\"{$class} loading=\"{$loading}\" sizes=\"{$sizes}\">\n";
    $html .= "</picture>\n";

    return $html;
}
