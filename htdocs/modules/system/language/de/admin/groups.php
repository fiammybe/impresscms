<?php
// $Id: groups.php 10326 2010-07-11 18:54:25Z malanciault $
//%%%%%%	Admin Module Name  AdminGroup 	%%%%%
if (!defined('_AM_DBUPDATED')) {define("_AM_DBUPDATED","Datenbank erfolgreich aktualisiert!");}

define("_AM_EDITADG","Gruppe bearbeiten");
define("_AM_MODIFY","Bearbeiten");
define("_AM_DELETE","Löschen");
define("_AM_CREATENEWADG","Neue Gruppe erstellen");
define("_AM_NAME","Name");
define("_AM_DESCRIPTION","Beschreibung");
define("_AM_INDICATES","* zeigt die Pflichtfelder an");
define("_AM_SYSTEMRIGHTS","Kann folgende Systemfunktionen verwalten");
define("_AM_ACTIVERIGHTS","Kann die folgenden Module verwalten");
define("_AM_IFADMIN","Wenn Admin-Recht für ein Modul aktiviert ist, wird der Zugriff rechts für das Modul immer aktiviert.");
define("_AM_ACCESSRIGHTS","Darf auf die folgenden Module zugreifen");
define("_AM_UPDATEADG","Gruppe aktualisieren");
define("_AM_MODIFYADG","Gruppe bearbeiten");
define("_AM_DELETEADG","Gruppe löschen");
define("_AM_AREUSUREDEL","Sind Sie sicher, dass Sie dieses Banner löschen möchten?");
define("_AM_YES","Ja");
define("_AM_NO","Nein");
define("_AM_EDITMEMBER","Mitglieder dieser Gruppe bearbeiten");
define("_AM_MEMBERS","Mitglieder");
define("_AM_NONMEMBERS","Nicht-Mitglieder");
define("_AM_ADDBUTTON"," hinzufügen --> ");
define("_AM_DELBUTTON","<--löschen");
define("_AM_UNEED2ENTER","Sie müssen die erforderlichen Informationen eingeben!");

// Added in RC3
define("_AM_BLOCKRIGHTS","Darf folgende Blöcke sehen");

define('_AM_FINDU4GROUP', 'Benutzer dieser Gruppe finden');
define('_AM_GROUPSMAIN', 'Hauptgruppen');

define('_AM_ADMINNO', 'Es muss mindestens ein Benutzer in der Webmaster-Gruppe sein');

# Adding dynamic block area/position system - TheRpLima - 2007-10-21
define("_AM_SBLEFT","Seitenblock - Links");
define("_AM_SBRIGHT","Seitenblock - Rechts");
define("_AM_CBLEFT","Mittenblock - Links");
define("_AM_CBRIGHT","Mittenblock - Rechts");
define("_AM_CBCENTER","Mittenblock - Mitte");
define("_AM_CBBOTTOMLEFT","Mitte Block - unten links");
define("_AM_CBBOTTOMRIGHT","Mitte Block - unten rechts");
define("_AM_CBBOTTOM","Mitte Block - unten mittig");
#

define("_AM_EDPERM","Kann den WYSIWYG Editor in den folgenden Modulen verwenden");
define("_AM_DEBUG_PERM","Kann den Debug-Modus in folgenden Modulen sehen");
define("_AM_GROUPMANAGER_PERM","Darf Berechtigungen für diese Gruppen ändern");

// Added Since 1.2
define('_MD_AM_ID', 'ID');

define("_AM_SBLEFT_ADMIN","Admin Seitenblock - Links");
define("_AM_SBRIGHT_ADMIN","Admin Seitenblock - Rechts");
define("_AM_CBLEFT_ADMIN","Admin Center Block - Links");
define("_AM_CBRIGHT_ADMIN","Admin Center Block - Rechts");
define("_AM_CBCENTER_ADMIN","Admin Center Block - Mitte");
define("_AM_CBBOTTOMLEFT_ADMIN","Admin Center Block - Unten links");
define("_AM_CBBOTTOMRIGHT_ADMIN","Admin Center Block - Unten rechts");
define("_AM_CBBOTTOM_ADMIN","Admin Center Block - Unten Mitte");
?>