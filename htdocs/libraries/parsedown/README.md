Parsedown integration for ImpressCMS

This directory is a vendor drop-in location for Parsedown without Composer.

How to install:
1) Download Parsedown.php from https://github.com/erusev/parsedown (stable release)
2) Place the file here as htdocs/libraries/parsedown/Parsedown.php
3) No further configuration is needed. The Markdown handler will detect and use it.

Notes:
- If Parsedown is not present, the Markdown handler will gracefully fall back to escaped plaintext.
- When you move to Composer later, simply remove this directory and run:
  composer require erusev/parsedown
- License: Check Parsedown’s LICENSE for redistribution terms.

