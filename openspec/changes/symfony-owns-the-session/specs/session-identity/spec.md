## MODIFIED Requirements

### Requirement: One session across both applications
The system SHALL use a single session for both applications, started and stored by the host. The session name, cookie lifetime and expiry SHALL follow the ImpressCMS preferences `use_mysession`, `session_name` and `session_expire`. The session SHALL be stored in the ImpressCMS `session` table, bound to the visitor's network as before. Sessions stored before the host owned the session SHALL remain valid. Data written to the session by one application SHALL be readable by the other on subsequent requests.

#### Scenario: Value written by legacy, read by new application
- **WHEN** a legacy page stores a value in the session and the next request with the same session cookie is handled by a new-application route
- **THEN** the new-application route reads the same value

#### Scenario: Custom session name
- **WHEN** ImpressCMS is configured with `use_mysession` enabled and session name `MYSITE`
- **THEN** responses from both applications use the `MYSITE` session cookie and no second session cookie is issued

#### Scenario: Session stored in the session table
- **WHEN** a user logs in
- **THEN** the session is stored in the `session` table under the id of the session cookie, with the visitor's IP address

## REMOVED Requirements

### Requirement: The ImpressCMS user is the authenticated identity in the new application
**Reason**: The Symfony security token is now the identity of both applications.
**Migration**: Replaced by "The security token is the identity of both applications". New-application routes keep the same user (now `App\Security\User\Entity`) and the same roles.

### Requirement: Login, logout and session expiry remain owned by ImpressCMS
**Reason**: Login and logout go through the host's security layer.
**Migration**: Replaced by "Legacy login and logout go through the host". Legacy code logs in with `Icms\Core\Session::login()`.

## ADDED Requirements

### Requirement: The security token is the identity of both applications
The system SHALL keep the authenticated user as a security token in the session. Legacy pages SHALL take `icms::$user` from that token, and new-application routes SHALL see the same user with the user's id, login name, display name and group memberships. The system SHALL write the user's id and groups to `$_SESSION['xoopsUserId']` and `$_SESSION['xoopsUserGroups']` for legacy code that reads them, and SHALL NOT take the identity from those keys, except once for a session that ImpressCMS stored before the host owned the session. A user who is deleted or deactivated after logging in SHALL be anonymous on the next request.

#### Scenario: Logged-in user
- **WHEN** a user logs in via the legacy `/user.php` and then requests a new-application route
- **THEN** that route sees the same user id and group memberships as `icms::$user` provides on a legacy page

#### Scenario: Anonymous visitor
- **WHEN** a visitor without a valid session requests a new-application route
- **THEN** the request is treated as anonymous with the anonymous group only

#### Scenario: Identity keys are a mirror
- **WHEN** a session stored by the host contains `xoopsUserId` but no security token
- **THEN** both applications treat the visitor as anonymous, and for a logged-in user `$_SESSION['xoopsUserId']` holds the user's id

#### Scenario: Login from before the host
- **WHEN** a user logged in while ImpressCMS still stored the session itself, and the site is then upgraded
- **THEN** that session is still logged in as the same user

#### Scenario: Deactivated user
- **WHEN** a logged-in user is deactivated and requests a page
- **THEN** the request is anonymous

### Requirement: Legacy login and logout go through the host
The login (`/user.php`, with every ImpressCMS authentication method) SHALL authenticate the user through the host's security layer, which SHALL regenerate the session id. A preload that sets `$_SESSION['xoopsUserId']` while the session starts SHALL log that user in the same way. The legacy logout SHALL end the authentication and invalidate the session for both applications.

#### Scenario: Login regenerates the session
- **WHEN** a visitor with a session logs in via the legacy `/user.php`
- **THEN** the session cookie changes and the new session is authenticated

#### Scenario: Logout
- **WHEN** a logged-in user logs out via the legacy logout and then requests a new-application route
- **THEN** that route treats the request as anonymous

#### Scenario: Login by a preload
- **WHEN** a preload sets `$_SESSION['xoopsUserId']` during the `sessionStart` event of an anonymous visitor
- **THEN** that user is logged in for the page and for later requests
