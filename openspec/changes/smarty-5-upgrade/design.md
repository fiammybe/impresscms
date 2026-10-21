## Context

Stage 2 of 2. Starts from Smarty 2.6.33 in Composer with ICMS plugins in `Icms\View\Smarty\plugins` (see `smarty-composer-2x`). Delimiters are `<{ }>`. Templates come from files and from the DB (`db:` resource). Current internals reliance: `Tpl::fetchFromData` uses `smarty_function_eval` and `$_tpl_vars`; `Tpl::touch` uses `_compile_resource` / `_get_compile_path`; the compilers emit `$this->_smarty_include`, `$this->_foreach` and `$this->_tpl_vars`, all gone in Smarty 5.

## Goals / Non-Goals

**Goals:** run on Smarty 5.8 with minimal template churn; legacy aliases for third-party code with deprecation logging.
**Non-Goals:** converting every template to modern syntax; removing the aliases (later major release); changing delimiters.

## Decisions

- **Aliases by compile-time rewrite, not emulation.** A Smarty pre-filter (`LegacySyntaxFilter`) rewrites `includeq`/`foreachq` to `include`/`foreach`, free-form `xoImgUrl`/`xoAppUrl` arguments to `path=`/`url=` attributes, and unquoted `cycle values=a,b`, and logs a deprecation per compiled template. Smarty 5 cannot take free-form arguments or the old compiler-plugin signature, so a rewrite is the only way to keep third-party templates working. Bundled templates are migrated off them.
- **`auto_literal` off.** Smarty 2 accepted `<{ if ...}>`; Smarty 5 treats it as text unless `setAutoLiteral(false)`. With the `<{ }>` delimiters there is no JS/CSS collision, so the legacy behaviour is kept.
- **`assignByRef()` on `Tpl`.** Smarty 5 dropped `assign_by_ref`; core callers rely on later changes showing up, so `Tpl::assignByRef()` shares the `Variable::$value` reference. `assign_by_ref()` stays as a deprecated wrapper.
- **One setup entry point.** `Icms\View\Smarty\Setup::apply()` registers the pre-filter, the tags and (optionally) the `db:` resource, so `Tpl` and the tests configure an engine the same way.
- **Upgrade step:** the system module update already empties `templates_c` and `cache` on every run, so no new step is needed.
- **Legacy method aliases in `Tpl`** via explicit wrapper methods (not `__call` magic) so grep and static analysis can find them; each calls a single `deprecated()` helper that uses `trigger_error(E_USER_DEPRECATED)` plus logging.
- **DB resource as `CustomPlugin`** (`fetch()` returns source and mtime), reusing the existing `smarty_resource_db_tplinfo` logic moved into the class.
- **String templates:** `fetchFromData()` uses the in-memory `eval:` resource with a child data scope (`createData()`) instead of mutating `$_tpl_vars`.
- **`touch()`** clears the cache of the resource and calls `compileTemplateSource()`, returning `false` on a `Smarty\Exception` (`compileTemplateSource()` itself returns nothing).
- **`{php}` templates:** the `theme_admin.html` block only set `site_msg`, which nothing reads (`header.php` and `cp_functions.php` already show and clear the redirect message), so it is removed. The notification token is assigned as `$xoops_notification['token']` in `include/notification_select.php`. `filter_image.html` gets `filters` and `filter_select` from `image-edit.php`, built by `icms_imageeditor_filterDefinitions()`.
- **Modifiers:** the bundled templates only use `substr` and `@count`, which Smarty 5 provides, so nothing is registered. Third-party templates that call other PHP functions as modifiers must register them (see `docs/smarty-5-upgrade.md`).
- **Security policy:** do not enable Smarty security by default; keep the current trust model.

## Risks / Trade-offs

- Third-party templates using `{php}` or arbitrary PHP modifiers break -> document in release notes; the alias layer covers tags and methods only.
- Variable scoping differs (Smarty 5 data/scope objects, `{include}` scope rules) -> test includes with passed vars in admin and a theme.
- `foreachq` only supported `iteration`/`total`; verify templates using `@iteration`/`@total` map to Smarty 5 `foreach` properties.
- Spec merge order: this change MODIFIES a requirement created in `smarty-composer-2x`; archive that change first.
- Test on PHP 8.2-8.4.

## Migration Plan

One PR to `pre-3`, after stage 1 is merged. The upgrade script wipes `templates_c`. Rollback by revert plus clearing `templates_c`.

## Open Questions

- Which PHP-function modifiers do bundled and common third-party templates actually use? (inventory before coding)
- Should deprecations also surface in the debug bar when `debug_mode` is on?
