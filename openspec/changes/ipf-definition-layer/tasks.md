## 1. Metadata (D2)

- [x] 1.1 Implement `App\Ipf\Metadata\FieldDefinition` and `EntityDefinition` as immutable value objects
- [x] 1.2 Implement `DefinitionProviderInterface` and `LegacyDefinitionProvider` (blank object from `handler->create()`; `$vars`, controls, key/identifier/summary, `getPermissions()`)
- [x] 1.3 Implement `DefinitionRegistry` (resolve `module/item` through `ModuleHandlerLocator`, memoise per request, error naming module and item for unknown objects)
- [x] 1.4 Tests (spec `ipf-metadata`): the mimetype description equals the object's own `$vars`; unknown object fails; the object and handler are unchanged after a request

## 2. List query and stores (D3, D4)

- [x] 2.1 Implement `ListQuery`, `InvalidListQueryException` and `ListQueryResolver` with the legacy and API dialects, the field whitelist and the limit cap
- [x] 2.2 Implement `ObjectStoreInterface`, `ListResult` and `HandlerStore` (criteria on the handler, `getObjects()`/`getCount()`)
- [x] 2.3 Implement `IpfRepository` (`find`, `list`) choosing the store for a definition
- [x] 2.4 Tests (spec `ipf-lists`): legacy parameter names, unknown sort field rejected, limit capped; row ids equal those of the legacy `Table` for the default list, a sorted list and a quick search

## 3. Table and renderers (D5)

- [x] 3.1 Implement `TableConfig` (columns with custom methods, quick search, actions, intro buttons, default sort) and `TableFactory` with defaults from the definition
- [x] 3.2 Implement `TableRendererInterface` and `LegacyTableRenderer` (adapter around `Icms\Ipf\View\Table`)
- [x] 3.3 Implement `TableView`, `TwigTableRenderer` and `templates/ipf/table.html.twig`
- [x] 3.4 Switch `MimetypeController::list()` to `TableFactory` + `LegacyTableRenderer`; the existing mimetype snapshots must stay unchanged, also with the takeover disabled
- [x] 3.5 Test: the Twig renderer shows the rows of the legacy list in the same order, with the description's captions

## 4. Read API (D6)

- [x] 4.1 Add `symfony/serializer` 7.4; add `api` to `icms.legacy.reserved_prefixes`
- [x] 4.2 Implement `App\Ipf\Api\ResourceRegistry` (reads `object_api` from the module manifest) and add the mimetype entry to `htdocs/modules/system/icms_version.php`
- [x] 4.3 Implement `App\Ipf\Api\EntityNormalizer` (typed raw values, id, title, url, field whitelist)
- [x] 4.4 Implement `App\Controller\Ipf\IpfApiController` (list and detail, GET only, declared read permission, 400/401/403/404 responses)
- [x] 4.5 Tests (spec `ipf-api`): webmaster 200, anonymous 401, `mediaadmin` 403, undeclared object and unknown module 404, search by name, unknown sort field 400, detail and unknown id, typed values, field whitelist, POST 405

## 5. ORM definition source (D7)

- [x] 5.1 Add `doctrine/orm` in a release compatible with the pinned `doctrine/dbal` and `doctrine/doctrine-bundle`; stop and ask if the pins would have to change
- [x] 5.2 Configure the ORM with attribute mapping for `src/Ipf/Example/` in the test environment only; implement `TablePrefixListener`
- [x] 5.3 Implement `DoctrineDefinitionProvider` (type mapping, key, length, required, caption convention) and the read-only example entity `App\Ipf\Example\Mimetype` as `system/mimetype_orm`
- [x] 5.4 Implement `DoctrineStore` (QueryBuilder with bound parameters, paginator)
- [x] 5.5 Tests: both providers give the same persistent field names, data types, captions and key for mimetype; both stores give the same ids, order and total for the same `ListQuery`

## 6. Documentation and verification

- [x] 6.1 Add "IPF definition layer" to `docs/symfony-host.md`: description, list pipeline, renderers, `object_api`, the ORM path and its limits (no schema tool, one store per object)
- [x] 6.2 Tag every test with `#[Group('spec:…')]` and its `@scenario`; run `bin/spec-coverage` with no gaps for `ipf-metadata`, `ipf-lists` and `ipf-api`
- [x] 6.3 Run all suites, PHPStan on `src/` with no new errors, and `php -l` on every changed file
