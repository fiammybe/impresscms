# Upgrading templates and modules to Smarty 5

ImpressCMS 3.0 runs Smarty 5 (installed through Composer, `smarty/smarty ^5.8.5`)
instead of the Smarty 2.6 copy that used to live in `htdocs/libraries/smarty/`.
The delimiters stay `<{` and `}>`. Most templates keep working: the ImpressCMS
specific syntax of 2.x is rewritten when a template is compiled, and the old
`Icms\View\Tpl` method names still exist. Both log a deprecation notice
(`E_USER_DEPRECATED`, in `<trust path>/var/log/`), so you can find what to update.

## What still works, with a deprecation notice

| Old | Now | Notes |
|---|---|---|
| `<{includeq file="x.html"}>` | `<{include file="x.html"}>` | Variables set inside the included template no longer leak into the caller; use `<{include ... scope="parent"}>` if you relied on that |
| `<{foreachq from=$a item=i}>` | `<{foreach from=$a item=i}>` | closing tag is `<{/foreach}>` in both |
| `<{xoImgUrl icons/a.png}>` | `<{xoImgUrl path="icons/a.png"}>` | |
| `<{xoAppUrl modules/x/y.php a=1}>` | `<{xoAppUrl url="modules/x/y.php" a=1}>` | `<{xoAppUrl /}>` becomes `<{xoAppUrl url="/"}>` |
| `<{cycle values=odd,even}>` | `<{cycle values="odd,even"}>` | the list must be quoted |
| `$tpl->assign_by_ref($n, $v)` | `$tpl->assignByRef($n, $v)` | `assignByRef()` is an ImpressCMS method; Smarty 5 has none |
| `clear_cache`, `clear_all_cache`, `clear_compiled_tpl` | `clearCache`, `clearAllCache`, `clearCompiledTemplate` | |
| `get_template_vars`, `template_exists`, `is_cached` | `getTemplateVars`, `templateExists`, `isCached` | |
| `register_function`, `register_modifier`, `register_block` | `registerPlugin('function' \| 'modifier' \| 'block', ...)` | |

## What no longer works

* `<{php}>…<{/php}>` and `<{include_php}>`. Assign the value from PHP instead
  (`$tpl->assign('token', icms::$security->createToken())`).
* Smarty 2 internals: `$tpl->_tpl_vars`, `$tpl->_plugins`, `_compile_resource()`,
  `$this->_foreach[...]`, and compiler plugins that receive the raw argument string.
* The `SMARTY_DIR` constant, `htdocs/libraries/smarty/`, and plugin files that
  `require` Smarty 2 helper files (for example `shared.escape_special_chars.php`).
* PHP functions as modifiers, except the few Smarty 5 ships (for example `substr`,
  `strlen`, `isset`, `empty`, `is_array`, `round`, `json_encode`, `str_repeat`, `nl2br`,
  and `@count` on arrays). Register your own with
  `$tpl->registerPlugin('modifier', 'name', 'php_function')`.

## Behaviour kept on purpose

* Unassigned variables render as an empty string, as in Smarty 2
  (`muteUndefinedOrNullWarnings()`).
* `<{ if $x }>` with a space after the delimiter is still a tag (`auto_literal` is off).

## Where the ImpressCMS additions live

`htdocs/libraries/Icms/View/Smarty/`: `Setup` (registers everything on the engine),
`Functions` (`xoAppUrl`, `xoImgUrl`, `xoInboxCount`, `xoPageNav`, `xoops_link`,
`resized_image`), `DbResource` (the `db:` resource) and `LegacySyntaxFilter` (the
rewrites in the first table).

## Upgrading a site

The system module update empties `templates_c/` and `cache/`; run it after
copying the new files. Templates stored in the database are compiled again on
first use.
