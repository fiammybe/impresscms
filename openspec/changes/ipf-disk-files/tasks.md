## 1. Library

- [x] 1.1 Add `getColumn()`, `getOperator()` and `getValue()` to `Icms\Db\Criteria\Item`
- [x] 1.2 Add `Icms\Ipf\DiskFile\{Exception, Entity, ImageEntity, VideoEntity, ZipEntity, Handler}`

## 2. Tests

- [x] 2.1 `HandlerTest`: listing, sorting, criteria, range, typed objects, paths outside the root
- [x] 2.2 `EntityTest`: rename, move, copy, delete, never overwrite, stay inside the root

## 3. Folder tree

- [x] 3.1 `Handler::getFolders()`, `FolderTree`, `TreeNode`
- [x] 3.2 `TreeView`: template variables for `system_persistabletable_display.html`
- [x] 3.3 `TreeTest`
- [ ] 3.4 Show the tree in a page with the real template (needs the database templates)

## 4. Verify

- [ ] 4.1 Library suite and `bin/spec-coverage --change=ipf-disk-files` green
- [ ] 4.2 PHPStan
