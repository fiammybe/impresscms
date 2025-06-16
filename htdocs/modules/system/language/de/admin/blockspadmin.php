<?php
define("_AM_SBLEFT","Seitenblock - Links");
define("_AM_SBRIGHT","Seitenblock - Rechts");
define("_AM_CBLEFT","Mittenblock - Links");
define("_AM_CBRIGHT","Mittenblock - Rechts");
define("_AM_CBCENTER","Mittenblock - Mitte");
define("_AM_CBBOTTOMLEFT","Mitte Block - unten links");
define("_AM_CBBOTTOMRIGHT","Mitte Block - unten rechts");
define("_AM_CBBOTTOM","Mitte Block - unten mittig");

######################## Added in 1.2 ###################################
define('_AM_SYSTEM_BLOCKSPADMIN_TITLE',"Blockpositionen Verwaltung");
define('_AM_SYSTEM_BLOCKSPADMIN_CREATED', "Neue Blockposition angelegt");
define('_AM_SYSTEM_BLOCKSPADMIN_MODIFIED', "Blockposition geändert");
define('_AM_SYSTEM_BLOCKSPADMIN_CREATE',"Neue Blockposition hinzufügen");
define('_AM_SYSTEM_BLOCKSPADMIN_EDIT',"Blockposition bearbeiten");
define('_AM_SYSTEM_BLOCKSPADMIN_INFO','Um die neuen Blockpositionen auf dem Theme einzufügen, setzen Sie den Code unten an der Stelle, wo er möchte, dass die Blöcke erscheinen.
<div style="border: 1px dashed #AABBCC; padding:10px; width:86%;">
<{foreach from=$xoBlocks.<b>name_of_position</b> item=block}><br /><{include file="<b>path_to_theme_folder/file_to_show_blocks.html</b>"}><br /><{/foreach}>
</div>
');

define("_CO_SYSTEM_BLOCKSPADMIN_ID", "Id");
define("_CO_SYSTEM_BLOCKSPADMIN_TITLE", "Titel");
define("_CO_SYSTEM_BLOCKSPADMIN_PNAME", "Name der Position");
define('_CO_SYSTEM_BLOCKSPADMIN_PNAME_DSC',"Name der Block-Position, mit diesem Namen muss die Schleife im Thema für die Ausstellung von Blöcken erstellt werden.<br/>Verwenden Sie einen Namen mit Kleinbuchstaben, ohne Leerzeichen und Sonderzeichen.");
define("_CO_SYSTEM_BLOCKSPADMIN_DESCRIPTION", "Beschreibung");

define("_AM_SBLEFT_ADMIN","Admin Seitenblock - Links");
define("_AM_SBRIGHT_ADMIN","Admin Seitenblock - Rechts");
define("_AM_CBLEFT_ADMIN","Admin Center Block - Links");
define("_AM_CBRIGHT_ADMIN","Admin Center Block - Rechts");
define("_AM_CBCENTER_ADMIN","Admin Center Block - Mitte");
define("_AM_CBBOTTOMLEFT_ADMIN","Admin Center Block - Unten links");
define("_AM_CBBOTTOMRIGHT_ADMIN","Admin Center Block - Unten rechts");
define("_AM_CBBOTTOM_ADMIN","Admin Center Block - Unten Mitte");
?>