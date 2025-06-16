<?php
// $Id: blocksadmin.php 11329 2011-08-28 11:04:24Z phoenyx $
//%%%%%%	Admin Module Name  Blocks 	%%%%%
if (!defined('_AM_DBUPDATED')) {if (!defined('_AM_DBUPDATED')) {define("_AM_DBUPDATED","Datenbank erfolgreich aktualisiert!");}}

//%%%%%%	blocks.php 	%%%%%
define("_AM_BADMIN","Blockverwaltung");

# Adding dynamic block area/position system - TheRpLima - 2007-10-21
define('_AM_BPADMIN',"Blockpositionen Verwaltung");

define("_AM_ADDBLOCK","Neuen Block hinzufügen");
define("_AM_LISTBLOCK","Alle Blöcke auflisten");
define("_AM_SIDE","Seite");
define("_AM_BLKDESC","Block Beschreibung");
define("_AM_TITLE","Titel");
define("_AM_WEIGHT","Wert");
define("_AM_ACTION","Aktion");
define("_AM_BLKTYPE","Block Typ");
define("_AM_LEFT","Links");
define("_AM_RIGHT","Rechts");
define("_AM_CENTER","Mittig");
define("_AM_VISIBLE","Sichtbar");
define("_AM_POSCONTT","Position der zusätzlichen Inhalte");
define("_AM_ABOVEORG","Über dem ursprünglichen Inhalt");
define("_AM_AFTERORG","Nach dem ursprünglichen Inhalt");
define("_AM_EDIT","Bearbeiten");
define("_AM_DELETE","Löschen");
define("_AM_SBLEFT","Seitenblock - Links");
define("_AM_SBRIGHT","Seitenblock - Rechts");
define("_AM_CBLEFT","Mittenblock - Links");
define("_AM_CBRIGHT","Mittenblock - Rechts");
define("_AM_CBCENTER","Mittenblock - Mitte");
define("_AM_CBBOTTOMLEFT","Mitte Block - unten links");
define("_AM_CBBOTTOMRIGHT","Mitte Block - unten rechts");
define("_AM_CBBOTTOM","Mitte Block - unten mittig");
define("_AM_CONTENT","Inhalt");
define("_AM_OPTIONS","Optionen");
define("_AM_CTYPE","Inhaltstyp");
define("_AM_HTML","HTML");
define("_AM_PHP","PHP Skript");
define("_AM_AFWSMILE","Automatisches Format (Smilies aktiviert)");
define("_AM_AFNOSMILE","Automatisches Format (Smilies deaktiviert)");
define("_AM_SUBMIT","Senden");
define("_AM_CUSTOMHTML","Eigener Block (HTML)");
define("_AM_CUSTOMPHP","Eigener Block (PHP)");
define("_AM_CUSTOMSMILE","Benutzerdefinierter Block (Auto Format + Smilies)");
define("_AM_CUSTOMNOSMILE","Benutzerdefinierter Block (Auto-Format)");
define("_AM_DISPRIGHT","Nur rechte Blöcke anzeigen");
define("_AM_SAVECHANGES","Änderungen speichern");
define("_AM_EDITBLOCK","Block bearbeiten");
define("_AM_SYSTEMCANT","System-Blöcke können nicht gelöscht werden!");
define("_AM_MODULECANT","Dieser Block kann nicht direkt gelöscht werden! Wenn Sie diesen Block deaktivieren möchten, deaktivieren Sie das Modul.");
define("_AM_RUSUREDEL","Bist du sicher, dass du Block '%s ' löschen möchtest?");
define("_AM_NAME","Name");
define("_AM_USEFULTAGS","Nützliche Schlagworte");
define("_AM_BLOCKTAG1","%s wird %s ausdrucken");
define('_AM_SVISIBLEIN', 'Blöcke anzeigen in %s');
define('_AM_TOPPAGE', 'Hauptseite');
define('_AM_VISIBLEIN', 'Sichtbar in');
define('_AM_ALLPAGES', 'Alle Seiten');
define('_AM_TOPONLY', 'Nur Hauptseite');
define('_AM_ADVANCED', 'Erweiterte Einstellungen');
define('_AM_BCACHETIME', 'Cache-Dauer');
define('_AM_BALIAS', 'Alias Name');
define('_AM_CLONE', 'Klonen');  // clone a block
define('_AM_CLONEBLK', 'Klonen'); // cloned block
define('_AM_CLONEBLOCK', 'Einen Klon-Block erstellen');
define('_AM_NOTSELNG', "'%s' ist nicht ausgewählt!"); // error message
define('_AM_EDITTPL', 'Template bearbeiten');
define('_AM_MODULE', 'Module');
define('_AM_GROUP', 'Gruppe');
define('_AM_UNASSIGNED', 'Nicht zugewiesen');

define('_AM_CHANGESTS', 'Die Sichtbarkeit des Blocks ändern');

######################## Added in 1.2 ###################################
define('_AM_BLOCKS_PERMGROUPS','Gruppen, die diesen Block sehen dürfen');

/**
 * The next Language definitions are included since 2.0 of blockadmin module, because now is based on IPF.
 * TODO: Add the rest of the fields, are added only the ones which are shown.
 */
// Texts

// Actions
define("_AM_SYSTEM_BLOCKSADMIN_CREATE", "Neuen Block erstellen");
define("_AM_SYSTEM_BLOCKSADMIN_EDIT", "Block bearbeiten");
define("_AM_SYSTEM_BLOCKSADMIN_MODIFIED", "Block erfolgreich geändert!");
define("_AM_SYSTEM_BLOCKSADMIN_CREATED", "Block erfolgreich erstellt!");

// Fields
define("_CO_SYSTEM_BLOCKSADMIN_NAME", "Name");
define("_CO_SYSTEM_BLOCKSADMIN_NAME_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_TITLE", "Titel");
define("_CO_SYSTEM_BLOCKSADMIN_TITLE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_MID", "Module");
define("_CO_SYSTEM_BLOCKSADMIN_MID_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_VISIBLE", "Sichtbar");
define("_CO_SYSTEM_BLOCKSADMIN_VISIBLE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_CONTENT", "Inhalt");
define("_CO_SYSTEM_BLOCKSADMIN_CONTENT_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_SIDE", "Seite");
define("_CO_SYSTEM_BLOCKSADMIN_SIDE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_WEIGHT", "Wert");
define("_CO_SYSTEM_BLOCKSADMIN_WEIGHT_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_BLOCK_TYPE", "Block Typ");
define("_CO_SYSTEM_BLOCKSADMIN_BLOCK_TYPE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_C_TYPE", "Inhaltstyp");
define("_CO_SYSTEM_BLOCKSADMIN_C_TYPE_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_OPTIONS", "Optionen");
define("_CO_SYSTEM_BLOCKSADMIN_OPTIONS_DSC", "");
define("_CO_SYSTEM_BLOCKSADMIN_BCACHETIME", "Block Cache Zeit");
define("_CO_SYSTEM_BLOCKSADMIN_BCACHETIME_DSC", "");

define("_CO_SYSTEM_BLOCKSADMIN_BLOCKRIGHTS", "Block-Ansicht-Berechtigung");
define("_CO_SYSTEM_BLOCKSADMIN_BLOCKRIGHTS_DSC", "");

define("_AM_SBLEFT_ADMIN","Admin Seitenblock - Links");
define("_AM_SBRIGHT_ADMIN","Admin Seitenblock - Rechts");
define("_AM_CBLEFT_ADMIN","Admin Center Block - Links");
define("_AM_CBRIGHT_ADMIN","Admin Center Block - Rechts");
define("_AM_CBCENTER_ADMIN","Admin Center Block - Mitte");
define("_AM_CBBOTTOMLEFT_ADMIN","Admin Center Block - Unten links");
define("_AM_CBBOTTOMRIGHT_ADMIN","Admin Center Block - Unten rechts");
define("_AM_CBBOTTOM_ADMIN","Admin Center Block - Unten Mitte");
?>