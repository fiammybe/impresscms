# PSR-14 Events in ImpressCMS (Migration Guide)

This guide explains how to use the new PSR-14 event system in ImpressCMS and how to migrate from legacy delegates and preloads while preserving backward compatibility.

ImpressCMS integrates the league/event dispatcher and exposes a central PSR-14 dispatcher via `icms::$events`. Legacy systems continue to work, but are deprecated and bridged to PSR‑14.

## TL;DR (Cheat Sheet)

- Listen to typed events:
  ```php
  icms::$events->subscribeTo(\ImpressCMS\Events\SystemInitEvent::class, function ($e) { /* ... */ });
  ```
- Dispatch typed events:
  ```php
  icms::$events->dispatch(new \ImpressCMS\Events\SystemInitEvent(['stage' => 'boot']));
  ```
- Listen to legacy string events (via LegacyEvent bridge):
  ```php
  icms::$events->subscribeTo(\ImpressCMS\Events\LegacyEvent::class, function (\ImpressCMS\Events\LegacyEvent $e) {
      if (strcasecmp($e->name, 'finishcoreboot') !== 0) return;
      $payload = $e->payload; /* ... */
  });
  ```
- Dispatch legacy string events (bridge):
  ```php
  icms::$events->dispatch(new \ImpressCMS\Events\LegacyEvent('finishcoreboot', $payload));
  ```
- Deprecated (still supported): `icms_Event::attach()`, `icms_Event::trigger()`, `icms_preload_Handler::triggerEvent()`, `icms_preload_Item`. Use PSR‑14 instead.

## Why PSR-14

- Standardized event dispatching across PHP ecosystem
- Strong typing for new events, better tooling and IDE support
- Coexists with legacy systems via a compatibility bridge

## Central Dispatcher

`icms::$events` is an instance of `League\Event\EventDispatcher` (PSR‑14 compliant). It is instantiated early during boot so listeners can register in time.

## Typed Events Provided

- `ImpressCMS\Events\SystemInitEvent` – Dispatched during early boot (stage: `boot`).
- `ImpressCMS\Events\ContentSavedEvent` – Dispatched after successful IPF save/insert/update.
- `ImpressCMS\Events\ModuleInstalledEvent` – Dispatched after a module is successfully installed (admin UI).
- `ImpressCMS\Events\ThemeRenderEvent` – Dispatched before and after theme canvas render (`stage`: `before`/`after`).
- `ImpressCMS\Events\UserLoginSuccessEvent` – Available for wiring at the login success point.

You can add more typed events as needed while maintaining legacy support.

## Listening (Subscribers)

### Prefer typed events
```php
use ImpressCMS\Events\ContentSavedEvent;

icms::$events->subscribeTo(ContentSavedEvent::class, function (ContentSavedEvent $e) {
    // $e->moduleDirname, $e->entity, $e->id, $e->data (the saved object)
});
```

### Legacy string events via LegacyEvent
```php
use ImpressCMS\Events\LegacyEvent;

icms::$events->subscribeTo(LegacyEvent::class, function (LegacyEvent $e) {
    if (strcasecmp($e->name, 'beforefiltertextareainput') !== 0) return;
    $payload = $e->payload; // legacy array/object payload
});
```

### Stopping propagation
`LegacyEvent` implements PSR‑14 `StoppableEventInterface`:
```php
icms::$events->subscribeTo(LegacyEvent::class, function (LegacyEvent $e) {
    if ($e->name === 'someevent') {
        // do something
        $e->stopPropagation(); // prevents subsequent listeners from running
    }
});
```

## Dispatching

### Typed events
```php
use ImpressCMS\Events\ThemeRenderEvent;

icms::$events->dispatch(new ThemeRenderEvent('before', $themeName, [
    'canvasTemplate' => $canvas,
    'contentTemplate' => $content,
]));
```

### Legacy string events (bridge)
```php
use ImpressCMS\Events\LegacyEvent;

icms::$events->dispatch(new LegacyEvent('finishcoreboot', $payload));
```

## Migration from Preloads and Delegates

Legacy preloads (classes with `event*` methods) and delegates remain supported. They are automatically bridged to PSR‑14 by subscribing to `LegacyEvent` under the hood. Migration is encouraged but not required.

### Old (preload method)
```php
class IcmsPreloadExample {
    // Receives legacy payload from preloads
    public function eventFinishCoreBoot($payload = null) {
        // ...
    }
}
```

### New (PSR‑14 listener)
```php
use ImpressCMS\Events\LegacyEvent;

icms::$events->subscribeTo(LegacyEvent::class, function (LegacyEvent $e) {
    if (strcasecmp($e->name, 'finishcoreboot') !== 0) return;
    $payload = $e->payload; // same legacy payload
    // ...
});
```

### Prefer typed events where available
```php
use ImpressCMS\Events\ContentSavedEvent;

icms::$events->subscribeTo(ContentSavedEvent::class, function (ContentSavedEvent $e) {
    // migrate logic from legacy save hooks here
});
```

## Where core dispatches typed events

- Early boot: `SystemInitEvent` – in `icms::boot()`
- IPF save: `ContentSavedEvent` – in `icms_ipf_Handler::insert()` after successful save
- Module install: `ModuleInstalledEvent` – in system admin module installer
- Theme rendering: `ThemeRenderEvent` – in theme render `before` and `after`

These ensure you can migrate incrementally without breaking existing behavior.

## Deprecations (still supported)

- `icms_Event::attach()` → Register PSR‑14 listeners via `icms::$events->subscribeTo()`
- `icms_Event::trigger()` → Dispatch via `icms::$events->dispatch()`; use `LegacyEvent` for string events
- `icms_preload_Handler::triggerEvent()` → Dispatch via `icms::$events->dispatch(new LegacyEvent($event, $payload))`
- `icms_preload_Item` → Avoid extending; use PSR‑14 listeners directly

Deprecation notices are emitted via `icms_core_Debug::setDeprecated()` with migration hints.

## Backward Compatibility

- All legacy delegates and preloads continue to function
- New PSR‑14 listeners can be introduced alongside legacy code
- The `LegacyEvent` bridge enables listening/dispatching with the old event names

## Notes

- `league/event` v3 is used as the PSR‑14 dispatcher implementation
- Follow PSR‑12 and ImpressCMS coding conventions
- Prefer typed events for new work; reserve `LegacyEvent` for bridging legacy flows

