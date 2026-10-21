<?php

namespace Icms\View\Smarty;

use Smarty\Template;

/**
 * Pre-filter that rewrites the template syntax of ImpressCMS 2.x (Smarty 2)
 * into the Smarty 5 equivalent, and logs a deprecation for every rewrite.
 *
 * - <{includeq ...}> becomes <{include ...}>
 * - <{foreachq ...}> becomes <{foreach ...}>
 * - <{xoImgUrl icons/a.png}> becomes <{xoImgUrl path="icons/a.png"}>
 * - <{xoAppUrl modules/x/y.php a=1}> becomes <{xoAppUrl url="modules/x/y.php" a=1}>
 * - <{cycle values=odd,even}> becomes <{cycle values="odd,even"}>
 *
 * It runs when a template is compiled, so a notice appears once per compiled template.
 */
class LegacySyntaxFilter
{
    public function __invoke(string $source, Template $template): string
    {
        $left = preg_quote($template->getSmarty()->getLeftDelimiter(), '/');
        $right = preg_quote($template->getSmarty()->getRightDelimiter(), '/');
        $leftTag = $template->getSmarty()->getLeftDelimiter();

        $source = $this->rewrite(
            $source,
            "/$left(\\s*)(include|foreach)q\\b/",
            fn (array $m): string => $leftTag . $m[1] . $m[2],
            $template,
            'includeq/foreachq: use include/foreach'
        );

        $source = $this->rewrite(
            $source,
            "/$left(\\s*)xoImgUrl\\s+([^=]*?)\\s*$right/",
            fn (array $m): string => $leftTag . $m[1] . 'xoImgUrl path="' . addcslashes(trim($m[2]), '"') . '"'
                . $template->getSmarty()->getRightDelimiter(),
            $template,
            'xoImgUrl: pass the file as path="..."'
        );

        $source = $this->rewrite(
            $source,
            "/($left\\s*cycle\\b(?:(?!$right).)*?\\bvalues=)([\\w,]+)(?=[\\s}])/",
            fn (array $m): string => $m[1] . '"' . $m[2] . '"',
            $template,
            'cycle: quote the values="a,b" attribute'
        );

        return $this->rewrite(
            $source,
            "/$left(\\s*)xoAppUrl\\s+(?![^\\s=]*=)((?:(?!$right)\\S)+)(.*?)\\s*$right/",
            fn (array $m): string => $leftTag . $m[1] . 'xoAppUrl url=' . $this->quoteArgument($m[2]) . $m[3]
                . $template->getSmarty()->getRightDelimiter(),
            $template,
            'xoAppUrl: pass the address as url="..."'
        );
    }

    private function quoteArgument(string $argument): string
    {
        return str_starts_with($argument, '$') ? $argument : '"' . addcslashes($argument, '"') . '"';
    }

    private function rewrite(string $source, string $pattern, callable $replacement, Template $template, string $advice): string
    {
        $count = 0;
        $source = preg_replace_callback($pattern, $replacement, $source, -1, $count);

        if ($count > 0) {
            trigger_error(
                "Template '{$template->template_resource}' uses legacy syntax ($advice).",
                E_USER_DEPRECATED
            );
        }

        return $source;
    }
}
