## Purpose

Defines the provider-neutral contracts between consumer modules and capability providers: the generic capability contract, how IPF objects are referenced, how contracts are versioned, and the reference contracts for tagging and taxonomy.

## ADDED Requirements

### Requirement: Contracts live in the core
Every capability contract SHALL be defined by the ImpressCMS core library, never by a provider module. This way a consumer that depends on a contract depends on no module. Every contract SHALL extend a generic capability contract, which exposes:
- the capability name
- the implemented contract version
- the provider module
- whether the capability can serve a given object type

#### Scenario: Consumer without provider dependency
- **WHEN** a consumer module type-hints against the tagging contract and no tagging provider is installed
- **THEN** the consumer's code still loads and runs, and the availability check reports `tagging` as unavailable

### Requirement: IPF objects are referenced provider-neutrally
Capabilities SHALL reference objects by an object reference made of the owning module's directory name, the IPF item name and the object's key value. The reference SHALL be derivable from any `icms_ipf_Object` instance, and also from the three values without loading the object. Only `icms_ipf_Object` instances and their subclasses SHALL be accepted. Any other object SHALL be rejected with an error.

#### Scenario: Reference from an IPF object
- **WHEN** a reference is created from article 42 of the `news` module's `article` item
- **THEN** the reference is `news/article/42`, and it equals a reference built from those three values

#### Scenario: Non-IPF object
- **WHEN** a reference is requested for an object that is not an `icms_ipf_Object`
- **THEN** the request is rejected with an error stating that only IPF objects are supported

#### Scenario: New, unsaved object
- **WHEN** a reference is requested for an IPF object that has no key value yet
- **THEN** the request is rejected, and the capability-aware save flow defers the provider's write until after the object has been inserted

### Requirement: Object references resolve back to IPF objects
Providers SHALL be able to resolve one or several object references back to their IPF objects. Several references of one object type SHALL be resolved in a single query per type, and within one request an object that has already been loaded SHALL NOT be queried again. A reference whose module is not installed or not active, or whose object no longer exists, SHALL resolve to nothing, without an error.

#### Scenario: Resolving a page of tagged articles
- **WHEN** a provider resolves the references of 20 articles of `news/article`
- **THEN** it receives the 20 article objects, loaded with one query for that type

#### Scenario: Consumer module uninstalled
- **WHEN** a provider resolves a reference to `news/article/42` after the `news` module was uninstalled
- **THEN** the reference resolves to nothing, and no error is raised

#### Scenario: Deleted object
- **WHEN** a provider resolves a reference to an article that was deleted with SQL
- **THEN** the reference resolves to nothing, so the provider can treat its data as orphaned

### Requirement: Contracts are versioned
Each contract SHALL carry a semantic version.
- Adding optional behaviour SHALL raise the minor version.
- Changing or removing behaviour that providers or consumers rely on SHALL raise the major version.

The core SHALL support one major version of each contract at a time.

#### Scenario: Minor extension
- **WHEN** the tagging contract adds an optional method in version 1.1
- **THEN** providers that implement 1.1 or higher remain compatible, and consumers can detect whether the active provider implements 1.1

### Requirement: Contracts support batch access
Read operations that consumers use for lists SHALL accept several object references at once and return results keyed by reference. This lets a list of N objects be served in a fixed number of provider queries, not N.

#### Scenario: Tags for a list of articles
- **WHEN** a consumer requests the tags of 20 articles in one call
- **THEN** it receives a map from each article reference to its tags, and objects without tags map to an empty list

### Requirement: Tagging contract
The `tagging` contract (version 1.0) SHALL let consumers:
- set, add and remove the tags of an object
- read the tags of one or several objects
- find object references by tag, optionally limited to an object type, with paging
- search tags by prefix for autocompletion
- read tag usage counts for a tag cloud, optionally limited to an object type
- remove all tags of an object

A tag SHALL be exposed with a stable identifier, a display label and a URL-safe slug. How the provider stores tags is not part of the contract.

#### Scenario: Setting tags
- **WHEN** a consumer sets the tags `php` and `cms` on `news/article/42`
- **THEN** reading the tags of `news/article/42` returns exactly `php` and `cms`

#### Scenario: Finding objects by tag
- **WHEN** a consumer asks for `news/article` objects tagged `php`
- **THEN** it receives the references of exactly those articles, in a stable order, limited to the requested page

#### Scenario: Autocomplete
- **WHEN** a consumer searches tags with the prefix `ph`
- **THEN** it receives at most the requested number of tags whose label starts with `ph`, case-insensitively

### Requirement: Taxonomy contract
The `taxonomy` contract (version 1.0) SHALL let consumers:
- list vocabularies
- read the hierarchical terms of a vocabulary
- assign terms to an object and remove them
- read the terms of one or several objects
- find object references by term, optionally including the term's descendants
- remove all classifications of an object

A term SHALL be exposed with a stable identifier, its vocabulary, a label, a slug and its parent identifier. Whether a vocabulary allows one term or several terms per object SHALL be visible to consumers.

#### Scenario: Hierarchical lookup
- **WHEN** an article is classified under `Europe > Belgium` and a consumer asks for objects in `Europe` including descendants
- **THEN** the article is included

#### Scenario: Single-term vocabulary
- **WHEN** a consumer assigns two terms of a vocabulary that allows a single term per object
- **THEN** the assignment is rejected with an error naming the vocabulary

### Requirement: Object removal clears capability data
Every contract SHALL include an operation that removes all data the provider holds for an object reference. The operation SHALL succeed when there is nothing to remove.

#### Scenario: Removing an untagged object
- **WHEN** the clear operation is called for an object without tags
- **THEN** it succeeds and changes nothing
