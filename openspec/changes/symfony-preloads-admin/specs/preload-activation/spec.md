## Purpose

Decides which preloads are loaded on a request. Every installed preload file is loaded unless the site has disabled it, so that an administrator can switch a preload off without touching its file.

## ADDED Requirements

### Requirement: Installed preloads are loaded unless disabled
On every request that boots ImpressCMS, the system SHALL load each preload file in the preloads directory and attach its event methods, except the preloads the site has disabled. A disabled preload's file SHALL NOT be included, so none of its code runs and none of its events fire.

#### Scenario: No preload disabled
- **WHEN** the site has no disabled preloads and a page is requested
- **THEN** every preload in the preloads directory is loaded, exactly as before this change

#### Scenario: Disabled preload
- **WHEN** the `googleanalytics` preload is disabled and a page is requested
- **THEN** the `googleanalytics` preload file is not included and its events do not fire, while all other preloads are loaded

#### Scenario: Request served by the framework application
- **WHEN** the `keepalive` preload is disabled and a route of the framework application boots ImpressCMS
- **THEN** the `keepalive` preload is not loaded for that request either

### Requirement: The disabled set is site configuration outside the web root
The set of disabled preloads SHALL be stored per site, outside the web root, and SHALL be available before the database connection exists. It SHALL survive replacing or updating the preload files, a cache clear and a system module update. A preload SHALL be identified by its file name without extension.

#### Scenario: Preload file updated
- **WHEN** a disabled preload's file is replaced by a newer version with the same name
- **THEN** the preload stays disabled

#### Scenario: Cache cleared
- **WHEN** the ImpressCMS and framework caches are cleared
- **THEN** the disabled preloads stay disabled

### Requirement: Disabling never fails a request
Names in the disabled set that do not match an installed preload SHALL be ignored. A missing or unreadable disabled set SHALL be treated as empty, so that every preload is loaded.

#### Scenario: Disabled preload removed from disk
- **WHEN** the disabled set names `oldplugin` and no `oldplugin` preload is installed
- **THEN** pages load normally and every installed preload is loaded

#### Scenario: Corrupt disabled set
- **WHEN** the stored disabled set cannot be read
- **THEN** every installed preload is loaded and the request succeeds
