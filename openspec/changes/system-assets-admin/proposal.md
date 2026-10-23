## Why

Administrators have no way to see what is in the `uploads/` folder. Avatars, smilies, rank images, image manager files and the uploads of modules pile up there, and the only way to see or clean them up is FTP or a shell on the server. Pull request #119 (`ipf-disk-files`) adds the missing building blocks: a root-confined handler for the files of a folder, a folder tree and a view that fills the IPF table template. It deliberately has no admin page. This change puts that page on top of it, as the second page served by the Symfony host after the mimetype pilot.

A file in `uploads/` is usually referenced from the database (`users.user_avatar`, `avatar.avatar_file`, `smiles.smile_url`, `image.image_name`, `rank_image`). Deleting it without a word leaves broken images behind, so the page has to tell the administrator about that before it deletes.

## What Changes

- Add a system admin function `admin.php?fct=assets` ("Asset manager", in the Media group) that shows the folders and files below `ICMS_UPLOAD_PATH` as a tree: name, size and modification time.
- Add deleting a single file: a confirmation first, then a POST with a valid ImpressCMS security token. The path of the file is resolved by the PR #119 handler, so nothing outside `uploads/` can be reached.
- The confirmation warns when the file is still referenced by one of the known columns (`users.user_avatar`, `avatar.avatar_file`, `smiles.smile_url`, `image.image_name`, `rank_image`) and names what refers to it. The warning does not block the deletion.
- Protected files cannot be deleted and show no delete action: `index.html`, files whose name starts with a dot, and `blank.gif`.
- Folders cannot be deleted, renamed or created. Files cannot be uploaded, renamed, moved or copied.
- Access has its own right: a new system admin category `ICMS_SYSTEM_ASSETS` (27). Webmasters have it; other administrators only after a group permission is granted.
- Built on the pieces of the mimetype pilot: `#[SystemAdminFunction]`, `AdminPageRenderer`, `LegacyRedirect` and `Icms\Core\Message::confirm()`. The page is new, so it has no legacy fallback.
- **Depends on** PR #119: this change is stacked on its branch and needs `Icms\Ipf\DiskFile\*`.

## Capabilities

### New Capabilities
- `system-assets-admin`: The asset manager page of the system module: browse the upload folder as a tree, delete a file with confirmation and security token, warn about database references, protect core files, and restrict access with a dedicated admin category.

### Modified Capabilities

None. `admin-page-integration` and `ipf-disk-files` are used as they are.

## Impact

- **New code (host)**: `src/Controller/System/AssetsController.php` and a small service that finds references to an upload file.
- **New legacy-side files**: `htdocs/modules/system/admin/assets/icms_version.php` (menu entry, category and group), a stub `main.php` for when the framework application does not serve the page, the `ICMS_SYSTEM_ASSETS` constant in `modules/system/constants.php`, the language file `language/<lang>/admin/assets.php` for each of the five languages, and the two admin constants for the menu title and description.
- **Library (`Icms\`)**: no changes.
- **Dependencies**: PR #119 (`Icms\Ipf\DiskFile\Handler`, `FolderTree`, `TreeView`) until it is merged.
- **Behaviour**: new page only. Existing pages are unchanged. The only destructive action is deleting a file, behind a confirmation and a token.
- **Out of scope**:
  - uploading, renaming, moving, copying or deleting folders
  - previews, thumbnails or "orphan" detection
  - blocking or cascading deletes of referenced files
  - references inside free text (articles, block content) and in modules' own tables
  - mass delete
