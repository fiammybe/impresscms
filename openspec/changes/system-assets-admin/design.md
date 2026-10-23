## Context

See `proposal.md` (Why). This change builds on `ipf-on-symfony-mimetype-pilot` (host pieces: `#[SystemAdminFunction]`, `SystemAdminContext`, `AdminPageRenderer`, `LegacyRedirect`) and on PR #119, change `ipf-disk-files` (`Icms\Ipf\DiskFile\{Handler, Entity, FolderTree, TreeView}`). Neither is edited. The branch of this change is stacked on `pr119` of `fiammybe/impresscms`.

Facts about the upload folder that shape the design:

- `ICMS_UPLOAD_PATH` is `htdocs/uploads`. It holds files directly (smilies, avatars, `blank.gif`, `img*.png`), the image manager folder `imagemanager/` (`ICMS_IMANAGER_FOLDER_PATH`) and per-module folders created by the IPF handlers, such as `system/userrank/`.
- Most files are referenced from the database by file name relative to a folder: `users.user_avatar` and `avatar.avatar_file` (relative to `uploads/`), `smiles.smile_url` (relative to `uploads/`), `image.image_name` (relative to `uploads/imagemanager/`) and `rank_image` (an IPF upload, relative to `uploads/system/userrank/`).
- `uploads/index.html` and the like are the guard against directory listings. `blank.gif` is read by `image.php` and used as the fallback avatar.
- `TreeView::templateVariables()` fills `system_persistabletable_display.html`; actions are `\Closure(TreeNode): string` returning an HTML link per node.
- `admin.php` includes `admin/<fct>/main.php`, and the menu and the right come from `admin/<fct>/icms_version.php` (or the older `xoops_version.php`, which the code accepts as a fallback: `admin.php`, the menu block, the group permission form and `SystemAdminAccess` all check `icms_version.php` first). A new function is not known to the legacy code except through those files.

## Goals / Non-Goals

**Goals:**
- A page that is safe to give to administrators: it can only see and delete files below `uploads/`, only with a token, and never the guard files.
- Reuse the mimetype pilot's controller shape, so the next admin pages look the same.
- Tell the administrator what a deletion breaks, without making the page depend on every module's tables.

**Non-Goals:**
- A generic "file manager" API. The controller is specific to `uploads/`.
- Changing `Icms\Ipf\DiskFile`. Gaps found there go back to PR #119.
- Twig rendering. Output stays legacy, as in the pilot.

## Decisions

### D1. One controller, op dispatch, no takeover switch
`App\Controller\System\AssetsController` is `#[SystemAdminFunction('assets')]` with one route on `/modules/system/admin.php`, methods GET and POST, and the condition on `fct` from the mimetype controller (query string, overridden by the POST body, because the confirmation posts without a query string). It dispatches `op`: default shows the tree, `del` shows the confirmation or, with `confirm` and a valid token, deletes.

There is no `ICMS_TAKEOVERS_DISABLED` key: the page has no legacy version to fall back to. A fixed `main.php` exists only because `admin.php` includes it when the route does not match (see D8).

*Alternative*: one route per `op`. Rejected for the same reason as in the pilot (D2): it duplicates the legacy `op` parsing in expressions.

### D2. The tree comes from PR #119, rooted at `ICMS_UPLOAD_PATH`
The controller builds `new Handler(ICMS_UPLOAD_PATH)`, a `FolderTree` on it and a `TreeView` with the columns `basename`, `size` and `mtime`. One `addAction` closure returns the delete link for a file node and `''` for a folder or a protected file. The template variables are assigned to the admin template and shown with `db:system_persistabletable_display.html`, as the PR does by hand. A small content template `system_adm_assets.html` carries the title and explanation and includes that table.

The handler's `get()` is the only way a request path becomes a file: it resolves the path (symbolic links followed) and throws `Icms\Ipf\DiskFile\Exception` when it lies outside the root or does not exist. The controller passes it the value of `path` and turns the exception into "not selected". It never touches the filesystem with a request value itself.

### D3. A request names a file by its path relative to `uploads/`
`path` in the links and hidden fields is relative (`system/userrank/gold.png`), not absolute: it does not reveal the server layout, it matches the form in which the database stores file names, and the handler is given `ICMS_UPLOAD_PATH . '/' . $path`. The tree node's absolute path is converted to a relative one by stripping the handler's `root()`.

*Alternative*: absolute paths in the URL. Rejected: they leak the installation path, and a pasted URL would not survive a move of the site.

### D4. Deletion is confirm, then POST with token
Same flow as `MimetypeController::delete()`:
1. `op=del&path=…`: look the file up, refuse protected files, show `Icms\Core\Message::confirm()` through `AdminPageRenderer::renderFragment()` with hidden `op=del`, `path`, `confirm=1`, `fct`, and the token that `confirm()` adds. The reference warning (D6) is part of the message text.
2. `confirm=1` as POST: check `icms::$security->check()` (invalid → `LegacyRedirect` with `_CORE_TOKENINVALID`), re-resolve the file, re-check protection, call `Entity::delete()`, then redirect to `admin.php?fct=assets` with the "deleted" or "could not be deleted" message.

A GET never deletes: `confirm` is read from the POST body only. The redirect target is built by the controller, not read from a hidden `redirect_page` field, so there is no open redirect to guard.

### D5. Protected files are a fixed list in the controller
`AssetsController::isProtected(string $basename): bool` is true for `index.html`, `blank.gif` and any name starting with a dot. It is applied to the action closure (D2) and again at delete time (D4), so a hand-made request meets the same rule. The list is a class constant, not configuration.

*Alternative*: an allow-list of deletable folders. Rejected: modules add their own folders, so a list would hide files the administrator should see.

### D6. References are found with a fixed list of columns
`App\Admin\UploadReferences::find(string $relativePath): list<string>` returns the kinds that refer to a file, from a constant list of (kind label constant, table, column, base folder relative to `uploads/`):

| Kind | Table | Column | Base |
|---|---|---|---|
| user avatar | `users` | `user_avatar` | `` |
| avatar list | `avatar` | `avatar_file` | `` |
| smilie | `smiles` | `smile_url` | `` |
| image manager | `image` | `image_name` | `imagemanager/` |
| user rank | `ranks` | `rank_image` | `system/userrank/` |

For each entry it runs `SELECT 1 … WHERE column = ? LIMIT 1` with the path relative to the base (only when the path is below the base), using the existing database object (`icms::$xoopsDB`, the only name the core offers for it) with `quoteString` and `prefix()`. It does one query per entry and per file, only on the confirmation page, so it costs five cheap queries.

The result is best effort and the page says so. It does not see free text (article bodies, block content), tables of third-party modules, or the filesystem layout of the table name.

*Alternatives*: (A) no check, rejected as the user decision was warning (option B). (C) block when in use, rejected: files can be referenced by things the list cannot see, so a block would suggest a certainty we do not have. A `LIKE` over all text columns: rejected, slow and still incomplete.

### D7. Own right: `ICMS_SYSTEM_ASSETS` = 27
`modules/system/constants.php` gets the value 27: 24 is `XOOPS_SYSTEM_AUTOTASKS`, and 25 and 26 are reserved for two parallel changes that add their own `ICMS_SYSTEM_*` constants. The existing constants keep their names; only the new ones are named `ICMS_`. `admin/assets/icms_version.php` declares `'category' => ICMS_SYSTEM_ASSETS`, `'group' => _MD_AM_GROUPS_MEDIA`, `'adminpath' => 'admin.php?fct=assets'`. The pilot's `SystemAdminAccess` already reads the category from that file, so webmasters get in and others only after a `system_admin` permission is set.

*Alternative*: reuse `XOOPS_SYSTEM_IMAGE`. Rejected: administrators of the image manager would silently gain the right to delete any file under `uploads/`, including avatars of other users.

### D8. Stub `main.php` for a site where the host does not serve the page
`admin.php` includes `admin/assets/main.php` when no route matches. This file shows an admin-themed message that the asset manager needs the framework application. It exists so the legacy path does not fatal on a missing include, and holds no logic.

### D9. Rendering stays legacy, strings in language files
Texts are constants in `language/<lang>/admin/assets.php` (loaded by `#[SystemAdminFunction]` as `system/<fct>`): title, explanation, column captions, delete caption, confirmation text with a `%s` for the name, the five kind labels, warning text, "deleted", "could not be deleted", "protected", "not selected" (reuses `_CO_ICMS_NOT_SELECTED`). English, Dutch, French, Italian and Simplified Chinese all get every constant, following the repository's language policy (the English text where no translation exists yet).

## Risks / Trade-offs

- [A referenced file is deleted after the warning is ignored] → Intended (decision: warn, not block). The warning names the kind. A file that nothing in the list refers to can still be in use; the page text says the check is not complete.
- [The directory tree of a big `uploads/` is slow or large] → `FolderTree` bounds its depth; the page is an admin page and loads the tree once. If a site has thousands of files in one folder, paging will be the follow-up (see Open Questions).
- [PR #119 changes while this change is stacked on it] → Rebase on it; keep the code to the public API (`Handler`, `FolderTree`, `TreeView`, `Entity::delete()`), so only signatures can break.
- [A relative path built from a node can differ from the stored database value on Windows (`\`)] → Normalise to `/` when making the relative path; test on a Windows runner with a nested folder.
- [Race: another request deletes the file between confirmation and delete] → `Entity::delete()` throws; the controller reports it as "could not be deleted".
- [Webmasters get a new destructive action] → Token on the delete, protected files, no folder delete, and a separate right so it is not handed out with the image manager.

## Migration Plan

1. Merge PR #119 (or keep this branch stacked on it).
2. Add the page, constant, language files and menu entry. Nothing in the database changes; the right is a `group_permission` row that administrators create when they want to delegate.
3. After the upgrade, webmasters see "Asset manager" under Media.

**Rollback**: remove the controller route (revert the change). No data to undo; deleted files are not recoverable from the application.

## Open Questions

- **Paging for big folders.** The page shows the whole tree. If real sites have too many files in one folder this needs `Handler`'s criteria start/limit and a pager; it can be added without changing the specs.


### D10. New names use `icms`, never `xoops`
Nothing this change introduces carries the XOOPS name: the constant is `ICMS_SYSTEM_ASSETS`, the version file is `icms_version.php`, and classes, templates and language constants use `icms`, `ICMS` or the `_MD_AM_` prefix. Existing names the change must call stay as they are (`XOOPS_SYSTEM_*` constants of other functions, `icms::$xoopsDB`, `XOOPS_GROUP`-style helpers); renaming them is a separate change.
