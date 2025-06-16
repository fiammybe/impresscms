<?php
// $Id: modulesadmin.php 11129 2011-03-29 00:57:50Z skenow $
//%%%%%%	File Name  modulesadmin.php 	%%%%%
define("_MD_AM_MODADMIN","Moduladministration");
define("_MD_AM_MODULE","Modul");
define("_MD_AM_VERSION","Version");
define("_MD_AM_LASTUP","Letzte Aktualisierung");
define("_MD_AM_DEACTIVATED","Deaktiviert");
define("_MD_AM_ACTION","Aktion");
define("_MD_AM_DEACTIVATE","Deaktivieren");
define("_MD_AM_ACTIVATE","Aktivieren");
define("_MD_AM_UPDATE","Aktualisieren");
define("_MD_AM_DUPEN","Doppelter Eintrag in Modultabelle!");
define("_MD_AM_DEACTED","Das ausgewählte Modul wurde deaktiviert. Sie können das Modul nun sicher deinstallieren.");
define("_MD_AM_ACTED","Das ausgewählte Modul wurde aktiviert!");
define("_MD_AM_UPDTED","Das ausgewählte Modul wurde aktualisiert!");
define("_MD_AM_SYSNO","System Modul kann nicht deaktiviert werden.");
define("_MD_AM_STRTNO","Dieses Modul wird auf der Startseite sichtbar sein. Bitte stellen sie es Ihren Wünschen entsprechend ein.");

// added in RC2
define("_MD_AM_PCMFM","Bitte bestätigen Sie:");

// added in RC3
define("_MD_AM_ORDER","Position");
define("_MD_AM_ORDER0","(0 = versteckt)");
define("_MD_AM_ACTIVE","Aktiv");
define("_MD_AM_INACTIVE","Inaktiv");
define("_MD_AM_NOTINSTALLED","Nicht installiert");
define("_MD_AM_NOCHANGE","Keine Änderung");
define("_MD_AM_INSTALL","Installieren");
define("_MD_AM_UNINSTALL","Deinstallieren");
define("_MD_AM_SUBMIT","Senden");
define("_MD_AM_CANCEL","Abbrechen");
define("_MD_AM_DBUPDATE","Datenbank erfolgreich aktualisiert!");
define("_MD_AM_BTOMADMIN","Zurück zur Modul-Administrationsseite");

// %s represents module name
define("_MD_AM_FAILINS","%s konnte nicht installiert werden.");
define("_MD_AM_FAILACT","%s kann nicht aktiviert werden.");
define("_MD_AM_FAILDEACT","%s kann nicht deaktiviert werden.");

define("_MD_AM_FAILUNINS","%s kann nicht deinstalliert werden.");
define("_MD_AM_FAILORDER","Kann %s nicht neu sortieren.");
define("_MD_AM_FAILWRITE","Kann nicht im Hauptmenü aufgeführt werden.");
define("_MD_AM_ALEXISTS","Modul %s existiert bereits.");
define("_MD_AM_ERRORSC", "Fehler:");
define("_MD_AM_OKINS","Modul %s erfolgreich installiert.");
define("_MD_AM_OKACT","Modul %s erfolgreich aktiviert.");
define("_MD_AM_OKDEACT","Modul %s erfolgreich deaktiviert.");
define("_MD_AM_OKUPD","Modul %s erfolgreich aktualisiert.");
define("_MD_AM_OKUNINS","Modul %s erfolgreich deinstalliert.");
define("_MD_AM_OKORDER","Modul %s wurde erfolgreich geändert.");

define('_MD_AM_RUSUREINS', 'Drücken Sie die Schaltfläche unten, um dieses Modul zu installieren');
define('_MD_AM_RUSUREUPD', 'Drücken Sie die Schaltfläche unten, um dieses Modul zu aktualisieren');
define('_MD_AM_RUSUREUNINS', 'Sind Sie sicher, dass Sie dieses Modul deinstallieren wollen?');
define('_MD_AM_LISTUPBLKS', 'Die folgenden Blöcke werden aktualisiert.<br />Wählen Sie die Blöcke, von denen Inhalte (Vorlage und Optionen) überschrieben werden dürfen.<br />');
define('_MD_AM_NEWBLKS', 'Neue Blöcke');
define('_MD_AM_DEPREBLKS', 'Veraltete Blöcke');

define('_MD_AM_MODULESADMIN_SUPPORT', 'Modul Support-Seite');
define('_MD_AM_MODULESADMIN_STATUS', 'Status');
define('_MD_AM_MODULESADMIN_MODULENAME', 'Modul Name');
define('_MD_AM_MODULESADMIN_MODULETITLE', 'Module Titel');
######################## Added in 1.2 ###################################
define('_MD_AM_MOD_DATA_UPDATED',' Moduldaten aktualisiert.');
define('_MD_AM_MOD_REBUILD_BLOCKS','Blöcke neu zusammenstellen...');
define('_MD_AM_INSTALLED', 'Installierte Module');
define('_MD_AM_NONINSTALL', 'Nicht installierte Module');
define('_MD_AM_IMAGESFOLDER_UPDATE_TITLE', 'Das Verzeichnis im Bild-Manager benötigt Schreibrechte');
define('_MD_AM_IMAGESFOLDER_UPDATE_TEXT', '384 / 5000
Übersetzungsergebnisse
Die neue Version des Image Managers hat den Speicherort Ihrer Bilder geändert. Dieses Update versucht, Ihre Bilder an den richtigen Ort zu verschieben, aber dafür muss der Speicherordner über Schreibberechtigungen verfügen. Bitte legen Sie die richtige Berechtigung im Ordner fest <strong>und aktualisieren Sie diese Seite</strong>, bevor Sie unten auf die Schaltfläche Aktualisieren klicken.<br /><strong>Image Manager-Ordner</strong>: %s ');
define('_MD_AM_PLUGINSFOLDER_UPDATE_TITLE', 'Plugins/Preloads muss Schreibrechte haben');
define('_MD_AM_PLUGINSFOLDER_UPDATE_TEXT', 'Die neue Version von ImpressCMS hat den Ort für das preloads Verzeichnis geändert. Dieses Update will das Verzeichnis an die richtige Stelle verschieben, aber dies erfordert, das die Verzeichnisse für plugins und preloads Schreibrechte haben. Vergeben Sie jetzt Schreibrechte für diese beiden Verzeichnisse <strong>und aktualisieren Sie diese Seite</strong> bevor Sie den Update Button betätigen.<br /><strong>Plugins Verzeichnis</strong>: %s<br /><strong>Preloads Verzeichnis</strong>: %s');

// Added and Changed in 1.3
define("_MD_AM_UPDATE_FAIL","%s konnte nicht aktualisiert werden.");
define('_MD_AM_FUNCT_EXEC','Funktion %s wurde erfolgreich ausgeführt.');
define('_MD_AM_FAIL_EXEC','Ausführung von %s fehlgeschlagen.');
define('_MD_AM_INSTALLING','Installieren ');
define('_MD_AM_SQL_NOT_FOUND', 'SQL-Datei bei %s nicht gefunden');
define('_MD_AM_SQL_FOUND', "SQL-Datei bei %s gefunden. <br  /> Tabellen erstellen...");
define('_MD_SQL_NOT_VALID', 'ist keine gültige SQL Datei!');
define('_MD_AM_TABLE_CREATED', 	'Tabelle %s erstellt.');
define('_MD_AM_DATA_INSERT_SUCCESS', 'Daten wurden in Tabelle %s eingefügt.');
define('_MD_AM_RESERVED_TABLE', '%s ist ein reservierter Table!');
define('_MD_AM_DATA_INSERT_FAIL', '%s der INSERT für die Daten konnte in der Datenbank nicht ausgeführt werden.');
define('_MD_AM_CREATE_FAIL', 'Fehler: %s konnte nicht erstellt werden');

define('_MD_AM_MOD_DATA_INSERT_SUCCESS', 'Moduldaten wurden als INSERT hinzugefügt. Modul ID: %s');

define('_MD_AM_BLOCK_UPDATED', 'Block %s aktualisiert. Block ID: %s.');
define('_MD_AM_BLOCK_CREATED', 'Block %s erstellt. Block-ID: %s.');

define('_MD_AM_BLOCKS_ADDING', 'Blöcke hinzufügen...');
define('_MD_AM_BLOCKS_ADD_FAIL', 'FEHLER: Der Block %1$s konnte nicht zur Datenbank hinzugefügt werden! Datenbankfehler: %2$s');
define('_MD_AM_BLOCK_ADDED', 'Block %1$s hinzugefügt. Block ID: %2$s');
define('_MD_AM_BLOCKS_DELETE', 'Block wird gelöscht...');
define('_MD_AM_BLOCK_DELETE_FAIL', 'FEHLER: Block %1$skonnte nicht gelöscht werden. Block-ID: %2$s');
define('_MD_AM_BLOCK_DELETED', 'Block %1$s gelöscht. Block ID: %2$s');
define('_MD_AM_BLOCK_TMPLT_DELETE_FAILED', 'FEHLER: Template des Blocks %1$s konnte nicht von der Datenbank gelöscht werden. Template ID: %2$s');
define('_MD_AM_BLOCK_TMPLT_DELETED', 'Block Template %1$s wurde aus der Datenbank gelöscht. Template ID: %2$s');
define('_MD_AM_BLOCK_ACCESS_FAIL', 'FEHLER: Block Zugriffsrechte konnten nicht hinzugefügt werden. Block ID: %1$s  Gruppen ID: %2$s');
define('_MD_AM_BLOCK_ACCESS_ADDED', 'Block Zugriffsrechte hinzugefügt. Block ID: %1$s, Gruppen ID: %2$s');

define('_MD_AM_CONFIG_ADDING', 'Füge Modul-Konfigurationsdaten hinzu...');
define('_MD_AM_CONFIGOPTION_ADDED', 'Konfigurations-Option hinzugefügt. Name: %1$s Wert: %2$s');
define('_MD_AM_CONFIG_ADDED', 'Konfiguration %s  wurde zur Datenbank hinzugefügt.');
define('_MD_AM_CONFIG_ADD_FAIL', 'FEHLER: Konnte die Konfiguration %s nicht in die Datenbank einfügen.');

define('_MD_AM_PERMS_ADDING', 'Gruppenrechte festlegen...');
define('_MD_AM_ADMIN_PERM_ADD_FAIL', 'FEHLER: Admin-Zugriffsrechte konnten nicht hinzugefügt werden für die Guppe ID: %s');
define('_MD_AM_ADMIN_PERM_ADDED', 'Admin-Rechte für Gruppe ID %s hinzugefügt');
define('_MD_AM_USER_PERM_ADD_FAIL', 'FEHLER: Benutzer-Zugriffsrechte konnten nicht hinzugefügt werden für die Guppe ID: %s');
define('_MD_AM_USER_PERM_ADDED', 'Benutzer-Zugriffsrechte wurden hinzugefügt für die Gruppe ID: %s');

define('_MD_AM_AUTOTASK_FAIL', 'FEHLER: Es konnte ein Auto Task nicht zur Datenbank hinzugefügt werden. Name: %s');
define('_MD_AM_AUTOTASK_ADDED', 'Task zur Auto Task Liste hinzugefügt. Task Name: %s');
define('_MD_AM_AUTOTASK_UPDATE', 'Aktualisiere AutoTasks...');
define('_MD_AM_AUTOTASKS_DELETE', 'Automatische Aufgaben werden gelöscht...');

define('_MD_AM_SYMLINKS_DELETE', 'Lösche Links aus dem Symlink-Manager...');
define('_MD_AM_SYMLINK_DELETE_FAIL', 'FEHLER: Es konnte der Link %1$s nicht von der Datenbank gelöscht werden. Link ID: %2$s');
define('_MD_AM_SYMLINK_DELETED', 'Link %1$s aus der Datenbank gelöscht. Link-ID: %2$s');

define('_MD_AM_DELETE_FAIL', 'FEHLER: %s konnte nicht gelöscht werden');

define('_MD_AM_MOD_UP_TEM','Aktualisiere Templates...');
define('_MD_AM_TEMPLATE_INSERT_FAIL','FEHLER: Vorlage %s konnte nicht in die Datenbank eingefügt werden.');
define('_MD_AM_TEMPLATE_UPDATE_FAIL','FEHLER: Template %s konnte nicht aktualisiert werden.');
define('_MD_AM_TEMPLATE_INSERTED','Vorlage %s zur Datenbank hinzugefügt. (ID: %s)');
define('_MD_AM_TEMPLATE_COMPILE_FAIL','Fehler: Fehler beim Kompilieren der Vorlage %s.');
define('_MD_AM_TEMPLATE_COMPILED','Template %s kompiliert.');
define('_MD_AM_TEMPLATE_RECOMPILED','Vorlage %s neu kompiliert.');
define('_MD_AM_TEMPLATE_RECOMPILE_FAIL','FEHLER: Vorlage %s konnte nicht neu kompiliert werden.');

define('_MD_AM_TEMPLATES_ADDING', 'Vorlagen hinzufügen...');
define('_MD_AM_TEMPLATES_DELETE', 'Lösche Vorlagen...');
define('_MD_AM_TEMPLATE_DELETE_FAIL', 'FEHLER: Vorlage %1$s konnte nicht aus der Datenbank gelöscht werden. Vorlagen-ID: %2$s');
define('_MD_AM_TEMPLATE_DELETED', 'Vorlage %1$s  aus der Datenbank gelöscht. Vorlagen-ID: %2$s');
define('_MD_AM_TEMPLATE_UPDATED', 'Template %s aktualisiert.');

define('_MD_AM_MOD_TABLES_DELETE', 'Modultabellen werden gelöscht...');
define('_MD_AM_MOD_TABLE_DELETE_FAIL', 'FEHLER: Tabelle %s konnte nicht gelöscht werden');
define('_MD_AM_MOD_TABLE_DELETED', 'Tabelle %s gelöscht.');
define('_MD_AM_MOD_TABLE_DELETE_NOTALLOWED', 'FEHLER: Es ist nicht erlaubt die Tabelle %s zu löschen!');

define('_MD_AM_COMMENTS_DELETE', 'Kommentare werden gelöscht...');
define('_MD_AM_COMMENT_DELETE_FAIL', 'Fehler: Kommentare konnten nicht gelöscht werden');
define('_MD_AM_COMMENT_DELETED', 'Kommentare gelöscht');

define('_MD_AM_NOTIFICATIONS_DELETE', 'Benachrichtigungen werden gelöscht...');
define('_MD_AM_NOTIFICATION_DELETE_FAIL', 'FEHLER: Konnte Benachrichtigungen nicht löschen');
define('_MD_AM_NOTIFICATION_DELETED', 'Benachrichtigung gelöscht');

define('_MD_AM_GROUPPERM_DELETE', 'Gruppenberechtigungen werden gelöscht...');
define('_MD_AM_GROUPPERM_DELETE_FAIL', 'FEHLER: Konnte die Gruppenberechtigungen nicht löschen');
define('_MD_AM_GROUPPERM_DELETED', 'Gruppen-Berechtigungen gelöscht');

define('_MD_AM_CONFIGOPTIONS_DELETE', 'Modul-Konfigurationsoptionen werden gelöscht...');
define('_MD_AM_CONFIGOPTION_DELETE_FAIL', 'FEHLER: Konfigurationsdaten konnten nicht aus der Datenbank gelöscht werden. Konfigurations-ID: %s');
define('_MD_AM_CONFIGOPTION_DELETED', 'Konfigurationsdaten aus der Datenbank gelöscht. Konfigurations-ID: %s');

