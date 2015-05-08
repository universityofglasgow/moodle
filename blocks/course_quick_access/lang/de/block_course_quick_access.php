<?php
// This file is part of course-quick-access plugin for Moodle
// http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * English language strings for the quick-course-block.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Kurs-Schnellzugriff';

// ===== Block ====
$string['usernotenrolledincourses'] = 'In keine Kurse eingeschrieben';
$string['coursenotcategorized'] = 'Nicht zugewiesene Kurse';

// ===== Block Footer =====
$string['allmoodlecourses'] = 'Alle Kurse';
$string['allmycourses'] = 'Alle meine Kurse';
$string['configureplugin'] = 'Schnellzugriff konfigurieren';

// ===== Admin Configuration =====
$string['usejavascript'] = 'Javascript verwenden';
$string['descusejavascript'] = 
	'Aktiviert/Deaktiviert die Javascript Funktionalitäten für die Benutzer';
$string['showsearchsubmitbutton'] = 'Such-Submit Schaltfläche anzeigen';
$string['descshowsearchsubmitbutton'] = 
	'Zeigt/Verbirgt die Such-Submit Schaltfläche.';
$string['searchsubmitbutton2line'] = 
	'Such-Submit-Button in die zweite Zeile verlegen';
$string['descsearchsubmitbutton2line'] = 
	'Zeigt die Such-Submit-Schaltfläche erst in der zweiten Zeile an';

// ===== User Configuration =====
$string['configpagetitle'] = 'Kurs-Schnellzugriff konfigurieren';
$string['configpageheading'] = 'Kurs-Schnellzugriff Konfiguration';
$string['confignavigationtitle'] = 'Kurs-Schnellzugriff konfigurieren';

// -- New/Edit Category Form --
$string['createnewcategory'] = 'Neue Kategorie erstellen';
$string['createoreditcategory'] = 'Neue Kategorie erstellen/editieren';
$string['nospecialchars'] = 
	'An dieser Stelle sind nur Buchstaben, Zahlen und die folgenden '.
	'Sonderzeichen: "/", "-" erlaubt';

// -- Delete Category Form --
$string['deletecategoryheader'] = 'Kategorie löschen';
$string['deletecategorymessage'] = 
	'Möchten Soe die folgende Kategorie wirklich löschen?';
$string['deletecategorysubmit'] = 'Kategorie löschen';

// -- Category List Form --
$string['categorylistheadline'] = 'Benutzer-Kategorien';
$string['nocatcreatedtext'] = 
	'Sie haben noch keine Kategorien erstellt. '.
	'Um den Kurs-Schnellzugriff zu konfigurieren, '.
	'nutzen Sie den nachfolgenden Link '.
	'<b>Neue Kategorie erstellen</b>.';
$string['createmorecattext'] = 
	'Um weitere Kategorien anzulegen können Sie den nachfolgenden '.
	'<b>Neue Kategorie erstellen</b> Link nutzen.';
$string['categorylistdesc'] = 
	'Nachfolgend sind alle Ihre eigenen Kategorien aufgelistet. '.
	'Die Kategorien lassen sich über die entsprechenden <b>Symbole</b> '.
	'umbenennen, verstecken, löschen und neu anordenen. '.
	'Versteckte Kategorien oder Kategorien, denen keine Kurse zugeordnet '.
	'wurden, werden nicht in der Schnellzugriffs-Liste angezeigt.';
$string['categorynameheadline'] = 'Name der Kategorie';
$string['courseamountheadline'] = 'Zugewiesene Kurse';
$string['categoryeditheadline'] = 'Editieren';
$string['categorymoveheadline'] = 'Anordnen';
$string['cateditlinktext'] = 'Umbenennen';
$string['catdeletelinktext'] = 'Löschen';
$string['cathidelinktext'] = 'Verstecken';
$string['catshowlinktext'] = 'Anzeigen';
$string['catmoveup'] = 'Eins nach oben';
$string['catmovedown'] = 'Eins nach unten';

// -- Course List Form --
$string['courselistheadline'] = 'Kategorie-Kurs Zuweisungen';
$string['nocourses'] = 'Sie sind derzeit in keinen Kurs eingeschrieben.';
$string['courselistdesc'] = 
	'Alle Kurse, in die Sie eingeschrieben sind, werden nachfolgend '.
	'aufgeführt. Nutzen Sie die Auswahlliste, '.
	'um diese Kategorien zuzuweisen. '.
	'Klicken Sie zum Abschluss auf die Schaltfläche <b>Änderungen speichern</b>, '.
	'um Ihre Einstellungen zu übernehmen.';
$string['courselistcoursehead'] = '<b>Kurse</b>';
$string['courselistselecthead'] = '<b>Zugewiesene Kategorie</b>';
$string['newcategory'] = 'Name der Kategorie';
$string['categoriyisvisible'] = 'Ist sichtbar';

// ===== Javascript =====
$string['hiddencoursesnotloggedin'] = 'Es werden nur Kurs-Kategorien angezeigt, '.
	'die mit der Sucheanfrage übereinstimmen, drücken Sie "Enter" um über '.
	'alle Moodle-Kurse zu suchen.';
$string['hiddencourses'] = 'Es werden nur diejenigen Ihrer Kurse angezeigt, '.
	'die mit der Sucheanfrage übereinstimmen, drücken Sie "Enter" um über '.
	'alle Kurse zu suchen.';
