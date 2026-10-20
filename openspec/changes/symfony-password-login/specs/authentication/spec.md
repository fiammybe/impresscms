## ADDED Requirements

### Requirement: Passwords are stored with a memory-hard hash
The system SHALL hash new and changed passwords with Argon2id, or bcrypt where PHP offers no Argon2id, in the host and in legacy code alike. Passwords SHALL be verified in full, without truncation, and in constant time.

#### Scenario: New password
- **WHEN** a password is set through registration, the account settings, the password reset or the admin user form
- **THEN** the stored value is an Argon2id (or bcrypt) hash

#### Scenario: Long password
- **WHEN** a user whose password is longer than 32 characters logs in with it
- **THEN** the login succeeds, and the same password cut to 32 characters is rejected

### Requirement: Legacy hashes are wrapped and still verify
The system module update SHALL replace every legacy password hash by an Argon2id hash of that legacy hash, so that no legacy hash remains in the database. It SHALL do so in batches that continue on the next run, and a console command SHALL do it at once. A wrapped or legacy hash SHALL still accept the user's password, for every legacy format of ImpressCMS. A successful login SHALL replace it by a plain Argon2id hash.

#### Scenario: Every legacy format verifies
- **WHEN** a password was stored in any legacy format (the salt-column format with `enc_type` 0-16, `$20$`, or `$<enc>$<iterations>$<salt>-<hash>` with `enc_type` 21-40), plain or wrapped
- **THEN** the original password verifies and any other password does not

#### Scenario: Upgrade wraps the hashes
- **WHEN** the system module of a site with legacy password hashes is updated
- **THEN** every legacy hash is replaced by a wrapped one, and its users can still log in

#### Scenario: Login upgrades the hash
- **WHEN** a user with a wrapped hash logs in
- **THEN** the stored value becomes a plain Argon2id hash

### Requirement: The password login runs through the host
The legacy login form (`POST /user.php` with `op=login`) and the closed-site login form SHALL be handled by the host's password authenticator, with the same field names. It SHALL use the configured ImpressCMS authentication method, accept the login name or the e-mail address, reject form posts from another site, redirect to the requested page within the site with the legacy message, and send users with an expired password to the password reset form.

#### Scenario: Successful login
- **WHEN** a user posts the correct login name and password to `/user.php` with a return URL within the site
- **THEN** the user is logged in and redirected there with the welcome message

#### Scenario: Wrong password
- **WHEN** a visitor posts a wrong password
- **THEN** the visitor stays anonymous and is redirected to the login page with the "incorrect login" message

#### Scenario: Login with the e-mail address
- **WHEN** a user posts the e-mail address instead of the login name
- **THEN** the user is logged in

#### Scenario: Cross-site login post
- **WHEN** a login form is posted with an `Origin` or `Referer` of another site
- **THEN** the login is refused

#### Scenario: Expired password
- **WHEN** a user whose password has expired logs in
- **THEN** the user is logged in and redirected to the password reset form

### Requirement: Failed logins are throttled
The system SHALL refuse further login attempts for a login name from an IP address after five failed attempts within a minute.

#### Scenario: Too many attempts
- **WHEN** five wrong passwords for one login name are posted from one address within a minute
- **THEN** a sixth attempt is refused, also with the correct password

### Requirement: Account state is checked at login
The system SHALL refuse the login of accounts that are not activated or deactivated, of users outside the allowed groups while the site is closed, and of users who are already online when multiple logins are not allowed.

#### Scenario: Not activated
- **WHEN** a user whose account is not activated posts correct credentials
- **THEN** the login is refused with the "not activated" message

#### Scenario: Closed site
- **WHEN** the site is closed and a user outside the allowed groups posts correct credentials to the closed-site login form
- **THEN** the login is refused, and a webmaster's login succeeds
