## Context

The starting point is the state after `smarty-5-upgrade`: `Icms\View\Tpl` extends `Smarty\Smarty` 5.8, delimiters are `<{ }>`, and the compile id is `<template_set>-<theme_set>`. The host runs Symfony 7.4 with `symfony/translation-contracts` but without `symfony/translation`. Language files live in `<web root>/language/<dir>/` and `<web root>/modules/<module>/language/<dir>/` (optionally under `admin/`). `icms_loadLanguageFile()` includes them with `include_once` for `$icmsConfig['language']`, falling back to `english`. There are about 260 files. Values contain `sprintf` placeholders (`%s`, `%1$s`), mail-style tokens (`{X_SITENAME}`) and references to other constants (`ICMS_URL . '...'`).

The imponeer packages and the source that was checked (latest tags):

| Package | Tag | Relevant behaviour |
|---|---|---|
| `symfony-translations-constants-loader` | v2.1.0 | `PHPFileLoader::load()` evaluates the file inside a random namespace that defines its own `define()`. The constants go into a `MessageCatalogue`, and no global constant is defined. |
| `smarty-translate` | v3.0.6 | `TranslationSmartyExtension(TranslatorInterface)`: `{trans}` block and `trans` modifier, with parameters, domain and locale. |
| `smarty-db-resource` | v4.0.6 | `DatabaseResource` (a `CustomPlugin`) on a `PDO`. A set other than the default serves `tpl_source` from the DB. The default set resolves a file through `templatePathGetter(array $row)`. It runs one query per template. |
| `smarty-xo` | v1.1.8 | `XOExtension` with callables for path, URL building, image URL, inbox count and page URLs. It has no `xoops_link`. |
| `smarty-includeq` / `smarty-foreachq` | v3.0.4 / v2.0.4 | Compiler tags built on Smarty 5's `include`/`foreach`. Neither one logs a deprecation. |
| `smarty-image` | v3.2.1 | `resized_image` returns the image as a `data:` URI (both as `img` src and with `return=url`), cached in PSR-6. Its default `fit` is `outside`; ImpressCMS uses `inside`. |

## Goals / Non-Goals

**Goals:**
- One Translator that Symfony code, Twig and Smarty can use, fed by the existing language files, without touching them.
- Translate into a language other than the current one within a request, for example mail in the recipient's language.
- Replace in-house Smarty 5 ports with maintained packages wherever output stays identical.
- Pin the PHP 8.3 baseline in one place (Composer).

**Non-Goals:**
- Rewriting language files into YAML or XLIFF, or changing constant names.
- Replacing `icms_loadLanguageFile()` or constant use in legacy code and modules.
- Converting `sprintf` placeholders to ICU or `%name%` parameters.
- Adopting the other imponeer libraries (see proposal, *Out of scope*).

## Decisions

### PHP 8.3 minimum
Set `require.php` to `>=8.3 <8.5` and `config.platform.php` to `8.3.0`. The requirements page reads the minimum from `vendor/composer/platform_check.php` (`host-deployment`), so it needs no code change. CI drops 8.2. The smoke test's expected string becomes `PHP 8.3.0 or newer`. The alternative was to keep 8.2 and fork the packages with a lower constraint. That was rejected because 8.2 reaches end of security support within weeks, and a fork defeats the point.

### Translator: domains, locales, fallback
- **Domains**: `icms` for the core files (`language/<dir>/*.php` and `admin/*.php`), and the module dirname for module files (`system`, ...). Constants are effectively global, so a key could live in one domain. But per-module domains mean an uninstalled module's strings disappear cleanly, and two modules defining the same constant cannot shadow each other.
- **Locales**: each language directory is mapped to a locale through the `_LANGCODE` from its `global.php` (`english`→`en`, `nederlands`→`nl`, `schinese`→`zh_CN` after normalising `-` to `_` and the region to upper case). A directory without `_LANGCODE` is skipped and logged. `App\Translation\LocaleMap` exposes dir↔locale both ways for other code.
- **Current locale**: on each request the Translator's locale is set to the locale of `$icmsConfig['language']`, after the shared runtime has booted (kernel request listener). Fallback locales: `['en']`, the same as legacy's `english` fallback.
- **Placeholders stay `sprintf`**: `trans()` returns the raw string. Callers apply `sprintf` as legacy does (Twig: `|trans|format(...)`; Smarty: the `sprintf`/`string_format` modifier). `%name%`-style parameters work for new strings only.

### Registering resources at runtime, not at container compile
The loader evaluates PHP. Many files start with `if (!defined('ICMS_ROOT_PATH')) die(...)`, and values reference `ICMS_URL`. Evaluated in `bin/console cache:warmup` without a booted legacy runtime, these files would kill the process or throw `Undefined constant`. So the files are not added through `framework.translator.paths`. A `LegacyTranslationResources` service adds them to the Translator on first use within a request (`Translator::addResource`), after `LegacyRuntime` has booted. The resources are the installed modules (from the module handler) × installed languages. Catalogues are cached in `var/cache/<env>/translations` as usual. Because they embed `ICMS_URL`, the cache is per site, which matches one trust path per site.

**Cache invalidation**: installing, updating or uninstalling a module or language, and changing `ICMS_URL`, must clear the translation catalogue cache. The module admin's install/update/uninstall hooks call a `TranslationCacheClearer`. In `debug` mode Symfony's resource freshness check covers edits to files.

### Loader fixes go upstream first
The loader evaluates each file with the real global `defined()`. About 49 files guard definitions with `if (!defined('_X')) define(...)`. When legacy already defined `_X` earlier in the same request, the guard skips the `define()` and the key is missing from the catalogue. So the catalogue would depend on what legacy loaded before. Fix upstream: shadow `defined()` (and `constant()`) in the virtual namespace so they consult the temporary bag first, then the global constants. `PHPFileLoader::load()` is `final`, so it cannot be fixed by extending it. Until a release contains the fix, ImpressCMS ships `App\Translation\ConstantsFileLoader` with the same contract and switches back to the package loader once the release is out.

**Excluded files**: `xoopsmailerlocal.php` (declares a class that extends a global class, which fails under a namespace) and `pdf.php` (fills an array, no constants) are never registered. Neither are installer-only files. The registrar has an explicit exclusion list plus a guard: a file whose evaluation throws is logged and skipped, and never breaks the request.

### Smarty translation
`TranslationSmartyExtension` is added in `Icms\View\Tpl::__construct()` with the shared Translator from the container. In a context without the host (tests, the installer) a Translator with the same resources is built locally. The default domain for `<{trans}>` is `icms`. Module templates pass `domain="<dirname>"`.

### `db:` resource via `smarty-db-resource`
`DatabaseResource` is constructed in `Tpl` with:
- the PDO from the shared DBAL connection (`getNativeConnection()`), which keeps one connection per request (`shared-runtime`);
- the table `<prefix>_tplfile` and the existing column names;
- `tplSetName` = `$icmsConfig['template_set']`, default set `default`;
- a `templatePathGetter` that ports `smarty_resource_db_tplinfo`: theme override at `<theme>/modules/<module>/[blocks/]<file>` (checking both `themes/` and `modules/system/themes/`), then `modules/<module>/templates/[blocks/]<file>`. The theme is read when each template is fetched, not at construction.

Two differences from legacy have to be closed before the switch:
1. **DB-only templates in the default set.** Legacy falls back to `tpl_source` when no file exists. The package returns "not found". Fix upstream (fall back to the source column when the getter returns `null`). Until then, a thin subclass `Icms\View\Smarty\DbResource` overrides `fetch()` and falls back to `tpl_source` itself.
2. **Query count.** Legacy prefetches a whole template set with `getPrefetchedBlock()`. The package runs one query per template, and with `compile_check` on, that happens on every render. Measure on the front page and an admin page. If the count rises noticeably, add a per-request memo in the subclass or contribute a prefetch option upstream.

### `xo*` tags via `smarty-xo`
`XOExtension` gets callables backed by existing ImpressCMS code: path = `icms::$urls`/`ICMS_URL` mapping of pseudo-paths, build URL = legacy query builder, image URL = theme image resolution (`xoImgUrl` rules, including the theme `images/` override), inbox = the private-message handler's unread count for the current user, page URL = legacy `xoPageNav` URL scheme. `xoAppUrl` and `xoImgUrl` are compiler tags: static URLs are baked into compiled templates, as before. The compile id already varies per theme, and an `ICMS_URL` change requires wiping `templates_c` (documented in release notes, already true today). `xoops_link` stays an ImpressCMS port.

### `includeq` / `foreachq` with deprecation
Register `IncludeQExtension` and `ForeachQExtension` through subclasses in `Icms\View\Smarty\` that call the deprecation helper from `smarty-5-upgrade` (`E_USER_DEPRECATED`, naming template and replacement) at compile time, then delegate. This covers `smarty-5-upgrade`'s legacy alias requirement with maintained compilers. Bundled templates are still migrated off both tags (that change, task 4.4).

### `resized_image` not adopted
Inline `data:` URIs grow every page that shows thumbnails, defeat browser and proxy caching, and change what `return="url"` gives (a URI instead of a link to `cache/`). The ImpressCMS port from `smarty-5-upgrade` stays. Reconsider once the package offers file output with a public URL and `inside` as a configurable default. That is an upstream proposal, not a blocker.

## Risks / Trade-offs

- **`eval` of language files** → executes PHP from module language directories. That is no new trust boundary, because legacy already includes the same files. Errors are contained per file (logged and skipped).
- **Upstream dependency on fixes** → interim ImpressCMS loader and `DatabaseResource` subclass. Each has a task to remove it once a release is out. Pin minor versions.
- **Different string per call site**: legacy callers see the constant as defined first in the request, while the Translator sees each file's own value → only matters for duplicated constants across files. Surfaced by a check that reports constants defined with different values in more than one file.
- **Catalogue size**: all modules × languages. Catalogues load per locale and domain, so a request pays only for what it uses.
- **PHP 8.3 BREAKING** → hosts still on 8.2 get the requirements page (503) instead of a white page. Release notes and the installer's requirement check name 8.3.

## Migration Plan

1. PR 1: PHP 8.3 baseline (composer, CI, smoke test, notes). It can land independently, before or after `smarty-5-upgrade`.
2. PR 2: Translator, constants loader, interim loader fix, locale map, cache clearing.
3. PR 3 (on top of `smarty-5-upgrade`): `smarty-db-resource`, `smarty-xo`, `includeq`/`foreachq` subclasses, `smarty-translate`.

Rollback: revert the PR, then clear `var/cache` and `templates_c`.

## Open Questions

- Should the module admin offer a "clear translation cache" action, or is clearing on install/update/uninstall enough?
- Smarty 5 ships `debug_print_var` in its default extension. Can the ICMS modifier copy (`modifier.debug_print_var.php`) be dropped without an output change?
- Should new strings in Symfony code go to the constant files (one place for translators, Crowdin unchanged) or to XLIFF/YAML domains? This change assumes the constant files until decided.
