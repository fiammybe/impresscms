## 1. Library

- [x] 1.1 Add `getColumn()`, `getOperator()` and `getValue()` to `Icms\Db\Criteria\Item`
- [x] 1.2 Add `Icms\Ipf\DiskFile\{Exception, Entity, ImageEntity, VideoEntity, ZipEntity, Handler}`

## 2. Tests

- [x] 2.1 `HandlerTest`: listing, sorting, criteria, range, typed objects, paths outside the root
- [x] 2.2 `EntityTest`: rename, move, copy, delete, never overwrite, stay inside the root

## 3. Verify

- [ ] 3.1 Library suite and `bin/spec-coverage --change=ipf-disk-files` green
- [ ] 3.2 PHPStan
