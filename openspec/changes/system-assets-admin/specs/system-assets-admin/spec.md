## Purpose

The asset manager of the system module: an administration page where administrators see the folders and files of the site's upload folder and delete single files, with a warning when the database still refers to the file.

## ADDED Requirements

### Requirement: The asset manager is a system admin function
The system SHALL serve the asset manager at `/modules/system/admin.php?fct=assets` and SHALL list it in the Media group of the system administration menu.

#### Scenario: Open the page
- **WHEN** a webmaster requests `GET /modules/system/admin.php?fct=assets`
- **THEN** the page is shown inside the administration theme

#### Scenario: Menu entry
- **WHEN** a webmaster opens the system administration menu
- **THEN** the Media group contains the entry for the asset manager

### Requirement: Access has its own right
Access to the asset manager SHALL require an administrator of the system module who is a webmaster or who has been granted the system admin right for the asset manager category. Everyone else SHALL get the denial that the other system admin functions give.

#### Scenario: Administrator without the right
- **WHEN** an administrator of the system module who has no right for the asset manager requests the page
- **THEN** the page is not shown and the administrator is sent to the administration start page, as for other functions

#### Scenario: Anonymous visitor
- **WHEN** a visitor who is not logged in requests the page
- **THEN** the page is not shown and the visitor gets the denial of the other system admin functions

#### Scenario: Granted administrator
- **WHEN** an administrator of the system module who has been granted the right requests the page
- **THEN** the page is shown

### Requirement: The upload folder is shown as a tree
The page SHALL show the folders and files below the upload folder as a tree, folders before files, each level indented, with name, size and modification time. Folders SHALL be shown even when empty. Nothing outside the upload folder SHALL be shown, and links that lead out of it SHALL NOT be followed.

#### Scenario: Files and folders
- **WHEN** a webmaster opens the page and the upload folder contains the folders `imagemanager` and `system` and several files
- **THEN** the folders are listed before the files, with the files of each folder below it and indented one level

#### Scenario: Link leaving the upload folder
- **WHEN** the upload folder contains a link to a folder or file outside it
- **THEN** that link is not listed and its target is not shown

#### Scenario: Names are shown as text
- **WHEN** a file name contains characters with a meaning in HTML
- **THEN** the page shows the name literally and does not interpret it as markup

### Requirement: Only files can be deleted
Every file SHALL have a delete action. Folders SHALL have none. The page SHALL offer no other action on files or folders: no upload, rename, move, copy or folder creation.

#### Scenario: File row
- **WHEN** the tree shows a file that is not protected
- **THEN** its row has a delete action

#### Scenario: Folder row
- **WHEN** the tree shows a folder
- **THEN** its row has no action

### Requirement: Protected files cannot be deleted
`index.html`, every file whose name starts with a dot, and `blank.gif` SHALL be protected. A protected file SHALL be shown without a delete action, and a request to delete it SHALL be refused without changing anything, even with a confirmation and a valid token.

#### Scenario: Protected file in the tree
- **WHEN** the tree shows `index.html`
- **THEN** its row has no delete action

#### Scenario: Delete request for a protected file
- **WHEN** a confirmed delete with a valid token names a protected file
- **THEN** the file is not deleted and the response says it is protected

### Requirement: Deleting asks for confirmation first
A request to delete a file SHALL first show a confirmation that names the file, and SHALL NOT delete anything. The file to delete SHALL be given by its path relative to the upload folder.

#### Scenario: Confirmation
- **WHEN** a webmaster requests the deletion of an existing, unprotected file
- **THEN** a confirmation names the file and the file still exists

#### Scenario: Unknown file
- **WHEN** a webmaster requests the deletion of a path that does not exist
- **THEN** the response says that no file was selected and nothing is deleted

#### Scenario: Path outside the upload folder
- **WHEN** the requested path points outside the upload folder, through `..`, an absolute path or a link
- **THEN** nothing is deleted and the response says that no file was selected

### Requirement: The confirmation warns about references
The confirmation SHALL state when the file is referenced by a user avatar, an avatar in the avatar list, a smilie, an image manager image or a user rank, and SHALL say which kind it is. The warning SHALL NOT prevent the deletion. A file without references SHALL get no warning.

#### Scenario: Referenced file
- **WHEN** a webmaster asks to delete a file that is the avatar of a user
- **THEN** the confirmation warns that the file is in use as a user avatar

#### Scenario: Unreferenced file
- **WHEN** a webmaster asks to delete a file that nothing refers to
- **THEN** the confirmation shows no warning

### Requirement: Deleting needs a confirmed request with a valid token
Only a confirmed POST with a valid ImpressCMS security token SHALL delete the file. The page SHALL then show the "deleted" message and return to the asset manager. The database SHALL NOT be changed by the deletion.

#### Scenario: Confirmed delete
- **WHEN** a webmaster confirms the deletion of a file with a valid token
- **THEN** the file is removed from disk, the "deleted" message is shown and the tree no longer lists it

#### Scenario: Missing or replayed token
- **WHEN** the confirmation is posted with a missing, invalid or already used token
- **THEN** the file is not deleted and the response reports an invalid token

#### Scenario: Delete by link
- **WHEN** the delete URL is requested with GET and the confirmation flag
- **THEN** the file is not deleted

#### Scenario: Referenced file is deleted
- **WHEN** a webmaster confirms the deletion of a referenced file
- **THEN** the file is removed and the rows that refer to it are unchanged

#### Scenario: File gone in the meantime
- **WHEN** the confirmed file no longer exists when the confirmation is posted
- **THEN** the response says that no file was selected and nothing else is changed

#### Scenario: Deletion fails
- **WHEN** the file exists but the system cannot remove it
- **THEN** the response reports that the file could not be deleted and the file is left as it is
