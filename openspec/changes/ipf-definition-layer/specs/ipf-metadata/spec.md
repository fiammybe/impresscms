## Purpose

Gives every framework feature one uniform, read-only description of an IPF object (its fields, types, captions, controls, key and permissions), derived from the object's existing definition, so that an object is defined once and nothing has to be configured a second time.

## ADDED Requirements

### Requirement: Description is derived from the existing object definition
The system SHALL describe an IPF object from the definition its class already declares, without any change to the object or its handler. The description SHALL contain, for every variable, its name, data type, required flag, maximum length, caption, description, control and whether it is persistent, and for the object its module, item name, table, key field, identifier field, summary field and handler permissions.

#### Scenario: Mimetype object
- **WHEN** the description of the system module's mimetype object is requested
- **THEN** it lists the fields `mimetypeid`, `extension`, `types`, `name` and `dirname` with the data types, required flags and captions of the object's own definition, names `mimetypeid` as the key and `name` as the summary field, and lists the permission `use_extension`

#### Scenario: Unknown object
- **WHEN** a description is requested for a module or item that does not exist
- **THEN** the request fails with an error that names the module and item

### Requirement: Description is read-only and does not alter the object
Requesting a description SHALL NOT change the object, its handler, or any stored data, and the description SHALL NOT be modifiable by its consumers.

#### Scenario: Legacy access keeps working
- **WHEN** a description has been requested and legacy code then reads the object's variables and the handler's item and key names directly
- **THEN** it sees the same values as before the request

### Requirement: An ORM-mapped entity yields the same description
The system SHALL describe an object that is declared as an ORM-mapped entity in the same form as an IPF object, taking field names, types, lengths, the key and required flags from the mapping and captions from the same language-constant convention IPF uses.

#### Scenario: Same table, two declarations
- **WHEN** descriptions are requested for the IPF mimetype object and for an ORM entity mapped on the same table
- **THEN** both list the same persistent field names with the same data types and captions, and name the same key field
