<?php

/**
 * Rector configuration for ImpressCMS
 *
 * Configures Rector to generate code compatible with PHP 7.4 through 8.5.
 * The minimum target version is PHP 7.4, ensuring the generated code
 * runs on all supported PHP versions (7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5).
 *
 * Applies PSR-12 compatible code style improvements.
 */

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/htdocs',
        __DIR__ . '/upgrade',
    ])
    ->withSkip([
        __DIR__ . '/.github',
        __DIR__ . '/docs',
        __DIR__ . '/extras',
        __DIR__ . '/htdocs/cache',
        __DIR__ . '/htdocs/images',
        __DIR__ . '/htdocs/libraries/WideImage',
        __DIR__ . '/htdocs/libraries/geshi',
        __DIR__ . '/htdocs/libraries/htmlpurifier',
        __DIR__ . '/htdocs/libraries/image-editor',
        __DIR__ . '/htdocs/libraries/jalalijscalendar',
        __DIR__ . '/htdocs/libraries/jquery',
        __DIR__ . '/htdocs/libraries/jscalendar',
        __DIR__ . '/htdocs/libraries/lightbox',
        __DIR__ . '/htdocs/libraries/paginationstyles',
        __DIR__ . '/htdocs/libraries/phpmailer',
        __DIR__ . '/htdocs/libraries/recaptcha',
        __DIR__ . '/htdocs/libraries/simplepie',
        __DIR__ . '/htdocs/libraries/smarty',
        __DIR__ . '/htdocs/libraries/tcpdf',
        __DIR__ . '/htdocs/libraries/xml',
        __DIR__ . '/htdocs/templates_c',
        __DIR__ . '/htdocs/themes',
        __DIR__ . '/htdocs/uploads',
        // Skip rules that generate code incompatible with PHP 7.4
        InlineConstructorDefaultToPropertyRector::class,
    ])
    // Target PHP 7.4 as minimum version for broad compatibility (7.4 to 8.5)
    ->withPhpVersion(PhpVersion::PHP_74)
    // Enable PHP 7.4 compatible feature set
    ->withPhpSets(php74: true)
    // Enable prepared sets for code quality and PSR-12 style improvements
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        earlyReturn: true
    )
    // Apply coding style sets for PSR-12 compliance
    ->withSets([
        SetList::CODING_STYLE,
    ]);
