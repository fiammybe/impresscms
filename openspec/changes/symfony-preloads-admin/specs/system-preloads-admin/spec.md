## Purpose

The preloads administration page of the system module, served by the framework application under its own URL. It shows the installed preloads and lets authorised administrators disable and re-enable them.

## ADDED Requirements

### Requirement: The page is served by the framework application under its own URL
The system SHALL serve the preloads administration at `/admin/preloads` through the framework application. At routing level 1, where clean URLs do not reach the application, the same page SHALL be reachable as `/index.php/admin/preloads`. A request to `/modules/system/admin.php?fct=preloads` SHALL redirect to the page.

#### Scenario: Clean URL
- **WHEN** a webmaster requests `GET /admin/preloads` at routing level 2
- **THEN** the framework application serves the preloads page

#### Scenario: Routing level 1
- **WHEN** a webmaster requests `GET /index.php/admin/preloads` at routing level 1
- **THEN** the framework application serves the preloads page

#### Scenario: Old-style admin link
- **WHEN** a webmaster requests `GET /modules/system/admin.php?fct=preloads`
- **THEN** the response redirects to the preloads page

### Requirement: The page is a system admin function
The page SHALL be a system admin function with its own category and SHALL follow the access rules and denial behaviour of the other system admin functions: a logged-in administrator of the system module who is either a webmaster or holds the `system_admin` right for the preloads category is granted access; others are denied as on the legacy system admin pages. The category SHALL be assignable to groups on the group administration page.

#### Scenario: Webmaster
- **WHEN** a webmaster requests the preloads page
- **THEN** access is granted

#### Scenario: Anonymous visitor
- **WHEN** an anonymous visitor requests the preloads page
- **THEN** the visitor is redirected to the login page with the "no permission" message, as for other system admin functions

#### Scenario: Administrator without the preloads right
- **WHEN** a system module administrator who is not a webmaster and lacks the `system_admin` right for the preloads category requests the page
- **THEN** access is denied with a redirect to the start page of the administration

#### Scenario: Right granted to a group
- **WHEN** a webmaster grants a group the system admin right for preloads on the group administration page
- **THEN** administrators of that group can use the preloads page

### Requirement: The page appears in the system admin menu
The preloads function SHALL appear in the system administration menu, in the dashboard of system functions and in the system module's option list, for exactly the users who may use it. Its menu link SHALL lead to the page at every routing level.

#### Scenario: Menu entry for a webmaster
- **WHEN** a webmaster opens any administration page after the admin menu has been rebuilt
- **THEN** the system menu contains a "Preloads" entry that links to the preloads page

#### Scenario: Menu entry hidden without the right
- **WHEN** an administrator without the preloads right opens the administration
- **THEN** the system menu has no "Preloads" entry

### Requirement: The page lists the installed preloads
The page SHALL list every preload file in the preloads directory, enabled and disabled alike, sorted by name. For each preload it SHALL show its name, the events it handles and whether it is enabled. Inspecting a disabled preload SHALL NOT run its code. The page SHALL be rendered inside the administration theme, like the other system admin pages.

#### Scenario: Listing
- **WHEN** a webmaster opens the preloads page on a default installation
- **THEN** every file of the preloads directory is listed with its events, for example `keepalive` with the event `beforeFooter`, and all are shown as enabled

#### Scenario: Disabled preload in the list
- **WHEN** the `debug_mode` preload is disabled and a webmaster opens the page
- **THEN** `debug_mode` is listed as disabled, with its events, and its code has not run

### Requirement: Administrators can disable and enable preloads
An authorised administrator SHALL be able to disable an enabled preload and enable a disabled one from the page. The change SHALL take effect from the next request on and SHALL be confirmed with a message, after which the page shows the new state. Only installed preloads SHALL be accepted; requests naming another preload SHALL change nothing and report an error.

#### Scenario: Disable a preload
- **WHEN** a webmaster disables `autologin` with a valid token
- **THEN** `autologin` is stored as disabled, a confirmation message is shown, the page lists it as disabled, and the next request does not load it

#### Scenario: Enable a preload
- **WHEN** a webmaster enables the disabled `autologin` with a valid token
- **THEN** `autologin` is loaded again from the next request on and the page lists it as enabled

#### Scenario: Unknown preload
- **WHEN** a request asks to disable `../../mainfile` or a name that is not an installed preload
- **THEN** nothing is stored and an error is reported

### Requirement: Changes require a security token
Enabling and disabling SHALL only be performed by a POST request that carries a valid ImpressCMS security token. A request without a valid token, or a GET request, SHALL change nothing.

#### Scenario: Missing token
- **WHEN** a disable request is posted without a valid security token
- **THEN** nothing is stored and the response reports an invalid token

#### Scenario: GET request
- **WHEN** a webmaster requests the disable action with GET
- **THEN** nothing is stored

### Requirement: Storage problems are reported
When the disabled set cannot be written, the system SHALL keep the previous state and report the error on the page, naming the location that must be writable.

#### Scenario: Read-only storage
- **WHEN** a webmaster disables a preload while the storage location is not writable
- **THEN** no preload changes state and an error names the location that must be writable
