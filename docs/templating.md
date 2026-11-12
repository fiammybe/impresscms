# Templating in `htdocs/libraries/icms`

Library classes must not build presentational HTML in PHP strings. Render it through `icms_view_Tpl`
(Smarty 2.6.31, delimiters `<{` and `}>`) with a `db:` template stored in
`htdocs/modules/system/templates/` and registered in `htdocs/modules/system/icms_version.php`
(`$modversion['templates']`). After adding or changing a template, update the system module so it is imported.

```php
$tpl = new icms_view_Tpl();
$tpl->assign('ele_name', $this->getName());
return $tpl->fetch('db:icms_form_elements_text_display.html');
```

Naming: `icms_<package>_<class>_<variant>.html`. Form elements may expose `$customTemplate` to override the default.
Values are assigned as they were previously concatenated (already escaped where they were); new templates must use
`|escape` for raw user input.

## Guard
`php bin/find-hardcoded-html.php --check=bin/hardcoded-html-baseline.txt` (CI: *Hardcoded HTML guard*) fails when a
library file gets more string literals containing HTML tags than recorded in the baseline.
After migrating a file, regenerate the baseline with `php bin/find-hardcoded-html.php --format=baseline > bin/hardcoded-html-baseline.txt`.
Files that must keep markup (sanitisers, bootstrap/error output) are listed in the OpenSpec inventory
(`openspec/changes/replace-hardcoded-html-with-smarty/inventory.md`).

## Template reference (new in this change)
| Template | Used by | Variables |
|---|---|---|
| `icms_view_pagenav_text.html` | `icms_view_PageNav::renderNav` | `style`, `items[]` (`type`: prev, prev_disabled, current, page, ellipsis, next, next_disabled; `url`, `label`, `arrow`) |
| `icms_view_pagenav_select.html` | `renderSelect` | `options[]` (`value`, `label`, `selected`), `showbutton`, `go_label` |
| `icms_view_pagenav_image.html` | `renderImageNav` | `items[]`, `blank_img` |
| `icms_view_pagenav_stylesheet.html` | `renderNav` (no theme) | `stylesheet_url` |
| `icms_form_table.html`, `icms_form_theme.html`, `icms_form_simple.html`, `icms_ipf_form_base.html`, `icms_form_groupperm.html` | form renderers | `title`, `name`, `action`, `method`, `extra`, `align`, `elements[]`, `hidden` |
| `icms_form_break_row.html` | `insertBreak()` | `extra`, `class` |
| `icms_form_validation_script.html` | `renderValidationJS(true)` | `js` |
| `icms_form_elements_{select,textarea,groupperm}_display.html` | form elements | see templates |
| `icms_form_elements_captcha_{hint,expression}.html` | captcha | `rule` / `expression` |
| `icms_ipf_controller_{link,imagelink}.html` | `icms_ipf_Controller` | `url`, `label` / `image_url`, `title` |
| `icms_ipf_form_elements_{page,file}_display.html`, `icms_ipf_form_elements_image_*.html` | IPF elements | see templates |
| `icms_view_tree_{selbox,pathlink}.html` | tree classes | `name`, `id`, `onchange`, `options[]` / `url`, `label` |
| `icms_view_pagebuilder_blockedit.html` | `icms_view_PageBuilder::buildBlock` | `bid`, `rtn`, `is_custom`, `admin_url`, `images_url`, `lang_*` |
| `icms_data_page_seloptions.html` | `icms_data_page_Handler::getPageSelOptions` | `top_*`, `all_*`, `groups[]` |
| `icms_data_comment_{status,adminfooter,posterlink}.html` | `icms_data_comment_Renderer` | see templates |
| `icms_data_{file,urllink}_link.html`, `icms_member_userlink.html`, `icms_module_{message,mainlink}.html` | object `render()`/link helpers | see templates |
