## 1. PHP 8.3 baseline (PR 1)

- [ ] 1.1 `composer.json`: `require.php` `>=8.3 <8.5`, `config.platform.php` `8.3.0`; update the lock
- [ ] 1.2 CI: drop 8.2 from the `tests.yml` matrix; smoke test expects `PHP 8.3.0 or newer`
- [ ] 1.3 Update `REFACTOR_NOTES.md` and the `smarty-5-upgrade` design ("Test on PHP 8.2-8.4") to 8.3-8.4
- [ ] 1.4 Release notes: PHP 8.3 required; hosts on 8.2 get the requirements page

## 2. Upstream fixes (imponeer)

- [ ] 2.1 `symfony-translations-constants-loader`: shadow `defined()` and `constant()` in the virtual namespace (bag first, then globals); add tests for guarded definitions
- [ ] 2.2 `smarty-db-resource`: fall back to the source column when `templatePathGetter` returns `null` or the file is missing in the default set
- [ ] 2.3 `smarty-db-resource` (optional, depending on 5.2): per-instance memo or prefetch of a template set
- [ ] 2.4 `smarty-image` (proposal only): file output with a public URL and a configurable default `fit`

## 3. Translator (PR 2)

- [ ] 3.1 `composer require symfony/translation:7.4.* imponeer/symfony-translations-constants-loader:^2.1`
- [ ] 3.2 `config/packages/translation.yaml`: default locale `en`, fallbacks `[en]`, no `paths` for legacy files
- [ ] 3.3 `App\Translation\ConstantsFileLoader` (interim, until 2.1 is released) registered as the `php_consts` loader
- [ ] 3.4 `App\Translation\LocaleMap`: language dir ↔ locale from `_LANGCODE`, normalised; skip and log dirs without it
- [ ] 3.5 `App\Translation\LegacyTranslationResources`: add core and installed-module files per language on first use after `LegacyRuntime` boot; domains `icms` and `<dirname>`; exclusion list (`xoopsmailerlocal.php`, `pdf.php`, installer files); per-file error containment with logging
- [ ] 3.6 Kernel request listener: set the Translator locale from `$icmsConfig['language']`
- [ ] 3.7 `App\Translation\TranslationCacheClearer`, called on module install/update/uninstall and language changes
- [ ] 3.8 Check: report constants defined with different values in more than one file (console command `icms:translations:lint`)
- [ ] 3.9 Tests: every scenario in `specs/translations/spec.md`, including `cache:warmup` evaluating no language file and the guarded-definition case

## 4. Smarty extensions (PR 3, on top of `smarty-5-upgrade`)

- [ ] 4.1 `composer require imponeer/smarty-translate:^3.0 imponeer/smarty-db-resource:^4.0 imponeer/smarty-xo:^1.1 imponeer/smarty-includeq:^3.0 imponeer/smarty-foreachq:^2.0`
- [ ] 4.2 `Icms\View\Tpl`: register `TranslationSmartyExtension` (shared Translator from the container, local fallback for installer and tests)
- [ ] 4.3 `Icms\View\Smarty\DbResource` (subclass of `DatabaseResource`): shared PDO via `getNativeConnection()`, `<prefix>_tplfile`, `templatePathGetter` porting `smarty_resource_db_tplinfo` (theme override in `themes/` and `modules/system/themes/`, then module templates); interim DB-source fallback until 2.2 is released
- [ ] 4.4 Callbacks for `XOExtension` (path, URL builder, image URL with theme override, inbox count, page URL); register on `Tpl`
- [ ] 4.5 Deprecating subclasses of `IncludeQExtension` / `ForeachQExtension` using the `smarty-5-upgrade` deprecation helper
- [ ] 4.6 Keep the `xoops_link` and `resized_image` ports; remove the replaced ICMS ports (`resource.db`, `compiler.xoAppUrl`, `compiler.xoImgUrl`, `function.xoPageNav`, `function.xoInboxCount`, `compiler.includeq`, `compiler.foreachq`)
- [ ] 4.7 Mark `smarty-5-upgrade` tasks 3.1, 3.2 (except `xoops_link`, `resized_image`), 4.1 and 4.2 as covered by this change

## 5. Verify

- [ ] 5.1 Output snapshots (before/after) of templates using each `xo*` tag, `includeq`, `foreachq` and `db:` in default and custom sets with and without theme overrides
- [ ] 5.2 Count template queries on the front page and an admin page, before and after; decide on 2.3
- [ ] 5.3 Tests: every scenario in `specs/template-engine/spec.md`
- [ ] 5.4 Manual pass: frontend theme, admin (iTheme), tplsets admin with a custom set, a module page, a page in `nederlands` and `schinese`
- [ ] 5.5 Replace the interim loader and DB fallback once 2.1 and 2.2 are released; bump constraints
- [ ] 5.6 `openspec validate adopt-imponeer-libraries` and `bin/spec-coverage` without gaps
