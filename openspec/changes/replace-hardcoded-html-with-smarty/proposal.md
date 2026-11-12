# Change: Identify hardcoded HTML in the ICMS library and replace it with Smarty templates

## Why
`htdocs/libraries/icms/` still builds a large amount of markup via string concatenation
and `echo` (e.g. `core/Logger_render.php`, `form/elements/Dhtmltextarea.php`, `view/PageNav.php`,
`ipf/Controller.php`, `member/user/Handler.php`, `core/Message.php`, `view/theme/Object.php`).
This makes the output impossible for theme designers to override, hard to escape consistently
(XSS risk), hard to test and hard to move towards a cleaner MVC separation.
A few classes (`form/elements/Text`, `Button`, `Checkbox`, `Password`, `Passwordtray`,
`ipf/About`, `ipf/view/Single|Table`) already render through `icms_view_Tpl` and
`db:`/`file:` Smarty templates — that is the pattern to extend.

## What Changes
1. **Inventory (phase 0, deliverable)**: a reproducible scan produces
   `openspec/changes/replace-hardcoded-html-with-smarty/inventory.md`, listing every
   file/method in `htdocs/libraries/icms/` that emits HTML, classified per the rules in `design.md`.
2. **Migration (phases 1–4)**: classes classified *Migrate* get their markup moved into
   Smarty templates, rendered through `icms_view_Tpl`, with the same output (visual/semantic parity).
3. **Exceptions are documented**: classes classified *Keep* (e.g. pure low-level helpers,
   emergency output before Smarty is available) are listed with a reason.
4. **Guard (phase 5)**: a CI/lint script flags *new* hardcoded HTML in the library
   (baseline-based, so existing *Keep* items do not fail the build).

Initial hotspot ranking (lines containing HTML tags, indicative only — the inventory is authoritative):

| Priority | File | ~HTML lines |
|---|---|---|
| High | `form/elements/Dhtmltextarea.php` | 31 |
| High | `view/PageNav.php` | 27 |
| High | `ipf/Controller.php` | 23 |
| High | `member/user/Handler.php` | 22 |
| Medium | `view/theme/Object.php`, `core/Message.php`, `core/Filesystem.php`, `form/Base.php`, `ipf/form/Base.php`, `form/Theme.php`, `data/page/Handler.php`, `data/comment/Renderer.php`, `view/PageBuilder.php`, `ipf/form/elements/Page.php`, `form/Table.php`, `form/elements/Groupperm.php` | 11–20 each |
| Low | remaining `form/elements/**`, `ipf/form/elements/**`, `view/Tree.php`, `ipf/Tree.php`, `view/block/*`, `file/MediaUploadHandler.php`, etc. | 1–10 each |
| Keep | `core/Logger.php`, `core/Logger_render.php` (out of scope, dev-only), `core/DataFilter.php` (HTML *filtering/sanitising*, not rendering), `core/Textsanitizer.php`, `core/Debug.php`, `core/StopSpammer.php` | n/a |

## Impact
- Affected spec: `library-templating` (new capability).
- Affected code: `htdocs/libraries/icms/**`; new templates in `htdocs/modules/system/templates/`
  (existing convention for `icms_form_elements_*_display.html`) and theme-overridable paths.
- **BREAKING (minor, mitigated)**: subclasses/modules that override `render()` or parse the
  returned HTML string keep working because public method signatures and returned strings
  stay the same; only the internal construction changes. Template names become a new public
  extension point and must be documented.
- Requires template installation/update during module (system) update — see `tasks.md` §6.
- Templates are written for Smarty 2.6.31 (bundled with ImpressCMS 2.0.4) and stored in `htdocs/modules/system/templates/`.
