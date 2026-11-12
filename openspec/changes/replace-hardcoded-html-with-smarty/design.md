## Context
`icms_view_Tpl extends Smarty` (`view/Tpl.php`). Existing pattern (see `form/elements/Text.php`):

```php
$this->tpl = new icms_view_Tpl();
$this->tpl->assign('ele_name', $this->getName());
$element_html_template = $this->customTemplate ?: 'icms_form_elements_text_display.html';
return $this->tpl->fetch('db:' . $element_html_template);
```
Templates live in `htdocs/modules/system/templates/` and are loaded as `db:` templates (Smarty 2.6.31).

## Goals / Non-Goals
- Goals: find all hardcoded HTML; move presentational markup to Smarty; keep output
  and public APIs stable; make escaping explicit in templates.
- Non-Goals: redesigning markup/CSS, changing form APIs, rewriting `icms_ipf_*` architecture,
  touching modules or themes outside the library (except adding templates).

## Decision 1: Detection method (inventory)
A script `bin/find-hardcoded-html.php` (or a shell/ripgrep one-liner as MVP) scans
`htdocs/libraries/icms/**/*.php` for:
- `echo|print` followed by a string starting with `<`;
- string literals / heredocs containing opening tags from an allow-list
  (`div table tr td th form input select option textarea a span ul ol li img p br h1-h6 script style label button iframe`);
- concatenations assigning tag fragments (`$x .= '<...`).
Output: `file:line`, enclosing class::method, tag count, snippet. Comments/docblocks are stripped
first (use `token_get_all`, only inspect `T_CONSTANT_ENCAPSED_STRING`, `T_ENCAPSED_AND_WHITESPACE`,
`T_INLINE_HTML`, `T_START_HEREDOC` content) to avoid false positives.

## Decision 2: Classification
| Class | Meaning | Action |
|---|---|---|
| **Migrate** | Renders UI markup (forms, nav, tables, messages, pagination, debug panels) | Move to Smarty template |
| **Partial** | Mixed logic + a few inline tags (`<br />`, `<b>`) | Move if >1 tag or any attribute; otherwise Keep |
| **Keep** | Sanitising/filtering code that *handles* HTML (DataFilter, Textsanitizer regexes), plain-text/email bodies, output before Smarty is bootable (fatal-error/maintenance fallbacks), JS/CSS payloads better served as assets | Document reason in inventory |
| **Asset** | Inline `<script>`/`<style>` blocks | Extract to a static file or a `.js`/`.css` Smarty template, not inline PHP strings |

## Decision 3: Template conventions
- Name: `icms_<package>_<class>_<variant>.html` (extends existing `icms_form_elements_*_display.html`),
  e.g. `icms_view_pagenav.html`, `icms_core_message_error.html`, `icms_core_logger_render.html`.
- Location: `htdocs/modules/system/templates/` (db: templates, theme-overridable via template sets).
- Every class gets a `protected $customTemplate`-style override where it does not already exist.
- Escaping: assigned values are **pre-escaped by default in PHP only when they already were**;
  new templates use `|escape` for plain-text values and leave known-safe HTML (e.g. rendered
  child elements) un-escaped, with a comment in the template. No behaviour change for existing output.
- One `icms_view_Tpl` instance per render call is acceptable; prefer reusing a shared instance
  for hot paths (`PageNav`, `Tree`, form elements rendered in loops) to avoid compile overhead.
  Caching stays off unless the caller already enables it.

## Decision 4: Parity verification
For each migrated class: capture HTML before (golden file, whitespace-normalised) and
after; tests compare normalised output. Where whitespace differences exist but DOM is identical, accept.

## Risks / Trade-offs
- Performance: Smarty fetch per element in large forms → mitigate with shared Tpl instance and compiled templates.
- Templates stored in DB: need an update path (system module update + template set sync) → tasks §6.
- Bootstrapping: error pages that may run before Smarty is ready → classified Keep.
- Third-party code relying on exact whitespace → covered by parity tests; document in changelog.

## Migration plan
Phased by priority so each phase is independently shippable: 0 inventory → 1 forms →
2 view (PageNav, Tree, Theme, PageBuilder) → 3 ipf → 4 core (Message, Filesystem…) →
5 guard/lint → 6 docs & changelog. Roll back = revert the phase's commit; PHP signatures unchanged.

## Resolved Decisions
- **Template location**: all new templates go in `htdocs/modules/system/templates/`, next to the existing `icms_form_elements_*` templates. No library-owned template directory is created.
- **Smarty version**: Smarty 2.6.31 as bundled with ImpressCMS 2.0.4 (`htdocs/libraries/smarty`). Templates use Smarty 2 syntax only, with the ImpressCMS delimiters `<{` and `}>` (`<{$var}>`, `<{if}>`, `<{foreach from=... item=...}>`, `|escape`); no Smarty 3+ features.
- **Logger/debug output out of scope**: `core/Logger.php` and `core/Logger_render.php` are not migrated (dev-only output); they are listed as Keep in the inventory.

## Open Questions
- None.
