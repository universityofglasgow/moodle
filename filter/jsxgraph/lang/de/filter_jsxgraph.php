<?php
// This file is part of JSXGraph Moodle Filter.
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
 * This is a plugin to enable function plotting and dynamic geometry constructions with JSXGraph within a Moodle platform.
 *
 * JSXGraph is a cross-browser JavaScript library for interactive geometry,
 * function plotting, charting, and data visualization in the web browser.
 * JSXGraph is implemented in pure JavaScript and does not rely on any other
 * library. Special care has been taken to optimize the performance.
 *
 * @package    filter_jsxgraph
 * @copyright  2023 JSXGraph team - Center for Mobile Learning with Digital Technology – Universität Bayreuth
 *             Matthias Ehmann,
 *             Michael Gerhaeuser,
 *             Carsten Miller,
 *             Andreas Walter <andreas.walter@uni-bayreuth.de>,
 *             Alfred Wassermann <alfred.wassermann@uni-bayreuth.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['filtername'] = 'JSXGraph';

$string['yes'] = 'ja';
$string['no'] = 'nein';

$string['on'] = 'aktiviert';
$string['off'] = 'deaktiviert';

$string['error'] = 'FEHLER:';
$string['error0.99.5'] = 'Leider wird die Core-Version 0.99.5 aufgrund eines CDN-Fehlers vom JSXGraph-Filter nicht unterstützt. Bitte kontaktieren Sie Ihren Administrator.';
$string['error0.99.6'] = 'Leider wird die Core-Version 0.99.6 vom JSXGraph-Filter nicht unterstützt. Bitte kontaktieren Sie Ihren Administrator.';

$string['header_docs'] = 'Allgemeine Informationen';
$string['docs'] = 'Vielen Dank, dass sie den JSXGraph-Filter benutzen. Für aktuelle Informationen über JSXGraph besuchen Sie einfach unsere <a href="http://jsxgraph.uni-bayreuth.de/" target="_blank">Homepage</a>.<br>Beachten Sie unsere <a href="https://github.com/jsxgraph/moodle-filter_jsxgraph/blob/master/README.md" target="_blank">detaillierte Filter-Dokumentation auf GitHub</a>.<br>Informationen über die Verwendung von JSXGraph finden sie <a href="http://jsxgraph.uni-bayreuth.de/wp/docs/index.html" target="_blank">in den docs</a>.<br><br>Nehmen Sie auf dieser Seite <b>globale Einstellungen</b> für den Filter vor. Einige davon lassen sich in Tag-Attributen lokal überschreiben. Siehe hierzu die <a href="https://github.com/jsxgraph/moodle-filter_jsxgraph/blob/master/README.md#jsxgraph-tag-attributes" target="_blank">Dokumentation.</a>';
$string['header_versions'] = 'Versionsinformationen';
$string['filterversion'] = 'Sie benutzen derzeit die folgende <b>Version des JSXGraph-Filters</b> für Moodle:';
$string['recommendedversion_pre'] = 'Es wird empfohlen, <b>JSXGraph ';
$string['recommendedversion_post'] = '</b> zu verwenden (oder "<code>automatisch</code>").';

$string['header_jsxversion'] = 'Version der verwendeten JSXGraph-Bibliothek';
$string['header_libs'] = 'Erweiterungen für den JSXGraph-Filter';
$string['header_codingbetweentags'] = 'Codierung zwischen den Tags';
$string['header_globaljs'] = 'Globales JavaScript';
$string['header_dimensions'] = 'Standard-Dimensionen';
$string['header_deprecated'] = 'Veraltete Einstellungen';

$string['versionJSXGraph'] = 'JSXGraph-Version';
$string['versionJSXGraph_desc'] = 'Wähle hier, welche JSXGraph-Version genutz werden soll. Achtung: Für Responsivität wird mindestens Version 1.3.2+ benötigt.';
$string['versionJSXGraph_auto'] = 'aktuellste mitgelieferte Version (automatisch)';

$string['formulasextension'] = 'Fragetyp formulas';
$string['formulasextension_desc'] = 'Ist diese Option aktiviert, wird eine weitere JavaScript Bibliothek geladen, mit deren Hilfe ein JSXGraph-Board in einer Frage des Typs "formulas" verwendet werden kann. (Hierzu muss dieser Fragetyp installiert sein!)<br>Eine Dokumentation der Erweiterung findet sich im <a href="https://github.com/jsxgraph/moodleformulas_jsxgraph" target="_blank">zugehörigen Repository bei GitHub</a>.';

$string['HTMLentities'] = 'HTMLentities';
$string['HTMLentities_desc'] = 'Einstellung, ob HTMLentities wie z.B. "&", "<",... innerhalb des JavaScript-Codes für JSXGraph unterstützt werden.';

$string['convertencoding'] = 'Konvertiere Text-Codierung';
$string['convertencoding_desc'] = 'Einstellung, ob die Codierung des Texts zwischen den JSXGraph-Tags in UTF-8 konvertiert werden soll oder nicht.';

$string['globalJS'] = 'Globales JavaScript';
$string['globalJS_desc'] = 'Definieren Sie hier einen allgemein gültigen JavaScript-Code, der in jedem JSXGraph-Tag vor dem darin enthalteten Code geladen wird. Um Sonderzeichen wie beispielsweise "<" zu nutzen, verwenden Sie die entsprechende Methode <code>JXG.Math.lt(...)</code>.';

$string['dimensions'] =
    '<p>Hier können Sie die Standard-Dimensionen für Ihre Boards definieren. Bitte beachten Sie, dass lokale Tag-Attribute nur Teile der hier definierten Werte überschreiben und es dadurch zu unvorhergesehenen Überschneidungen kommen kann. Benutzen Sie diese Einstellungen deshalb mit Bedacht!</p>' .
    '<p><b>Um die Responsivität von Boards nutzen zu können, dürfen nicht Höhe und Breite gleichzeitig angegeben werden. Stattdessen sollten Sie <code>width</code> und <code>aspect-ratio</code> verwenden,</b> denn bei gegebener Höhe und Breite wird das Seitenverhältnis ignoriert.</p>' .
    '<p>Für mehr Informationen und verschiedene Anwendungsfälle nutzen Sie bitte die <a href="https://github.com/jsxgraph/moodle-filter_jsxgraph#dimensions" target="_blank">Dokumentation des Filters</a>.</p>';

$string['aspectratio'] = 'Seitenverhältnis';
$string['aspectratio_desc'] = 'Format z.B. <code>1 / 1</code>';

$string['fixwidth'] = 'Breite';
$string['fixwidth_desc'] = 'Wir empfehlen, hier einen relativen Wert zu verwenden, z.B. <code>100%.</code>';

$string['fixheight'] = 'Höhe';
$string['fixheight_desc'] = 'Wir empfehlen, dieses Feld leer zu lassen und stattdessen <a href="#admin-aspectratio">Seitenverhältnis</a> und <a href="#admin-width">Breite</a> zu verwenden.';

$string['maxwidth'] = 'Maximale Breite';
$string['maxwidth_desc'] = '';

$string['maxheight'] = 'Maximale Höhe';
$string['maxheight_desc'] = '';

$string['fallbackaspectratio'] = 'Fallback-Seitenverhältnis';
$string['fallbackaspectratio_desc'] = 'Siehe Beschreibung der Standard-Dimensionen.';

$string['fallbackwidth'] = 'Fallback-Breite';
$string['fallbackwidth_desc'] = 'Siehe Beschreibung der Standard-Dimensionen.';

$string['usedivid'] = 'Benutze div-Präfix';
$string['usedivid_desc'] =
    'Für bessere Kompatibilität sollten Sie hier "Nein" wählen. Dadurch werden die IDs nicht mit dem Präfix aus "<a href="#admin-divid">divid</a>" und einer Nummer versehen, sondern mit einer eindeutigen ID. <br>Verwenden Sie noch alte Konstruktionen, sollten Sie "Ja" auswählen. Dann wird die veraltete Einstellung "<a href="#admin-divid">divid</a>" weiter verwendet.';

$string['divid'] = 'Festes Board-ID-Präfix';
$string['divid_desc'] =
    '<b>Veraltet! Sie sollten von nun an die Konstante "<code>BOARDID</code>" innerhalb des <jsxgraph\>-Tags benutzen.</b><br>' .
    '<small>Jedes <code>div</code>, das ein JSXGraph-Board enthält, benötigt eine eindeutige ID auf der Seite. Wird diese ID im JSXGraph-Tag angegeben (siehe <a href="https://github.com/jsxgraph/moodle-filter_jsxgraph/blob/master/README.md#jsxgraph-tag-attributes" target="_blank">Dokumentation</a>), so gilt sie für das komplette enthaltene JavaScript.<br>' .
    'Ist im Tag keine Board-ID angegeben, wird diese automatisch erzeugt. Hierzu wird das hier angegebene Präfix verwendet und um eine fortlaufende Nummer pro Seite ergänzt, z.B. box0, box1,...<br>' .
    'Der Benutzer braucht die ID nicht zu kennen. Sie kann in jedem Fall innerhalb des JavaScript über die Konstante "<code>BOARDID</code>" referenziert werden.</small>';

$string['privacy'] = 'Dieses Plugin dient lediglich dazu, JSXGraph-Konstruktionen, die mithilfe des jsxgraph-Tags im Editor eingegeben werden, anzuzeigen. Es speichert und übermittelt selbst keine personenbezonenen Daten. Die eventuell extern eingebundene Bibliothek jsxgraphcore.js verarbeitet ebenfalls keinerlei personenbezogene Daten.';
