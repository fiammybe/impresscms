<?php
// $Id: mailusers.php 10326 2010-07-11 18:54:25Z malanciault $
//%%%%%%	Admin Module Name  MailUsers	%%%%%
if (!defined('_AM_DBUPDATED')) {define("_AM_DBUPDATED","Datenbank erfolgreich aktualisiert!");}

//%%%%%%	mailusers.php 	%%%%%
define("_AM_SENDTOUSERS","Nachricht an Benutzer senden:");
define("_AM_SENDTOUSERS2","Senden an:");
define("_AM_GROUPIS","Gruppe ist (optional)");
define("_AM_TIMEFORMAT", "(Format jjjjjj-mm-dd, optional)");
define("_AM_LASTLOGMIN","Letzter Login ist nach");
define("_AM_LASTLOGMAX","Letzter Login ist vor");
define("_AM_REGDMIN","Registrierungsdatum ist nach");
define("_AM_REGDMAX","Registrierungsdatum ist vor");
define("_AM_IDLEMORE","Letzter Login war vor mehr als X Tagen (optional)");
define("_AM_IDLELESS","Letzte Anmeldung war vor weniger als X Tagen (optional)");
define("_AM_MAILOK","Nachricht nur an Benutzer senden, die Benachrichtigungen akzeptieren (optional)");
define("_AM_INACTIVE","Nachricht nur an inaktive Benutzer senden (optional)");
define("_AMIFCHECKD", "Wenn diese Option aktiviert ist, werden alle oben genannten plus privaten Nachrichten ignoriert");
define("_AM_MAILFNAME","Von Name (nur E-Mail)");
define("_AM_MAILFMAIL","Von E-Mail (nur E-Mail)");
define("_AM_MAILSUBJECT","Betreff");
define("_AM_MAILBODY","Inhalt");
define("_AM_MAILTAGS","Nützliche Schlagworte");
define("_AM_MAILTAGS1","{X_UID} druckt Benutzer-Id an");
define("_AM_MAILTAGS2","{X_UNAME} druckt den Benutzernamen");
define("_AM_MAILTAGS3","{X_UEMAIL} druckt die E-Mail des Benutzers");
define("_AM_MAILTAGS4","{X_UACTLINK} druckt den Aktivierungslink für Benutzer");
define("_AM_SENDTO","Senden an");
define("_AM_EMAIL","E-Mail");
define("_AM_PM","Private Nachricht");
define("_AM_SENDMTOUSERS", "Nachricht an Benutzer senden");
define("_AM_SENT", "An Benutzer senden");
define("_AM_SENTNUM", "%s - %s (insgesamt: %s Benutzer)");
define("_AM_SENDNEXT", "Nächste");
define("_AM_NOUSERMATCH", "Kein Benutzer gefunden");
define("_AM_SENDCOMP", "Nachrichten wurden erfolgreich gesendet.");
?>