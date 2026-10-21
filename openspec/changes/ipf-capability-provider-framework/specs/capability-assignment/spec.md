## Purpose

Lets the administrator decide which capabilities apply to which IPF object type, and lets consumers and providers check whether a capability is enabled for a given object.

## ADDED Requirements

### Requirement: Object types come from installed IPF modules
The assignable object types SHALL be the IPF items that installed, active modules declare in their module metadata (`object_items`). Each type is identified as `<module>/<item>`. Objects of other kinds SHALL NOT be assignable.

#### Scenario: Listing object types
- **WHEN** the `news` module declares the items `article` and `category`
- **THEN** `news/article` and `news/category` are listed as assignable object types

### Requirement: Capabilities are assigned per object type
The administrator SHALL be able to enable or disable each known capability for each assignable object type, on the capabilities page, in a matrix of object types and capabilities. A capability SHALL be disabled for every object type until it is enabled explicitly, unless the consumer module declares it as enabled by default. Changes SHALL be protected by a security token and SHALL take effect from the next request on.

#### Scenario: Enabling tagging for articles
- **WHEN** the administrator enables `tagging` for `news/article` and disables it for `content/page`
- **THEN** articles get tagging and content pages do not

#### Scenario: Assignment of an unavailable capability
- **WHEN** `workflow` has no provider
- **THEN** its column is shown as unavailable and cannot be enabled, and existing assignments to it are kept

### Requirement: Consumers may restrict and preset assignments
A consumer module MAY declare in its metadata, for each of its object items:
- capabilities it does not support, which SHALL NOT be enabled for that item
- capabilities that are enabled by default when the capability first becomes available

A consumer module SHALL NOT be required to declare anything to make its items assignable.

#### Scenario: Unsupported capability
- **WHEN** the `news` module declares `workflow` as unsupported for `news/category`
- **THEN** the matrix shows that cell as not available, and an attempt to enable it is rejected

#### Scenario: Default enabled capability
- **WHEN** the `news` module declares `tagging` as enabled by default for `news/article` and a tagging provider becomes active for the first time
- **THEN** `tagging` is enabled for `news/article` without administrator action, and the administrator can disable it

### Requirement: Assignments survive module lifecycle changes
Assignments SHALL be kept when a capability loses its provider or when the provider is switched. They SHALL be removed when the consumer module that owns the object type is uninstalled.

#### Scenario: Consumer uninstalled
- **WHEN** the `news` module is uninstalled
- **THEN** all assignments for `news/*` object types are removed

### Requirement: Enabled check for an object
Consumers, providers and the framework SHALL be able to ask whether a capability is enabled for an IPF object or object type. The answer SHALL be true only when the capability is assigned to the object's type, not excluded by the consumer, and has an active provider. The check SHALL NOT query the database more than once per request.

#### Scenario: Enabled check
- **WHEN** `tagging` is assigned to `news/article` and has an active provider
- **THEN** the check returns true for article 42 and false for a `content/page` object

#### Scenario: Provider missing
- **WHEN** `tagging` is assigned to `news/article` but is `unresolved`
- **THEN** the check returns false
