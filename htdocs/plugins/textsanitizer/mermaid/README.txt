Mermaid.js TextSanitizer Plugin
=================================

This plugin transforms [mermaid]...[/mermaid] BBCode tags into HTML elements
that can be rendered by the Mermaid.js library.

ARCHITECTURE:
-------------
This plugin works in conjunction with the Mermaid preload filter:
- TextSanitizer plugin: Converts [mermaid] BBCode to <div class="mermaid"> HTML
- Preload filter: Detects diagrams and loads Mermaid.js library from CDN
- No local JavaScript files needed - everything uses CDN or inline code

MISSING ICON:
-------------
You need to add a mermaid.png icon file (16x16 or similar) to this directory
for the editor toolbar button. You can:
- Create a simple icon with a flowchart or diagram symbol
- Download the Mermaid logo from https://mermaid.js.org/
- Use any diagram-related icon

The icon should be named: mermaid.png

INSTALLATION:
-------------
1. Add the mermaid.png icon file to this directory
2. Enable the plugin in the ImpressCMS admin panel:
   System > Preferences > Text Sanitizer Options
3. The Mermaid preload filter (htdocs/plugins/preloads/mermaid.php) will
   automatically detect and load the Mermaid.js library from CDN when needed

USAGE:
------
In your content, use the [mermaid] BBCode tag:

[mermaid]
graph TD
    A[Start] --> B{Is it working?}
    B -->|Yes| C[Great!]
    B -->|No| D[Debug]
    D --> A
[/mermaid]

The plugin will convert this to a <div class="mermaid"> element that
Mermaid.js (loaded from CDN) will render as an interactive diagram.

