## 1. Stack on PR #119

- [x] 1.1 Bring the `pr119` branch of `fiammybe/impresscms` into this branch (merge or rebase, agreed with the user) and run the Library suite to confirm `Icms\Ipf\DiskFile\*` loads
- [x] 1.2 Read `FolderTree`, `TreeNode` and `TreeSource` and note the constructor arguments, the depth limit and what `TreeNode` exposes (path, level, is-folder); adjust D2 if the API differs from the design

## 2. Function, right and language files (D7, D8, D9)

- [x] 2.1 Find where the user rank image is stored and what `rank_image` holds (folder and file name form); fix the last row of the table in design D6 if needed
- [x] 2.2 Add `ICMS_SYSTEM_ASSETS` (27) to `htdocs/modules/system/constants.php`
- [x] 2.3 Add `htdocs/modules/system/admin/assets/icms_version.php` (name, description, `adminpath`, category `ICMS_SYSTEM_ASSETS`, group `_MD_AM_GROUPS_MEDIA`) and the stub `main.php` (D8)
- [x] 2.4 Add the menu constants (`_MD_AM_ASSETS`, `_MD_AM_ASSETS_DSC`) to `language/<lang>/admin.php` and the page constants to `language/<lang>/admin/assets.php`, for english, nederlands, francais, italiano and schinese; every language gets every constant
- [x] 2.5 Add the content template `system_adm_assets.html` (title, explanation, the tree table) and register it as the other system admin templates are

## 3. References and protection (D5, D6)

- [x] 3.1 Implement `App\Admin\UploadReferences::find()` with the constant list of columns and base folders, database access through `icms::$xoopsDB`, and paths normalised to `/`
- [x] 3.2 Implement the protected-file rule (`index.html`, `blank.gif`, names starting with a dot) as a method that the action closure and the delete both use
- [x] 3.3 Tests (`#[Group('spec:system-assets-admin')]`, `@scenario`): protected names are protected and `index.htm` is not (`tests/Unit/Admin/ProtectedUploadsTest`); a file referenced by each of the five kinds is warned about, a file below `imagemanager/` matches `image_name` relative to it, and an unreferenced file gets no warning (the Http tests of group 5, which need the database)

## 4. Controller (D1, D2, D3, D4)

- [x] 4.1 Implement `App\Controller\System\AssetsController` with `#[SystemAdminFunction('assets')]`, the route and `fct` condition from `MimetypeController`, and `op` dispatch
- [x] 4.2 List: build `Handler(ICMS_UPLOAD_PATH)`, `FolderTree` and `TreeView` with the columns name, size and modified; one delete action for unprotected files only; render through `AdminPageRenderer`
- [x] 4.3 Delete confirmation: resolve `path` through the handler, report "not selected" for unknown paths, paths outside the root and folders, refuse protected files, show `Message::confirm()` with the reference warning
- [x] 4.4 Confirmed delete: read `confirm` from the POST body only, check the security token, re-resolve and re-check protection, call `Entity::delete()`, redirect to `admin.php?fct=assets` with the result message; failures become "could not be deleted"

## 5. HTTP tests

- [x] 5.1 Fixtures: `AssetsTest` creates its files below `uploads/assets-test/` (a nested folder, an empty folder, an unreferenced file, `index.html`, a file with an `&` in its name) and removes them after each test; references are rows inserted per test. The administrator without the right is the existing `mediaadmin`; the granted administrator is `mimetypeadmin` with a `system_admin` permission for `ICMS_SYSTEM_ASSETS` granted in the test, so the shared fixture users stay as they are
- [x] 5.2 `tests/Http/System/AssetsTest.php`: page opens for a webmaster, menu entry in the Media group, and the denial cases (anonymous, `assetadmin`, a granted administrator)
- [x] 5.3 Tree tests: folders before files, indentation per level, the empty folder, a link out of the upload folder is not listed (skip where links cannot be created), names are escaped
- [x] 5.4 Action tests: files have a delete action, folders and protected files have none
- [x] 5.5 Delete tests: confirmation names the file and deletes nothing, with and without a reference warning; unknown path, `..`, absolute path and link target; protected file refused even with a token; confirmed delete with a token removes the file and leaves the database rows alone; missing and replayed token; GET with `confirm`; file removed between confirmation and delete
- [x] 5.6 Run `bin/spec-coverage --change=system-assets-admin` and make sure every scenario is covered

## 6. Documentation and checks

- [x] 6.1 Add the asset manager to the list of Symfony-served admin pages in `docs/symfony-host.md`, including the dedicated right and that it has no takeover switch
- [x] 6.2 Run PHPStan on the new classes and the Unit, Integration and Http suites; report failures that exist on `pr119` apart from failures introduced here
