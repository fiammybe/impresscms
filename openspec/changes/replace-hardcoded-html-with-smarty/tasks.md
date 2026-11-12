## 0. Inventory
- [ ] 0.1 Write `bin/find-hardcoded-html.php` (token-based, per design Decision 1)
- [ ] 0.2 Generate `inventory.md` (file, method, tag count, classification, rationale)
- [ ] 0.3 Review inventory with maintainers; settle Keep/Asset decisions
- [ ] 0.4 Capture golden HTML outputs for all Migrate classes (parity baseline)

## 1. Forms (`form/**`, `ipf/form/**`)
- [ ] 1.1 `form/Base`, `form/Table`, `form/Theme`, `form/Simple`, `form/Element`
- [ ] 1.2 `form/elements/Dhtmltextarea` (extract inline JS to asset), `Groupperm`, `Select`, `File`, `Hidden`, `Colorpicker`, `select/User`, `select/Image`, `captcha/*`
- [ ] 1.3 `form/Groupperm`
- [ ] 1.4 `ipf/form/Base`, `ipf/form/elements/{Page,Image,Richfile,File,Upload,Imageupload,Fileupload}`

## 2. View layer
- [ ] 2.1 `view/PageNav` (+ shared Tpl instance)
- [ ] 2.2 `view/Tree`, `ipf/Tree`
- [ ] 2.3 `view/theme/Object`, `view/PageBuilder`, `view/block/{Handler,Object}`

## 3. IPF
- [ ] 3.1 `ipf/Controller`, `ipf/Object`, `ipf/Highlighter`, `ipf/view/Table`

## 4. Core / data / member
- [ ] 4.1 `core/Message`, `core/Filesystem`, `core/StopSpammer`
- [ ] 4.2 `member/user/Handler`, `data/page/Handler`, `data/comment/Renderer`, `module/Object`, `module/Handler`
- [ ] 4.3 `file/MediaUploadHandler`, `messaging/*`

## 5. Guard
- [ ] 5.1 Baseline file of accepted Keep items
- [ ] 5.2 CI step running the scanner; fail on new, non-baselined hardcoded HTML
- [ ] 5.3 Document in contributor guide

## 6. Templates, upgrade & docs
- [ ] 6.1 Place templates in `htdocs/modules/system/templates/` (Smarty 2.6.31 syntax) and register new templates in `modules/system/xoops_version.php` (`$modversion['templates']`)
- [ ] 6.2 Upgrade script so existing installs get the templates
- [ ] 6.3 Document template names/variables as extension points; update `docs/changelog.md`
- [ ] 6.4 Run parity tests and manual smoke test (admin forms, pagination, messages, debug panel)
