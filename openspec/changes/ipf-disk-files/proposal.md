## Why

IPF lists, filters and sorts database rows, but not the files of a folder. Pages that manage files on disk (the image manager, backups, uploads) each use their own `scandir()` and `unlink()` code, with their own checks. A small IPF-style layer for files on disk gives them one tested place for listing, filtering with the existing criteria classes and the basic file actions, with one rule for which paths may be touched.

This replaces the first version of this change (PR #109), which was written against `main` in the legacy `icms_ipf_*` / `libraries/icms/` layout. It did not load on `pre-3`: `delete()` was incompatible with `Icms\Ipf\Entity::delete($force = false)`.

## What Changes

- Add `Icms\Ipf\DiskFile\Handler`: lists (`getList()`, `getObjects()`) and loads (`get()`) the files of a folder inside a root given to the constructor, filters them with `Icms\Db\Criteria\Item` and `Compo` on `basename`, `extension`, `size` and `mtime`, and sorts them.
- Add `Icms\Ipf\DiskFile\Entity`, with `ImageEntity` (`getDimensions()`), `VideoEntity` and `ZipEntity` (`getEntryCount()`): the properties of one file, and `rename()`, `moveTo()`, `copyTo()` and `delete()`.
- Add `getColumn()`, `getOperator()` and `getValue()` to `Icms\Db\Criteria\Item`, so that criteria can be evaluated without reflection. Additive.
- Add `FolderTree`, `TreeNode` and `TreeView`: the folders and files below a folder in tree order, and the rows and variables that the IPF table template `system_persistabletable_display.html` reads, with `Handler::getFolders()` to list sub folders. `Icms\Ipf\View\Tree` itself cannot show a folder tree: it needs an `Icms\Ipf\Handler`, and it is unused and fails on PHP 8 (`Table::render()` passes `'all'` to `Criteria\Element::setLimit(int)`).
- Every path is resolved with `realpath()` and must lie inside the root. Actions never overwrite an existing file. Failures throw `Icms\Ipf\DiskFile\Exception`; unsupported criteria throw `InvalidArgumentException`.

## Capabilities

### New Capabilities
- `ipf-disk-files`: Listing, filtering, loading and acting on files in a folder as IPF-style objects, confined to a root.

### Modified Capabilities
<!-- none -->

## Impact

- **New code (library)**: `htdocs/libraries/Icms/Ipf/DiskFile/*`. New classes, so no legacy `icms_ipf_*` aliases.
- **Library change**: three getters on `Icms\Db\Criteria\Item`.
- **Dependencies**: none. `symfony/finder` and `symfony/filesystem` arrive only through `symfony/framework-bundle` and are not declared, so they are not used here.
- **Behaviour**: unchanged for existing code; nothing uses the new classes yet.
- **Out of scope**: persisting disk files (no `store()`), recursive listing, overwriting, video metadata, a Symfony service or controller on top.
