# Migrating an IPF resource to Laravel

A maintainer's checklist for giving one more IPF resource a Laravel
implementation. You never edit generated files. Background:
[ipf-on-laravel.md](ipf-on-laravel.md).

The running example is `system.userrank` (`htdocs/modules/system/admin/userrank/`).

## 0. Can it be migrated?

Compile the resource (step 3) and read the `support` section of
`app-modules/system/Generated/Userrank/UserrankDescriptor.php`:

- **Both lists are empty.** You can use the Blade renderer and the FormRequest.
- **`unsupported.validation` lists fields.** No FormRequest is generated.
  Either add a rule for the control in `App\Ipf\Compiler\RuleCompiler` (with
  tests), or keep the resource on `validation=legacy` and `renderer=legacy`.
- **`unsupported.blade` lists fields.** Either add a component to
  `resources/views/components/ipf/` plus its case in `field.blade.php` (with
  tests), or keep `renderer=legacy`.

`UnsupportedDefinitionException` names the declaration it cannot represent.
Extend `App\Ipf\Definition\DefinitionReader` for it, or change the
declaration.

## 1. Record the legacy behaviour first

Copy `tests/Http/System/MimetypeLegacyCharacterizationTest.php` and
`MimetypeCharacterizationTest.php` for the resource:
- list, sorted list, quick search
- create and edit forms
- create, update and delete posts
- unknown id
- denials

Record the snapshots with the takeover off (`ICMS_UPDATE_SNAPSHOTS=1`). Assert
the database effects directly: rows, `group_permission`, the stored HTML
filter marks.

Add the handler's event order to an integration test, as
`MimetypeResourceTest` does with a recording handler subclass.

## 2. Declare the missing metadata on the handler

In the handler class, using the `Icms\Ipf\Metadata` attributes, restate what
the admin script (`main.php`) builds by hand:

```php
#[\Icms\Ipf\Metadata\ListView(quickSearch: ['rank_title'], createButton: true)]
#[\Icms\Ipf\Metadata\ListColumn('rank_title')]
#[\Icms\Ipf\Metadata\ListColumn('rank_min', align: 'center', width: 80)]
#[\Icms\Ipf\Metadata\LegacyOperations('/modules/system/admin.php', match: ['fct' => 'userrank'], form: 'mod', save: 'adduserrank', delete: 'del')]
class SystemUserrankHandler extends icms_ipf_Handler {
```

- **Inert for legacy.** The attributes change nothing in legacy code.
- **Relations are explicit only.** Add `#[Relation]` only for a real relation;
  a choice control's `itemHandler` stays a choice provider.
- **Pin the copy.** Add a test that checks the attributes against the admin
  script, as `DefinitionCompilerTest::testListMetadataMatchesTheLegacyAdminScript`
  does.

## 3. Register and compile

For a module's first resource, create `app-modules/<dirname>/` with its
`<Dirname>ModuleServiceProvider.php`, and add the module's psr-4 entry to
`composer.json`, for example `"Modules\Profile\\": "app-modules/profile/"`.

`app-modules/system/resources.php`:

```php
'userrank' => [
    'handler' => 'module:system/userrank',
    'controller' => Modules\System\Http\UserrankController::class,
    'middleware' => ['icms.admin:userrank'],
],
```

`app-modules/system/config.php`, with the switches, starting on the legacy
side:

```php
'userrank' => [
    'renderer' => env('ICMS_SYSTEM_USERRANK_RENDERER', 'legacy'),
    'persistence' => env('ICMS_SYSTEM_USERRANK_PERSISTENCE', 'legacy'),
    'validation' => env('ICMS_SYSTEM_USERRANK_VALIDATION', 'form-request'),
    'policy' => env('ICMS_SYSTEM_USERRANK_POLICY', 'generated'),
],
```

Then compile and commit what it writes:

```bash
ICMS_MAINFILE=$PWD/var/test/mainfile.php php artisan icms:definitions:compile --module=system
```

Commit `app-modules/system/Generated/Userrank/`. CI's
`icms:definitions:check` fails whenever those files differ from the
definition, whether edited by hand or not recompiled.

## 4. Write the controller

Copy `app-modules/system/Http/MimetypeController.php`. Its shape:

- `#[ForResource('system.userrank')] IpfResource $resource` for the
  repository, descriptor, texts and `authorize()`
- the generated `UserrankRequest` in `store()` and `update()`;
  `$request->values()` and `$request->permissionGroups()` go to
  `$resource->repository->save()`
- `App\Ipf\View\AdminViews` for Blade pages, and a small
  `Modules\System\View\UserrankLegacyViews` that reproduces the admin
  script's output for `renderer=legacy`
- `App\Admin\LegacyNavigation` for redirects, the previous page and the
  page before the form

It must not use:
- `icms::`
- `icms_getModuleHandler()`
- handler objects
- query-string parsing (`LegacyOperationsController` maps `op` to actions)

The takeover of the legacy URL is registered automatically from
`#[LegacyOperations]`, so routes need no change. Run
`php artisan icms:routes:ownership "/modules/system/admin.php?fct=userrank"`.

## 5. Prove parity, then switch

1. Run the characterization corpus against the takeover with
   `renderer=legacy`. The snapshots must match, except for intended changes
   (the form's security token).
2. Add a semantic Blade test like `MimetypeBladeTest`: field names, values,
   choices, captions, list links under the legacy parameter names, and the
   database effects.
3. Optionally switch to `persistence=eloquent-read`: extend
   `EloquentParityTest` to the resource, then run the Blade test with that
   switch (see `MimetypeEloquentReadTest`).
4. Run `php artisan icms:schema:diff system.userrank`. Fix incompatible
   differences by hand, never automatically. Use `--migration` only for
   additive ones.
5. Run the suites, including the Http suite at routing level 3 with
   `php artisan route:cache`.
6. Change the switches' defaults to the Laravel side in `config.php`.

## Rollback

Without reverting code:
- the whole URL: `ICMS_TAKEOVERS_DISABLED=system.userrank`
- one concern: its switch

See the rollback table in [ipf-on-laravel.md](ipf-on-laravel.md).
