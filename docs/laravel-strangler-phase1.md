# Laravel strangler bridge: phase 1

## Design summary

Phase 1 makes a Laravel-hosted HTTP shell the outer runtime while keeping the legacy ImpressCMS application under `htdocs/` intact.

- `public/index.php` is the new public entry point.
- A lightweight Laravel host built from Illuminate routing/container components boots first.
- Explicit Laravel routes stay in `routes/web.php`.
- Everything not claimed by Laravel is resolved into legacy under `htdocs/`.
- Legacy scripts execute in-process and keep their original working directory.
- Direct legacy assets are served as file responses and do not fall through to legacy PHP routing.

## Request ownership

`App\LegacyBridge\RequestOwnershipResolver` reserves Laravel-owned requests in two ways:

1. configured path prefixes from `config/legacy-bridge.php`
2. actual Laravel routes registered in `routes/web.php`

Anything else is legacy-owned by default.

## Legacy resolution behavior

`App\LegacyBridge\LegacyScriptResolver` resolves requests into one of three strategies:

- `direct_asset` for existing non-PHP files under `htdocs/`
- `direct_script` for existing PHP files under `htdocs/`
- `fallback_front_controller` for unmatched legacy routes, which fall back to `htdocs/index.php`

The resolver rejects traversal attempts and keeps all resolved targets inside `htdocs/`.

## Bridge runtime behavior

`App\LegacyBridge\LegacyBridge` and `App\LegacyBridge\LegacyRequestHydrator` provide the bridge runtime.

- snapshots and restores PHP superglobals and process state
- hydrates `$_SERVER`, `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`, `$_REQUEST`, and `$_SESSION`
- sets `SCRIPT_FILENAME`, `SCRIPT_NAME`, `PHP_SELF`, `REQUEST_URI`, and `DOCUMENT_ROOT` for legacy execution
- switches the working directory to the target script directory before inclusion
- captures output buffering, headers, cookies, and response status
- converts the result back into a Symfony/Laravel response object
- emits diagnostic plain-text failures with request metadata and stack traces in debug mode

## MIME behavior

Known web asset extensions use explicit MIME mappings before any `finfo` or `mime_content_type()` fallback. This avoids Windows/fileinfo misclassification for CSS, JS, JSON, SVG, and similar assets.

## Known bridge behaviors and risks

- Legacy scripts that call `exit()` are finalized through the bridge shutdown handler so redirects and installer pages still emit bridged responses.
- The bridge intentionally executes legacy PHP at top level through `bootstrap/legacy_dispatch.php` so installer globals such as `$wizard` remain compatible with `global $wizard` access patterns.
- This phase does not migrate any legacy page into Laravel controllers yet; it only establishes the host/runtime boundary.
- Root and legacy dependencies are now split: install root Composer dependencies for the Laravel host and `htdocs/` Composer dependencies for legacy dependencies.
