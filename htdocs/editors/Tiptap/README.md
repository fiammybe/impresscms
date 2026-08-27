# Tiptap editor for ImpressCMS

This editor plugin wires a bundled Tiptap frontend to ImpressCMS form textareas and uses `ueberdosis/tiptap-php` to normalize existing HTML before it is loaded into the editor when the PHP dependency is available.

## Rebuild the frontend bundle

```bash
cd /home/runner/work/impresscms/impresscms/htdocs/editors/Tiptap
npm install
npm run build
```

## Refresh the PHP dependency

```bash
cd /home/runner/work/impresscms/impresscms/htdocs/editors/Tiptap
composer install --no-dev
```
