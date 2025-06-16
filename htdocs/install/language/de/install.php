<?php
/**
 * Installer main english strings declaration file.
 * @copyright	The ImpressCMS project https://www.impresscms.org/
 * @license      http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author       Skalpa Keo <skalpa@xoops.org>
 * @author       Martijn Hertog (AKA wtravel) <martin@efqconsultancy.com>
 * @since        1.0
 * @version		$Id: install.php 12168 2013-05-22 13:25:59Z skenow $
 * @package 		installer
 */

define("SHOW_HIDE_HELP", "Hilfe einblenden oder ausblenden");

define("ALTERNATE_LANGUAGE_MSG", "Laden Sie ein alternatives Sprachpaket von der ImpressCMS-Website herunter");
define("ALTERNATE_LANGUAGE_LNK_MSG", "Wählen Sie eine andere Sprache aus, die hier nicht aufgeführt ist.");
define("ALTERNATE_LANGUAGE_LNK_URL", "https://sourceforge.net/projects/impresscms/files/ImpressCMS%20Languages/");
// Configuration check page
define("SERVER_API", "API des Servers");
define("PHP_EXTENSION", "%s Erweiterung");
define("CHAR_ENCODING", "Zeichenkodierung");
define("XML_PARSING", "XML Analyse");
define("REQUIREMENTS", "Voraussetzungen");
define("_PHP_VERSION", "PHP Version");
define("RECOMMENDED_SETTINGS", "Empfohlene Einstellungen");
define("RECOMMENDED_EXTENSIONS", "Empfohlene Erweiterungen");
define("SETTING_NAME", "Name der Einstellung");
define("RECOMMENDED", "Empfohlen");
define("CURRENT", "Aktuell");
define("RECOMMENDED_EXTENSIONS_MSG", "Die folgende Erweiterungen sind für den normalen Gebrauch nicht unbedingt erforderlich, 
	können aber notwendig sein, um bestimmte Funktionen zu nutzen (z. B. Unterstützung für mehrere Sprachen oder RSS). Die Installation dieser Erweiterungen ist daher empfehlenswert.");
define("NONE", "Nichts");
define("SUCCESS", "Erfolgreich");
define("WARNING", "Warnung");
define("FAILED", "Nicht erfolgreich");

// Titles (main and pages)
define("XOOPS_INSTALL_WIZARD", "%s - Installationsassistent");
define("INSTALL_STEP", "Schritt");
define("INSTALL_H3_STEPS", "Schritte");
define("INSTALL_OUTOF", " von ");
define("INSTALL_COPYRIGHT", "Copyright &copy; 2007-" . date('Y', time()) . " <a href=\"https://www.impresscms.org\" target=\"_blank\">The ImpressCMS Project</a>");

define("LANGUAGE_SELECTION", "Sprachauswahl");
define("LANGUAGE_SELECTION_TITLE", "Bitte Sprache wählen");		// L128
define("INTRODUCTION", "Einleitung");
define("INTRODUCTION_TITLE", "Willkommen zum ImpressCMS Installationsassistenten");		// L0
define("CONFIGURATION_CHECK", "Überprüfung der Konfiguration");
define("CONFIGURATION_CHECK_TITLE", "Überprüfe die Serverkonfiguration");
define("PATHS_SETTINGS", "Pfadeinstellungen");
define("PATHS_SETTINGS_TITLE", "Pfadeinstellungen");
define("DATABASE_CONNECTION", "Datenbankverbindung");
define("DATABASE_CONNECTION_TITLE", "Datenbankverbindung");
define("DATABASE_CONFIG", "Konfiguration der Datenbank");
define("DATABASE_CONFIG_TITLE", "Konfiguration der Datenbank");
define("CONFIG_SAVE", "Konfiguration wird gesichert");
define("CONFIG_SAVE_TITLE", "Sichern der Systemkonfiguration");
define("TABLES_CREATION", "Tabellen anlegen");
define("TABLES_CREATION_TITLE", "Anlegen der Tabellen in der Datenbank");
define("INITIAL_SETTINGS", "Grundeinstellungen");
define("INITIAL_SETTINGS_TITLE", "Bitte die grundlegenden Einstellungen vornehmen");
define("DATA_INSERTION", "Daten werden eingefügt");
define("DATA_INSERTION_TITLE", "Speichern der Einstellungen in der Datenbank");
define("WELCOME", "Willkommen");
define("NO_PHP5", "No PHP 7");
define("WELCOME_TITLE", "Die Installation von ImpressCMS wurde fertiggestellt");		// L0
define("MODULES_INSTALL", "Modulinstallation");
define("MODULES_INSTALL_TITLE", "Installation von Modulen");
define("NO_PHP5_TITLE", "No PHP 7");
define("NO_PHP5_CONTENT", "PHP 7.4.0 minimum is required (PHP 8.3 is recommended) for ImpressCMS to function properly - your installation cannot continue. Please work with your hosting provider to upgrade your environment to a version of PHP that is newer than 7.4.0 (8.3 is recommended) before attempting to install again.");
define("SAFE_MODE", "Safe Mode aktiv");
define("SAFE_MODE_TITLE", "Safe Mode aktiv");
define("SAFE_MODE_CONTENT", "ImpressCMS hat einen aktiven Safe Mode in den Einstellungen von PHP festgestellt. Die Installation kann deshalb nicht fortgesetzt werden. Bitte vor einem erneuten Installationsversuch Rücksprache mit dem Provider halten um diese Einstellung zu ändern.");

// Settings (labels and help text)
define("XOOPS_ROOT_PATH_LABEL", "Physischer Pfad des ImpressCMS Wurzelverzeichnisses"); // L55
define("XOOPS_ROOT_PATH_HELP", "Dies ist der tatsächliche Pfad des ImpressCMS Ordners, das Wurzelverzeichnis der ImpressCMS Installation."); // L59
define("_INSTALL_TRUST_PATH_HELP", "Dies ist der tatsächliche Ort des ImpressCMS trust path. Der trust path ist ein Ordner in einem vertrauenswürdigen Pfad, in dem ImpressCMS und die Module zur Erhöhung der Sicherheit sensible Informationen und Code ablegen. Dieser Ordner sollte außerhalb des ImpressCMS Wurzelverzeichnisses liegen und damit nicht von einem Browser aus erreichbar sein. Falls dieser Ordner noch nicht existiert wird ImpressCMS versuchen ihn anzulegen. Wenn dies nicht möglich ist muss er manuell erstellt werden. <br /><br /><a target='_blank' href='http://wiki.impresscms.org/index.php?title=Trust_Path'>Hier klicken</a> um mehr über den trust path zu erfahren."); // L59

define("XOOPS_URL_LABEL", "Adresse der Website (URL)"); // L56
define("XOOPS_URL_HELP", "Der URL der verwendet wird um diese ImpressCMS Installation aufzurufen."); // L58

define("LEGEND_CONNECTION", "Serververbindung");
define("LEGEND_DATABASE", "Datenbank"); // L51

define("DB_HOST_LABEL", "Hostname des Servers");	// L27
define("DB_HOST_HELP",  "Hostname des Datenbank Servers. Im Zweifelsfall könnte <em>localhost</em> funktionieren"); // L67
define("DB_USER_LABEL", "Benutzername");	// L28
define("DB_USER_HELP",  "Name des Benutzers der sich mit dem Datenbankserver verbinden soll."); // L65
define("DB_PASS_LABEL", "Passwort");	// L52
define("DB_PASS_HELP",  "Passwort des Benutzers der sich mit dem Datenbankserver verbinden soll."); // L68
define("DB_NAME_LABEL", "Name der Datenbank");	// L29
define("DB_NAME_HELP",  "Der Name der Datenbank auf dem Server. Die Installationsroutine wird versuchen eine Datenbank anzulegen wenn noch keine vorhanden ist."); // L64
define("DB_CHARSET_LABEL", "Zeichenkodierung der Datenbank, es wird DRINGEND empfohlen UTF-8 als Standard zu verwenden.");
define("DB_CHARSET_HELP",  "MySQL beinhaltet die Unterstützung zum Speichern von Daten in einer Vielzahl von Zeichenkodierungen sowie zum Vergleich anhand einer Vielzahl an Kollationen.");
define("DB_COLLATION_LABEL", "Datenbank Kollation");
define("DB_COLLATION_HELP",  "Eine Kollation ist ein Regelsatz um Zeichen in einem Zeichensatz zu vergleichen.");
define("DB_PREFIX_LABEL", "Präfix für die Tabellen");	// L30
define("DB_PREFIX_HELP",  "Dieses Präfix wird allen neu hinzugefügten Tabellen vorangestellt um Konflikte mit den Namen bereits vorhandener Tabellen zu vermeiden. Die Voreinstellung kann belassen werden."); // L63
define("DB_PCONNECT_LABEL", "Persistente Verbindung benutzen");	// L54

define("DB_SALT_LABEL", "Salt key für Passwörter");	// L98
define("DB_SALT_HELP",  "Dieser Schlüssel wird Passwörtern in der Funktion icms_encryptPass() hinzugefügt und zum Erzeugen einmaliger und sicherer Passwörter verwendet. Dieser Schlüssel darf später nicht mehr geändert werden, da ansonsten alle gespeicherten Passwörter ungültig werden. Die Voreinstellung kann belassen werden."); // L97

define("LEGEND_ADMIN_ACCOUNT", "Zugang für den Administrator");
define("ADMIN_LOGIN_LABEL", "Benutzerkennung des Administrators"); // L37
define("ADMIN_EMAIL_LABEL", "E-Mail Adresse des Administrators"); // L38
define("ADMIN_PASS_LABEL", "Passwort des Administrators"); // L39
define("ADMIN_CONFIRMPASS_LABEL", "Passwort bestätigen"); // L74
define("ADMIN_SALT_LABEL", "Salt key für Passwörter"); // L99

// Buttons
define("BUTTON_PREVIOUS", "Zurück"); // L42
define("BUTTON_NEXT", "Weiter"); // L47
define("BUTTON_FINISH", "Fertigstellen");
define("BUTTON_REFRESH", "Aktualisieren");
define("BUTTON_SHOW_SITE", "Seite anzeigen");

// Messages
define("XOOPS_FOUND", "%s gefunden");
define("CHECKING_PERMISSIONS", "Berechtigungen der Dateien und Ordner werden geprüft..."); // L82
define("IS_NOT_WRITABLE", "Keine Schreibrechte für %s vorhanden."); // L83
define("IS_WRITABLE", "Schreibrechte für %s vorhanden."); // L84
define("ALL_PERM_OK", "Alle Rechte sind korrekt.");

define("READY_CREATE_TABLES", "Es wurden keine Tabellen von ImpressCMS gefunden.<br />Die Installationsroutine wird die ImpressCMS Tabellen jetzt erstellen.<br />Bitte <em>Weiter</em> drücken um fortzufahren");
define("XOOPS_TABLES_FOUND", "Die ImpressCMS Tabellen sind bereits in der Datenbank vorhanden.<br />Bitte <em>Weiter</em> drücken um fortzufahren."); // L131
define("READY_INSERT_DATA", "Die Installationsroutine wird nun die ersten Daten in die Datenbank einfügen.");
define("READY_SAVE_MAINFILE", "Die Installationsroutine wird nun die Einstellungen in die Datei  <em>mainfile.php</em> schreiben.<br />Bitte <em>Weiter</em> drücken um fortzufahren.");
define("DATA_ALREADY_INSERTED", "Die Daten von ImpressCMS befinden sich bereits in der Datenbank. Es werden keine weiteren Daten abgelegt.<br />Bitte <em>Weiter</em> drücken um fortzufahren.");

// %s is database name
define("DATABASE_CREATED", "Die Datenbank %s wurde angelegt!"); // L43
// %s is table name
define("TABLE_NOT_CREATED", "Tabelle %s konnte nicht angelegt werden"); // L118
define("TABLE_CREATED", "Tabelle %s wurde angelegt."); // L45
define("ROWS_INSERTED", "Es wurde %d Einträge in die Tabelle %s eingefügt."); // L119
define("ROWS_FAILED", "%d Einträge konnten nicht in Tabelle %s eingefügt werden."); // L120
define("TABLE_ALTERED", "Tabelle %s wurde aktualisiert."); // L133
define("TABLE_NOT_ALTERED", "Tabelle %s konnte nicht aktualisiert werden."); // L134
define("TABLE_DROPPED", "Tabelle %s wurde entfernt."); // L163
define("TABLE_NOT_DROPPED", "Tabelle %s konnte nicht entfernt werden."); // L164

// Error messages
define("ERR_COULD_NOT_ACCESS", "Auf den angegebenen Ordner konnte nicht zugegriffen werden. Bitte sicherstellen das er vorhanden und vom Server lesbar ist.");
define("ERR_NO_XOOPS_FOUND", "Im angegebenen Ordner konnte keine Installation von ImpressCMS gefunden werden.");
define("ERR_INVALID_EMAIL", "E-Mail Adresse ungültig"); // L73
define("ERR_REQUIRED", "Bitte alle erforderlichen Angaben tätigen."); // L41
define("ERR_PASSWORD_MATCH", "Die beiden Passwörter stimmen nicht überein");
define("ERR_NEED_WRITE_ACCESS", "Der Server benötigt Schreibrechte auf die folgenden Dateien und Ordner<br/>(z. B. <em>chmod 777 Ordner_Name</em> auf einem UNIX bzw. LINUX Server)"); // L72
define("ERR_NO_DATABASE", "Die Datenbank konnte nicht erstellt werden. Bitte den Administrator des Datenbankservers kontaktieren um das Problem zu beheben."); // L31
define("ERR_NO_DBCONNECTION", "Es konnte keine Verbindung zum Datenbankserver aufgebaut werden."); // L106
define("ERR_WRITING_CONSTANT", "Konstante %s konnte nicht geschrieben werden."); // L122

define("ERR_COPY_MAINFILE", "Die mitgelieferte Datei konnte nicht nach mainfile.php kopiert werden");
define("ERR_WRITE_MAINFILE", "Es konnte nicht in die Datei mainfile.php geschrieben werden. Bitte die Dateirechte prüfen und noch einmal versuchen.");
define("ERR_READ_MAINFILE", "Die Datei mainfile.php konnte nicht zum Lesen geöffnet werden");

define("ERR_WRITE_SDATA", "Es konnte nicht in die Datei sdata.php geschrieben werden. Bitte die Dateirechte prüfen und noch einmal versuchen.");
define("ERR_READ_SDATA", "Die Datei sdata.php konnte nicht zum Lesen geöffnet werden");
define("ERR_INVALID_DBCHARSET", "Der Zeichensatz '%s' wird nicht unterstützt.");
define("ERR_INVALID_DBCOLLATION", "Die Kollation '%s' wird nicht unterstützt.");
define("ERR_CHARSET_NOT_SET", "Es ist kein Standard Zeichensatz für die ImpressCMS Datenbank definiert.");

//
define("_INSTALL_SELECT_MODS_INTRO", 'Bitte aus der nachstehenden Liste die Module auswählen die auf dieser Seite installiert werden sollen.<br /><br />
Die Gruppen der Administratoren und der registrierten Benutzer können standardmäßig auf alle installierten Module zugreifen.<br /><br />
Die Zugriffsrechte für anonyme Gäste können nach der Installation im Administrationsbereich vergeben werden.<br /><br />
Bitte <a href="http://wiki.impresscms.org/index.php?title=Permissions" rel="external">wiki</a> besuchen um weitere Informationen über die Administration von Gruppen zu erhalten.');

define("_INSTALL_SELECT_MODULES", 'Module zur Installation auswählen');
define("_INSTALL_SELECT_MODULES_ANON_VISIBLE", 'Module auswählen die für Besucher sichtbar sein sollen.');
define("_INSTALL_IMPOSSIBLE_MOD_INSTALL", "Das Modul %s konnte nicht installiert werden.");
define("_INSTALL_ERRORS", 'Fehler');
define("_INSTALL_MOD_ALREADY_INSTALLED", "Das Modul %s wurde bereits installiert.");
define("_INSTALL_FAILED_TO_EXECUTE", "Fehler bei der Ausführung");
define("_INSTALL_EXECUTED_SUCCESSFULLY", "Erfolgreich ausgeführt");

define("_INSTALL_MOD_INSTALL_SUCCESSFULLY", "Das Modul %s wurde erfolgreich installiert.");
define("_INSTALL_MOD_INSTALL_FAILED", "Das Modul %s konnte nicht durch die Installationsroutine installiert werden.");
define("_INSTALL_NO_PLUS_MOD", "Es wurden keine Module zur Installation ausgewählt. Bitte mit der Installation durch Klicken auf Weiter fortfahren.");
define("_INSTALL_INSTALLING", "Das Modul %s wird installiert");

define("_INSTALL_TRUST_PATH", "Trust Pfad");
define("_INSTALL_TRUST_PATH_LABEL", "Vertrauenswürdiges Verzeichnis für die ImpressCMS Installation");
define("_INSTALL_WEB_LOCATIONS", "Webadresse");
define("_INSTALL_WEB_LOCATIONS_LABEL", "Webadresse");

define("_INSTALL_TRUST_PATH_FOUND", "Der trust path wurde gefunden.");
define("_INSTALL_ERR_NO_TRUST_PATH_FOUND", "Der trust path konnte nicht gefunden werden.");

define("_INSTALL_COULD_NOT_INSERT", "Modul %s konnte durch die Installationsroutine nicht in der Datenbank registriert werden.");
define("_INSTALL_CHARSET", "UTF-8");

define("_INSTALL_PHYSICAL_PATH", "Physischer Pfad");

define("TRUST_PATH_VALIDATE", "Für den trust path wurde oben ein Name vorgeschlagen. Falls ein anderer Name verwendet werden soll, bitte den obigen Vorschlag mit einem anderen Namen ersetzen. <br /><br />Danach auf Trust path anlegen klicken.");
define("TRUST_PATH_NEED_CREATED_MANUALLY", "Der trust path konnte nicht angelegt werden. Bitte den Pfad manuell erstellen und auf die Schaltfläche Aktualisieren klicken.");
define("BUTTON_CREATE_TUST_PATH", "Trust path anlegen");
define("TRUST_PATH_SUCCESSFULLY_CREATED", "Der trust path wurde erfolgreich angelegt.");

// welcome custom blocks
define("WELCOME_WEBMASTER", "Willkommen!");
define("WELCOME_ANONYMOUS", "Willkommen auf einer mit ImpressCMS realisierten Website!");
define("_MD_AM_MULTLOGINMSG_TXT", 'Die Anmeldung war nicht erfolgreich!! <br />
        <p align="left" style="color:red;">
        Mögliche Ursachen:<br />
         - Eine Anmeldung ist bereits erfolgt.<br />
         - Jemand anderes hat sich unter Verwendung dieses Benutzernamens und Passwortes angemeldet.<br />
         - Die Seite wurde ohne Abmeldung verlassen bzw. wurde der Browser geschlossen.<br />
        </p>
        Bitte ein paar Minuten warten und noch einmal versuchen. Sollte das Problem weiterhin bestehen bitte wir um Kontaktaufnahme mit dem Administrator der Seite.');
define("_MD_AM_RSSLOCALLINK_DESC", 'http://community.impresscms.org/modules/smartsection/backend.php'); //Link to the rrs of local support site
define("_INSTALL_LOCAL_SITE", 'http://www.impresscms.org/'); //Link to local support site
define("_LOCAOL_STNAME", 'ImpressCMS'); //Link to local support site
define("_LOCAL_SLOCGAN", 'Einen dauerhaften Eindruck machen'); //Link to local support site
define("_LOCAL_FOOTER", 'Powered by ImpressCMS &copy; 2007-' . date('Y', time()) . ' <a href=\"https://www.impresscms.org/\" rel=\"external\">The ImpressCMS Project</a>'); //footer Link to local support site
define("_LOCAL_SENSORTXT", '#OPS-#'); //Add local translation
define("_ADM_USE_RTL", "0"); // turn this to 1 if your language is right to left
define("_DEF_LANG_TAGS", 'en,fr,it,nl'); //Add local translation
define("_DEF_LANG_NAMES", 'english,french,italian,dutch'); //Add local translation
define("_LOCAL_LANG_NAMES", 'English,français,Italiano,Nederlands'); //Add local translation
define("_EXT_DATE_FUNC", "0"); // change 0 to 1 if this language has an extended date function

######################## Added in 1.2 ###################################
define("ADMIN_DISPLAY_LABEL", "Pseudonym des Administrators"); // L37
define('_CORE_PASSLEVEL1', 'Zu kurz');
define('_CORE_PASSLEVEL2', 'Schwach');
define('_CORE_PASSLEVEL3', 'Gut');
define('_CORE_PASSLEVEL4', 'Streng');
define('DB_PCONNECT_HELP', "Persistente Verbindungen sind bei langsameren Internetverbindungen nützlich. Für die meisten Installation sind sie nicht erforderlich. Standardeinstellung ist \"Nein\". Im Zweifelsfall \"Nein\" auswählen."); // L69
define("DB_PCONNECT_HELPS",  "Persistente Verbindungen sind bei langsameren Internetverbindungen nützlich. Für die meisten Installation sind sie nicht erforderlich."); // L69

// Added in 1.3
define("FILE_PERMISSIONS", "Dateirechte");
