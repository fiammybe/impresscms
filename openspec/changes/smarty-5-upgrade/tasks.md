## 1. Inventory

- [x] 1.1 List modifiers/functions used by all bundled templates; decide which to register (only `substr` and `@count`; both supported)
- [x] 1.2 List every PHP call site of the Smarty 2 API (`Tpl`, `$xoopsTpl`, `$icmsTpl`) and classify (callers: assign_by_ref, clear_cache, clear_compiled_tpl, is_cached, get_template_vars, 8 files)

## 2. Engine switch

- [x] 2.1 `composer require smarty/smarty:^5.8`; update lock (`^5.8.5`; the temporary advisory ignore from stage 1 is removed)
- [x] 2.2 Rewrite `Icms\View\Tpl` on `Smarty\Smarty` (delimiters, dirs, compile id, caching, debugging)
- [x] 2.3 Replace `fetchFromData()` and `touch()` internals (`eval:` resource, `compileTemplateSource()`)
- [x] 2.4 Remove `SMARTY_DIR`

## 3. Plugins and resource

- [x] 3.1 Port the `db:` resource to a `CustomPlugin` class (`Icms\View\Smarty\DbResource`)
- [x] 3.2 Port `xoAppUrl`, `xoImgUrl`, `xoPageNav`, `xoInboxCount`, `xoops_link`, `resized_image` (registered by `Icms\View\Smarty\Setup`)
- [x] 3.3 Register needed modifiers (`substr`, others from 1.1) (nothing to register: `substr` is built in)

## 4. Legacy aliases

- [x] 4.1 `includeq` -> `include` alias with deprecation notice (pre-filter rewrite, `Icms\View\Smarty\LegacySyntaxFilter`)
- [x] 4.2 `foreachq` -> `foreach` alias with deprecation notice (same pre-filter)
- [x] 4.3 Snake_case method wrappers on `Tpl` with deprecation notice
- [x] 4.4 Migrate bundled templates off `includeq`/`foreachq`

## 5. Callers and templates

- [x] 5.1 Update the PHP callers found in 1.2 (tplsets admin, `cp_functions`, `header.php`, `Theme/Entity`, `PageBuilder`, ...)
- [x] 5.2 Remove `<{php}>` from `theme_admin.html`, `system_notification_select.html`, `filter_image.html`
- [x] 5.3 Fix `cycle`/`counter`/`section`/`@iteration` usages that behave differently (one unquoted `cycle values=`, and `<{ if }>` with a space, handled by `auto_literal`; all 91 bundled templates compile)

## 6. Upgrade and verify

- [x] 6.1 Upgrade step wiping `templates_c` (already done by the system module update, which empties `templates_c` and `cache`)
- [x] 6.2 Tests: aliases (assert deprecation), legacy rewrites, tag registration, modifiers, auto-literal. Not unit-tested (need a booted CMS): `db:` resource and `touch()` (verified on lar.test via the system module update), `fetchFromData()` (not exercised), the tag functions themselves
- [x] 6.3 Manual pass on lar.test: frontend theme, admin dashboard, tplsets, blocks, mime types, image manager list, preferences, system module update. Only compile-checked, not rendered: notification select, image editor filter plugin
- [x] 6.4 Release notes: breaking changes for third-party templates (`docs/smarty-5-upgrade.md`)
- [ ] 6.5 Open PR targeting `pre-3` (after stage 1 is merged)
