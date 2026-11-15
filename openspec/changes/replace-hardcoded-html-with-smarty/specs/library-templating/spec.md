## ADDED Requirements

### Requirement: Hardcoded HTML inventory
The project SHALL maintain a reproducible inventory of all places in `htdocs/libraries/icms/`
where PHP code emits HTML markup, each classified as Migrate, Partial, Keep or Asset.

#### Scenario: Running the scanner
- **WHEN** the scanner script is executed against `htdocs/libraries/icms/`
- **THEN** it lists every string literal, heredoc or `echo` that contains HTML tags, with file, line and enclosing method
- **AND** docblocks and code comments are excluded from the results

#### Scenario: Kept exceptions
- **WHEN** a location is classified Keep
- **THEN** the inventory records the reason (e.g. sanitiser, plain-text mail, pre-Smarty bootstrap)

### Requirement: Presentational markup rendered via Smarty
Library classes that generate presentational markup SHALL render it through `icms_view_Tpl`
using a named Smarty template instead of string concatenation or `echo` in PHP.

#### Scenario: Rendering a migrated component
- **WHEN** a migrated class (e.g. `icms_view_PageNav::renderNav()`) is called
- **THEN** it assigns data to an `icms_view_Tpl` instance and returns the result of fetching its template
- **AND** the returned string remains semantically identical to the previous output

#### Scenario: Public API stability
- **WHEN** an existing module calls a migrated method
- **THEN** the method signature and return type are unchanged

### Requirement: Overridable templates
Each migrated component SHALL use a documented template name that can be overridden by a theme
or template set, and SHALL allow a custom template to be specified where the component already
supports `customTemplate`.

#### Scenario: Theme override
- **WHEN** a template set provides a replacement for `icms_view_pagenav.html`
- **THEN** the pagination output uses the replacement template

### Requirement: Explicit escaping in templates
Templates SHALL escape plain-text values explicitly and SHALL only output unescaped content
for values documented as pre-rendered, trusted HTML.

#### Scenario: User-supplied value
- **WHEN** a value originating from user input is assigned to a template
- **THEN** the template applies `|escape` (or the value was already sanitised by the caller, noted in a template comment)

### Requirement: Regression guard
The build SHALL fail when new hardcoded HTML markup is added to `htdocs/libraries/icms/`
outside the accepted baseline.

#### Scenario: New hardcoded markup
- **WHEN** a pull request adds `echo '<div>'` to a library class not in the baseline
- **THEN** the CI scanner step reports it and fails
