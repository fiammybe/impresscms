## Purpose

Defines how the active providers of enabled capabilities take part in the life of IPF objects: edit forms, saving and deleting, single views, admin tables, templates, blocks and admin pages. Consumer modules need no code changes for this.

## ADDED Requirements

### Requirement: Integration is inert without enabled capabilities
For an object type that has no enabled capability with an active provider, the following SHALL be identical to the behaviour without the capability framework:
- the IPF forms, saves, deletes, single views and tables
- their HTML output and database writes

#### Scenario: Unassigned object type
- **WHEN** a `content/page` object with no enabled capabilities is edited, saved and listed
- **THEN** the form HTML, the stored row and the table output equal those produced without the framework

### Requirement: Providers contribute fields to IPF edit forms
When an IPF form is built for an object whose type has enabled capabilities, each active provider that declares a form contributor SHALL be able to add fields to the form. The fields SHALL be grouped in one section per capability, after the object's own fields and before the buttons. Field names SHALL be namespaced per capability, so that they cannot collide with the object's own variables. Field values SHALL be prefilled from the provider's data for existing objects.

#### Scenario: Tag field on the article form
- **WHEN** an administrator opens the edit form of article 42, with `tagging` enabled for `news/article`
- **THEN** the form contains a tagging section with an autocomplete tag field prefilled with the article's current tags, and the article's own fields are unchanged

#### Scenario: Name collision avoided
- **WHEN** a consumer object has its own variable named `tags`
- **THEN** the tagging field is submitted under the capability namespace, and the object's `tags` variable is bound unchanged

### Requirement: Submitted capability fields are stored by the provider
When an IPF object is saved through its handler and the request contains that object's capability fields, each provider SHALL validate and store its fields after the object has been stored successfully, using the object's final key value. The fields SHALL be processed only for a form protected by a valid security token. When a provider rejects its fields, its error SHALL be reported in the same way as the object's own errors. The object SHALL stay stored.

#### Scenario: Creating a tagged article
- **WHEN** a new article is submitted with tags `php, cms`
- **THEN** the article is inserted, and the tagging provider stores `php` and `cms` for the article's new reference

#### Scenario: Programmatic save
- **WHEN** consumer code saves an article without a submitted form
- **THEN** no provider changes its data for that article

#### Scenario: Object save fails
- **WHEN** the object's own validation fails and nothing is stored
- **THEN** no provider stores data for the object

### Requirement: Object deletion is propagated to providers
When an IPF object is deleted successfully through its handler, every active provider of a capability enabled for its type SHALL remove its data for that object. A failing provider SHALL NOT undo the deletion. The failure SHALL be logged.

#### Scenario: Deleting a tagged article
- **WHEN** article 42 is deleted
- **THEN** the tagging provider holds no tags for `news/article/42` anymore

### Requirement: Providers contribute to the generic single view
When an object is rendered by the generic IPF single view, active providers that declare a view contributor SHALL be able to add rows to it, for example the tags with links, or the classification.

#### Scenario: Tags in the single view
- **WHEN** article 42 with tags is shown in the generic IPF single view
- **THEN** a row labelled with the capability title lists the tags as links to the provider's tag page

### Requirement: Providers contribute filters to IPF admin tables
When an IPF table lists objects of a type with enabled capabilities, active providers that declare a filter contributor SHALL be able to offer a filter. Applying the filter SHALL restrict the listed objects by limiting the object's key values. This restriction SHALL be combined with the table's own criteria.

#### Scenario: Filtering articles by tag
- **WHEN** an administrator filters the article table by the tag `php`
- **THEN** only articles tagged `php` are listed, with the table's paging and sorting still working

### Requirement: Templates can render capability output for an object
A template tag SHALL render a capability's output for a given IPF object, in a named display variant (for example `list` or `cloud`). The variants are defined by the provider. When the capability is not enabled for the object's type or has no active provider, the tag SHALL render nothing and SHALL NOT raise an error. A theme or a template override SHALL be able to add the tag without changing the consumer module's code.

#### Scenario: Tag list in a custom template
- **WHEN** an article template contains the capability tag for `tagging` with the variant `list`
- **THEN** the active tagging provider's tag list for that article is rendered

#### Scenario: Capability disabled
- **WHEN** the same template is rendered while `tagging` is disabled for `news/article`
- **THEN** the tag renders an empty string

### Requirement: Provider UI is delivered through standard module features
Providers SHALL deliver their management screens and widgets as ordinary module features: admin pages and menu entries, blocks, and pages for the user side. Widgets that show capability data SHALL obtain it through the capability contract, not through the consumer module. A provider's admin page SHALL be linked from the capabilities page.

#### Scenario: Tag cloud block
- **WHEN** the active tagging provider's tag cloud block is placed and configured for `news/article`
- **THEN** it shows tag counts for articles only, without any code in the `news` module

### Requirement: Framework-handled IPF forms receive the same contributions
Forms that the framework application builds from IPF metadata SHALL receive the same capability sections, the same field names and the same storage behaviour as legacy IPF forms.

#### Scenario: Symfony-handled form
- **WHEN** an IPF object type with `tagging` enabled is edited through a framework-handled page
- **THEN** the tagging section is present and its submission is stored by the active provider, exactly as on the legacy page
