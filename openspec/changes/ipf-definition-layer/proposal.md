## Why

In IPF one object definition (`quickInitVar`/`initVar`/`setControl` in the entity constructor) drives forms, lists, filters and links. On the Symfony host that definition is still read ad hoc: the form mapping of the mimetype pilot reads `Entity::$vars` its own way, lists run entirely through the legacy `Icms\Ipf\View\Table` (which parses `$_GET`/`$_POST`/`$_COOKIE` itself), and there is no API layer at all, so services such as the internal link search (`include/ajax/internallink.php`) query tables with their own SQL. ImpressCMS 3 needs the definition to stay the single source of truth while Symfony components do the work, without asking module developers for extra configuration.

## What Changes

- Introduce a **shared, read-only description of an IPF object** (fields, types, captions, controls, key, identifier, permissions), derived from the existing object definition. Existing objects need no change.
- Accept a **second source of definitions**: a plain Doctrine ORM entity yields the same description, so that new 3.x modules can use ORM relations and still get lists, filters and the API. Existing IPF objects are not moved onto the ORM.
- Introduce a **list pipeline**: search, filters, sorting and paging are resolved from the request against the description (unknown fields are rejected), executed through the object's own handler (or the ORM for ORM-defined objects), and rendered through a renderer interface with two renderers: the legacy table (byte-identical output, theme overrides keep working) and a Twig table.
- Introduce an **opt-in, read-only JSON API** per object (list, search, detail), declared by one manifest entry in `icms_version.php` and guarded by ImpressCMS permissions. Objects that do not opt in are not exposed.
- Proof of concept on the system module's **mimetype** object: its admin list is built through the new pipeline with unchanged output, and it gets an API endpoint. A test-only ORM entity mapped read-only on the same table proves the ORM path.
- No breaking changes. The `Icms\` library is not changed.

## Capabilities

### New Capabilities
- `ipf-metadata`: A uniform, read-only description of an IPF object, derived from its existing definition or from ORM mapping metadata, that every other framework feature consumes.
- `ipf-lists`: Lists of IPF objects with search, filters, sorting and paging derived from the description, with interchangeable renderers.
- `ipf-api`: An opt-in, read-only JSON interface per IPF object (list, search, detail) with ImpressCMS access control.

### Modified Capabilities

None.

## Impact

- **New code (host)**: `src/Ipf/Metadata/*`, `src/Ipf/Query/*`, `src/Ipf/Store/*`, `src/Ipf/Table/*`, `src/Ipf/Api/*`, `src/Controller/Ipf/IpfApiController.php`, `templates/ipf/table.html.twig`, a test-only example entity.
- **Changed code (host)**: `src/Controller/System/MimetypeController.php` builds its list through the new table factory (output unchanged, pinned by the existing snapshots).
- **Legacy files**: one additive manifest key `object_api` in `htdocs/modules/system/icms_version.php`. No change to `htdocs/libraries/Icms/`.
- **Dependencies**: `symfony/serializer` 7.4 and `doctrine/orm` (a release compatible with the pinned `doctrine/dbal` 4.2 and `doctrine/doctrine-bundle` 2.13).
- **Out of scope**: writing through the API; converting `internallink.php`; moving the form mapping (`ControlMap`) onto the new description; upload, date and editor controls; forms and writes for ORM-defined objects; generating the table schema from the description; the other five IPF system pages.
