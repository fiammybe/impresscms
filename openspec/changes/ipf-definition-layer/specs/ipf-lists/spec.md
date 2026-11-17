## Purpose

Produces lists of IPF objects with search, filters, sorting and paging taken from the object's description, and renders them through interchangeable renderers, so that a module needs no hand-written list logic and existing themes keep working.

## ADDED Requirements

### Requirement: List options come from the request and are checked against the description
The system SHALL read search text, filters, sort field, sort order, offset and limit from a request, accepting the parameter names the legacy IPF table uses as well as a short API form (`q`, `sort`, `order`, `page`, `limit`, `filter[<field>]`). It SHALL reject a sort, filter or search field that the object's description does not contain as a persistent field, and SHALL cap the limit.

#### Scenario: Legacy parameter names
- **WHEN** a request carries `mimetype_sortsel=extension` and `mimetype_ordersel=DESC`
- **THEN** the list is sorted by extension, descending

#### Scenario: Unknown sort field
- **WHEN** a request asks to sort by a field the object does not have
- **THEN** the request is rejected and no query is executed

#### Scenario: Excessive limit
- **WHEN** a request asks for more items than the maximum
- **THEN** at most the maximum number of items is returned

### Requirement: Lists return what the legacy table returns
For an IPF object, the system SHALL fetch list items through the object's own handler, so that for the same options the list contains the same objects in the same order as the legacy IPF table, together with the total number of matches.

#### Scenario: Default list
- **WHEN** the mimetype list is requested without options
- **THEN** it contains the same mimetypes, in the same order, as the legacy table

#### Scenario: Quick search
- **WHEN** the mimetype list is requested with a quick search for a text that occurs in one mimetype's name
- **THEN** it contains exactly the mimetypes the legacy table shows for that search, and the total equals their number

### Requirement: Renderers are interchangeable
The system SHALL render a list through a renderer chosen per page. The legacy renderer SHALL produce the same output as the legacy IPF table for the same request. The template-engine renderer SHALL show the same columns and rows, with links for sorting and paging.

#### Scenario: Mimetype admin list unchanged
- **WHEN** the mimetype administration list is served with the legacy renderer
- **THEN** its output equals the recorded output of the legacy page

#### Scenario: Twig renderer shows the same rows
- **WHEN** the mimetype list is rendered with the template-engine renderer
- **THEN** it shows one row per mimetype of the legacy list, in the same order, with the column captions of the object's description

### Requirement: Lists work for ORM-defined objects
For an object declared as an ORM-mapped entity, the system SHALL produce lists with the same options and the same result shape.

#### Scenario: Same rows from both declarations
- **WHEN** the same list options are applied to the IPF mimetype object and to an ORM entity mapped on the same table
- **THEN** both lists contain the same ids in the same order and report the same total
