## Purpose

Lets the administrator see which providers exist for each capability and choose the single active provider. Provider switching and conflict situations are handled explicitly and never silently.

## ADDED Requirements

### Requirement: One active provider per capability
At most one provider SHALL be active for each capability at any time. The active provider SHALL be an installed, active module with a compatible contract version.

#### Scenario: Activating a second provider
- **WHEN** SmartTags is the active tagging provider and the administrator activates AdvancedTags
- **THEN** AdvancedTags becomes the only active tagging provider and SmartTags is listed as available but inactive

### Requirement: Automatic resolution only when unambiguous
When a capability has no active provider and exactly one compatible provider is installed, that provider SHALL become active automatically. When a provider is installed for a capability that already has an active provider, the active provider SHALL stay unchanged. When several compatible providers exist and none is active, the capability SHALL stay `unresolved` until the administrator chooses one.

#### Scenario: First provider installed
- **WHEN** SmartTags is installed and no other tagging provider exists
- **THEN** SmartTags becomes the active tagging provider

#### Scenario: Second provider installed
- **WHEN** AdvancedTags is installed while SmartTags is the active tagging provider
- **THEN** SmartTags stays active, and the capabilities page shows AdvancedTags as an available alternative

### Requirement: Provider administration page
The system administration SHALL have a capabilities page, available only to users with access to that system admin function. For every known capability it SHALL show:
- the title and contract version
- its state (`active`, `unresolved`, `unavailable`)
- every provider, with module name, module version, declared contract version, compatibility, and a link to the provider's own administration when it has one
- which provider is active

The page SHALL let the administrator choose the active provider per capability and rebuild the registry.

#### Scenario: Viewing providers
- **WHEN** an administrator opens the capabilities page with SmartTags and AdvancedTags installed
- **THEN** `tagging` is listed with both providers and the active one marked

#### Scenario: Access denied
- **WHEN** a user without access to the capabilities admin function opens the page
- **THEN** access is denied as for any other system admin function

#### Scenario: Incompatible provider
- **WHEN** a provider declares an incompatible contract version
- **THEN** it is listed with the reason, and it cannot be chosen

### Requirement: Provider switch is confirmed and data stays with its owner
Switching the active provider of a capability that is assigned to at least one object type SHALL require confirmation. The confirmation SHALL state that the existing data of the previous provider is not moved and will not be visible through the new provider. The switch SHALL be protected by a security token. The previous provider's data SHALL stay untouched, so that switching back makes it visible again.

#### Scenario: Confirmed switch
- **WHEN** the administrator switches `tagging` from SmartTags to AdvancedTags and confirms
- **THEN** AdvancedTags is active from the next request on, and SmartTags' tables are unchanged

#### Scenario: Switch without a valid token
- **WHEN** a switch is submitted without a valid security token
- **THEN** it is rejected and the active provider stays unchanged

#### Scenario: Switching back
- **WHEN** the administrator switches `tagging` back to SmartTags
- **THEN** the tags stored earlier through SmartTags are returned again

### Requirement: Conflict states are handled explicitly
The following situations SHALL put the capability in a visible conflict state, shown on the capabilities page and as a warning on the system admin dashboard:
- the active provider is uninstalled or deactivated
- the active provider's update makes its contract version incompatible
- the active provider's declaration becomes invalid

The capability SHALL NOT switch to another provider automatically. Until an administrator resolves the conflict, the capability SHALL be `unresolved`, or `unavailable` when no provider remains.

#### Scenario: Active provider uninstalled while an alternative exists
- **WHEN** AdvancedTags is the active tagging provider and is uninstalled while SmartTags is still installed
- **THEN** `tagging` becomes `unresolved`, a warning asks the administrator to choose a provider, and SmartTags is not activated automatically

#### Scenario: Last provider uninstalled
- **WHEN** the only tagging provider is uninstalled
- **THEN** `tagging` becomes `unavailable`, and its assignments to object types are kept but have no effect

#### Scenario: Uninstall warning
- **WHEN** an administrator starts uninstalling a module that is the active provider of a capability assigned to object types
- **THEN** the uninstall confirmation names the affected capabilities and object types before the uninstall proceeds
