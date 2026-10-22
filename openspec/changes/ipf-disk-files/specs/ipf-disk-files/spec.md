## ADDED Requirements

### Requirement: Files of a folder are listed as objects
The system SHALL list the files directly inside a folder as objects with the properties `path`, `dirname`, `basename`, `extension` (lower-case), `size`, `mtime`, `is_readable` and `is_writable`, sorted by natural, case-insensitive base name unless another of `basename`, `extension`, `size`, `mtime` or `path` and an order are given. Sub folders are not listed.

#### Scenario: List a folder
- **WHEN** the files of a folder are listed without criteria
- **THEN** every file directly in the folder is returned once, keyed by its path, in natural case-insensitive base name order

### Requirement: Files are filtered with the existing criteria classes
The system SHALL filter the listed files in memory with `Icms\Db\Criteria\Item` and `Compo` on `basename`, `extension`, `size` and `mtime`, with the operators `=`, `!=`, `<>`, `<`, `<=`, `>`, `>=`, `LIKE`, `NOT LIKE`, `IN` and `NOT IN`, combined with `AND` and `OR`, and SHALL apply the criteria's start and limit after sorting.

#### Scenario: Filter with criteria
- **WHEN** a list is requested with criteria on extension, size, modification time or base name
- **THEN** only the files that satisfy the criteria are returned

#### Scenario: Unsupported criteria
- **WHEN** the criteria name another field or operator than the supported ones
- **THEN** the request is refused with an `InvalidArgumentException` and no list is returned

### Requirement: Files are loaded as typed objects
The system SHALL load a file as an object of a type chosen by its extension: images, videos and zip archives get their own class; any other file gets the base class.

#### Scenario: Typed file objects
- **WHEN** a file is loaded
- **THEN** the object carries the file's properties and has the class of its type, and a zip archive reports its entry count and an image its dimensions, or `null` when the content cannot be read

### Requirement: Paths stay inside the root
The system SHALL refuse every path that does not resolve, symbolic links followed, to a file or folder inside the root the handler was created with.

#### Scenario: Paths outside the root
- **WHEN** a file or folder is loaded, listed or used as a target, and it lies outside the root, is reached through `..` or through a link that leaves the root, or does not exist
- **THEN** an `Icms\Ipf\DiskFile\Exception` is thrown and nothing on disk changes

#### Scenario: Actions stay inside the root
- **WHEN** a file is moved or copied to a folder outside the root
- **THEN** an exception is thrown and the file is neither moved nor copied

### Requirement: Actions on a file never overwrite
The system SHALL rename, move, copy and delete a file only as thin filesystem actions that throw `Icms\Ipf\DiskFile\Exception` when they fail, and SHALL NOT overwrite an existing file.

#### Scenario: Rename a file
- **WHEN** a file is renamed to a valid file name
- **THEN** the file has the new name and the object is reloaded from it
- **AND** a name that is empty, `.`, `..` or contains a path separator or NUL is refused

#### Scenario: Move a file
- **WHEN** a file is moved to another folder inside the root
- **THEN** it exists only there and the object is reloaded from the new path

#### Scenario: Copy a file
- **WHEN** a file is copied to another folder inside the root
- **THEN** the copy exists, the original is unchanged, and the copy is returned as an object

#### Scenario: Delete a file
- **WHEN** a file is deleted
- **THEN** it no longer exists and deleting it again throws an exception

#### Scenario: Actions never overwrite
- **WHEN** a rename, move or copy would replace an existing file
- **THEN** an exception is thrown and the existing file keeps its content

### Requirement: Folders can be shown as a tree in the IPF table template
The system SHALL list the folders and files below a folder in tree order (in every folder its sub folders first, then its files, sorted as for a flat list), SHALL NOT follow symbolic links to folders, SHALL bound the walk by a maximum depth, and SHALL leave out folders without a matching file when criteria are given. It SHALL provide the rows and variables that the IPF table template `system_persistabletable_display.html` reads, with the names of the nodes indented by level, folders marked, and every name and path escaped for HTML, without needing an `Icms\Ipf\Handler`.

#### Scenario: Folder tree
- **WHEN** the tree below a folder is listed
- **THEN** each folder is followed by its own sub folders and files, each node knows its level and the folder it is in, folders without matching files are left out when criteria are given, and the walk stops at the maximum depth

#### Scenario: Folder tree stays inside the root
- **WHEN** the tree is requested for a folder outside the root, or a folder contains a link to a folder
- **THEN** the first is refused with an exception and the link is not entered

#### Scenario: Rows for the tree table template
- **WHEN** the template variables of a tree view are requested
- **THEN** they hold the columns, one row per node with its cells, actions and escaped id, and the tree flags the template expects, and file and folder names containing HTML are escaped
