## ADDED Requirements

### Requirement: Hardcoded HTML inventory
The project SHALL maintain a reproducible inventory of all places in `htdocs/libraries/Icms/`
where PHP code emits HTML markup, each classified as Migrate, Partial, Keep or Asset.

#### Scenario: Running the scanner
- **WHEN** the scanner script is executed against `htdocs/libraries/Icms/`
- **THEN** it lists every string literal, heredoc or `echo` that contains HTML tags, with file, line and enclosing method
- **AND** docblocks and code comments are excluded from the results

#### Scenario: Kept exceptions
- **WHEN** a location is classified Keep
- **THEN** the inventory records the reason (e.g. sanitiser, plain-text mail, pre-Smarty bootstrap)

### Requirement: Presentational markup rendered via Smarty
Library classes that generate presentational markup SHALL render it through `Icms\View\Tpl` (`Icms\View\SystemTemplate`)
using a named Smarty template instead of string concatenation or `echo` in PHP.

#### Scenario: Rendering a migrated component
- **WHEN** a migrated class (e.g. `Icms\View\PageNav::renderNav()`) is called
- **THEN** it assigns data to an `Icms\View\Tpl` instance and returns the result of fetching its template
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
The build SHALL fail when new hardcoded HTML markup is added to `htdocs/libraries/Icms/`
outside the accepted baseline.

#### Scenario: New hardcoded markup
- **WHEN** a pull request adds `echo '<div>'` to a library class not in the baseline
- **THEN** the CI scanner step reports it and fails

### Requirement: Forms render from a neutral definition
Form controls SHALL describe themselves as a field definition (`Icms\Form\Definition`) that depends on no
renderer, and the form templates SHALL receive these definitions rather than the pre-rendered HTML of the
controls they contain.

#### Scenario: Definition free of renderers
- **WHEN** the definition classes are loaded
- **THEN** they refer to no Smarty, Symfony or HTML rendering code

#### Scenario: Control described by its definition
- **WHEN** a select list with options, selected values and a size is described
- **THEN** its definition is a choice field with those options in order, the selected values, the size and whether several values can be selected

#### Scenario: Control that renders itself
- **WHEN** a subclass of a described control overrides `render()` without describing itself
- **THEN** its definition is an explicit legacy field and form layouts use its own HTML

#### Scenario: Layout without pre-rendered control HTML
- **WHEN** a form layout template renders a described control
- **THEN** it can do so from the control's definition alone

#### Scenario: Template not yet imported
- **WHEN** a library template is not in the database yet because the system module was not updated
- **THEN** the template file of the system module is used and hidden fields and tokens still render
