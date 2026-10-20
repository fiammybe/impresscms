## ADDED Requirements

### Requirement: Remember me uses rotating persistent tokens
When the "remember me" preference is on and the user asks for it at login, the system SHALL set a remember-me cookie that refers to a random token stored in the database, and SHALL log the user in from it when the session has ended. The token SHALL change at every use and SHALL be deleted at logout. The cookie SHALL NOT be derived from the password or other secrets of the site.

#### Scenario: Remembered login
- **WHEN** a user logs in with "remember me" and returns after the session has ended
- **THEN** the user is logged in again, and the stored token has changed

#### Scenario: Logout forgets the user
- **WHEN** a remembered user logs out
- **THEN** the remember-me cookie is cleared and its token no longer logs anyone in

### Requirement: Users can log in with a link sent by e-mail
The system SHALL send a login link to the address of an active account on request. The link SHALL be signed, SHALL expire after 15 minutes, SHALL work once, and SHALL stop working when the account's password changes. Opening the link SHALL show a confirmation that logs the user in when submitted; opening alone SHALL NOT log in. The response to a request SHALL be the same whether the address is known or not; an unknown address SHALL receive a link to the registration form when registration is open. Requests SHALL be limited per address and IP.

#### Scenario: Login by link
- **WHEN** a user requests a login link, opens it and confirms
- **THEN** the user is logged in

#### Scenario: Opening is not logging in
- **WHEN** a login link is opened without confirming
- **THEN** nobody is logged in, and the link still works for the confirmation

#### Scenario: A link works once
- **WHEN** a login link has been used
- **THEN** using it again does not log in

#### Scenario: Unknown address
- **WHEN** a login link is requested for an address without an account
- **THEN** the response is the same as for a known address, and the mail (if registration is open) only links to the registration form

### Requirement: Lost passwords are reset by link
The system SHALL send a password reset link instead of a password. The link SHALL lead, after confirmation, to a form for a new password, and SHALL stop working once the password has been changed. No password SHALL be sent by e-mail.

#### Scenario: Reset by link
- **WHEN** a user requests a reset, opens the link, confirms and sets a new password
- **THEN** the user can log in with the new password, and the link no longer works

#### Scenario: No password by mail
- **WHEN** a user requests a reset
- **THEN** the mail contains a link and no password
