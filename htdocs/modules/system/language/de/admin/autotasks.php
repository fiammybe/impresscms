<?php
define("_CO_ICMS_AUTOTASKS_NAME", "Aufgabenname");
define("_CO_ICMS_AUTOTASKS_NAME_DSC", "Geben Sie den Aufgabennamen ein.");
define("_CO_ICMS_AUTOTASKS_CODE", "Quelltext");
define("_CO_ICMS_AUTOTASKS_CODE_DSC", "Hier können Sie PHP-Code schreiben, der als Aufgabe ausgeführt werden soll.<p style='color:red'>Ohne &lt;?php und ?&gt;</p><br /><br />Hauptdatei. hp wird bereits aufgenommen.<br />Verwenden Sie <i>icms::\\$xoopsDB</i> , um das Datenbankobjekt zu nutzen.");
define("_CO_ICMS_AUTOTASKS_REPEAT", "Wiederholung");
define("_CO_ICMS_AUTOTASKS_REPEAT_DSC", "Wie oft soll diese Aufgabe wiederholt werden? Geben Sie '0' ein, wenn Sie eine dauerhaft laufende Aufgabe erstellen möchten.");
define("_CO_ICMS_AUTOTASKS_INTERVAL", "Intervall");
define("_CO_ICMS_AUTOTASKS_INTERVAL_DSC", "Ausführungsintervall (in Minuten).<br /><br />60: einmal pro Stunde<br />1440: einmal pro Tag");
define("_CO_ICMS_AUTOTASKS_ONFINISH", "Automatisch löschen");
define("_CO_ICMS_AUTOTASKS_ONFINISH_DSC", "Soll  Diese Aufgabe nach der angegebenen Anzahl an Wiederholungen gelöscht werden? Wählen Sie ‚Ja‘, wenn Sie  Diese Aufgabe automatisch aus der Aufgabenliste entfernen möchten oder ‚Nein‘, um  Diese Aufgabe in den Pause-Modus zu wechseln.<br />  Diese Regel gilt nur für Wiederholungen größer als ‚0‘.");
define("_CO_ICMS_AUTOTASKS_ENABLED", "Aktiviert");
define("_CO_ICMS_AUTOTASKS_ENABLED_DSC", "Wählen Sie 'Ja', um diese Aufgabe zu aktivieren.");
define("_CO_ICMS_AUTOTASKS_TYPE", "Typ");
define("_CO_ICMS_AUTOTASKS_LASTRUNTIME", "Letzte Ausführung");

define("_CO_ICMS_AUTOTASKS_CREATE", "Neue Aufgabe erstellen");
define("_CO_ICMS_AUTOTASKS_EDIT", "Aufgabe bearbeiten");

define("_CO_ICMS_AUTOTASKS_CREATED", "Aufgabe hinzugefügt");
define("_CO_ICMS_AUTOTASKS_MODIFIED", "Aufgabe geändert");

define("_CO_ICMS_AUTOTASKS_NOTYETRUNNED", "Noch nicht ausgeführt");

define("_CO_ICMS_AUTOTASKS_TYPE_CUSTOM", "Benutzer");
define("_CO_ICMS_AUTOTASKS_TYPE_ADDON", "System");

define("_CO_ICMS_AUTOTASKS_FOREVER", "dauerhaft");

define("_CO_ICMS_AUTOTASKS_INIT_ERROR", "Fehler: Ausgewählte automatische Aufgaben können nicht initialisiert werden.");

define("_CO_ICMS_AUTOTASKS_SOURCECODE_ERROR", "Fehler in Autotask SourceCode: Autotask kann nicht ausgeführt werden");
?>