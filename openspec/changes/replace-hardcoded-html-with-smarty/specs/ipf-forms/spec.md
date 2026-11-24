## ADDED Requirements

### Requirement: One definition for the legacy and the framework form
The legacy IPF form and the metadata-driven framework form SHALL be built from one definition of the entity
(`Icms\Ipf\Form\DefinitionBuilder`): the same fields, in the same order, with the same labels, options and
required and read-only state.

#### Scenario: Legacy elements match the definition
- **WHEN** the legacy IPF form of an entity is created
- **THEN** its elements describe themselves as the fields of the entity's definition

#### Scenario: One definition for both forms
- **WHEN** the framework form of an entity is created
- **THEN** its fields are the definition's fields and permissions, with their labels, required and disabled state and choices
