<?php
// This file is part of Moodle - http://moodle.org/
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
 * Quiz Filedownloader report version information.
 *
 * @package   quiz_filedownloader
 * @copyright 2019 ETH Zurich
 * @author    Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['adminsetting_accepted_qtypes'] = 'Akzeptierte Fragetypen';
$string['adminsetting_accepted_qtypes_help'] = 'Bestimmt, welche Fragetypen beim Download mit einbezogen werden sollen.';
$string['adminsetting_accepted_qtypefileareas'] = 'Fileareas';
$string['adminsetting_accepted_qtypefileareas_help'] = 'Jedem angegebenem Fragetyp muss sein Filearea-Wert zugeordnet werden.';
$string['adminsetting_anonymizedownload'] = 'Download anonymisieren';
$string['adminsetting_anonymizedownload_help'] = 'Anonymisiert die im Download enthaltenen Benutzerinformationen.';
$string['adminsetting_choosefilestructure'] = 'Verzeichnisstruktur wählbar';
$string['adminsetting_choosefilestructure_help'] = 'Lehrer können sich beim Download entscheiden, alle abgegebenen Dateien in ein Verzeichnis zu speichern, anstatt für jede Datei einen separaten Ordner anzulegen. Beachte: Falls die Dateien in einen Ordner gespeichert werden, ändert sich der jeweilige Dateiname.';
$string['adminsetting_chooseanonymize'] = 'Anonymisierung wählbar';
$string['adminsetting_chooseanonymize_help'] = 'Lehrer können sich beim Download entscheiden, ob heruntergeladene Benutzerdaten anonymisiert werden sollen.';
$string['download'] = 'Herunterladen';
$string['downloadsettings'] = 'Downloadeinstellungen';
$string['eventupdate_log'] = 'Quiz Dateiabgaben wurden heruntergeladen';
$string['filedownloader'] = 'Dateiabgaben herunterladen';
$string['filedownloaderreport'] = 'Quiz Filedownloader';
$string['no'] = 'Nein';
$string['pluginname'] = 'Quiz Filedownloader';
$string['plugindescription'] = 'Lädt die im Test abgegebenen Dateien herunter.<br>';
$string['privacy:metadata'] = 'Das Filedownloader Plugin speichert keine persönlichen Daten.';
$string['response_invalidfilearea'] = 'Für folgenden Fragetyp wurde in den Plugin-Voreinstellungen ein fehlerhafter filearea-Wert angegeben:<br>';
$string['response_noattempts'] = 'In diesem Test sind keine auswertbaren Versuche vorhanden.';
$string['response_noconfigfileareas'] = 'Die in den Plugin-Voreinstellungen angegebene Anzahl der Filearea-Werte stimmt nicht mit der Anzahl der angegebenen Fragetypen überein.<br> Jedem Fragetyp muss ein filearea-Wert zugeordnet werden.';
$string['response_noconfigqtypes'] = 'Entweder wurden in den Plugin-Voreinstellungen keine Fragetypen angegeben oder das Quiz beinhaltet keinen der angegebenen Fragetypen.';
$string['response_nofilearea'] = 'Für folgenden Fragetyp wurde in den Plugin-Voreinstellungen kein filearea-Wert angegeben:<br>';
$string['response_nofiles'] = 'Es wurden keine Dateien heruntergeladen.';
$string['response_noquestions'] = 'Der Test beinhaltet keine Fragen.';
$string['response_nosuchqtype'] = 'Folgende Fragetypen sind auf dem System nicht installiert oder deaktiviert und werden beim Herunterladen nicht einbezogen:<br>';
$string['texfile_anonymized'] = '-anonymisiert- ';
$string['textfile_notavailable'] = '-nicht verfügbar-';
$string['yes'] = 'Ja';
$string['zip_inonefolder'] = 'Dateien in einen einzigen Ordner pro Frage herunterladen';
$string['zip_inonefolder_help'] = 'Abgegebene Dateien werden zusammengefasst in je einem Ordner pro gestellter Frage gespeichert.<br>Es werden keine zusätzlichen Unterordner für Studenten und Versuche angelegt.<br><b>(nicht empfohlen für summative Prüfungen)</b>';
