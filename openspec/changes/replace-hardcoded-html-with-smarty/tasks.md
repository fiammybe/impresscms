## 0. Inventory
- [x] 0.1 Write `bin/find-hardcoded-html.php` (token-based, per design Decision 1)
- [x] 0.2 Generate `inventory.md` (file, method, tag count, classification, rationale)
- [ ] 0.3 Review inventory with maintainers; settle Keep/Asset decisions (initial classification is in `inventory.md`, awaiting review)
- [x] 0.4 Parity baseline: old (git HEAD) vs. new output compared side by side for PageNav, form Table/Theme/Simple, Groupperm, Select, Textarea; templates for the other migrated classes verified against the old literal strings. No stored golden files / no PHPUnit suite exists in the repo

## 1. Forms (`form/**`, `ipf/form/**`)
- [x] 1.1 `form/Base` (validation JS wrapper, break row), `form/Table`, `form/Theme`, `form/Simple`
- [x] 1.2a `form/elements/{Select,Textarea,Groupperm}`, `captcha/{Image,Text}`
- [ ] 1.2b `form/elements/Dhtmltextarea` (extract inline JS to asset first), `select/User`, `select/Image`, `Colorpicker`
- [x] 1.3 `form/Groupperm`
- [x] 1.4a `ipf/form/Base`, `ipf/form/elements/{Page,Image,File}`
- [ ] 1.4b `ipf/form/elements/{Richfile,Signature,Passwordtray}`

## 2. View layer
- [x] 2.1 `view/PageNav` (+ shared Tpl instance)
- [x] 2.2 `view/Tree`, `ipf/Tree`
- [x] 2.3a `view/PageBuilder` (block edit buttons)
- [ ] 2.3b `view/theme/Object` (meta script/style wrappers)

## 3. IPF
- [x] 3.1 `ipf/Controller` link helpers
- [ ] 3.2 `ipf/view/Table` (sort icons); `ipf/Highlighter` stays Keep

## 4. Core / data / member
- [ ] 4.1 `core/Filesystem` (check installer/bootstrap usage first); `core/Message` stays Keep (installer/updater use)
- [x] 4.2 `member/user/Handler::getUserLink`, `data/page/Handler`, `data/comment/Renderer`, `module/Object`, `data/file/Object`, `data/urllink/Object`
- [ ] 4.3 Remaining single `<br />`/`<h4>` message strings stay Keep (see `inventory.md`)

## 5. Guard
- [x] 5.1 Baseline file `bin/hardcoded-html-baseline.txt` (Keep + Deferred items)
- [x] 5.2 CI workflow `.github/workflows/hardcoded-html.yml` fails on new, non-baselined hardcoded HTML
- [x] 5.3 Documented in `docs/templating.md`
- [ ] 5.4 Lower the baseline as each Deferred item is migrated

## 6. Templates, upgrade & docs
- [x] 6.1 Templates placed in `htdocs/modules/system/templates/` (Smarty 2.6.31, `<{ }>` delimiters) and registered in `modules/system/icms_version.php`
- [ ] 6.2 Verify on a real install that updating the system module imports the new `db:` templates (no DB schema change, so `ICMS_SYSTEM_DBVERSION` is unchanged); decide whether the module version must be bumped to trigger the update prompt
- [x] 6.3 Template names/variables documented in `docs/templating.md`; entry added to `docs/changelog.md`
- [ ] 6.4 Manual smoke test in a browser (admin forms, pagination, comments, block edit buttons, group permissions). Only template-level and class-level comparisons were run so far
