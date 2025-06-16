<?php
define("_AM_SBLEFT","Bloc de cot&eacute; - gauche");
define("_AM_SBRIGHT","Bloc du c&ocirc;t&eacute; - Droite");
define("_AM_CBLEFT","Center Block - Gauche");
define("_AM_CBRIGHT","Bloc centre - Droite");
define("_AM_CBCENTER","Bloc centre - Center");
define("_AM_CBBOTTOMLEFT","Bloc centre - Bas Gauche");
define("_AM_CBBOTTOMRIGHT","Bloc centre - Bas Droite");
define("_AM_CBBOTTOM","Bloc centre - Bas");

######################## Added in 1.2 ###################################
define('_AM_SYSTEM_BLOCKSPADMIN_TITLE',"Gestion de la position des blocs");
define('_AM_SYSTEM_BLOCKSPADMIN_CREATED', "New Block Position Created");
define('_AM_SYSTEM_BLOCKSPADMIN_MODIFIED', "Block Position Modified");
define('_AM_SYSTEM_BLOCKSPADMIN_CREATE',"Add New Block Position");
define('_AM_SYSTEM_BLOCKSPADMIN_EDIT',"Edit Block Position");
define('_AM_SYSTEM_BLOCKSPADMIN_INFO','To include the new block positions on the theme, put the code bellow in the place where it desires that the blocks appear.
<div style="border: 1px dashed #AABBCC; padding:10px; width:86%;">
<{foreach from=$xoBlocks.<b>name_of_position</b> item=block}><br /><{include file="<b>path_to_theme_folder/file_to_show_blocks.html</b>"}><br /><{/foreach}>
</div>
');

define("_CO_SYSTEM_BLOCKSPADMIN_ID", "Id");
define("_CO_SYSTEM_BLOCKSPADMIN_TITLE", "Titre");
define("_CO_SYSTEM_BLOCKSPADMIN_PNAME", "Position Name");
define('_CO_SYSTEM_BLOCKSPADMIN_PNAME_DSC',"Name of Block Position, it is with this name that will have to be created the Loop in the theme for the exhibition of blocks.<br/>Use a name with small_caption letters, without spaces and special characters.");
define("_CO_SYSTEM_BLOCKSPADMIN_DESCRIPTION", "Description");

define("_AM_SBLEFT_ADMIN","Administration bloc lateral - gauche");
define("_AM_SBRIGHT_ADMIN","Administration bloc lateral - droit");
define("_AM_CBLEFT_ADMIN","Administration bloc central - gauche");
define("_AM_CBRIGHT_ADMIN","Administration bloc central - droit");
define("_AM_CBCENTER_ADMIN","Administration bloc central - centre");
define("_AM_CBBOTTOMLEFT_ADMIN","Administration bloc central - Gauche-dessous");
define("_AM_CBBOTTOMRIGHT_ADMIN","Administration bloc central - Droit-dessous");
define("_AM_CBBOTTOM_ADMIN","Administration bloc central - dessous");
?>