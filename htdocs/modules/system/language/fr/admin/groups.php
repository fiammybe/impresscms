<?php
// $Id: groups.php 10326 2010-07-11 18:54:25Z malanciault $
//%%%%%%	Admin Module Name  AdminGroup 	%%%%%
if (!defined('_AM_DBUPDATED')) {define("_AM_DBUPDATED","Base de donn&eacute;es mise &agrave; jour avec succ&egrave;s!");}

define("_AM_EDITADG","Editer les groupes");
define("_AM_MODIFY","Modifier");
define("_AM_DELETE","Effacer");
define("_AM_CREATENEWADG","Cr&eacute;er un nouveau groupe");
define("_AM_NAME","Nom");
define("_AM_DESCRIPTION","Description");
define("_AM_INDICATES","* indique les champs requis");
define("_AM_SYSTEMRIGHTS","Droits d'administration du Syst&egrave;me");
define("_AM_ACTIVERIGHTS","Droits d'administration des Modules");
define("_AM_IFADMIN","Si un droit d'administration pour un module est coch&eacute;, le droit d'acc&egrave;s pour ce module est toujours activ&eacute;.");
define("_AM_ACCESSRIGHTS","Droits d'acc&egrave;s des modules");
define("_AM_UPDATEADG","Mettre &agrave; jour le groupe");
define("_AM_MODIFYADG","Modifier le groupe");
define("_AM_DELETEADG","Effacer le groupe");
define("_AM_AREUSUREDEL","Etes-vous s&ucirc;r de vouloir suprimer %s ?");
define("_AM_YES","Oui");
define("_AM_NO","Non");
define("_AM_EDITMEMBER","Editer les membres de ce groupe");
define("_AM_MEMBERS","Membres");
define("_AM_NONMEMBERS","Non membres");
define("_AM_ADDBUTTON"," ajouter -->&nbsp;");
define("_AM_DELBUTTON"," <-- retirer&nbsp;");
define("_AM_UNEED2ENTER","Vous devez entrer les infos requises !");

// Added in RC3
define("_AM_BLOCKRIGHTS","Droits d'acc&egrave;s des blocs");

define('_AM_FINDU4GROUP', 'Trouver les utilisateurs pour ce groupe');
define('_AM_GROUPSMAIN', 'Groupes principaux');

define('_AM_ADMINNO', 'Au moins un membre obligatoire dans le groupe webmasters');

# Adding dynamic block area/position system - TheRpLima - 2007-10-21
define("_AM_SBLEFT","Bloc du c&ocirc;t&eacute; - Gauche");
define("_AM_SBRIGHT","Bloc du c&ocirc;t&eacute; - Droite");
define("_AM_CBLEFT","Center Block - Gauche");
define("_AM_CBRIGHT","Bloc centre - Droite");
define("_AM_CBCENTER","Bloc centre - Center");
define("_AM_CBBOTTOMLEFT","Bloc centre - Bas Gauche");
define("_AM_CBBOTTOMRIGHT","Bloc centre - Bas Droite");
define("_AM_CBBOTTOM","Bloc centre - Bas");
#

define("_AM_EDPERM","Autoris&eacute; le choix de l'&eacute;diteur WYSIWYG dans les modules suivants");
define("_AM_DEBUG_PERM","Autoris&eacute; le mode d&eacute;bugs dans les modules suivants");
define("_AM_GROUPMANAGER_PERM","Autoris&eacute; le changement des permissions sur ces groupes");

// Added Since 1.2
define('_MD_AM_ID', 'ID');

define("_AM_SBLEFT_ADMIN","Administration bloc lateral - gauche");
define("_AM_SBRIGHT_ADMIN","Administration bloc lateral - droit");
define("_AM_CBLEFT_ADMIN","Administration bloc central - gauche");
define("_AM_CBRIGHT_ADMIN","Administration bloc central - droit");
define("_AM_CBCENTER_ADMIN","Administration bloc central - centre");
define("_AM_CBBOTTOMLEFT_ADMIN","Administration bloc central - Gauche-dessous");
define("_AM_CBBOTTOMRIGHT_ADMIN","Administration bloc central - Droit-dessous");
define("_AM_CBBOTTOM_ADMIN","Administration bloc central - dessous");
?>