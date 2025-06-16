<?php
define("_AM_SBLEFT","Zijblok - links");
define("_AM_SBRIGHT","Zijblok - Rechts");
define("_AM_CBLEFT","Middenblok - Links");
define("_AM_CBRIGHT","Middenblok - Rechts");
define("_AM_CBCENTER","Middenblok - Midden");
define("_AM_CBBOTTOMLEFT","Middenblok - Linksonder");
define("_AM_CBBOTTOMRIGHT","Middenblok - Rechtsonder");
define("_AM_CBBOTTOM","Middenblok - Middenonder");

######################## Added in 1.2 ###################################
define('_AM_SYSTEM_BLOCKSPADMIN_TITLE',"Blok Posities Beheer");
define('_AM_SYSTEM_BLOCKSPADMIN_CREATED', "Nieuwe blokposities aangemaakt");
define('_AM_SYSTEM_BLOCKSPADMIN_MODIFIED', "Blokpositie gewijzigd");
define('_AM_SYSTEM_BLOCKSPADMIN_CREATE',"Nieuwe blokpositie aanmaken");
define('_AM_SYSTEM_BLOCKSPADMIN_EDIT',"Blokpositie aanpassen");
define('_AM_SYSTEM_BLOCKSPADMIN_INFO','Om de nieuwe blok positie op te nemen in het thema, plaatst u de onderstaande code op de plaats waar u het blok wilt laten verschijnen.
<div style="border: 1px dashed #AABBCC; padding:10px; width:86%;">
<{foreach from=$xoBlocks.<b>name_of_position</b> item=block}><br /><{include file="<b>path_to_theme_folder/file_to_show_blocks</b>"}><br /><{/foreach}>
</div>
');

define("_CO_SYSTEM_BLOCKSPADMIN_ID", "Id");
define("_CO_SYSTEM_BLOCKSPADMIN_TITLE", "Titel");
define("_CO_SYSTEM_BLOCKSPADMIN_PNAME", "Positie naam");
define('_CO_SYSTEM_BLOCKSPADMIN_PNAME_DSC',"Naam van de blokpositie, Met deze naam wordt Loop in het theme aangemaakt om de blokken weer te geven.<br/>Gebruik een naam met kleine_letters zonder spaties en speciale karakters.");
define("_CO_SYSTEM_BLOCKSPADMIN_DESCRIPTION", "Omschrijving");

define("_AM_SBLEFT_ADMIN","Administratie zijde blok - Links");
define("_AM_SBRIGHT_ADMIN","Administratie zijde blok - Rechts");
define("_AM_CBLEFT_ADMIN","Administratie centrum blok - Links");
define("_AM_CBRIGHT_ADMIN","Administratie centrum blok - Rechts");
define("_AM_CBCENTER_ADMIN","Administratie centrum blok - Midden");
define("_AM_CBBOTTOMLEFT_ADMIN","Administratie centrum blok - Linksonder");
define("_AM_CBBOTTOMRIGHT_ADMIN","Administratie centrum blok - Rechtsonder");
define("_AM_CBBOTTOM_ADMIN","Administratie centrum blok - Onder");
?>