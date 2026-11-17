## Purpose

Offers an opt-in, read-only JSON interface per IPF object (list, search and detail) that follows from the object's description and is guarded by ImpressCMS permissions, so that services such as an internal link search can be built without per-object endpoint code.

## ADDED Requirements

### Requirement: Objects are exposed only when their module opts in
The system SHALL expose an object through the API only when its module's manifest declares it, together with the permission that reading requires. Any other object, module or item SHALL answer as not found, without revealing whether it exists.

#### Scenario: Object without a declaration
- **WHEN** the API is asked for an object whose module manifest does not declare it for the API
- **THEN** the response is 404

#### Scenario: Unknown module
- **WHEN** the API is asked for a module that is not installed
- **THEN** the response is 404

### Requirement: Reading requires the declared permission
The system SHALL check the declared read permission with ImpressCMS' own access rules before returning any data. A visitor who is not logged in SHALL get 401 and a logged-in user without the permission SHALL get 403; neither response contains object data.

#### Scenario: Webmaster reads mimetypes
- **WHEN** a webmaster requests the mimetype list from the API
- **THEN** the response is 200 with the mimetypes

#### Scenario: Anonymous visitor
- **WHEN** an anonymous visitor requests the mimetype list from the API
- **THEN** the response is 401 and contains no mimetype data

#### Scenario: Administrator without the right
- **WHEN** a system administrator without the mimetype administration right requests the mimetype list
- **THEN** the response is 403 and contains no mimetype data

### Requirement: List and search
The system SHALL return a JSON list of an exposed object with the items, the total number of matches, the page and the limit, honouring the search, filter, sort and paging options. A search SHALL match only the fields declared as searchable. Invalid options SHALL answer 400.

#### Scenario: Search by name
- **WHEN** the mimetype list is requested with a search text that occurs in one mimetype's name
- **THEN** the response contains that mimetype and the total equals the number of matches

#### Scenario: Unknown sort field
- **WHEN** the list is requested sorted by a field the object does not have
- **THEN** the response is 400

### Requirement: Detail
The system SHALL return a single object by its id as JSON, and 404 for an id that does not exist.

#### Scenario: Existing mimetype
- **WHEN** an existing mimetype is requested by id
- **THEN** the response is 200 with that mimetype's fields

#### Scenario: Unknown id
- **WHEN** a mimetype id that does not exist is requested
- **THEN** the response is 404

### Requirement: Items carry typed values of the exposed fields only
Each item SHALL contain its id, its title and the exposed fields with typed, unformatted values (numbers as numbers, lists as lists). Fields that are not persistent, or that the declaration's field whitelist leaves out, SHALL NOT appear.

#### Scenario: Typed values
- **WHEN** a mimetype is returned
- **THEN** `mimetypeid` is a number, `extension` and `name` are strings, and `dirname` is a list of module directory names

#### Scenario: Field whitelist
- **WHEN** an object is declared with a field whitelist
- **THEN** the returned items contain only those fields, besides id and title

### Requirement: The API is read-only
The system SHALL reject every request method other than GET on API addresses and SHALL NOT change stored data.

#### Scenario: POST to the list
- **WHEN** a POST is sent to the mimetype list address
- **THEN** the response is 405 and nothing is stored
