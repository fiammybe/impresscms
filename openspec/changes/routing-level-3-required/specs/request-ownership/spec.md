## REMOVED Requirements

### Requirement: Single public entry point
**Reason**: Directly executed legacy scripts no longer share the session with the host; every PHP request must reach the entry point.
**Migration**: Replaced by "Every request goes through the single entry point". Configure the web server as in `docs/server/`.

## ADDED Requirements

### Requirement: Every request goes through the single entry point
The system SHALL accept every request for the site through one entry point: `index.php` in the web root. The web server SHALL route every PHP request, and every request for an unknown path, to that entry point. The entry point SHALL dispatch each request to the new application or to the legacy application. Once the host is installed, a legacy script that the web server executes itself SHALL NOT run.

#### Scenario: Request reaches the front controller
- **WHEN** an HTTP request is routed to the web root's `index.php`
- **THEN** the host handles it and exactly one of the two applications produces the response

#### Scenario: Script executed by the web server itself
- **WHEN** the host is installed and the web server executes `/user.php` itself instead of sending it to `index.php`
- **THEN** the response is 503 with the routing page and `user.php` does not run, while `/` still reaches the host, and before the host is installed `user.php` runs
