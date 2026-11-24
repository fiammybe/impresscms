# Templating in `htdocs/libraries/Icms`

Library classes do not build presentational HTML in PHP strings. They render it with a template of the
system module (`htdocs/modules/system/templates/`, Smarty 2.6.33, delimiters `<{` and `}>`), registered in
`htdocs/modules/system/icms_version.php` (`$modversion["templates"]`), through one helper:

```php
return \Icms\View\SystemTemplate::fetch('icms_form_elements_select_display.html', ['field' => $field->toArray()], $this->customTemplate);
```

`SystemTemplate::fetch()` reads the template through the `db:` resource, so template sets and theme overrides
(`themes/<theme>/modules/system/<template>`) apply. A template that is not in the database yet (the system
module was not updated after an upgrade) is read from its file in `modules/system/templates/`, so forms,
hidden fields and security tokens keep working until the update. For the same reason a template never includes
another one with a literal `db:` name: PHP passes the resource (`SystemTemplate::resource()`) in a variable,
`<{include file=$row.template ...}>`.

Naming: `icms_<package>_<class>_<variant>.html`. Smarty 2 drops the final newline of a template; end a template
with `<{"\n"}>` where the output must end with one.

## Forms: one definition, two renderers

Form controls and IPF entities are described by a neutral definition (`Icms\Form\Definition`), which both the
legacy Smarty templates and the Symfony form bridge read:

```text
IPF entity metadata ─ Icms\Ipf\Form\DefinitionBuilder ─┐
                                                       ├─ Definition\Form / Field ─┬─ Smarty: Icms\Form\View\FormView + templates
legacy Icms\Form\Element ─ Element::getDefinition() ───┘                           └─ Symfony: App\Ipf\Form\ControlMap / IpfEntityType
```

| Class | Properties |
|---|---|
| `Field` | `name` (submitted name, without `[]`), `type` (logical type), `label`, `description`, `value` (raw, as the control holds it), `required`, `disabled`, `hidden`, `attributes` (presentation hints: `size`, `maxlength`, `rows`, `cols`, `id`, ...), `constraints` (`maxlength`, `integer`, `number`), `errors`, `extra` (the legacy raw attribute string of `setExtra()`; only the legacy templates use it) |
| `ChoiceField` | adds `options` (`Choice`: `value`, `label`, `group`), `selected`, `multiple`, `size` |
| `CompoundField` | adds `children` by role (`upload`, `url`, `delete`, `code`, ...) |
| `Form` | `name`, `title`, `action`, `method`, `rows` (visible fields and sections, in order), `hidden`, `actions` (buttons), `extra` |

Logical types: `text`, `password`, `textarea`, `hidden`, `label`, `button`, `checkbox`, `radio`, `yesno`,
`select`, `file`, `image`, `page`, `captcha`, `groupperm`, `tray`, `actions`, `section`, `section_close` and
`legacy`. The definition classes have no Smarty, Symfony or HTML dependency (tested).

### Controls

Every `Icms\Form\Element` implements `Describable::getDefinition()`. A class describes its control by
overriding `describe()`; `definitionTemplate()` names its template and `viewData()` adds presentation data.
When a subclass overrides `render()` below the class that describes the control (`Dhtmltextarea`, `Date`,
`Colorpicker`, module controls), the definition is `Field::legacy()` (type `legacy`, attribute `class`):
renderers that need a definition refuse it instead of guessing. A module control opts in by overriding
`describe()` in the class that renders it.

| Control | Definition | Template |
|---|---|---|
| `Select` (and its subclasses that do not render themselves: `Select\Country`, `Select\Group`, `Select\Timezone`, ..., IPF `Select`, `Selectmulti`; `Select\Image` renders itself and is legacy) | `ChoiceField` `select` | `icms_form_elements_select_display.html` |
| `Textarea` | `Field` `textarea` (`attributes`: `id`, `rows`, `cols`) | `icms_form_elements_textarea_display.html` |
| `Hidden`, `Hiddentoken` | `Field` `hidden` (`attributes.multiple` for an array value) | `icms_form_elements_hidden_display.html` |
| `Groupperm` | `ChoiceField` `groupperm` (`attributes`: `group_id`, `tree`) | `icms_form_elements_groupperm_display.html` (`view`: `rows`, `inputs`, `checkall`, `all_label`) |
| `Captcha\Image`, `Captcha\Text` | `CompoundField` `captcha` (child `code`; `attributes.mode`) | `icms_form_elements_captcha_{image,text}_display.html` (`view`: `rule`, `hint` / `expression`, `hint`) |
| IPF `File` | `CompoundField` `file` (child `upload`; `value`: current file) | `icms_ipf_form_elements_file_display.html` (`view.current_label`) |
| IPF `Image` | `CompoundField` `image` (children `upload`, `url`, `delete`; `attributes`: `image_type`, `image_url`) | `icms_ipf_form_elements_image_display.html` (`view.parts`) |
| IPF `Page` | `ChoiceField` `page` (`visiblein`, options grouped by module) | `icms_ipf_form_elements_page_display.html` |
| `Text`, `Password`, `Checkbox`, `Button` | `Field` / `ChoiceField` | their released templates with `ele_*` variables (unchanged) |
| `Label`, `Radio`, `Radioyn`, `File`, `Tray`, IPF `Section`, `Upload`, `Fileupload` | `Field` / `ChoiceField` / `CompoundField` | no template of their own (HTML from `render()`) |

A control template receives `field` (`Field::toArray()`: the properties above; a `ChoiceField` adds `options`
with `selected`, `groups` with `label` and `options`) and `view`. `customTemplate` on the element replaces the
template; the replacement gets the same variables.

Escaping is the template's job and reproduces each control's legacy output: `select` escapes names, values
and labels; `hidden` and `textarea` output values unescaped, because callers pass them encoded
(`getVar($key, 'e')`); labels and descriptions of rows may contain HTML, as legacy captions do.

### Layouts

`Theme`, `Table`, `Simple`, `GroupPermission` and `Icms\Ipf\Form\Base` render once with a layout template
(`$form->customTemplate` replaces it). The template receives `form` (`Icms\Form\View\FormView::form()`):

| Key | Content |
|---|---|
| `name`, `title`, `action`, `method`, `extra`, `align` | the form tag (`extra`: legacy raw attributes, e.g. `enctype`) and the text alignment |
| `elements` | all controls, in order (hidden ones included) |
| `rows` | the visible controls and the HTML added as a string (`insertBreak()`, `addElement('<tr>...')`), in order |
| `hidden` | the hidden controls |
| `validation` | `required` (names), `javascript` (the legacy validation function) and `script` (it in its script tags, `icms_form_validation_script.html`): compatibility values until validation moves to the definitions |
| `definition` | `Definition\Form::toArray()` |
| `description` | the permission description (`icms_form_groupperm.html` only) |

Every control is an array with `type` (the logical type, or `raw`), `field`, `view`, `template` (the resource
of its template, when it has one) and `html` (its own HTML, only for controls without a template and for raw
rows). A layout renders a control with:

```smarty
<{if $row.template}><{include file=$row.template field=$row.field view=$row.view}><{else}><{$row.html}><{/if}>
```

so designers can change both the layout and every control, and a layout can render a control differently from
its `field` alone (for example `<{foreach from=$row.field.options item=option}>`), without parsing HTML.

`assign()` (used by `system_common_form.html` and module templates) is unchanged and adds `field` (the
definition) to every element.

### IPF entities and Symfony

`Icms\Ipf\Form\DefinitionBuilder` holds the rules of the IPF form: `plan()` (hidden, control, skip, or no
control for every variable), `fields()`, `permissions()`, `options()` and `build()`. `Icms\Ipf\Form\Base` creates
its elements and permission selects from it; `App\Ipf\Form\IpfEntityType` builds its fields from the same
definitions, `ControlMap::fromDefinition()` translates one into a Symfony type, options and constraints, and
`IpfEntityDataMapper` stays the only boundary between entity values and submitted values. A definition that
ControlMap does not support (legacy controls, numbers, compound fields) raises
`UnsupportedIpfControlException`; nothing is skipped silently. Sections are layout and have no Symfony field.
Form extensions that add fields to entity forms (such as a capability extension) should build them from
definitions and go through `ControlMap::fromDefinition()` as well, rather than mapping controls themselves.

## Backward compatibility contract

Stable: constructors, `render()`, `display()`, `assign()` and its keys, getters and setters, `customTemplate`,
`insertBreak()`, `getElements()` (strings stay strings), form and input names, ids, tokens, the POST structure,
permission storage and the validation JavaScript. `Icms\Form\Elements\Captcha\Image::loadImage()` is
deprecated but kept; the internal `Groupperm::_renderOptionTree()` was removed.

The templates and variables listed here are new in this release and become contract once it is released.
From then on templates and their variables only grow: existing names, types and meanings do not change.
The `ele_*` variables of the released element templates (text, password, checkbox, button, passwordtray) stay
as they are.

Output: the controls render byte for byte as before; the layouts render the same markup (whitespace may
differ). `htdocs/tests/Unit/Form/LegacyOutputTest.php` compares them with output captured from pre-3 before
the templates (`htdocs/tests/Support/capture-form-output.php`). Intentional differences: the page navigation
with images no longer prints a stray `</a>`, `selected=selected` in the page lists is quoted, and the IPF
validation script comment reads "Validation".

## Template reference

| Template | Used by | Variables |
|---|---|---|
| `icms_form_theme.html`, `icms_form_table.html`, `icms_form_simple.html`, `icms_form_groupperm.html`, `icms_ipf_form_base.html` | form layouts | `form` (above) |
| `icms_form_break_row.html` | `Theme::insertBreak()`, `Table::insertBreak()` (rendered when the break is added) | `extra`, `class` |
| `icms_form_validation_script.html` | `renderValidationJS(true)`, `form.validation.script` | `validation.javascript` |
| control templates | see "Controls" | `field`, `view` |
| `icms_data_page_seloptions.html` | `Icms\Data\Page\Handler::getPageSelOptions()` (options of `getPageChoices()`) | `field` |
| `icms_view_pagenav_text.html` | `Icms\View\PageNav::renderNav()` | `style`, `stylesheet_url` (empty when a theme is present), `stylesheet_template`, `items[]` (`type`: prev, prev_disabled, current, page, ellipsis, next, next_disabled; `url`, `label`, `arrow`) |
| `icms_view_pagenav_select.html` | `renderSelect()` | `options[]` (`value`, `label`, `selected`), `showbutton`, `go_label` |
| `icms_view_pagenav_image.html` | `renderImageNav()` | `items[]`, `blank_img` |
| `icms_view_pagenav_stylesheet.html` | included by `icms_view_pagenav_text.html` | `stylesheet_url` |
| `icms_ipf_controller_{link,imagelink}.html` | `Icms\Ipf\Controller` | `url`, `label` / `image_url`, `title` |
| `icms_view_tree_{selbox,pathlink}.html` | `Icms\View\Tree`, `Icms\Ipf\Tree` | `name`, `id`, `onchange`, `options[]` / `crumbs[]` (`url`, `label`), `separator`, `lead`, `tail` |
| `icms_view_pagebuilder_blockedit.html` | `Icms\View\PageBuilder::buildBlock()` | `bid`, `rtn`, `is_custom`, `admin_url`, `images_url`, `lang_*` |
| `icms_data_comment_{status,adminfooter,posterlink}.html` | `Icms\Data\Comment\Renderer` (status labels and poster links rendered once per status and user) | `status`, `label` / `status_label`, `ip`, `bottom_margin`, `status_template` / `url`, `uname` |
| `icms_data_{file,urllink}_link.html`, `icms_member_userlink.html`, `icms_module_{message,mainlink}.html` | object `render()` and link helpers | see templates |

## Guard

`php bin/find-hardcoded-html.php --check=bin/hardcoded-html-baseline.txt` (CI: *Hardcoded HTML guard*) fails when
a library file gets more string literals containing HTML tags than recorded in the baseline. After migrating a
file, regenerate the baseline with `php bin/find-hardcoded-html.php --format=baseline > bin/hardcoded-html-baseline.txt`.
Files that keep markup (sanitisers, bootstrap/error output) are listed in the OpenSpec inventory
(`openspec/changes/replace-hardcoded-html-with-smarty/inventory.md`).
