## Why

The Symfony migration keeps porting XOOPS-era building blocks by hand: the Smarty `db:` resource, the `xo*` tags, `includeq`/`foreachq`, and soon the language files. The imponeer organisation already maintains most of these as standalone, tested Composer packages built for Smarty 5 and Symfony. They grew out of ImpressCMS and XOOPS and are released under permissive licences. Using them shrinks the `smarty-5-upgrade` port work and moves code ImpressCMS has to maintain into versioned dependencies.

Translations are the larger gap. Legacy strings are PHP constants (`define('_US_NICKNAME', ...)`), loaded by `icms_loadLanguageFile()`. A constant can hold only one language per request, and Symfony controllers and Twig templates cannot reach the strings through the standard `TranslatorInterface`. `imponeer/symfony-translations-constants-loader` reads those same files into the Symfony Translator, so legacy, Smarty and Twig share one set of strings without converting about 260 language files.

## What Changes

- **BREAKING**: raise the PHP minimum from 8.2 to 8.3. Every imponeer package requires PHP 8.3, and PHP 8.2 loses security support on 2026-12-31. The requirements page picks up the new minimum from Composer by itself (`host-deployment`), so no code changes there.
- Add a **shared Symfony Translator** that loads the legacy language files of the core and of every installed module through `imponeer/symfony-translations-constants-loader`. The locale comes from each language's `_LANGCODE`, with English as the fallback. Legacy code keeps using constants. Nothing changes for modules.
- Register **`imponeer/smarty-translate`** on `Icms\View\Tpl`, which gives templates a `<{trans}>` block and a `|trans` modifier that use the same Translator.
- Build the Smarty 5 integration from imponeer extensions instead of in-house ports:
  - `db:` resource from `imponeer/smarty-db-resource`, with ImpressCMS's theme-override and module-path rules in its path callback;
  - `xoAppUrl`, `xoImgUrl`, `xoPageNav` and `xoInboxCount` from `imponeer/smarty-xo`, with ImpressCMS's URL, image, pagination and inbox services as callbacks;
  - `includeq` and `foreachq` from `imponeer/smarty-includeq` and `imponeer/smarty-foreachq`, extended so each use still logs the deprecation notice that `smarty-5-upgrade` requires.
- Keep ImpressCMS ports for what the packages do not cover or would change: `xoops_link` (not in `smarty-xo`) and `resized_image`. `imponeer/smarty-image` inlines resized images as `data:` URIs instead of writing them to `cache/`, so for now it is not adopted.
- Contribute upstream fixes that the constants loader needs to read ImpressCMS language files reliably (see design).

## Capabilities

### New Capabilities
- `translations`: One Symfony Translator, fed by the legacy constant-based language files of the core and installed modules, usable from Symfony code, Twig and Smarty, in any installed language within one request.

### Modified Capabilities
- `template-engine`: ImpressCMS template tags and the `db:` resource come from imponeer Smarty extensions wired to ImpressCMS services, instead of only from classes under `htdocs/libraries/Icms/View/Smarty/`. Templates gain `<{trans}>`. (This delta applies on top of the `template-engine` spec as `smarty-5-upgrade` leaves it, so `smarty-composer-2x` and `smarty-5-upgrade` must be archived first.)

## Impact

- **Dependencies**: `symfony/translation` 7.4, `imponeer/symfony-translations-constants-loader` ^2.1, `imponeer/smarty-translate` ^3.0, `imponeer/smarty-db-resource` ^4.0, `imponeer/smarty-xo` ^1.1, `imponeer/smarty-includeq` ^3.0, `imponeer/smarty-foreachq` ^2.0. `config.platform.php` becomes 8.3.0.
- **Ordering**: comes after `smarty-5-upgrade` (Smarty ^5.8). Implemented before that change's tasks 3.1, 3.2 (minus `xoops_link` and `resized_image`), 4.1 and 4.2, it takes their place. They then do not need to be built twice.
- **Host code**: `src/Translation/*` (resource registrar, locale map, cache invalidation), Translator configuration in `config/packages/translation.yaml`.
- **Library (`Icms\`)**: `Icms\View\Tpl` registers the extensions. `htdocs/libraries/Icms/View/Smarty/` keeps only adapters (path, URL, inbox callbacks, deprecating `includeq`/`foreachq` subclasses) plus the `xoops_link` and `resized_image` ports.
- **CI**: the PHP matrix drops 8.2. The requirements smoke test expects 8.3.0.
- **Modules and themes**: no changes required. Third-party code that runs on PHP 8.2 must move to 8.3.
- **Out of scope** (later changes): `extension-info-contracts` / `extensions-setup-contracts` (module installer), `editor-contracts` (editor integration), `criteria` (Doctrine DBAL is the target for queries), `object-errors`, `toarray-interface`, `log-data-output-decorator`, `smarty-image`, `smarty-debug`, and replacing constants in legacy code with translator calls.
