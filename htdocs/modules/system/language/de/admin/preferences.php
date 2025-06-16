<?php
// $Id: preferences.php 12227 2013-07-19 08:07:21Z fiammy $
// %%%%%% Admin Module Name AdminGroup %%%%%
// dont change
if (!defined('_AM_DBUPDATED')) {
	define("_AM_DBUPDATED", "Datenbank Update war erfolgreich!");
}

define("_MD_AM_SITEPREF", "Webseite Einstellungen");
define("_MD_AM_SITENAME", "Website-Name");
define("_MD_AM_SLOGAN", "Slogan Ihrer  Webseite");
define("_MD_AM_ADMINML", "Admin-E-Mail-Adresse");
define('_MD_AM_ADMINMLDSC', 'Alle Informationen werden mit dieser E-Mail-Adresse verschickt, wir empfehlen eine Adresse von Ihrer Web-Domain.');
define("_MD_AM_LANGUAGE", "Standard Sprache");
define("_MD_AM_LANGUAGEDSC", "Wählen Sie Ihre Hauptsprache. Wenn Sie die Mehrsprache aktiviert haben, können Sie eine Sprache wählen. Und wenn Sie in der Mehrsprachigkeit die Sprache Ihres Browsers einstellen, wird ImpressCMS diese Option ignorieren.");
define("_MD_AM_STARTPAGE", "Modul oder Seite Ihrer Startseite");
define("_MD_AM_NONE", "Nichts");
define("_MD_CONTENTMAN", "Inhaltsmanager");
define("_MD_AM_SERVERTZ", "Zeitzone des Servers");
define("_MD_AM_DEFAULTTZ", "Standard-Zeitzone");
define("_MD_AM_DTHEME", "Standard-Template");
define("_MD_AM_THEMESET", "Themes Set");
define("_MD_AM_ANONNAME", "Benutzername für anonyme Benutzer");
define("_MD_AM_MINPASS", "Mindestlänge des Passwortes erforderlich");
define("_MD_AM_NEWUNOTIFY", "Per E-Mail benachrichtigen, wenn sich ein neuer Benutzer registriert hat?");
define("_MD_AM_SELFDELETE", "Benutzern das Löschen ihres eigenen Kontos gestatten?");
define("_MD_AM_SELFDELETEDSC", "Wenn Sie JA wählen, können Ihre Benutzer einen neuen Button in dem Konto finden, mit dem das Konto gelöscht werden kann.");
define("_MD_AM_LOADINGIMG", "Zeige Laden... Bild?");
define("_MD_AM_USEGZIP", "Gzip-Kompression verwenden?");
define("_MD_AM_UNAMELVL", "Wählen Sie den Grad der Strenge für die Benutzernamensfilterung");
define("_MD_AM_STRICT", "Strikt (nur Buchstaben und Zahlen)");
define("_MD_AM_MEDIUM", "Mittel");
define("_MD_AM_LIGHT", "Hell (empfohlen für Multi-Byte-Zeichen)");
define("_MD_AM_USERCOOKIE", "Name für Benutzercookies.");
define("_MD_AM_USERCOOKIEDSC", "Dieses Cookie enthält nur einen Benutzernamen und wird ein Jahr lang in einem PC gespeichert (wenn der Benutzer dies wünscht). Wenn ein Benutzer dieses Cookie besitzt, wird der Benutzername automatisch in das Login-Feld eingefügt.");
define("_MD_AM_USEMYSESS", "Benutzerdefinierte Sitzung verwenden");
define("_MD_AM_USEMYSESSDSC", "Wählen Sie Ja aus, um Sitzungsbezogene Werte anzupassen.");
define("_MD_AM_SESSNAME", "Sitzungsname");
define("_MD_AM_SESSNAMEDSC", "Der Name der Sitzung (gültig nur, wenn 'benutzerdefinierte Sitzung verwenden' aktiviert ist)");
define("_MD_AM_SESSEXPIRE", "Sitzungsablauf");
define("_MD_AM_SESSEXPIREDSC", "Maximale Dauer der Session-Leerlaufzeit in Minuten (Gültig nur wenn 'Benutzerdefinierte Sitzung verwenden' aktiviert ist. Funktioniert nur, wenn Sie PHP4.2.0 oder höher verwenden.)");
define("_MD_AM_MYIP", "Deine IP-Adresse");
define("_MD_AM_MYIPDSC", "Diese IP zählt nicht als Impression für Banner ");
define("_MD_AM_ALWDHTML", "HTML-Tags sind in allen Beiträgen erlaubt.");
define("_MD_AM_INVLDMINPASS", "Ungültiger Wert für die Mindestlänge des Passworts.");
define("_MD_AM_INVLDUCOOK", "Ungültiger Wert für UserCookie-Namen.");
define("_MD_AM_INVLDSCOOK", "Ungültiger Wert für sessioncookie-Name.");
define("_MD_AM_INVLDSEXP", "Ungültiger Wert für sessioncookie-Name.");
define("_MD_AM_ADMNOTSET", "Admin-E-Mail ist nicht gesetzt.");
define("_MD_AM_YES", "Ja");
define("_MD_AM_NO", "Nein");
define("_MD_AM_DONTCHNG", "Keine Änderung!");
define("_MD_AM_REMEMBER", "Denken Sie daran, diese Datei zu chmod 666 zu verwenden, um das System richtig darauf schreiben zu lassen.");
define("_MD_AM_IFUCANT", "Wenn Sie die Berechtigungen nicht ändern können, können Sie den Rest der Datei von Hand bearbeiten.");

define("_MD_AM_COMMODE", "Standardmodus für Kommentaranzeige");
define("_MD_AM_COMORDER", "Anzeigereihenfolge für Kommentare");
define("_MD_AM_ALLOWHTML", "HTML Tags in Benutzerkommentare erlauben?");
define("_MD_AM_DEBUGMODE", "Entwickler Übersicht");
define("_MD_AM_DEBUGMODEDSC", "Mehrere Debug-Optionen. Auf einer laufenden Webseite sollte dies deaktiviert sein.");
define("_MD_AM_AVATARALLOW", "Eigenen Avatar hochladen erlauben?");
define("_MD_AM_AVATARALLOWDSC", "Wenn Sie diese Option aktivieren, können Sie mehrere Optionen einstellen für die Avatare  (Breite, Höhe, Datei Größe).");
define('_MD_AM_AVATARMP', 'Minimal Anzahl von Beiträgen');
define('_MD_AM_AVATARMPDSC', 'Geben Sie die minimale Anzahl von Beiträgen ein, die zum Hochladen eines benutzerdefinierten Avatars erforderlich sind');
define("_MD_AM_AVATARW", "Maximale Breite des Avatars (Pixel)");
define("_MD_AM_AVATARH", "Maximale Höhe des Avatars (Pixel)");
define("_MD_AM_AVATARMAX", "Maximale Dateigröße des Avatars (Byte)");
define("_MD_AM_AVATARCONF", "Benutzerdefinierte Avatar Einstellungen");
define("_MD_AM_CHNGUTHEME", "Ändere das Theme aller Benutzer");
define("_MD_AM_NOTIFYTO", "Wählen Sie eine Gruppe aus, an die neue Benutzer-Benachrichtigungsmail gesendet wird");
define("_MD_AM_ALLOWTHEME", "Benutzern erlauben, das Design auszuwählen?");
define("_MD_AM_ALLOWIMAGE", "Benutzern das Anzeigen von Bilddateien in Beiträgen erlauben?");

define("_MD_AM_USERACTV", "Erfordert die Aktivierung durch den Benutzer (empfohlen)");
define("_MD_AM_AUTOACTV", "Automatisch aktivieren");
define("_MD_AM_ADMINACTV", "Aktivierung durch Administrator");
define("_MD_AM_REGINVITE", "Registrierung per Einladung");
define("_MD_AM_ACTVTYPE", "Aktivierungstyp von neu registrierten Benutzern auswählen");
define("_MD_AM_ACTVGROUP", "Gruppe auswählen, an die Aktivierungsmail gesendet werden soll");
define("_MD_AM_ACTVGROUPDSC", "Gültig nur wenn 'Aktivierung durch Administratoren' ausgewählt ist");
define('_MD_AM_USESSL', 'SSL für den Login verwenden?');
define('_MD_AM_USESSLDSC', 'Wählen Sie nur JA aus, wenn Sie ein SSL-Zertifikat haben. Wenn Sie diese Option nutzen möchten, kopieren Sie bitte die richtigen Dateien aus dem heruntergeladenen ImpressCMS EXTRA Ordner in Ihren Root-Pfad.');
define('_MD_AM_SSLPOST', 'SSL Post-Variablenname');
define('_MD_AM_SSLPOSTDSC', 'Der Name der Variable, die zur Übertragung des Sitzungswertes über POST verwendet wird. Wenn Sie sich nicht sicher sind, setzen Sie einen Namen, der schwer zu erraten ist.');
define('_MD_AM_DEBUGMODE0', 'Aus');
define('_MD_AM_DEBUGMODE1', 'Debug aktivieren (Inline-Modus)');
define('_MD_AM_DEBUGMODE2', 'Debug aktivieren (Popup-Modus)');
define('_MD_AM_DEBUGMODE3', 'Smarty Templates Debug');
define('_MD_AM_MINUNAME', 'Minimale Länge des Benutzernamens erforderlich');
define('_MD_AM_MAXUNAME', 'Maximale Länge des Nutzernamens');
define('_MD_AM_GENERAL', 'Allgemeine Einstellungen');
define('_MD_AM_USERSETTINGS', 'Benutzereinstellungen');
define('_MD_AM_ALLWCHGMAIL', 'Benutzern erlauben, die E-Mail-Adresse zu ändern?');
define('_MD_AM_ALLWCHGMAILDSC', '');
define('_MD_AM_IPBAN', 'IP-Sperrung');
define('_MD_AM_BADEMAILS', 'Geben Sie E-Mails ein, die nicht im Nutzerprofil verwendet werden sollen');
define('_MD_AM_BADEMAILSDSC', 'Trennen Sie jede mit einem <b>|</b>, Groß-/Kleinschreibung unempfindlich, Regex aktiviert.');
define('_MD_AM_BADUNAMES', 'Geben Sie Namen ein, die nicht als Benutzername gewählt werden sollen');
define('_MD_AM_BADUNAMESDSC', 'Trennen Sie jede mit einem <b>|</b>, Groß-/Kleinschreibung unempfindlich, Regex aktiviert.');
define('_MD_AM_DOBADIPS', 'IP-Bannungen aktivieren?');
define('_MD_AM_DOBADIPSDSC', 'Benutzer von angegebenen IP-Adressen können Ihre Website nicht sehen');
define('_MD_AM_BADIPS', 'Geben Sie die IP-Adressen ein, die von der Site gebannt werden sollen.<br />Trennen Sie jede mit einem <b>|</b>, Groß-/Kleinschreibung unbeachtet, Regex aktiviert.');
define('_MD_AM_BADIPSDSC', '^aa.bbb.ccc wird Besuchern mit einer IP-Adresse, die mit aaa.bbb.ccc beginnt verbieten<br />aaa.bbbbbb. cc$ wird Besuchern mit einer IP-Adresse, die mit aaa.bbb.ccc endet<br />aaa.bbb.ccc verwehren, Besucher mit einer IP-Adresse, die aaa.bb.ccc enthält');
define('_MD_AM_PREFMAIN', 'Haupteinstellungen');
define('_MD_AM_METAKEY', 'Meta Keywords');
define('_MD_AM_METAKEYDSC', 'Die Schlüsselwörter Meta-Tag ist eine Reihe von Schlüsselwörtern, die den Inhalt Ihrer Website. Geben Sie Schlüsselwörter mit jedem durch ein Komma oder ein Leerzeichen getrennt ein. (Ex. ImpressCMS, PHP, Datenbank, Portal System)');
define('_MD_AM_METARATING', 'Meta-Bewertung');
define('_MD_AM_METARATINGDSC', 'Das Bewertungs-Meta-Tag definiert das Alter und die Inhaltsbewertung Ihrer Website');
define('_MD_AM_METAOGEN', 'Allgemein');
define('_MD_AM_METAO14YRS', '14 Jahre');
define('_MD_AM_METAOREST', 'Eingeschränkt');
define('_MD_AM_METAOMAT', 'Reife');
define('_MD_AM_METAROBOTS', 'Meta Robots');
define('_MD_AM_METAROBOTSDSC', 'Der Robots Tag erklärt Suchmaschinen, welche Inhalte zu indizieren sind');
define('_MD_AM_INDEXFOLLOW', 'Indizierung, folgen');
define('_MD_AM_NOINDEXFOLLOW', 'Kein Index, Folge');
define('_MD_AM_INDEXNOFOLLOW', 'Indizierung, folgen');
define('_MD_AM_NOINDEXNOFOLLOW', 'Kein Index, kein Folgen');
define('_MD_AM_METAAUTHOR', 'Meta-Autor');
define('_MD_AM_METAAUTHORDSC', 'Der Autor-Meta-Tag definiert den Namen des Autors des zu lesenden Dokuments. Unterstützte Datenformate beinhalten den Namen, die E-Mail-Adresse des Webmaster, den Firmennamen oder die URL.');
define('_MD_AM_METACOPYR', 'Meta Copyright');
define('_MD_AM_METACOPYRDSC', 'Das urheberrechtliche Meta-Tag definiert alle Copyright-Aussagen, die Sie über Ihre Webseiten-Dokumente offenlegen möchten.');
define('_MD_AM_METADESC', 'Meta-Beschreibung');
define('_MD_AM_METADESCDSC', 'Die Beschreibung des Meta-Tags ist eine allgemeine Beschreibung dessen, was in Ihrer Webseite enthalten ist');
define('_MD_AM_METAFOOTER', 'Meta + Fußzeile');
define('_MD_AM_FOOTER', 'Fußzeile');
define('_MD_AM_FOOTERDSC', 'Stellen Sie sicher, dass Sie Links im vollen Pfad ab http://, sonst funktionieren die Links nicht korrekt in Modulseiten.');
define('_MD_AM_CENSOR', 'Wortzensoring');
define('_MD_AM_DOCENSOR', 'Aktiviere Zensur von unerwünschten Wörtern?');
define('_MD_AM_DOCENSORDSC', 'Wörter werden zensiert, wenn diese Option aktiviert ist. Diese Option kann deaktiviert werden, um die Geschwindigkeit zu erhöhen.');
define('_MD_AM_CENSORWRD', 'Wörter zum Zensur');
define('_MD_AM_CENSORWRDDSC', 'Geben Sie Wörter ein, die in Benutzerbeiträgen zensiert werden sollen.<br />Trennen Sie jede mit einem <b>|</b>, Groß- und Kleinschreibung.');
define('_MD_AM_CENSORRPLC', 'Falsche Wörter werden ersetzt mit:');
define('_MD_AM_CENSORRPLCDSC', 'Zensierte Wörter werden durch die in dieser Textbox eingegebenen Zeichen ersetzt');

define('_MD_AM_SEARCH', 'Such-Optionen');
define('_MD_AM_DOSEARCH', 'Globale Suche aktivieren?');
define('_MD_AM_DOSEARCHDSC', 'Erlaubt die Suche nach Beiträgen/Elementen in Ihrer Website.');
define('_MD_AM_MINSEARCH', 'Mindestlänge des Schlüsselwortes');
define('_MD_AM_MINSEARCHDSC', 'Geben Sie die minimale Schlüsselwortlänge ein, die Benutzer eingeben müssen, um eine Suche durchzuführen');
define('_MD_AM_MODCONFIG', 'Modul-Konfigurationsoptionen');
define('_MD_AM_DSPDSCLMR', 'Haftungsausschluss anzeigen?');
define('_MD_AM_DSPDSCLMRDSC', 'Wählen Sie Ja aus, um den Haftungsausschluss in der Registrierungsseite anzuzeigen');
define('_MD_AM_REGDSCLMR', 'Haftungsausschluss der Registrierung');
define('_MD_AM_REGDSCLMRDSC', 'Geben Sie einen Text ein, der als Registrierungsausschluss angezeigt werden soll');
define('_MD_AM_ALLOWREG', 'Neue Benutzerregistrierung erlauben?');
define('_MD_AM_ALLOWREGDSC', 'Wählen Sie Ja zum Akzeptieren der neuen Benutzerregistrierung');
define('_MD_AM_THEMEFILE', 'Vorlagen auf Veränderungen prüfen?');
define('_MD_AM_THEMEFILEDSC', 'Wenn diese Option aktiviert ist, werden geänderte Vorlagen automatisch neu kompiliert, wenn sie angezeigt werden. Sie müssen diese Option auf einer Produktionsseite ausschalten.');
define('_MD_AM_CLOSESITE', 'Website deaktivieren?');
define('_MD_AM_CLOSESITEDSC', 'Wählen Sie "Ja", um Ihre Website auszuschalten, so dass nur Benutzer in ausgewählten Gruppen Zugang zu der Website haben. ');
define('_MD_AM_CLOSESITEOK', 'Wählen Sie die Gruppen, auf die zugegriffen werden darf, während die Website deaktiviert ist.');
define('_MD_AM_CLOSESITEOKDSC', 'Benutzern in der Standard-Webmasters Gruppe wird immer der Zugriff gewährt.');
define('_MD_AM_CLOSESITETXT', 'Grund für das Deaktivieren der Seite');
define('_MD_AM_CLOSESITETXTDSC', 'Der Text, der angezeigt wird, wenn die Seite geschlossen ist.');
define('_MD_AM_SITECACHE', 'Siteweite Cache');
define('_MD_AM_SITECACHEDSC', 'Versteckt den gesamten Inhalt der Website für eine bestimmte Zeit, um die Leistung zu verbessern. Das Setzen des siteweiten Caches überschreibt den Modul-Level-Cache, den Block-Level-Cache und ggf. den Artikel-Level-Cache des Moduls.');
define('_MD_AM_MODCACHE', 'Modulweite Cache');
define('_MD_AM_MODCACHEDSC', 'Cache-Modulinhalte für eine bestimmte Zeit, um die Leistung zu erhöhen. Das Setzen des modulweiten Caches überschreibt den Artikel-Level-Cache des Moduls, falls vorhanden.');
define('_MD_AM_NOMODULE', 'Es gibt kein Modul, das gecachet werden kann.');
define('_MD_AM_DTPLSET', 'Standardvorlagen-Set');
define('_MD_AM_DTPLSETDSC', 'Wenn Sie einen anderen Template-Set als Standard auswählen möchten, müssen Sie zuerst einen neuen Klon in Ihrem System erstellen. Danach können Sie diesen Klon als Standard festlegen.');
define('_MD_AM_SSLLINK', 'URL mit SSL-Login-Seite');

// added for mailer
define("_MD_AM_MAILER", "E-Mail einrichten");
define("_MD_AM_MAILER_MAIL", "");
define("_MD_AM_MAILER_SENDMAIL", "");
define("_MD_AM_MAILER_", "");
define("_MD_AM_MAILFROM", "VON Adresse");
define("_MD_AM_MAILFROMDESC", "");
define("_MD_AM_MAILFROMNAME", "VON Name");
define("_MD_AM_MAILFROMNAMEDESC", "");
// RMV-NOTIFY
define("_MD_AM_MAILFROMUID", "VON Benutzer");
define("_MD_AM_MAILFROMUIDDESC", "Wenn das System eine private Nachricht sendet, welcher Benutzer soll sie gesendet haben?");
define("_MD_AM_MAILERMETHOD", "Versandmethode");
define("_MD_AM_MAILERMETHODDESC", "Methode zur Zustellung von E-Mails. Standardmäßig ist \"mail\", verwenden Sie andere nur, wenn das Probleme macht.");
define("_MD_AM_SMTPHOST", "SMTP-Host(s)");
define("_MD_AM_SMTPHOSTDESC", "Liste der SMTP-Server, mit denen eine Verbindung hergestellt werden soll.");
define("_MD_AM_SMTPUSER", "SMTPAuth Benutzername");
define("_MD_AM_SMTPUSERDESC", "Benutzername zum Verbinden mit SMTPAuth.");
define("_MD_AM_SMTPPASS", "SMTPAuth Passwort");
define("_MD_AM_SMTPPASSDESC", "Passwort zum Verbinden mit SMTPAuth.");
define("_MD_AM_SENDMAILPATH", "Pfad zum Senden von E-Mails");
define("_MD_AM_SENDMAILPATHDESC", "Pfad zum Sendmail-Programm (oder Ersetzen) auf dem Webserver.");
define("_MD_AM_THEMEOK", "Auswählbare Designs");
define("_MD_AM_THEMEOKDSC", "Wählen Sie die Themes, die Benutzer als Standard-Theme auswählen können");

// Xoops Authentication constants
define("_MD_AM_AUTH_CONFOPTION_XOOPS", "ImpressCMS Datenbank");
define("_MD_AM_AUTH_CONFOPTION_LDAP", "Standard LDAP-Verzeichnis");
define("_MD_AM_AUTH_CONFOPTION_AD", "Microsoft Active Directory &copy");
define("_MD_AM_AUTHENTICATION", "Authentifizierung");
define("_MD_AM_AUTHMETHOD", "Authentifizierungsmethode");
define("_MD_AM_AUTHMETHODDESC", "Welche Authentifizierungsmethode möchten Sie verwenden, um Benutzer zu signieren.");
define("_MD_AM_LDAP_MAIL_ATTR", "LDAP - E-Mail Feldname");
define("_MD_AM_LDAP_MAIL_ATTR_DESC", "Der Name des E-Mail-Attributs im LDAP Verzeichnisbaum.");
define("_MD_AM_LDAP_NAME_ATTR", "LDAP - Common Name Feld Name");
define("_MD_AM_LDAP_NAME_ATTR_DESC", "Der Name des Attribute Common Name im LDAP-Verzeichnis.");
define("_MD_AM_LDAP_SURNAME_ATTR", "LDAP - Nachname Feldname ");
define("_MD_AM_LDAP_SURNAME_ATTR_DESC", "Der Name des Attribute Common Name im LDAP-Verzeichnis.");
define("_MD_AM_LDAP_GIVENNAME_ATTR", "LDAP - Common Name Feld Name");
define("_MD_AM_LDAP_GIVENNAME_ATTR_DSC", "Der Name des Attribute Common Name im LDAP-Verzeichnis.");
define("_MD_AM_LDAP_BASE_DN", "LDAP - Basis-DN");
define("_MD_AM_LDAP_BASE_DN_DESC", "Der Basis-DN (Distinguished Name) Ihres LDAP-Verzeichnisbaums.");
define("_MD_AM_LDAP_PORT", "LDAP - Portnummer");
define("_MD_AM_LDAP_PORT_DESC", "Die Portnummer, die benötigt wird, um auf Ihren LDAP-Verzeichnisserver zuzugreifen.");
define("_MD_AM_LDAP_SERVER", "LDAP - Servername");
define("_MD_AM_LDAP_SERVER_DESC", "Der Name Ihres LDAP-Verzeichnisservers.");

define("_MD_AM_LDAP_MANAGER_DN", "DN des LDAP-Managers");
define("_MD_AM_LDAP_MANAGER_DN_DESC", "Der DN des Benutzers erlaubt die Suche zu machen (zB Manager)");
define("_MD_AM_LDAP_MANAGER_PASS", "DN des LDAP-Managers");
define("_MD_AM_LDAP_MANAGER_PASS_DESC", "Das Passwort des Benutzers erlaubt die Suche");
define("_MD_AM_LDAP_VERSION", "LDAP-Versionsprotokoll");
define("_MD_AM_LDAP_VERSION_DESC", "Das LDAP-Versionsprotokoll: 2 oder 3");
define("_MD_AM_LDAP_USERS_BYPASS", " ImpressCMS Benutzer(e) LDAP Authentifizierung umgehen");
define("_MD_AM_LDAP_USERS_BYPASS_DESC", "ImpressCMS Benutzer(e) erlauben es, den LDAP Login zu umgehen. Loggen Sie sich direkt in ImpresssCMS ein<br />Trennen Sie jeden Loginnamen mit einem |");

define("_MD_AM_LDAP_USETLS", " TLS Verbindung verwenden");
define("_MD_AM_LDAP_USETLS_DESC", "Use a TLS (Transport Layer Security) connection. TLS use standard 389 port number<br />" . " and the LDAP version must be set to 3.");

define("_MD_AM_LDAP_LOGINLDAP_ATTR", "LDAP-Attribut verwenden, um den Benutzer zu suchen");
define("_MD_AM_LDAP_LOGINLDAP_ATTR_D", "Wenn der Anmeldename in der DN Option auf Ja gesetzt ist, muss der Anmeldename für ImpressCMS entsprechen");
define("_MD_AM_LDAP_LOGINNAME_ASDN", "Benutzername im DN");
define("_MD_AM_LDAP_LOGINNAME_ASDN_D", "Der ImpressCMS Benutzername wird im LDAP DN verwendet (zB : uid=<loginname>,dc=impresscms, c=org)<br />Der Eintrag wird direkt im LDAP Server ohne Suche gelesen");

define("_MD_AM_LDAP_FILTER_PERSON", "Die LDAP-Suchanfrage für den Suchfilter");
define("_MD_AM_LDAP_FILTER_PERSON_DESC", "Special LDAP Filter to find user. @@loginname@@ is replaced by the users's login name<br /> MUST BE BLANK IF YOU DON'T KNOW WHAT YOU DO' !" . "<br />Ex : (&(objectclass=person)(samaccountname=@@loginname@@)) for AD" . "<br />Ex : (&(objectclass=inetOrgPerson)(uid=@@loginname@@)) for LDAP");

define("_MD_AM_LDAP_DOMAIN_NAME", "Der Domain-Name");
define("_MD_AM_LDAP_DOMAIN_NAME_DESC", "Windows-Domänenname. Nur für ADS und NT Server");

define("_MD_AM_LDAP_PROVIS", "Automatische ImpressCMS Kontoeinrichtung");
define("_MD_AM_LDAP_PROVIS_DESC", "ImpressCMS Benutzerdatenbank erstellen, wenn nicht vorhanden");

define("_MD_AM_LDAP_PROVIS_GROUP", "Standardeinstellung für Gruppe");
define("_MD_AM_LDAP_PROVIS_GROUP_DSC", "Der neue Benutzer ist diesen Gruppen zugeordnet");

define("_MD_AM_LDAP_FIELD_MAPPING_ATTR", "ImpressCMS-Auth Serverfelder zuordnen");
define("_MD_AM_LDAP_FIELD_MAPPING_DESC", "Describe here the mapping between the ImpressCMS database field and the LDAP Authentication system field." . "<br /><br />Format [ImpressCMS Database field]=[Auth system LDAP attribute]" . "<br />for example : email=mail" . "<br />Separate each with a |" . "<br /><br />!! For advanced users !!");

define("_MD_AM_LDAP_PROVIS_UPD", "Warten Sie die Bereitstellung von ImpressCMS-Konten ");
define("_MD_AM_LDAP_PROVIS_UPD_DESC", "Das ImpressCMS Benutzerkonto wird immer mit dem Authentifizierungsserver synchronisiert");

// lang constants for secure password
define("_MD_AM_PASSLEVEL", "Minimale Sicherheitsstufe");
define("_MD_AM_PASSLEVEL_DESC", "Legen Sie fest, welche Sicherheitsstufe Sie für das Passwort des Benutzers haben möchten. Es wird empfohlen, es nicht zu niedrig oder zu stark zu setzen, vernünftig sein.");
define("_MD_AM_PASSLEVEL1", "Aus (unsicher)");
define("_MD_AM_PASSLEVEL2", "Schwach");
define("_MD_AM_PASSLEVEL3", "Vernünftig");
define("_MD_AM_PASSLEVEL4", "Stark");
define("_MD_AM_PASSLEVEL5", "Sicher");
define("_MD_AM_PASSLEVEL6", "Keine Einteilung");

define("_MD_AM_RANKW", "Maximale Breite des Rangbildes (Pixel)");
define("_MD_AM_RANKH", "Maximale Höhe des Avatarbildes (Pixel)");
define("_MD_AM_RANKMAX", "Maximale Dateigröße des Rangbildes (Byte)");

define("_MD_AM_MULTILANGUAGE", "Mehrsprachig");
define("_MD_AM_ML_ENABLE", "Mehrsprachigkeit aktivieren");
define("_MD_AM_ML_ENABLEDSC", "Auf Ja setzen, um die Mehrsprachigkeit auf der gesamten Seite zu aktivieren.");
define("_MD_AM_ML_TAGS", "Mehrsprachige Schlagwörter");
define("_MD_AM_ML_TAGSDSC", "Geben Sie die Tags ein, die auf dieser Webseite verwendet werden sollen, immer getrennt durch Komma ein. Dies würde zum Beispiel verwendet werden bei Sprachen für Englisch und Französisch zu definieren: en,fr");
define("_MD_AM_ML_NAMES", "Name der Sprache");
define("_MD_AM_ML_NAMESDSC", "Geben Sie die Namen der zu benutzenden Sprache ein, getrennt durch ein Komma");
define("_MD_AM_ML_CAPTIONS", "Sprachtitel");
define("_MD_AM_ML_CAPTIONSDSC", "Geben Sie die Beschriftungen ein, die Sie für diese Sprachen verwenden möchten");
define("_MD_AM_ML_CHARSET", "Charsets");
define("_MD_AM_ML_CHARSETDSC", "Zeichensätze dieser Sprachen eingeben");

define("_MD_AM_REMEMBERME", "Aktivieren Sie die Funktion 'Login merken.");
define("_MD_AM_REMEMBERMEDSC", "Die \"Remember me\"-Funktion kann ein Sicherheitsproblem darstellen. Verwenden Sie sie auf eigene Gefahr.");

define("_MD_AM_PRIVDPOLICY", "Aktivieren Sie die Seite \"Datenschutzerklärung\".");
define("_MD_AM_PRIVDPOLICYDSC", "Die \"Datenschutzerklärung\" sollte auf Ihre Website zugeschnitten sein und aktiv sein, wenn Sie eine Registrierung auf Ihrer Website zulassen.");
define("_MD_AM_PRIVPOLICY", "Geben Sie Ihre Seite \"Datenschutzerklärung\" ein.");
define("_MD_AM_PRIVPOLICYDSC", "");

define("_MD_AM_WELCOMEMSG", "Eine Willkommensnachricht an neu registrierte Benutzer senden");
define("_MD_AM_WELCOMEMSGDSC", "Senden Sie eine Willkommensnachricht an einen neuen Benutzer, wenn sein Account aktiviert wird. Der Inhalt dieser Nachricht kann in der folgenden Option konfiguriert werden.");
define("_MD_AM_WELCOMEMSG_CONTENT", "Inhalt der Willkommensnachricht");
define("_MD_AM_WELCOMEMSG_CONTENTDSC", "Sie können die Nachricht bearbeiten, die an den neuen Benutzer gesendet wird. Beachten Sie, dass Sie die folgenden Tags verwenden können: <br /><br />- {UNAME} = Benutzername des Benutzers<br />- {X_UEMAIL} = E-Mail des Benutzers<br />- {X_ADMINMAIL} = Admin-E-Mail-Adresse<br />- {X_SITENAME} = Name der Seite<br />- {X_SITEURL} = URL der Seite");

define("_MD_AM_SEARCH_USERDATE", "Benutzer und Datum in Suchergebnissen anzeigen");
define("_MD_AM_SEARCH_USERDATEDSC", "");
define("_MD_AM_SEARCH_NO_RES_MOD", "Module ohne Treffer in Suchergebnissen anzeigen");
define("_MD_AM_SEARCH_NO_RES_MODDSC", "");
define("_MD_AM_SEARCH_PER_PAGE", "Einträge pro Seite in Suchergebnissen");
define("_MD_AM_SEARCH_PER_PAGEDSC", "");

define("_MD_AM_EXT_DATE", "Möchten Sie eine erweiterte oder lokale Datumsfunktion verwenden?");
define("_MD_AM_EXT_DATEDSC", "Hinweis: durch Aktivieren dieser Option, ImpressCMS verwendet ein erweitertes Kalender-Skript <b>NUR</b> wenn Sie dieses Skript auf Ihrer Seite laufen lassen.<br />Bitte besuchen Sie <a href='https://www.impresscms.org/modules/simplywiki/index.php?page=Extended_date_function'>erweiterte Datumsfunktion</a> für weitere Informationen.");

define("_MD_AM_EDITOR_DEFAULT", "Standard Editor");
define("_MD_AM_EDITOR_DEFAULT_DESC", "Wählen Sie den Standard-Editor für die gesamte Website.");

define("_MD_AM_EDITOR_ENABLED_LIST", "Aktivierte Editoren");
define("_MD_AM_EDITOR_ENABLED_LIST_DESC", "Wählen Sie die auswählbaren Editoren der Module aus (wenn das Modul eine Konfiguration hat, um den Editor auszuwählen.)");

define("_MD_AM_ML_AUTOSELECT_ENABLED", "Automatisch die Sprache wählen, abhängig von der Browser-Konfiguration");

define("_MD_AM_ALLOW_ANONYMOUS_VIEW_PROFILE", "Anonymen Benutzern erlauben, Benutzerprofile zu sehen.");
define("_MD_AM_ALLOW_ANONYMOUS_VIEW_PROFILE_DESC", "Wenn Sie JA wählen, können alle Besucher die Profile von Ihrer Homepage sehen. Dies ist sehr nützlich für eine Gemeinschaft, aber vielleicht für die Privatsphäre nicht die beste Option.");

define("_MD_AM_ENC_TYPE", "Change Password Encryption (default is SHA 512)");
define("_MD_AM_ENC_TYPEDSC", "Ändert den Algorithmus zur Verschlüsselung von Benutzerpasswörtern.<br />Änderungen machen alle Passwörter ungültig! Alle Benutzer müssen ihr Passwort zurücksetzen, nachdem sie diese Einstellung geändert haben");
define("_MD_AM_ENC_MD5", "MD5 (nicht empfohlen)");
define("_MD_AM_ENC_SHA256", "SHA 256");
define("_MD_AM_ENC_SHA384", "SHA 384");
define("_MD_AM_ENC_SHA512", "SHA 512 (empfohlen)");
define("_MD_AM_ENC_RIPEMD128", "RIPEMD 128");
define("_MD_AM_ENC_RIPEMD160", "RIPEMD 160");
define("_MD_AM_ENC_WHIRLPOOL", "WIRLPOOL");
define("_MD_AM_ENC_HAVAL1284", "HAVAL 128,4");
define("_MD_AM_ENC_HAVAL1604", "HAVAL 160,4");
define("_MD_AM_ENC_HAVAL1924", "HAVAL 192,4");
define("_MD_AM_ENC_HAVAL2244", "HAVAL 224,4");
define("_MD_AM_ENC_HAVAL2564", "HAVAL 256,4");
define("_MD_AM_ENC_HAVAL1285", "HAVAL 128,5");
define("_MD_AM_ENC_HAVAL1605", "HAVAL 160,5");
define("_MD_AM_ENC_HAVAL1925", "HAVAL 192,5");
define("_MD_AM_ENC_HAVAL2245", "HAVAL 224,5");
define("_MD_AM_ENC_HAVAL2565", "HAVAL 256,5");

// Content Manager
define("_MD_AM_CONTMANAGER", "Inhaltsmanager");
define("_MD_AM_DEFAULT_CONTPAGE", "Standardseite");
define("_MD_AM_DEFAULT_CONTPAGEDSC", "Wählen Sie die Standardseite, die dem Benutzer im Content-Manager angezeigt werden soll. Leer lassen, um den Content-Manager auf die zuletzt erstellte Seite zu setzen.");
define("_MD_AM_CONT_SHOWNAV", "Navigationsmenü auf der Benutzerseite anzeigen?");
define("_MD_AM_CONT_SHOWNAVDSC", "Wählen Sie ja aus, um das Navigationsmenü des Content-Managers anzuzeigen.");
define("_MD_AM_CONT_SHOWSUBS", "Verwandte Seiten anzeigen?");
define("_MD_AM_CONT_SHOWSUBSDSC", "Wählen Sie ja aus, um Links zu verwandten Seiten auf den Content-Manager-Seiten anzuzeigen.");
define("_MD_AM_CONT_SHOWPINFO", "Benutzer und veröffentlichte Informationen anzeigen?");
define("_MD_AM_CONT_SHOWPINFODSC", "Select yes to show in the page informations about the poster and publish of the page.");
define("_MD_AM_CONT_ACTSEO", "Use menu title instead the id in the url (improve seo)?");
define("_MD_AM_CONT_ACTSEODSC", "Wählen Sie ja statt der Id in der URL der Seite den Wert des Menütitels aus.");
// Captcha (Security image)
define('_MD_AM_USECAPTCHA', 'Möchten Sie CAPTCHA im Anmeldeformular verwenden?');
define('_MD_AM_USECAPTCHADSC', 'Wählen Sie Ja zu CAPTCHA (Anti-Spam) im Anmeldeformular.');
define('_MD_AM_USECAPTCHAFORM', 'Möchten Sie CAPTCHA in Kommentarformularen verwenden?');
define('_MD_AM_USECAPTCHAFORMDSC', 'Wählen Sie "Ja", um CAPTCHA (Anti-Spam) dem Kommentarformular hinzuzufügen, um Spam zu vermeiden.');
define('_MD_AM_ALLWHTSIG', 'Erlaube externe Bilder und HTML in der Signatur anzuzeigen?');
define('_MD_AM_ALLWHTSIGDSC', 'Wenn einige Angreifer ein externes Bild mit [img]veröffentlichen, kann er wissen, dass IPs oder User-Agents von Benutzern Ihre Website besucht haben.<br />Das Erlauben von HTML kann Verwundbarkeit bei Script-Insertion verursachen, wenn böswillige Benutzer ihre Signatur ändern.');
define('_MD_AM_ALLWSHOWSIG', 'Möchten Sie Ihren Benutzern erlauben, eine Signatur in ihrem Profil/ihren Beiträgen zu verwenden?');
define('_MD_AM_ALLWSHOWSIGDSC', 'Wenn Sie diese Option aktivieren, können Benutzer eine persönliche Signatur verwenden, die (nach eigener Wahl) nach ihrem Beitrag hinzugefügt wird.');
// < personalizações > fabio - Sat Apr 28 11:55:00 BRT 2007 11:55:00
define("_MD_AM_PERSON", "Personalisierung");
define("_MD_AM_GOOGLE_ANA", "Google Analytics");
define("_MD_AM_GOOGLE_ANA_DESC", "Notieren Sie den Google Analytics Id-Code wie: <small>_uacct = \"UA-<font color=#FF0000><b>xxxxxx-x</b></font>\"</small><br />ODER<small><br />var pageTracker = _gat. getTracker(UA-\"<font color=#FF0000><b>xxxxxx-x</b></font>\");</small> (Sie müssen den roten fetten ID-Code schreiben).");
define("_MD_AM_LLOGOADM", "Admin-Logo links");
define("_MD_AM_LLOGOADM_DESC", " Wählen Sie ein Bild, das in der linken oberen Ecke des Admin-Panels verwendet werden soll. <br /><i>Um ein Bild auszuwählen oder zu senden, muss mindestens eine Bildkategorie im System > Bilder vorhanden sein</i> ");
define("_MD_AM_LLOGOADM_URL", "Admin Logo links Link URL");
define("_MD_AM_LLOGOADM_ALT", "Admin Logo links Link Titel");
define("_MD_AM_RLOGOADM", "Admin Logo rechts");
define("_MD_AM_RLOGOADM_DESC", " Wählen Sie ein Bild, das in der linken oberen Ecke des Admin-Panels verwendet werden soll. <br /><i>Um ein Bild auszuwählen oder zu senden, muss mindestens eine Bildkategorie im System > Bilder vorhanden sein</i> ");
define("_MD_AM_RLOGOADM_URL", "Admin Logo rechts Link URL");
define("_MD_AM_RLOGOADM_ALT", "Admin Logo rechts Titel URL");
define("_MD_AM_METAGOOGLE", "Google Meta Tag");
define("_MD_AM_METAGOOGLE_DESC", 'Code generiert von Google, um die Eigentümerschaft über eine Website zu bestätigen, so dass Sie die kompletten Fehlerseiten-Statistiken sehen können. Notieren Sie den Id-Code wie: <small>meta name="verify-v1" content="<font color=#FF0000><b>xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</b></font>" </small><br />(Sie müssen den roten fettgedruckten Id-Code schreiben).<br />
Weitere Informationen unter <a href="http://www.google.com/webmasters/" target="_blank">http://www.google.com/webmasters</a>.');
define("_MD_AM_RSSLOCAL", "Admin News Feed URL");
define("_MD_AM_RSSLOCAL_DESC", "URL eines RSS-Feeds, der unter The ImpressCMS Project > News angezeigt werden soll.");
define("_MD_AM_FOOTADM", "Admin-Fußzeile");
define("_MD_AM_FOOTADM_DESC", "Inhalt, der in der Fußzeile der Admin-Seiten angezeigt wird.");
define("_MD_AM_EMAILTTF", "Schriftart, die im E-Mail-Adressschutz verwendet wird ");
define("_MD_AM_EMAILTTF_DESC", "Wählen Sie aus, welche Schriftart verwendet werden soll, um den E-Mail-Schutz zu generieren.<br /><i>Diese Option gilt nur, wenn 'E-Mail Adressen vor SPAM schützen?' auf Ja gesetzt ist.</i>");
define("_MD_AM_EMAILLEN", "Schriftgröße für E-Mail-Schutz");
define("_MD_AM_EMAILLEN_DESC", "<i>Diese Option gilt nur, wenn 'E-Mail Adressen vor SPAM schützen?' auf Ja gesetzt ist.</i>");
define("_MD_AM_EMAILCOLOR", "Schriftfarbe für E-Mail-Schutz");
define("_MD_AM_EMAILCOLOR_DESC", "<i>Diese Option gilt nur, wenn 'E-Mail Adressen vor SPAM schützen?' auf Ja gesetzt ist.</i>");
define("_MD_AM_EMAILSHADOW", "Schattenfarbe für E-Mail-Schutz");
define("_MD_AM_EMAILSHADOW_DESC", "Wählen Sie eine Farbe für den Schatten des E-Mail-Adressenschutzes. Lassen Sie sie leer, wenn Sie keine verwenden möchten.<br /><i>Diese Option gilt nur, wenn 'E-Mail Adressen gegen SPAM schützen?' auf Ja gesetzt ist.</i>");
define("_MD_AM_SHADOWX", "X-Offset für Schatten im E-Mail-Schutz");
define("_MD_AM_SHADOWX_DESC", "Geben Sie einen Wert (in px)) ein, der den horizontalen Offset des Schatten im E-Mail-Schutz darstellt.<br /><i>Diese Option gilt nur, wenn 'Schattenfarbe für E-Mail-Adressenschutz' nicht leer ist.</i>");
define("_MD_AM_SHADOWY", "X-Offset für Schatten im E-Mail-Schutz");
define("_MD_AM_SHADOWY_DESC", "Geben Sie einen Wert (in px) ein, der den vertikalen Versatz des Schatten im E-Mail-Schutz darstellt.<br /><i>Diese Option gilt nur, wenn 'Schattenfarbe für E-Mail-Adressenschutz' nicht leer ist.</i>");
define("_MD_AM_EDITREMOVEBLOCK", "Blöcke bearbeiten und von der Benutzerseite ausblenden?");
define("_MD_AM_EDITREMOVEBLOCKDSC", "Wenn Sie diese Option aktivieren, sehen Sie zwei Symbole auf Block-Titel mit einem direkten Zugriff zum Entfernen oder Bearbeiten Ihres Blocks.");

define("_MD_AM_EMAILPROTECT", "E-Mail-Adressen gegen SPAM schützen?");
define("_MD_AM_EMAILPROTECTDSC", "Wenn Sie diese Option aktivieren, wird jedes Mal, wenn eine E-Mail-Adresse auf der Website verworfen wird, wird sie gegen SPAM-Roboter geschützt sein.<br /><i>Um reCAPTCHA Mailhide verwenden zu können, müssen Sie das mcrypt PHP-Modul installiert haben.</i>");
define("_MD_AM_MULTLOGINPREVENT", "Mehrfachanmeldung durch denselben Benutzer verhindern?");
define("_MD_AM_MULTLOGINPREVENTDSC", "Wenn diese Option aktiviert ist, wenn ein Benutzer bereits auf Ihrer Seite angemeldet ist der selbe Benutzername kann sich erst wieder anmelden, wenn die erste Sitzung geschlossen ist.");
define("_MD_AM_MULTLOGINMSG", "Multilogin Weiterleitungsnachricht:");
define("_MD_AM_MULTLOGINMSG_DESC", "Nachricht, die einem Benutzer angezeigt wird, der sich mit einem bereits eingeloggten Benutzernamen anmelden möchte. <br><i>Diese Option gilt nur, wenn 'Mehrfachanmeldung durch denselben Benutzer verhindern?' auf Ja gesetzt ist.</i>");
define("_MD_AM_GRAVATARALLOW", "Verwendung von GRAVATAR erlauben?");
define("_MD_AM_GRAVATARALWDSC", "Zeige Account-Bilder von Mitgliedern werden von <a href='http://www.gravatar.com/' target='_blank'>Gravatar</a>gehostet, einem kostenlosen Avatar-Service. ImpressCMS zeigt automatisch alle Gravatar-gehosteten Bilder an, die mit der E-Mail-Adresse der Mitglieder verknüpft sind.");

define("_MD_AM_SHOW_ICMSMENU", "ImpressCMS Project Dropdown-Menü anzeigen?");
define("_MD_AM_SHOW_ICMSMENU_DESC", "Wählen Sie KEINE um das Dropdown-Menü nicht anzuzeigen und JA um es anzuzeigen.");

define("_MD_AM_SHORTURL", "Lange URLs abschneiden ?");
define("_MD_AM_SHORTURLDSC", "Stellen Sie diese Option auf Ja ein, wenn Sie möchten, dass die gesamte URL auf Ihrer Website automatisch auf eine bestimmte Anzahl von Zeichen reduziert wird. Lange URLs, zum Beispiel in einem Forumsbeitrag, können oft das Design zerstören...");
define("_MD_AM_URLLEN", "Maximale URL Länge");
define("_MD_AM_URLLEN_DESC", "The maximum amount of characters of an URL. Extra characters will be truncated automatically.<br /><i>This option only applies if 'Truncate long URLs ?' is set to Yes.</i>");
define("_MD_AM_PRECHARS", "Amount of starting characters");
define("_MD_AM_PRECHARS_DESC", "Wie viele Zeichen sollen am Anfang einer URL angezeigt werden?<br /><i>Diese Option gilt nur, wenn 'lange URLs verkleinern?' auf Ja gesetzt ist.</i>");
define("_MD_AM_LASTCHARS", "Amount of ending characters");
define("_MD_AM_LASTCHARS_DESC", "Wie viele Zeichen sollen am Anfang einer URL angezeigt werden?<br /><i>Diese Option gilt nur, wenn 'lange URLs verkleinern?' auf Ja gesetzt ist.</i>");
define("_MD_AM_SIGMAXLENGTH", "Maximale Anzahl von Zeichen in den Unterschriften der Benutzer?");
define("_MD_AM_SIGMAXLENGTHDSC", "Hier kannst du die Länge deiner Benutzersignaturen auswählen.<br /> jedes Zeichen, das länger als dieser Betrag ist, wird ignoriert.<br /><i>Vorsicht, lange Unterschriften können das Design oft beschädigen...</i>");

define("_MD_AM_USE_GOOGLE_ANA", " Google Analytics aktivieren?");
define("_MD_AM_USE_GOOGLE_ANA_DESC", "");

// added in 1.1.2
define("_MD_AM_UNABLEENCCLOSED", "Datenbank-Update fehlgeschlagen, Passwort-Verschlüsselung kann nicht geändert werden, solange die Seite geschlossen ist");

# ####################### Added in 1.2 ###################################
define("_MD_AM_CAPTCHA", "Captcha-Einstellungen");
define("_MD_AM_CAPTCHA_MODE", "Captcha-Modus");
define("_MD_AM_CAPTCHA_MODEDSC", "Bitte wählen Sie einen Captcha-Typ für Ihre Webseite aus");
define("_MD_AM_CAPTCHA_SKIPMEMBER", "Captcha Freie Gruppen");
define("_MD_AM_CAPTCHA_SKIPMEMBERDSC", "Select groups which are not requiring a captcha. These groups will never see the captcha field.");
define("_MD_AM_CAPTCHA_CASESENS", "Case sensitive");
define("_MD_AM_CAPTCHA_CASESENSDSC", "Characters in image mode are case-sensitive");
define("_MD_AM_CAPTCHA_MAXATTEMP", "Maximum attempts");
define("_MD_AM_CAPTCHA_MAXATTEMPDSC", "Maximum attempts for each session");
define("_MD_AM_CAPTCHA_NUMCHARS", "Maximum characters?");
define("_MD_AM_CAPTCHA_NUMCHARSDSC", "Maximum number of characters to be generated");
define("_MD_AM_CAPTCHA_FONTMIN", "Minimum Schriftgröße");
define("_MD_AM_CAPTCHA_FONTMINDSC", "");
define("_MD_AM_CAPTCHA_FONTMAX", "Maximum Schriftgröße");
define("_MD_AM_CAPTCHA_FONTMAXDSC", "");
define("_MD_AM_CAPTCHA_BGTYPE", "Hintergrundbilder");
define("_MD_AM_CAPTCHA_BGTYPEDSC", "Hintergrundtyp im Bild-Modus");
define("_MD_AM_CAPTCHA_BGNUM", "Hintergrundbilder");
define("_MD_AM_CAPTCHA_BGNUMDSC", "Anzahl der zu generierenden Hintergrundbilder");
define("_MD_AM_CAPTCHA_POLPNT", "Polygon Punkte");
define("_MD_AM_CAPTCHA_POLPNTDSC", "Anzahl der zu generierenden Polygon-Punkte");
define("_MD_AM_BAR", "Leiste");
define("_MD_AM_CIRCLE", "Kreis");
define("_MD_AM_LINE", "Linie");
define("_MD_AM_RECTANGLE", "Rechteck");
define("_MD_AM_ELLIPSE", "Ellipse");
define("_MD_AM_POLYGON", "Polygon");
define("_MD_AM_RANDOM", "Zufällig");
define("_MD_AM_CAPTCHA_IMG", "Bild");
define("_MD_AM_CAPTCHA_TXT", "Text");
define("_MD_AM_CAPTCHA_OFF", "Deaktiviert");
define("_MD_AM_CAPTCHA_SKIPCHAR", "Zeichen überspringen");
define("_MD_AM_CAPTCHA_SKIPCHARDSC", "This option will skip the entered characters when generating Captcha");
define('_MD_AM_PAGISTYLE', 'Style of the paginations links:');
define('_MD_AM_PAGISTYLE_DESC', 'Select the style of the paginations links.');
define('_MD_AM_ALLWCHGUNAME', 'Benutzern erlauben, Anzeigenamen zu ändern?');
define('_MD_AM_ALLWCHGUNAMEDSC', '');
define("_MD_AM_JALALICAL", "Erweiterten Kalender mit Jalali verwenden?");
define("_MD_AM_JALALICALDSC", "Wenn Sie dies wählen, haben Sie einen erweiterten Kalender für Formulare.<br />Bitte beachten Sie, dass dieser Kalender in einigen Browsern nicht funktioniert.");
define("_MD_AM_NOMAILPROTECT", "Nichts");
define("_MD_AM_GDMAILPROTECT", "GD Schutz");
define("_MD_AM_REMAILPROTECT", "reCaptcha");
define("_MD_AM_RECPRVKEY", "reCaptcha private API-Code");
define("_MD_AM_RECPRVKEY_DESC", "");
define("_MD_AM_RECPUBKEY", "reCaptcha public api code");
define("_MD_AM_RECPUBKEY_DESC", "");
define("_MD_AM_CONT_NUMPAGES", "Number of pages on list by tag mode");
define("_MD_AM_CONT_NUMPAGESDSC", "Define the number of pages to show in user side on list by tag mode.");
define("_MD_AM_CONT_TEASERLENGTH", "Teaser Length");
define("_MD_AM_CONT_TEASERLENGTHDSC", "Number of characters of the page teaser in list by tag mode.<br />Set to 0 to not limit.");
define("_MD_AM_STARTPAGEDSC", "Select the desired Module or Page for your start page by each group.");
define("_MD_AM_DELUSRES", "Removing inactive users");
define("_MD_AM_DELUSRESDSC", "This Option will remove users who have registered but have not activated their accounts for X days.<br />Please enter an amount of days.");
define("_MD_AM_PLUGINS", "Plugin-Manager");
define("_MD_AM_SELECTSPLUGINS", "Select allowed plugins to be used");
define("_MD_AM_SELECTSPLUGINS_DESC", "Hiermit können Sie auswählen, welche Plugins verwendet werden, um Ihre Texte zu bereinigen.");
define("_MD_AM_GESHI_DEFAULT", "Plugin für geshi auswählen");
define("_MD_AM_GESHI_DEFAULT_DESC", "GeSHi (Generic Syntax Hilighter) ist ein Syntax-Highlighter für Ihre Codes.");
define("_MD_AM_SELECTSHIGHLIGHT", "Wählen Sie den Hervorhebungstyp für die Codes");
define("_MD_AM_SELECTSHIGHLIGHT_DESC", "Hiermit können Sie auswählen, welcher Hervorheber verwendet wird, um Ihre Codes hervorzuheben.");
define("_MD_AM_HIGHLIGHTER_GESHI", "GeSHi Hervorhebung");
define("_MD_AM_HIGHLIGHTER_PHP", "php Hervorhebung");
define("_MD_AM_HIGHLIGHTER_OFF", "Deaktiviert");
define('_MD_AM_DODEEPSEARCH', "Aktiviere \"Tiefen\"-Suche?");
define('_MD_AM_DODEEPSEARCHDSC', "Möchten Sie, dass Ihre anfängliche Suchergebnisseite angibt, wie viele Treffer in jedem Modul gefunden wurden? Hinweis: Das Einschalten kann den Suchprozess verlangsamen!");
define('_MD_AM_NUMINITSRCHRSLTS', "Anzahl der anfänglichen Suchergebnisse: (für 'einfache' Suchen)");
define('_MD_AM_NUMINITSRCHRSLTSDSC', "'Shallow' Suchvorgänge werden beschleunigt, indem die Ergebnisse begrenzt werden, die für jedes Modul auf der ersten Suchseite zurückgegeben werden.");
define('_MD_AM_NUMMDLSRCHRESULTS', "Number of search results per page:");
define('_MD_AM_NUMMDLSRCHRESULTSDSC', "This determines how many hits per page are shown after drilling down into a particular module's search results.");
define('_MD_AM_ADMIN_DTHEME', 'Admin Thema');
define('_MD_AM_ADMIN_DTHEME_DESC', '');
define('_MD_AM_CUSTOMRED', 'Ajax Weiterleitungsmethode verwenden?');
define('_MD_AM_CUSTOMREDDSC', '');
define('_MD_AM_DTHEMEDSC', 'Standard-Template zum Anzeigen Ihrer Website.');

// Added in 1.2

// HTML Purifier preferences
define("_MD_AM_PURIFIER", "HTMLPurifier Einstellungen");
define("_MD_AM_PURIFIER_ENABLE", "HTML-Purifier aktivieren");
define("_MD_AM_PURIFIER_ENABLEDSC", "Wählen Sie 'Ja', um den HTML-Purifier Filter zu aktivieren. Deaktivieren kann Ihre Website angreifbar machen, wenn Sie Ihren HTML-Inhalt zulassen");
// HTML section
define("_MD_AM_PURIFIER_HTML_TIDYLEVEL", "HTML Tidy Level");
define("_MD_AM_PURIFIER_HTML_TIDYLEVELDSC", "General level of cleanliness the Tidy module should enforce.<br /><br />
None = No extra tidying should be done,<br />Light = Only fix elements that would be discarded otherwise due to lack of support in doctype,<br />
Medium = Enforce best practices,<br />Heavy = Transform all deprecated elements and attributes to standards compliant equivalents.");
define("_MD_AM_PURIFIER_NONE", "Nichts");
define("_MD_AM_PURIFIER_LIGHT", "Leicht");
define("_MD_AM_PURIFIER_MEDIUM", "Mittel (empfohlen)");
define("_MD_AM_PURIFIER_HEAVY", "Schwer");
define("_MD_AM_PURIFIER_HTML_DEFID", "HTML-Definition-ID");
define("_MD_AM_PURIFIER_HTML_DEFIDDSC", "Legt den Standard-ID-Namen der Purifier Konfiguration fest (so lassen wie es ist, es sei denn, Sie erstellen benutzerdefinierte Konfigurationen & dass Sie wissen, was Sie tun");
define("_MD_AM_PURIFIER_HTML_DEFREV", "HTML-Definitions-Revision Nummer");
define("_MD_AM_PURIFIER_HTML_DEFREVDSC", "Example: revision 3 is more up-to-date than revision 2. Thus, when this gets incremented, the cache handling is smart enough to clean up any older revisions of your definition as well as flush the cache.<br />You can leave this as is unless you know what you are doing & are editing the purifier files directly");
define("_MD_AM_PURIFIER_HTML_DOCTYPE", "HTML DocType");
define("_MD_AM_PURIFIER_HTML_DOCTYPEDSC", "Doctype to use during filtering. Technically speaking this is not actually a doctype (as it does not identify a corresponding DTD), but we are using this name for sake of simplicity. When non-blank, this will override any older directives like XHTML or HTML (Strict).");
define("_MD_AM_PURIFIER_HTML_ALLOWELE", "Erlaubte Elemente");
define("_MD_AM_PURIFIER_HTML_ALLOWELEDSC", "Whitelist von HTML-Elementen, die verwendet werden dürfen. Alle hier eingegebenen Elemente werden nicht aus Benutzerbeiträgen gefiltert. Sie sollten nur sichere HTML-Elemente erlauben.");
define("_MD_AM_PURIFIER_HTML_ALLOWATTR", "Zulässige Attribute");
define("_MD_AM_PURIFIER_HTML_ALLOWATTRDSC", "Whitelist der HTML-Attribute, die verwendet werden dürfen. Alle hier eingegebenen Attribute werden nicht aus Benutzerbeiträgen gefiltert. Sie sollten nur sichere HTML-Attirbuts erlauben.<br /><br />Formatieren Sie Ihre Attribute wie folgt:<br />Element. ttribute (Beispiel: div.class) erlaubt es dir, das Klassenattribut mit div Tags zu verwenden. Sie können auch Platzhalter verwenden: *.class zum Beispiel erlaubt das Klassenattribut in allen erlaubten Elementen.");
define("_MD_AM_PURIFIER_HTML_FORBIDELE", "Verbotene Elemente");
define("_MD_AM_PURIFIER_HTML_FORBIDELEDSC", "This is the logical inverse of  HTML.Allowed Elements, and it will override that directive, or any other directive.");
define("_MD_AM_PURIFIER_HTML_FORBIDATTR", "Verbotene Attribute");
define("_MD_AM_PURIFIER_HTML_FORBIDATTRDSC", " Diese Direktive ähnelt HTML erlaubten Attributen für die Forwards-Kompatibilität mit XML, aber dieses Attribut hat eine andere Syntax.<br />Statt tag.attr, benutze tag@attr. Um href-Attribute in Tags zu verbieten, setze diese Direktive auf a@href.<br />Sie können ein Attribut auch global mit attr oder *@attr verbieten (entweder ist die Syntax in Ordnung; die letztere für die Konsistenz mit den HTML Erlaubten Attributen zur Verfügung gestellt wird).<br /><br />Warnung: Diese Direktive ergänzt HTML Verbotene Elemente entsprechend Überprüfen Sie diese Direktive für eine Diskussion darüber, warum Sie sich zweimal überlegen sollten, bevor Sie diese Direktive verwenden.");
define("_MD_AM_PURIFIER_HTML_MAXIMGLENGTH", "Maximale Bild-Länge");
define("_MD_AM_PURIFIER_HTML_MAXIMGLENGTHDSC", "Diese Direktive steuert die maximale Anzahl von Pixeln in den Breiten- und Höhenattributen in den img Tags. Dies ist vorhanden, um Absturzangriffe auf das Image zu verhindern. Deaktivieren Sie 0 auf eigene Gefahr. ");
define("_MD_AM_PURIFIER_HTML_SAFEEMBED", "Sicheres Einbinden aktivieren");
define("_MD_AM_PURIFIER_HTML_SAFEEMBEDDSC", "Gibt an, ob Tags in Dokumente eingebunden werden sollen oder nicht, mit einer Reihe zusätzlicher Sicherheitsfunktionen, die die Ausführung des Skripts verhindern. Dies ähnelt dem, was Webseiten wie MySpace tun, um Tags einzubetten. Einbetten ist ein proprietäres Element und wird dazu führen, dass Ihre Website die Validierung einstellt. Sie möchten dies wahrscheinlich mit HTML Safe Objects aktivieren. Sehr experimentell.");
define("_MD_AM_PURIFIER_HTML_SAFEOBJECT", "Sicheres Einbinden aktivieren");
define("_MD_AM_PURIFIER_HTML_SAFEOBJECTDSC", "Whether or not to permit object tags in documents, with a number of extra security features added to prevent script execution. This is similar to what websites like MySpace do to object tags. You may also want to enable  HTML Safe Embed for maximum interoperability with Internet Explorer, although embed tags will cause your website to stop validating. Highly experimental.");
define("_MD_AM_PURIFIER_HTML_ATTRNAMEUSECDATA", "Relax DTD Name Attribute Parsing");
define("_MD_AM_PURIFIER_HTML_ATTRNAMEUSECDATADSC", "The W3C specification DTD defines the name attribute to be CDATA, not ID, due to limitations of DTD. In certain documents, this relaxed behavior is desired, whether it is to specify duplicate names, or to specify names that would be illegal IDs (for example, names that begin with a digit.) Set this configuration directive to yes to use the relaxed parsing rules.");
// URI Section
define("_MD_AM_PURIFIER_URI_DEFID", "URI Definition ID");
define("_MD_AM_PURIFIER_URI_DEFIDDSC", "Unique identifier for a custom-built URI definition. If you want to add custom URIFilters, you must specify this value. (leave as is unless you know what you are doing)");
define("_MD_AM_PURIFIER_URI_DEFREV", "URI Definition Revision Number");
define("_MD_AM_PURIFIER_URI_DEFREVDSC", "Example: revision 3 is more up-to-date than revision 2. Thus, when this gets incremented, the cache handling is smart enough to clean up any older revisions of your definition as well as flush the cache.<br />You can leave this as is unless you know what you are doing & are editing the purifier files directly");
define("_MD_AM_PURIFIER_URI_DISABLE", "Disable all URI in user posts");
define("_MD_AM_PURIFIER_URI_DISABLEDSC", "Disabling URI will prevent users from posting any links whatsoever, it is not recommended to enable this except for test purposes.<br />Default is 'No'");
define("_MD_AM_PURIFIER_URI_BLACKLIST", "URI Blacklist");
define("_MD_AM_PURIFIER_URI_BLACKLISTDSC", "Domänennamen eingeben, die aus Benutzerbeiträgen gefiltert (entfernt) werden sollen.");
define("_MD_AM_PURIFIER_URI_ALLOWSCHEME", "Erlaubte URI-Schemas");
define("_MD_AM_PURIFIER_URI_ALLOWSCHEMEDSC", "Whitelist , die die Schemata definiert, die eine URI haben darf. Dies verhindert XSS-Attacken, Pseudo-Schemata wie Javascript oder Mocha zu verwenden.<br />Akzeptierte Werte (http, https, ftp, mailto, nntp, news)");
define("_MD_AM_PURIFIER_URI_HOST", "URI Basis-Domain");
define("_MD_AM_PURIFIER_URI_HOSTDSC", "URI Base eingeben. Leer lassen zum Deaktivieren!");
define("_MD_AM_PURIFIER_URI_BASE", "URI Basis-Domain");
define("_MD_AM_PURIFIER_URI_BASEDSC", "URI Base eingeben. Leer lassen zum Deaktivieren!");
define("_MD_AM_PURIFIER_URI_DISABLEEXT", "Externe Links deaktivieren");
define("_MD_AM_PURIFIER_URI_DISABLEEXTDSC", "Deaktiviert Links zu externen Webseiten. Dies ist eine hochwirksame Anti-Spam und Anti-Pagerank-Lektion Messung, aber kommt zu einem hohen Preis: Nolinks oder Bilder außerhalb Ihrer Domäne sind erlaubt.<br />Nicht verlinkte URIs bleiben erhalten. Wenn Sie auf Subdomains verweisen oder absolute URIs verwenden möchten, aktivieren Sie URI Host für Ihre Website.");
define("_MD_AM_PURIFIER_URI_DISABLEEXTRES", "Externe Ressourcen deaktivieren");
define("_MD_AM_PURIFIER_URI_DISABLEEXTRESDSC", "Deaktiviert die Einbettung externer Ressourcen, wodurch Benutzer daran gehindert werden, Dinge wie Bilder von anderen Hosts einzubetten. Dies verhindert Zugriffsverfolgung (gut für E-Mail-Betrachter), Bandbreitenleeching, Site-übergreifendes Anfragenschmieden, Ziege. x Posting und andere Verrücktheiten führen aber auch zu einem Verlust der Endanwender-Funktionalität (sie können nicht mehr direkt ein Bild posten, das sie von Flickr gepostet haben). Benutzen Sie es, wenn Sie kein robustes Moderationsteam für Benutzerinhalte haben. ");
define("_MD_AM_PURIFIER_URI_DISABLERES", "Ressourcen deaktivieren");
define("_MD_AM_PURIFIER_URI_DISABLERESDSC", "Deaktiviert das Einbetten von Ressourcen, was im Wesentlichen keine Bilder bedeutet. Du kannst sie trotzdem verknüpfen. Siehe URI Deaktiviere externe Ressourcen, warum dies eine gute Idee sein könnte.");
define("_MD_AM_PURIFIER_URI_MAKEABS", "URI Absolut");
define("_MD_AM_PURIFIER_URI_MAKEABSDSC", "Konvertiert alle URIs in absolute Formulare. Dies ist nützlich, wenn das gefilterte HTML einen bestimmten Basispfad annimmt, aber wird tatsächlich in einem anderen Kontext angezeigt (und das Setzen einer alternativen Basis-URI ist nicht möglich).<br /><br />URI Base muss aktiviert sein, damit diese Direktive funktioniert.");
// Filter Section
define("_MD_AM_PURIFIER_FILTER_EXTRACTSTYLEESC", "Gefährliche Zeichen in StyleBlocks vermeiden");
define("_MD_AM_PURIFIER_FILTER_EXTRACTSTYLEESCDSC", "Gibt an, ob die gefährlichen Zeichen <, > und & als <unk> C, <unk> E und <unk> <unk> bzw. <unk> entschärft werden sollen. Dies kann sicher auf false gesetzt werden, wenn der Inhalt von StyleBlocks in einem externen Stylesheet platziert wird wo es keine Gefahr gibt, dass es als HTML interpretiert wird.");
define("_MD_AM_PURIFIER_FILTER_EXTRACTSTYLEBLKSCOPE", "Enter StyleBlocks Scope");
define("_MD_AM_PURIFIER_FILTER_EXTRACTSTYLEBLKSCOPEDSC", "Wenn Benutzer externe Stylesheets definieren möchten aber sie können nur CSS-Deklarationen für einen bestimmten Knoten angeben und verhindern, dass sie mit anderen Elementen manipulieren, verwenden Sie diese Direktive.<br />Es akzeptiert jeden gültigen CSS-Selektor und wird dies jeder CSS-Deklaration, die aus dem Dokument extrahiert wurde, voranstellen.<br /><br />Zum Beispiel, wenn diese Direktive auf #user-content gesetzt ist und ein Benutzer den Selektor a:hover verwendet, der endgültige Selektor wird #user-content a:hover.<br /><br />Die Komma-Kurzform kann verwendet werden; denken Sie an das obige Beispiel mit #user-content, #user-content2, der endgültige Selektor ist #user-content a:hover, #user-content2 a:hover.");
define("_MD_AM_PURIFIER_FILTER_ENABLEYOUTUBE", "Erlaubtes Einbetten YouTube-Video");
define("_MD_AM_PURIFIER_FILTER_ENABLEYOUTUBEDSC", "Diese Direktive aktiviert das Einbinden von YouTube-Videos in HTML-Purifier. Überprüfen Sie <a href='http://htmlpurifier.org/docs/enduser-youtube.html'>dieses</a> Dokument, um Videos einzubinden, um weitere Informationen darüber zu erhalten, was dieser Filter tut.");
define("_MD_AM_PURIFIER_FILTER_EXTRACTSTYLEBLK", "Extract Style Blocks?");
define("_MD_AM_PURIFIER_FILTER_EXTRACTSTYLEBLKDSC", "Requires CSSTidy Plugin to be installed).<br /><br />This directive turns on the style block extraction filter, which removes style blocks from input HTML, cleans them up with CSSTidy, and places them in the StyleBlocks context variable, for further use by you, usually to be placed in an external stylesheet, or a style block in the head of your document.<br /><br />Warning: It is possible for a user to mount an imagecrash attack using this CSS. Counter-measures are difficult; it is not simply enough to limit the range of CSS lengths (using relative lengths with many nesting levels allows for large values to be attained without actually specifying them in the stylesheet), and the flexible nature of selectors makes it difficult to selectively disable lengths on image tags (HTML Purifier, however, does disable CSS width and height in inline styling). There are probably two effective counter measures: an explicit width and height set to auto in all images in your document (unlikely) or the disabling of width and height (somewhat reasonable). Whether or not these measures should be used is left to the reader.");
// Core Section
define("_MD_AM_PURIFIER_CORE_ESCINVALIDTAGS", "Escape Invalid Tags");
define("_MD_AM_PURIFIER_CORE_ESCINVALIDTAGSDSC", "When enabled, invalid tags will be written back to the document as plain text. Otherwise, they are silently dropped.");
define("_MD_AM_PURIFIER_CORE_ESCNONASCIICHARS", "Escape Non ASCII Characters");
define("_MD_AM_PURIFIER_CORE_ESCNONASCIICHARSDSC", "This directive overcomes a deficiency in %Core.Encoding by blindly converting all non-ASCII characters into decimal numeric entities before converting it to its native encoding. This means that even characters that can be expressed in the non-UTF-8 encoding will be entity-ized, which can be a real downer for encodings like Big5. It also assumes that the ASCII repetoire is available, although this is the case for almost all encodings. Anyway, use UTF-8!");
define("_MD_AM_PURIFIER_CORE_HIDDENELE", "Enable HTML Hidden Elements");
define("_MD_AM_PURIFIER_CORE_HIDDENELEDSC", "This directive is a lookup array of elements which should have their contents removed when they are not allowed by the HTML definition. For example, the contents of a script tag are not normally shown in a document, so if script tags are to be removed, their contents should be removed to. This is opposed to a b  tag, which defines some presentational changes but does not hide its contents.");
define("_MD_AM_PURIFIER_CORE_COLORKEYS", "Colour Keywords");
define("_MD_AM_PURIFIER_CORE_COLORKEYSDSC", "Lookup array of color names to six digit hexadecimal number corresponding to color, with preceding hash mark. Used when parsing colors.");
define("_MD_AM_PURIFIER_CORE_REMINVIMG", "Remove Invalid Image");
define("_MD_AM_PURIFIER_CORE_REMINVIMGDSC", "This directive enables pre-emptive URI checking in img tags, as the attribute validation strategy is not authorized to remove elements from the document. Default = yes");
// AutoFormat Section
define("_MD_AM_PURIFIER_AUTO_AUTOPARA", "Enable Paragraph Auto Format");
define("_MD_AM_PURIFIER_AUTO_AUTOPARADSC", "This directive turns on auto-paragraphing, where double newlines are converted in to paragraphs whenever possible.<br /> Auto-paragraphing:<br /><br />* Always applies to inline elements or text in the root node,<br />* Applies to inline elements or text with double newlines in nodes that allow paragraph tags,<br />* Applies to double newlines in paragraph tags.<br /></br>p tags must be allowed for this directive to take effect. We do not use br tags for paragraphing, as that is semantically incorrect.<br />To prevent auto-paragraphing as a content-producer, refrain from using double-newlines except to specify a new paragraph or in contexts where it has special meaning (whitespace usually has no meaning except in tags like pre, so this should not be difficult.) To prevent the paragraphing of inline text adjacent to block elements, wrap them in div tags (the behavior is slightly different outside of the root node.)");
define("_MD_AM_PURIFIER_AUTO_DISPLINKURI", "Enable Link Display");
define("_MD_AM_PURIFIER_AUTO_DISPLINKURIDSC", "This directive turns on the in-text display of URIs in <a> tags, and disables those links. For example, <a href=\"http://example.com\">example</a> becomes example (http://example.com).");
define("_MD_AM_PURIFIER_AUTO_LINKIFY", "Auto Linkify aktivieren");
define("_MD_AM_PURIFIER_AUTO_LINKIFYDSC", "Diese Direktive schaltet die Linkification, die automatische Verlinkung von http, ftp und https ein. Ein Tag mit dem href Attribut muss erlaubt sein. ");
define("_MD_AM_PURIFIER_AUTO_PURILINKIFY", "Aktiviere Purifier Interne Linkify");
define("_MD_AM_PURIFIER_AUTO_PURILINKIFYDSC", "Internal auto-formatter that converts configuration directives in syntax %Namespace.Directive to links. a tags with the href attribute must be allowed. (Leave this as is if you are not having any problems)");
define("_MD_AM_PURIFIER_AUTO_CUSTOM", "Erlaubte benutzerdefinierte Formatierung");
define("_MD_AM_PURIFIER_AUTO_CUSTOMDSC", "This directive can be used to add custom auto-format injectors. Specify an array of injector names (class name minus the prefix) or concrete implementations. Injector class must exist. please visit <a href='http://www.htmlpurifier.org'>HTML Purifier Homepage</a> for more info.");
define("_MD_AM_PURIFIER_AUTO_REMOVEEMPTY", "Remove Empty Elements");
define("_MD_AM_PURIFIER_AUTO_REMOVEEMPTYDSC", " When enabled, HTML Purifier will attempt to remove empty elements that contribute no semantic information to the document. The following types of nodes will be removed:<br /><br />
 * Tags with no attributes and no content, and that are not empty elements (remove \\<a\\>\\</a\\> but not \\<br /\\>), and<br />
 * Tags with no content, except for:<br />
   o The colgroup element, or<br />
   o Elements with the id or name attribute, when those attributes are permitted on those elements.<br /><br />
Please be very careful when using this functionality; while it may not seem that empty elements contain useful information, they can alter the layout of a document given appropriate styling. This directive is most useful when you are processing machine-generated HTML, please avoid using it on regular user HTML.<br /><br />
Elements that contain only whitespace will be treated as empty. Non-breaking spaces, however, do not count as whitespace. See 'Remove Empty Spaces' for alternate behavior.");
define("_MD_AM_PURIFIER_AUTO_REMOVEEMPTYNBSP", "Remove Non-Breaking Spaces");
define("_MD_AM_PURIFIER_AUTO_REMOVEEMPTYNBSPDSC", "When enabled, HTML Purifier will treat any elements that contain only non-breaking spaces as well as regular whitespace as empty, and remove them when 'Remove Empty Elements' is enabled.<br /><br />
See 'Remove Empty Nbsp Override' for a list of elements that don't have this behavior applied to them.");
define("_MD_AM_PURIFIER_AUTO_REMOVEEMPTYNBSPEXCEPT", "Remove empty Nbsp Override");
define("_MD_AM_PURIFIER_AUTO_REMOVEEMPTYNBSPEXCEPTDSC", "When enabled, this directive defines what HTML elements should not be removed if they have only a non-breaking space in them.");
// Attribute Section
define("_MD_AM_PURIFIER_ATTR_ALLOWFRAMETARGET", "Allowed Frame Targets");
define("_MD_AM_PURIFIER_ATTR_ALLOWFRAMETARGETDSC", "Lookup table of all allowed link frame targets. Some commonly used link targets include _blank, _self, _parent and _top. Values should be lowercase, as validation will be done in a case-sensitive manner despite W3C's recommendation. XHTML 1.0 Strict does not permit the target attribute so this directive will have no effect in that doctype. XHTML 1.1 does not enable the Target module by default, you will have to manually enable it (see the module documentation for more details.)");
define("_MD_AM_PURIFIER_ATTR_ALLOWREL", "Zulässige Dokumentbeziehungen");
define("_MD_AM_PURIFIER_ATTR_ALLOWRELDSC", "Liste der erlaubten Weiterleitungs-Dokumenten-Beziehungen im Rel-Attribut. Gemeinsame Werte können nofollow oder ausgeben.<br /><br />Standard = extern, nofollow, externe nofollow & lightbox.");
define("_MD_AM_PURIFIER_ATTR_ALLOWCLASSES", "Zulässige Klassenwerte");
define("_MD_AM_PURIFIER_ATTR_ALLOWCLASSESDSC", "Liste der zulässigen Klassenwerte im Klassenattribut. Lassen Sie dieses Feld leer, um alle Werte im Klassenattribut zu erlauben.");
define("_MD_AM_PURIFIER_ATTR_FORBIDDENCLASSES", "Forbidden Class Values");
define("_MD_AM_PURIFIER_ATTR_FORBIDDENCLASSESDSC", "Liste der Verbotenen Klassenwerte im Klassenattribut lassen. Leer lassen um alle Werte im Klassenattribut zu erlauben.");
define("_MD_AM_PURIFIER_ATTR_DEFINVIMG", "Ungültiges Standardbild");
define("_MD_AM_PURIFIER_ATTR_DEFINVIMGDSC", "Dies ist das Standardbild, auf das ein img-Tag verwiesen wird, wenn es kein gültiges src-Attribut hat. In zukünftigen Versionen kann es sein, dass das Image-Tag komplett entfernt wird, aber aufgrund von Designproblemen ist das momentan nicht möglich.");
define("_MD_AM_PURIFIER_ATTR_DEFINVIMGALT", "Ungültiges Standardbild");
define("_MD_AM_PURIFIER_ATTR_DEFINVIMGALTDSC", "Dies ist der Inhalt des Alt-Tags eines ungültigen Bildes, wenn der Benutzer zuvor kein Alt-Attribut angegeben hatte. Es hat keinen Effekt, wenn das Bild gültig ist, aber es war kein Alt-Attribut vorhanden.");
define("_MD_AM_PURIFIER_ATTR_DEFIMGALT", "Standard-Bild-Alt-Tag");
define("_MD_AM_PURIFIER_ATTR_DEFIMGALTDSC", "Dies ist der Inhalt des Alt-Tags eines Bildes, wenn der Benutzer zuvor kein Alt-Attribut angegeben hatte.<br />Dies gilt für alle Bilder ohne gültiges Alt-Attribut im Gegensatz zum Standard-ungültigen Alt-Tag , was nur auf ungültige Bilder zutrifft und im Falle eines ungültigen Bildes überschreibt.<br />Standardverhalten bei Null ist die Verwendung des Basisnamens des src Tags für die Alt.");
define("_MD_AM_PURIFIER_ATTR_CLASSUSECDATA", "NMTokens oder CDATA-Spezifikationen verwenden");
define("_MD_AM_PURIFIER_ATTR_CLASSUSECDATADSC", "Wenn null, wird die Klasse den Doctype automatisch erkennen und, wenn er mit XHTML 1.1 oder XHTML 2 übereinstimmt. verwendet die restriktive NMTOKENS-Spezifikation der Klasse, andernfalls wird eine entspannte CDATA-Definition verwendet. Wenn ja, wird die entspannte CDATA-Definition erzwungen, falls falsch, wird die NMTOKENS-Definition erzwungen. Um das Verhalten von HTML Purifier vor 4.0.0 zu erhalten, setzen Sie diese Direktive auf false. Etwas rational hinter der Auto-Erkennung: in früheren Versionen von HTML Purifier, Es wurde angenommen, dass die Form der Klasse NMTOKENS war, wie sie durch die XHTML-Modularisierung angegeben wurde (repräsentiert XHTML 1. Die DTDs für HTML 4. 1 und XHTML 1.0, aber Klasse als CDATA angeben. HTML 5 definiert es effektiv als CDATA, aber mit der zusätzlichen Einschränkung, dass jeder Name eindeutig sein sollte (dies wird in früheren Spezifikationen nicht explizit umrissen).");
define("_MD_AM_PURIFIER_ATTR_ENABLEID", "ID Attribute erlauben?");
define("_MD_AM_PURIFIER_ATTR_ENABLEIDDSC", "Erlaubt das ID-Attribut in HTML. Dies ist standardmäßig deaktiviert, da Benutzereingaben ohne eine korrekte Konfiguration die Validierung einer Webseite durch Angabe einer bereits im umgebenden HTML enthaltenen ID leicht durchbrechen können. Wenn Sie nichts dagegen haben, Vorsicht walten zu lassen, dann aktivieren Sie diese Richtlinie, aber ich empfehle Ihnen auch die Blacklisting IDs die Sie verwenden (ID Blacklist) oder Präfixierung aller von Ihnen gelieferten IDs (ID Prefix).");
define("_MD_AM_PURIFIER_ATTR_IDPREFIX", "Attribut-ID Präfix festlegen");
define("_MD_AM_PURIFIER_ATTR_IDPREFIXDSC", "Zeichenkette zum Präfix für IDs. Wenn Sie keine Ahnung haben, welche IDs Ihre Seiten verwenden können Sie können sich entscheiden, einfach ein Präfix zu allen von Ihnen übermittelten ID-Attributen hinzuzufügen, so dass diese noch verwendbar sind aber nicht in Konflikt mit den Kernseiten-IDs. Beispiel: Die Einstellung der Direktive auf 'user_' führt dazu, dass ein Benutzer 'foo' einreicht, um 'user_foo' zu werden. Stellen Sie sicher, dass 'Erlaube ID Attribute' auf 'yes gesetzt wird, bevor Sie dies verwenden.");
define("_MD_AM_PURIFIER_ATTR_IDPREFIXLOCAL", "Erlaubte benutzerdefinierte Formatierung");
define("_MD_AM_PURIFIER_ATTR_IDPREFIXLOCALDSC", "Temporäres Präfix für IDs, die in Verbindung mit Attribut-ID-Prefix verwendet werden. Wenn Sie mehrere Sätze von Benutzerinhalten auf der Webseite erlauben müssen Sie müssen unter Umständen einen separaten Präfix haben, der sich bei jeder Iteration ändert. Auf diese Weise werden auf der gleichen Seite einzeln eingereichte Benutzerinhalte nicht miteinander verwechselt. Ideale Werte sind eindeutige Bezeichner für den Inhalt, den er repräsentiert (d.h. die ID der Zeile in der Datenbank). Stellen Sie sicher, dass Sie am Ende einen Trennzeichen (wie ein Unterstrich) hinzufügen. Warnung: Diese Direktive funktioniert nicht, es sei denn, Attribut-ID-Präfix ist auf einen nicht-leeren Wert gesetzt!");
define("_MD_AM_PURIFIER_ATTR_IDBLACKLIST", "Attribut-ID Blacklist");
define("_MD_AM_PURIFIER_ATTR_IDBLACKLISTDSC", "Das Array der IDs ist im Dokument nicht erlaubt.");
// CSS Section
define("_MD_AM_PURIFIER_CSS_ALLOWIMPORTANT", "Erlaube !important in CSS-Styles");
define("_MD_AM_PURIFIER_CSS_ALLOWIMPORTANTDSC", "Dieser Parameter legt fest, ob wichtige Kaskaden-Modifikatoren im Benutzer-CSS erlaubt sein sollen. Wenn nein, wird !important entfernt.");
define("_MD_AM_PURIFIER_CSS_ALLOWTRICKY", "Tricky CSS-Styles erlauben");
define("_MD_AM_PURIFIER_CSS_ALLOWTRICKYDSC", "Dieser Parameter legt fest, ob \"tricky\" CSS-Eigenschaften und -Werte erlaubt werden sollen oder nicht. Tricky CSS properties/values können das Seitenlayout drastisch verändern oder für betrügerische Praktiken verwendet werden, stellen aber nicht direkt ein Sicherheitsrisiko dar. Zum Beispiel display:none; wird als eine heikle Eigenschaft betrachtet, die nur zulässig ist, wenn diese Direktive auf Null gesetzt ist.");
define("_MD_AM_PURIFIER_CSS_ALLOWPROP", "Erlaubte CSS-Eigenschaften");
define("_MD_AM_PURIFIER_CSS_ALLOWPROPDSC", "Wenn HTML-Purifier's Style-Attributset für Ihre Bedürfnisse unbefriedigend ist, können Sie es mit Ihrer eigenen Liste von Tags überladen zu erlauben. Beachten Sie, dass diese Methode subtraktiv ist: Sie erledigt ihren Auftrag, indem sie HTML Purifier übliches Feature Set wegnimmt so dass Sie kein Attribut hinzufügen können, das HTML Purifier überhaupt nicht unterstützt.<br /><br />Warnung: Wenn eine andere Präferenz mit den Elementen hier kollidiert, wird diese Präferenz gewinnen und überschreiben.");
define("_MD_AM_PURIFIER_CSS_DEFREV", "CSS Definition Revision");
define("_MD_AM_PURIFIER_CSS_DEFREVDSC", "Revisions-Bezeichner für Ihre benutzerdefinierte Definition. Weitere Informationen finden Sie in der HTML-Definition Revision .");
define("_MD_AM_PURIFIER_CSS_MAXIMGLEN", "Maximale CSS-Bild-Länge");
define("_MD_AM_PURIFIER_CSS_MAXIMGLENDSC", "Dieser Parameter legt die maximal zulässige Länge für img Tags fest, effektiv die Breite und Höhe Eigenschaften. Nur absolute Maßeinheiten (In, pt, pc, mm, cm) und Pixel (px) sind erlaubt. Dies ist vorhanden, um Absturzangriffe auf das Image zu verhindern. Deaktivieren Sie diese Option auf eigene Gefahr. Diese Direktive ist ähnlich der HTML Max Image Länge und beide sollten gleichzeitig bearbeitet werden, obwohl es subtile Unterschiede im Eingabeformat gibt (das CSS max ist eine Zahl mit einer Einheit).");
define("_MD_AM_PURIFIER_CSS_PROPRIETARY", "Sichere Proprietäre CSS erlauben");
define("_MD_AM_PURIFIER_CSS_PROPRIETARYDSC", "Gibt an, ob sichere, proprietäre CSS-Werte erlaubt werden sollen oder nicht.");
// purifier config options
define("_MD_AM_PURIFIER_401T", "HTML 4.01 Transitional");
define("_MD_AM_PURIFIER_401S", "HTML 4.01 Strict");
define("_MD_AM_PURIFIER_X10T", "HTML 1.0 Transitional");
define("_MD_AM_PURIFIER_X10S", "HTML 1.0 Strict");
define("_MD_AM_PURIFIER_X11", "XHTML 1.1");
define("_MD_AM_PURIFIER_WEGAME", "WEGAME-Videos");
define("_MD_AM_PURIFIER_VIMEO", "Vimeo-Filme");
define("_MD_AM_PURIFIER_LOCALMOVIE", "Lokale Filme");
define("_MD_AM_PURIFIER_GOOGLEVID", "Google-Videos");
define("_MD_AM_PURIFIER_LIVELEAK", "LiveLeak Filme");

define("_MD_AM_UNABLECSSTIDY", "Das CSSTidy-Plugin wurde nicht gefunden. Kopieren Sie bitte das CSSTidy-Plugin, das sich in Ihrem Plugin-Ordner befindet.");

// Autotasks
if (!defined('_MD_AM_AUTOTASKS')) {
	define('_MD_AM_AUTOTASKS', 'Automatische Aufgaben');
}
define("_MD_AM_AUTOTASKS_SYSTEM", "Verarbeitungssystem");
define("_MD_AM_AUTOTASKS_HELPER", "Hilfe-Anwendung");
define("_MD_AM_AUTOTASKS_HELPER_PATH", "Pfad für Helfer-Anwendung");

define("_MD_AM_AUTOTASKS_SYSTEMDSC", "Mit welchem Tasksystem sollen Aufgaben ausgeführt werden?");
define("_MD_AM_AUTOTASKS_HELPERDSC", "Bitte geben Sie für jedes andere Verarbeitungssystem als 'internal' eine Helferanwendung an. Allerdings wird nur eine Anwendung verwendet, also wählen Sie sorgfältig aus.");
define("_MD_AM_AUTOTASKS_HELPER_PATHDSC", "Wenn sich Ihre Helfer-Anwendung nicht im System-Standardpfad befindet, müssen Sie den Pfad zu Ihrer Helfer-Anwendung angeben.");
define("_MD_AM_AUTOTASKS_USER", "Systembenutzer");
define("_MD_AM_AUTOTASKS_USERDSC", "Systembenutzer, der für die Ausführung der Aufgabe verwendet wird.");

// source editedit
define("_MD_AM_SRCEDITOR_DEFAULT", "Standard Quellcode-Editor");
define("_MD_AM_SRCEDITOR_DEFAULT_DESC", "Wählen Sie den Standard-Editor für das Bearbeiten von Quellcodes.");

// added in 1.2.1
define("_MD_AM_SMTPSECURE", "SMTP Sichere Methode");
define("_MD_AM_SMTPSECUREDESC", "Authentifizierungsmethode für SMTPAuthentication. (Standard ist ssl)");
define("_MD_AM_SMTPAUTHPORT", "SMTP-Port");
define("_MD_AM_SMTPAUTHPORTDESC", "Die Port-Nutzung durch Ihren SMTP Mail-Server (Standard ist 465)");

// added in 1.3
define("_MD_AM_PURIFIER_OUTPUT_FLASHCOMPAT", "IE Flash-Kompatibilität aktivieren");
define("_MD_AM_PURIFIER_OUTPUT_FLASHCOMPATDSC", "Wenn aktiviert, generiert HTML Purifier den Internet Explorer Kompatibilitätscode für den gesamten Objektcode. Dies wird dringend empfohlen, wenn Sie HTML.SafeObject aktivieren.");
define("_MD_AM_PURIFIER_HTML_FLASHFULLSCRN", "Vollbild in Flash-Objekten erlauben");
define("_MD_AM_PURIFIER_HTML_FLASHFULLSCRNDSC", "Wenn aktiviert, erlaubt HTML Purifier die Verwendung von 'allowFullScreen' in eingebetteten Flash-Inhalten bei der Verwendung von HTML.SafeObject.");
define("_MD_AM_PURIFIER_CORE_NORMALNEWLINES", "Zeilenumbrüche normalisieren");
define("_MD_AM_PURIFIER_CORE_NORMALNEWLINESDSC", "Gibt an, ob Zeilenumbrüche auf die Standardeinstellung des Betriebssystems normalisiert werden sollen. Wenn falsch, versucht HTML Purifier gemischte Zeilenumbrüche zu speichern.");
define('_MD_AM_AUTHENTICATION_DSC', 'Verwalten Sie die Sicherheitseinstellungen im Zusammenhang mit der Barrierefreiheit. Einstellungen, die den Umgang mit Benutzerkonten beeinflussen.');
define('_MD_AM_AUTOTASKS_PREF_DSC', 'Einstellungen für das System Automatische Aufgaben.');
define('_MD_AM_CAPTCHA_DSC', 'Verwalten Sie die Einstellungen, die von Captcha auf Ihrer Website verwendet werden.');
define('_MD_AM_GENERAL_DSC', 'Die primäre Einstellungsseite für grundlegende Informationen, die vom System benötigt werden.');
define('_MD_AM_PURIFIER_DSC', 'HTMLPurifier schützt Ihre Website vor häufigen Angriffsmethoden.');
define('_MD_AM_MAILER_DSC', 'Konfigurieren Sie, wie Ihre Website mit E-Mails umgeht.');
define('_MD_AM_METAFOOTER_DSC', 'Verwalten Sie Ihre Meta-Informationen und Seitenfußzeile sowie Ihre Crawlereinstellungen.');
define('_MD_AM_MULTILANGUAGE_DSC', 'Verwalten Sie Ihre Websites mehrsprachige Einstellungen. Aktivieren und konfigurieren, welche Sprachen verfügbar sind und wie sie ausgelöst werden.');
define('_MD_AM_PERSON_DSC', 'Personalisieren Sie das System mit eigenen Logos und anderen Einstellungen.');
define('_MD_AM_PLUGINS_DSC', 'Wählen Sie aus, welche Plugins verwendet werden und welche auf Ihrer Website verwendet werden sollen.');
define('_MD_AM_SEARCH_DSC', 'Verwalten Sie die Funktionsweise der Suchfunktion für Ihre Benutzer.');
define('_MD_AM_USERSETTINGS_DSC', 'Verwalten Sie, wie Benutzer sich für Ihre Website registrieren. Länge der Server, Formatierung und Passwort-Optionen.');
define('_MD_AM_CENSOR_DSC', 'Verwalten Sie die Sprache, die auf Ihrer Website nicht erlaubt ist.');
define("_MD_AM_PURIFIER_FILTER_ALLOWCUSTOM", "Eigene Filter zulassen");
define("_MD_AM_PURIFIER_FILTER_ALLOWCUSTOMDSC", "Eigene Filter erlauben?<br /><br />wenn aktiviert, können Sie benutzerdefinierte Filter verwenden;<br />'libraries/htmlpurifier/standalone/HTMLPurifier/Filter'");

// added in 1.3.2
define("_MD_AM_PURIFIER_HTML_SAFEIFRAME", "Sicheres Iframes aktivieren");
define("_MD_AM_PURIFIER_HTML_SAFEIFRAMEDSC", "Ob Iframes in Dokumenten erlaubt werden soll oder nicht, mit einer Reihe zusätzlicher Sicherheitsfunktionen, um die Ausführung des Skripts zu verhindern. Sie müssen sichere Domains in den Safe Iframes URLs hinzufügen, bevor Sie sie aktivieren!.");
define("_MD_AM_PURIFIER_URI_SAFEIFRAMEREGEXP", "Sichere Iframes URLs");
define("_MD_AM_PURIFIER_URI_SAFEIFRAMEREGEXPDSC", "Eine Liste von URLs, die Sie erlauben möchten, Iframe-Inhalte auf Ihrer Website anzuzeigen. Dies wird auf eine Iframe-URL angepasst. Dies ist ein relativ unflexibles Schema, aber funktioniert gut genug für den häufigsten Anwendungsfall von iframes: eingebettetes Video. <br />Lassen Sie den Site-Besitzer explizit zulassen, dass unbekannte Sites iframes auf Ihrer Site mit Inhalten anzeigen, die Sie nicht kontrollieren können.<br /><br />
    Hier einige Beispielwerte:<br /><br />

    http://www. outube.com/embed/ - YouTube-Videos zulassen<br />
    http://player.vimeo. om/video/ - Vimeo-Videos erlauben<br />
    http://www.youtube.com/embed/|http://player. imeo.com/video/ - Erlauben Sie beiden<br /><br />HTML Safe Iframe muss aktiviert sein, damit dies funktioniert.");

// added in 1.3.3
define("_MD_AM_ENC_RIPEMD256", "RIPEMD 256");
define("_MD_AM_ENC_RIPEMD320", "RIPEMD 320");
define("_MD_AM_ENC_SNEFRU256", "Snefru 256");
define("_MD_AM_ENC_GOST", "Gast");