<?php

declare(strict_types=1);

namespace App\Front;

use App\LegacyBridge\LegacyExecutionResponse;
use App\LegacyBridge\LegacyResolution;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

/**
 * A page of the host inside the site's theme (OpenSpec change
 * remember-me-and-email-links, design D6). render() keeps the content and
 * returns the bridge's marker for htdocs/include/host_page.php, which host.php
 * runs at file scope like any legacy page: mainfile.php, header.php, the
 * content, footer.php. So blocks, the theme and the globals work as usual.
 *
 * The content is a closure that host_page.php calls after header.php: by then
 * legacy booted as for any page, with the language of the page type loaded.
 * A controller that renders a page must not boot legacy itself (headless, via
 * App\Legacy\LegacyRuntime): mainfile.php would then find the core booted
 * without the preloads and the page fails.
 */
final class Page
{
    /** @var (\Closure(): array{title: string, html: string})|null */
    private static ?\Closure $pending = null;

    public function __construct(
        #[Autowire('%icms.legacy.root%')]
        private readonly string $legacyRoot,
    ) {
    }

    /**
     * @param \Closure(): array{title: string, html: string} $content the title and the HTML
     * @param string $scriptName the legacy script the page stands for (blocks
     *                           and page types follow it)
     */
    public function render(\Closure $content, string $scriptName = '/user.php'): Response
    {
        self::$pending = $content;
        $script = rtrim(str_replace('\\', '/', $this->legacyRoot), '/') . '/include/host_page.php';

        return new LegacyExecutionResponse(new LegacyResolution(
            strategy: LegacyResolution::DIRECT_SCRIPT,
            requestPath: $scriptName,
            resolvedPath: $script,
            workingDirectory: dirname($script, 2),
            scriptName: $scriptName,
        ));
    }

    /**
     * The content htdocs/include/host_page.php is to show, once; null for a
     * request that did not come from render().
     *
     * @return (\Closure(): array{title: string, html: string})|null
     */
    public static function take(): ?\Closure
    {
        $content = self::$pending;
        self::$pending = null;

        return $content;
    }
}
