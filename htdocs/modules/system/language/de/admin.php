<?php
// $Id: admin.php 11023 2011-02-15 01:30:36Z skenow $
//%%%%%%	File Name  admin.php 	%%%%%
//define('_MD_AM_CONFIG','System Configuration');
define('_INVALID_ADMIN_FUNCTION', 'Ungültige Admin Funktion');

// Admin Module Names
define('_MD_AM_ADGS','Gruppen');
define('_MD_AM_BKAD','Blöcke');
define('_MD_AM_MDAD','Module Admin');
define('_MD_AM_SMLS','Smiley');
define('_MD_AM_RANK','Benutzer-Ränge');
define('_MD_AM_USER','Benutzer bearbeiten');
define('_MD_AM_FINDUSER', 'Benutzer finden');
define('_MD_AM_PREF','Einstellungen');
define('_MD_AM_VRSN','Versionsprüfung');
define('_MD_AM_MLUS', 'E-Mail Benutzer');
define('_MD_AM_IMAGES', 'Bilder-Manager');
define('_MD_AM_AVATARS', 'Avatare');
define('_MD_AM_TPLSETS', 'Templates');
define('_MD_AM_COMMENTS', 'Kommentare');
define('_MD_AM_BKPOSAD','Block Positionen');
define('_MD_AM_PAGES','Symlink Manager');
define('_MD_AM_CUSTOMTAGS', 'Eigene Schlagwörter');

// Group permission phrases
define('_MD_AM_PERMADDNG', 'Konnte %s Berechtigung zu %s für Gruppe %s nicht hinzufügen');
define('_MD_AM_PERMADDOK','%s Berechtigung zu %s für die Gruppe %s hinzugefügt');
define('_MD_AM_PERMRESETNG','Konnte die Gruppenberechtigung für Modul %s nicht zurücksetzen');
define('_MD_AM_PERMADDNGP', 'Alle übergeordneten Elemente müssen ausgewählt werden.');

// added in 1.2
if (!defined('_MD_AM_AUTOTASKS')) {define('_MD_AM_AUTOTASKS', 'Automatische Aufgaben');}
define('_MD_AM_ADSENSES', 'AdSense Anzeigen ');
define('_MD_AM_RATINGS', 'Bewertungen');
define('_MD_AM_MIMETYPES', 'Mime Typen');

// added in 1.3
define("_MD_AM_GROUPS_ADVERTISING", "Werbung");
define("_MD_AM_GROUPS_CONTENT", "Inhalt");
define("_MD_AM_GROUPS_LAYOUT", "Layout");
define("_MD_AM_GROUPS_MEDIA", "Medien");
define("_MD_AM_GROUPS_SITECONFIGURATION", "Seiten-Konfiguration");
define("_MD_AM_GROUPS_SYSTEMTOOLS", "System-Tools");
define("_MD_AM_GROUPS_USERSANDGROUPS", "Benutzer und Gruppen");
define('_MD_AM_ADSENSES_DSC', 'Adsenses sind Tags, die Sie überall auf Ihrer Website definieren und verwenden können.');
define('_MD_AM_AUTOTASKS_DSC', 'Automatische Aufgaben erlauben es Ihnen, einen Zeitplan von Aktionen zu erstellen, die das System automatisch ausführt.');
define('_MD_AM_AVATARS_DSC', 'Verwalten Sie die Avatare, die den Benutzern Ihrer Website zur Verfügung stehen.');
define('_MD_AM_BKPOSAD_DSC', 'Verwalten und erstellen Sie Blöcke Positionen, die innerhalb der Themes auf Ihrer Webseite verwendet werden.');
define('_MD_AM_BKAD_DSC', 'Verwalte und erstelle Blöcke, die auf deiner Webseite verwendet werden.');
define('_MD_AM_COMMENTS_DSC', 'Verwalten Sie die Kommentare der Benutzer auf Ihrer Website.');
define('_MD_AM_CUSTOMTAGS_DSC', 'Adsenses sind Tags, die Sie überall auf Ihrer Website definieren und verwenden können.');
define('_MD_AM_USER_DSC', 'Registrierte Benutzer erstellen, bearbeiten oder löschen.');
define('_MD_AM_FINDUSER_DSC', 'Durchsuchen Sie registrierte Benutzer mit Filtern.');
define('_MD_AM_ADGS_DSC', 'Verwalten Sie Berechtigungen, Mitglieder, Sichtbarkeit und Zugriffsrechte von Gruppen von Benutzern.');
define('_MD_AM_IMAGES_DSC', 'Erstellen Sie Gruppen von Bildern und verwalten Sie die Berechtigungen für jede Gruppe. Zuschneiden und ändern Sie die Größe hochgeladener Fotos.');
define('_MD_AM_MLUS_DSC', 'Senden Sie E-Mails an Benutzer ganzer Gruppen - oder filtern Sie Empfänger nach passenden Kriterien.');
define('_MD_AM_MIMETYPES_DSC', 'Verwalten Sie die erlaubten Erweiterungen von Dateien, die auf Ihre Webseite hochgeladen werden.');
define('_MD_AM_MDAD_DSC', 'Verwalten Sie den Status, den Namen der Module oder aktualisieren Sie die Module nach Bedarf.');
define('_MD_AM_RATINGS_DSC', 'Mit diesem Tool können Sie Ihren Modulen eine neue Bewertungsmethode hinzufügen und die Ergebnisse durch diesen Bereich steuern!');
define('_MD_AM_SMLS_DSC', 'Verwalten Sie die verfügbaren Smilies und definieren Sie den zugeordneten Code.');
define('_MD_AM_PAGES_DSC', 'Mit dem Symlink können Sie einen einzigartigen Link auf einer beliebigen Seite Ihrer Website erstellen , die für Blöcke, die spezifisch für eine Seiten-URL sind, oder um direkt innerhalb des Inhalts eines Moduls zu verlinken.');
define('_MD_AM_TPLSETS_DSC', 'Vorlagen sind Sätze von html/css-Dateien, die das Bildschirmlayout von Modulen rendern.');
define('_MD_AM_RANK_DSC', 'Benutzer-Ränge sind Bild, um zwischen den Benutzern in verschiedenen Ebenen Ihrer Website zu unterscheiden!');
define('_MD_AM_VRSN_DSC', 'Verwenden Sie dieses Tool, um Ihr System auf Updates zu überprüfen.');
define('_MD_AM_PREF_DSC',"ImpressCMS Seite Einstellungen");
