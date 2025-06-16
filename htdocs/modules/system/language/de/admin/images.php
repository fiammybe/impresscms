<?php
// $Id: images.php 11445 2011-11-18 08:17:59Z sato-san $
//%%%%%% Image Manager %%%%%

define('_MD_IMGMAIN','Bildmanager Verwaltung');

define('_MD_ADDIMGCAT','Bild-Kategorie hinzufügen:');
define('_MD_EDITIMGCAT','Bild-Kategorie bearbeiten:');
define('_MD_IMGCATNAME','Kategoriename:');
define('_MD_IMGCATRGRP','Wählen Sie Gruppen für den Bildmanager aus:<br /><br /><span style="font-weight: normal;">Dies sind Gruppen, die den Bildmanager verwenden dürfen, um Bilder auszuwählen, aber nicht hochzuladen. Webmaster hat automatischen Zugriff.</span>');
define('_MD_IMGCATWGRP','Wählen Sie Gruppen, die Bilder hochladen dürfen:<br /><br /><span style="font-weight: normal;">Typische Nutzung ist für Moderator und Administratorgruppen.</span>');
define('_MD_IMGCATWEIGHT','Reihenfolge im Bild-Manager anzeigen:');
define('_MD_IMGCATDISPLAY','Diese Kategorie anzeigen?');
define('_MD_IMGCATSTRTYPE','Bilder werden hochgeladen nach:');
define('_MD_STRTYOPENG','Das kann danach nicht geändert werden!');
define('_MD_INDB',' In der Datenbank speichern (als Binärdatei "blob" Daten)');
define('_MD_ASFILE',' Speichern als Dateien (im Verzeichnis %s)<br />');
define('_MD_RUDELIMGCAT','Sind Sie sicher, dass Sie diese Kategorie und alle ihre Bilddateien löschen möchten?');
define('_MD_RUDELIMG','Sind Sie sicher, dass Sie diese Bilddatei löschen möchten?');

define('_MD_FAILDEL', 'Fehler beim Löschen des Bildes %s aus der Datenbank');
define('_MD_FAILDELCAT', 'Fehler beim Löschen der Bildkategorie %s aus der Datenbank');
define('_MD_FAILUNLINK', 'Fehler beim Löschen des Bildes %s aus dem Serververzeichnis');

######################## Added in 1.2 ###################################
define('_MD_FAILADDCAT', 'Fehler beim Hinzufügen der Bildkategorie');
define('_MD_FAILEDIT', 'Update fehlgeschlagen');
define('_MD_FAILEDITCAT', 'Fehler beim Aktualisieren der Kategorie');
define('_MD_IMGCATPARENT','Übergeordnete Kategorie:');
define('_MD_DELETEIMGCAT','Bild-Kategorie löschen');

define('_MD_ADDIMGCATBTN','Neue Kategorie erstellen');
define('_MD_ADDIMGBTN','Neues Bild hinzufügen');

define('_MD_IMAGESIN', 'Bilder in %s');
define('_MD_IMAGESTOT', '<b>Bilder insgesamt:</b> %s');

define('_MD_IMAGECATID', 'ID');
define('_MD_IMAGECATNAME', 'Titel');
define('_MD_IMGCATFOLDERNAME', 'Ordnername');
define('_MD_IMGCATFOLDERNAME_DESC', 'Verwenden Sie keine Leerzeichen oder Sonderzeichen!');
define('_MD_IMAGECATMSIZE', 'Maximale Größe');
define('_MD_IMAGECATMWIDTH', 'Maximale Breite');
define('_MD_IMAGECATMHEIGHT', 'Maximale Höhe');
define('_MD_IMAGECATDISP', 'Anzeige');
define('_MD_IMAGECATSTYPE', 'Speichertyp');
define('_MD_IMAGECATATUORESIZE', 'Automatische Größenänderung');
define('_MD_IMAGECATWEIGHT', 'Reihenfolge');
define('_MD_IMAGECATOPTIONS', 'Optionen');
define('_MD_IMAGECATQTDE', '# Bilder');
define('_IMAGEFILTERS', 'Wählen Sie einen Filter:');
define('_IMAGEAPPLYFILTERS', 'Filter im Bild übernehmen');
define('_IMAGEFILTERSSAVE', 'Original Bild überschreiben?');
define('_IMGCROP', 'Zuschneiden Werkzeug');
define('_IMGFILTER', 'Filter Werkzeug');

define('_MD_IMAGECATSUBS', 'Unterkategorien');

define('_WIDTH', 'Breite');
define('_HEIGHT', 'Höhe');
define('_DIMENSION', 'Abmessungen');
define('_CROPTOOL', 'Schnittinspektor');
define('_IMGDETAILS', 'Bild Details');
define('_INSTRUCTIONS', 'Anleitung');
define('_INSTRUCTIONS_DSC', 'Um den Zuschneidebereich auszuwählen, ziehen und verschieben Sie das gepunktete Rechteck oder geben Sie Werte direkt in das Formular ein. ');

define('_MD_IMAGE_EDITORTITLE', 'Online-Bildbearbeitung');
define('_MD_IMAGE_VIEWSUBS', 'Unterkategorien anzeigen');
define('_MD_IMAGE_COPYOF', 'Kopie von ');

define('IMANAGER_FILE', 'Datei');
define('IMANAGER_WIDTH', 'Breite');
define('IMANAGER_HEIGHT', 'Höhe');
define('IMANAGER_SIZE', 'Größe');
define('IMANAGER_ORIGINAL', 'Original Bild');
define('IMANAGER_EDITED', 'Bild bearbeitet');
define('IMANAGER_FOLDER_NOT_WRITABLE', 'Ordner ist vom Server nicht beschreibbar.');

// added in 1.3
define('IMANAGER_NOPERM', 'Sie sind nicht berechtigt, auf diesen Bereich zuzugreifen!');