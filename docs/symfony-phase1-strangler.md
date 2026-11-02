# Symfony strangler migration - phase 1

## What this phase changes

- `htdocs/index.php` is now the primary bootstrap entrypoint.
- Requests entering that front controller boot Symfony first (`symfony/public/index.php`).
- Requests classified as Symfony-owned (`/__symfony/*` and `/__migration/*`) are handled by Symfony routes.
- All other requests are delegated to legacy ImpressCMS through `App\Bridge\LegacyBridge`.

## Components

- `App\Bridge\RequestOwnershipDecider`: classifies request ownership (`symfony` vs `legacy`).
- `App\Bridge\LegacyScriptResolver`: resolves legacy script path safely in PHP (no vhost/nginx rules required).
- `App\Bridge\LegacyRequestHydrator`: maps Symfony request data into legacy globals.
- `App\Bridge\LegacyBridge`: runs legacy scripts, captures output/headers, and returns a Symfony `Response`.
- `App\Controller\MigrationStatusController`: diagnostic endpoint at `/__migration/status` and `/__symfony/health`.

## Shared-hosting deployment model

Supported model (no custom vhost/routing required):

1. Keep legacy public files in `htdocs/`.
2. Use either:
   - webroot -> repository root `index.php` (forwards to `htdocs/index.php`), or
   - webroot -> `htdocs/` directly.
3. Install Symfony shell dependencies in `symfony/` with:
   - `cd symfony && composer install`

If Symfony dependencies are not installed, `symfony/public/index.php` falls back to `htdocs/legacy_index.php`.

## Routing and fallback behavior

- Default policy is legacy-first.
- Reserved Symfony routes are explicit and easy to extend in `symfony/config/services.yaml` (`app.symfony_prefixes`).
- Legacy script resolution tries direct `.php` scripts and `<path>/index.php` when appropriate.
- Unsafe path traversal attempts are rejected and fall back to legacy front controller behavior.

## Diagnostics and observability

- Classification logs are written through `error_log()` with `classification` and request path.
- In debug mode, bridge logs include resolved legacy script path and resolution strategy.
- `/__migration/status` returns bridge availability and confirms Symfony bootstrap success.

## Known limitations in phase 1

- This phase does not migrate modules, installer internals, authentication internals, templates, or DB abstraction.
- Static assets remain served from the existing legacy public structure.
- Legacy scripts that terminate execution very early (`exit`) may bypass some bridge post-processing.
