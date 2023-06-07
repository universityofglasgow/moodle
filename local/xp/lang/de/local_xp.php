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
 * Language file.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitycompleted'] = 'Aktivität abgeschlossen';
$string['afterimport'] = 'Nach dem Import';
$string['anonymousgroup'] = 'Eine andere Gruppe';
$string['anonymousiomadcompany'] = 'Eine andere Firma';
$string['anonymousiomaddepartment'] = 'Eine andere Abteilung';
$string['badgetheme'] = 'Design für das Erscheinungsbild der Abzeichen';
$string['badgetheme_help'] = 'Wählen Sie das Design für das Erscheinungsbild Ihrer Abzeichen.';
$string['categoryn'] = 'Kategorie: {$a}';
$string['clicktoselectcourse'] = 'Klicken Sie, um einen Kurs auszuwählen';
$string['clicktoselectgradeitem'] = 'Klicken Sie, um eine Bewertung auszuwählen';
$string['courseselector'] = 'Auswahl der Kurse';
$string['csvfieldseparator'] = 'Feldtrenner für CSV';
$string['csvfile'] = 'CSV-Datei';
$string['csvfile_help'] = 'Die CSV-Datei muss die Spalten __user__ und __points__ enthalten. Die Spalte __message__ ist optional und kann verwendet werden, wenn Benachrichtigungen aktiviert sind. Beachten Sie, dass die Spalte __user__ Benutzer-IDs, E-Mail-Adressen und Benutzernamen versteht.';
$string['csvisempty'] = 'Die CSV-Datei ist leer.';
$string['csvline'] = 'Zeile';
$string['csvmissingcolumns'] = 'In der CSV fehlen folgende Spalte(n): {$a}';
$string['currencysign'] = 'Punktesymbol';
$string['currencysign_help'] = 'Mit dieser Einstellung können Sie die Bedeutung der Punkte ändern. Sie wird neben der Anzahl der Punkte angezeigt, die Teilnehmer/innen als Ersatz für die Referenz auf __Erfahrungspunkte__ hat. Zum Beispiel kann man das Bild einer Karotte hochladen, damit die Teilnehmer/innen mit Karotten für ihre Aktionen belohnt werden.';
$string['currencysignformhelp'] = 'Das hier hochgeladene Bild wird neben den Punkten als Ersatz für die Referenz auf Erfahrungspunkte angezeigt. Die empfohlene Bildhöhe beträgt 18 Pixel.';
$string['currentpoints'] = 'Aktuelle Punkte';
$string['displaygroupidentity'] = 'Gruppenidentität anzeigen';
$string['enablecheatguard'] = 'Schummelwächter aktivieren';
$string['enablecheatguard_help'] = 'Der Schummelwächter verhindert, dass Teilnehmer/innen belohnt werden, sobald sie bestimmte Grenzen erreichen. Der Betrugsschutz verhindert, dass Teilnehmer/innen belohnt werden, sobald sie bestimmte Grenzen erreichen. [Mehr Infos finden Sie in der _Level up!_ Dokumentation.](https://levelup.plus/docs/article/level-up-cheat-guard?ref=localxp_help)';
$string['enablegroupladder'] = 'Aktiviere Gruppenrangliste';
$string['enablegroupladder_help'] = 'Wenn diese Funktion aktiviert ist, bekommen Teilnehmer/innen eine Rangliste der Kursgruppen angezeigt. Die Gruppenpunkte werden aus den Punkten berechnet, die die Mitglieder der jeweiligen Gruppe gesammelt haben. Dies gilt derzeit nur, wenn das Plugin pro Kurs verwendet wird, und nicht für die gesamte Website.';
$string['errorunknowncourse'] = 'Fehler: unbekannter Kurs';
$string['errorunknowngradeitem'] = 'Fehler: unbekannte Bewertung';
$string['filtergradeitems'] = 'Bewertungen filtern';
$string['for2weeks'] = 'Für 2 Wochen';
$string['for3months'] = 'Für 3 Monate';
$string['gradeitemselector'] = 'Bewertung auswählen';
$string['gradeitemtypeis'] = 'Die Bewertung ist eine {$a} Bewertung';
$string['gradereceived'] = 'Erhaltene Note';
$string['gradesrules'] = 'Bewertungs-Regeln';
$string['gradesrules_help'] = 'Die folgenden Regeln legen fest, wann Teilnehmer/innen Punkte für ihre Noten erhalten.

Teilnehmer/innen erhalten so viele Punkte wie ihre Note. Für eine Note von 5/10 und eine Note von 5/100 erhalten Teilnehmer/innen jeweils 5 Punkte. Wenn sich die Note mehrmals ändert, bekommen Teilnehmer/innen so viele Punkte, wie sie maximal erhalten haben. Den Teilnehmer/innen werden niemals Punkte abgezogen, und negative Noten werden ignoriert.

Beispiel: Alice reicht eine Aufgabe ein und erhält die Note 40/100. In _Level Up XP_ erhält Alice 40 Punkte für ihre Note. Alice versucht die Aufgabe erneut, aber dieses Mal wird ihre Note auf 25/100 gesenkt. Die Punkte von Alice in _Level up!_ ändern sich nicht. Für ihren letzten Versuch erzielt Alice 60/100 Punkte, sie erhält 20 zusätzliche Punkte in _Level Up XP_, ihre Gesamtpunktzahl beträgt 60.

[Mehr Infos finden Sie in der _Level Up XP_ Dokumentation.](https://levelup.plus/docs/article/grade-based-rewards?ref=localxp_help)';
$string['groupanonymity'] = 'Anonymität';
$string['groupanonymity_help'] = 'Diese Einstellung steuert, ob Teilnehmer/innen die Namen der Gruppen sehen können, denen sie nicht angehören.';
$string['groupladder'] = 'Gruppenrangliste';
$string['groupladdercols'] = 'Spalten für Rangliste';
$string['groupladdercols_help'] = 'Diese Einstellung bestimmt, welche Spalten neben den Rängen und Namen der Gruppen angezeigt werden.

Die Spalte __Punkte__ zeigt die Punkte der Gruppen an. Je nach gewählter _Ranglistenstrategie_ kann dieser Wert kompensiert worden sein.

Die Spalte __Fortschritt__ zeigt den Gesamtfortschritt der Gruppe an, bis alle Mitglieder die höchste Stufe erreicht haben. Mit anderen Worten: Der Fortschritt kann nur dann 100 % erreichen, wenn alle Gruppenmitglieder die maximale Stufe erreicht haben. Beachten Sie, dass die Anzahl der verbleibenden Punkte, die neben dem Fortschrittsbalken angezeigt wird, verwirrend sein kann, wenn die Gruppen unausgeglichen sind und die Punkte nicht ausgeglichen werden, da Gruppen mit mehr Mitgliedern mehr verbleibende Punkte haben als andere, auch wenn ihre Fortschritte ähnlich sein können.

Drücken Sie die STRG- oder CMD-Taste, während Sie klicken, um mehr als eine Spalte auszuwählen oder um die Auswahl einer ausgewählten Spalte aufzuheben.';
$string['groupladdersource'] = 'Teilnehmer/innen gruppieren nach';
$string['groupladdersource_help'] = 'Die Gruppenrangliste zeigt eine Rangliste mit der Summe der Punkte der Teilnehmer/innen an. Der von Ihnen gewählte Wert bestimmt, was _Level up!_ verwendet, um die Teilnehmer/innen zusammenzufassen. Wenn Sie den Wert _Gruppenrangliste deaktiviert_ einstellen, ist die Gruppenrangliste nicht verfügbar. Um die _Kursgruppen_, die in der Rangliste erscheinen, einzuschränken, können Sie eine neue Gruppierung erstellen, die die relevanten Gruppen enthält, und diese Gruppierung dann in den Kurseinstellungen als _Standardgruppierung_ festlegen.';
$string['groupname'] = 'Gruppenname';
$string['grouporderby'] = 'Bewertungsstrategie';
$string['grouporderby_help'] = 'Legt fest, was die Grundlage für die Gruppenrangliste ist. Bei der Einstellung __Punkte__ werden die Gruppen anhand der Summe der Punkte ihrer Mitglieder gereiht.

Bei der Einstellung __Punkte (mit Ausgleich)__ werden die Punkte von Gruppen mit weniger Mitgliedern als andere anhand des Durchschnitts pro Mitglied ihrer Gruppe kompensiert. Wenn einer Gruppe z. B. drei Mitglieder fehlen, erhält sie Punkte in Höhe des Dreifachen ihres Durchschnitts pro Mitglied. Dadurch wird eine ausgewogene Rangliste erstellt, in der alle Gruppen die gleichen Chancen haben.

Bei der Einstellung __Fortschritt__ werden die Gruppen auf der Grundlage ihres Gesamtfortschritts in Richtung des Erreichens der Endstufe durch alle Mitglieder gereiht, ohne dass ihre Punkte ausgeglichen werden.

Sie können __Fortschritt__ verwenden, wenn die Gruppen unausgeglichen sind, z. B. wenn einige Gruppen viel mehr Mitglieder haben als andere.';
$string['grouppoints'] = 'Punkte';
$string['grouppointswithcompensation'] = 'Punkte (mit Ausgleich)';
$string['groupsourcecohorts'] = 'globale Gruppen';
$string['groupsourcecoursegroups'] = 'Kursgruppen';
$string['groupsourceiomadcompanies'] = 'IOMAD Firmen';
$string['groupsourceiomaddepartments'] = 'IOMAD Abteilungen';
$string['groupsourcenone'] = 'Gruppenrangliste deaktiviert';
$string['hidegroupidentity'] = 'Gruppenidentität verbergen';
$string['importcsvintro'] = 'Verwenden Sie das folgende Formular, um Punkte aus einer CSV-Datei zu importieren. Der Import kann verwendet werden, um die Punkte der Teilnehmer/innen zu _erhöhen_ oder sie mit dem bereitgestellten Wert zu überschreiben. Beachten Sie, dass der Import __nicht__ dasselbe Format wie der exportierte Bericht verwendet. Das erforderliche Format ist in der [Dokumentation]({$a->docsurl}) beschrieben, zusätzlich ist eine Beispieldatei [hier]({$a->sampleurl}) verfügbar.';
$string['importpoints'] = 'Punkte importieren';
$string['importpointsaction'] = 'Punkte importieren';
$string['importpointsaction_help'] = 'Legt fest, was mit den in der CSV-Datei gefundenen Punkten geschehen soll.

**Set as total**

Die Punkte überschreiben die aktuellen Punkte der Teilnehmer/innen und machen sie zu ihrer neuen Gesamtzahl. Teilnehmer/innen werden nicht benachrichtigt, und es gibt keine Einträge in den Protokollen.

**Increase**

Die Punkte stellen die Anzahl der Punkte dar, die Teilnehmer/innen zuerkannt werden. Wenn aktiviert, wird eine Benachrichtigung mit der optionalen _Nachricht_ aus der CSV-Datei an die Empfänger gesendet. Außerdem wird den Protokollen ein _Manuelle Vergabe_-Eintrag hinzugefügt.';
$string['importpreview'] = 'Import-Vorschau';
$string['importpreviewintro'] = 'Hier ist eine Vorschau, die die ersten {$a} Datensätze von allen zu importierenden Datensätzen zeigt. Bitte überprüfen und bestätigen Sie, wenn Sie bereit sind, alles zu importieren.';
$string['importresults'] = 'Import-Ergebnisse';
$string['importresultsintro'] = '{$a->successful} Einträge von insgesamt {$a->total} erfolgreich importiert. Wenn einige Einträge nicht importiert werden konnten, werden Details unten angezeigt.';
$string['importsettings'] = 'Import-Einstellungen';
$string['increaseby'] = 'Erhöhe um';
$string['increaseby_help'] = 'Die Anzahl der Punkte, die Teilnehmer/innen erhalten sollen.';
$string['increasemsg'] = 'Optionale Nachricht';
$string['increasemsg_help'] = 'Ist eine Nachricht hinterlegt, wird sie der Benachrichtigung hinzugefügt.';
$string['invalidpointscannotbenegative'] = 'Negative Punkte sind nicht möglich.';
$string['keeplogsdesc'] = 'Die Logs spielen eine wichtige Rolle im Plugin. Sie werden für den Schummelwächter, für das Auffinden der letzten Belohnungen und für einige andere Dinge verwendet. Die Verkürzung der Aufbewahrungszeit der Protokolle kann die Verteilung der Punkte über die Zeit beeinflussen und sollte sorgfältig behandelt werden.';
$string['levelbadges'] = 'Erscheinungsbild der Abzeichen überschreiben';
$string['levelbadges_help'] = 'Hier können Sie ein eigenes Design für das Erscheinungsbild Ihrer Abzeichen hochladen.';
$string['manualawardnotification'] = 'Ihnen wurden {$a->points} Punkte von {$a->fullname} zugewiesen.';
$string['manualawardnotificationwithcourse'] = 'Ihnen wurden {$a->points} Punkte von {$a->fullname} im Kurs {$a->coursename} zugewiesen.';
$string['manualawardsubject'] = 'Sie haben {$a->points} erhalten!';
$string['manuallyawarded'] = 'Manuell zugewiesen';
$string['maxpointspertime'] = 'Max. Punkte im Zeitraum';
$string['maxpointspertime_help'] = 'Die maximale Anzahl von Punkten, die während des angegebenen Zeitraums gesammelt werden können. Wenn dieser Wert leer ist oder gleich Null ist, gilt er nicht.';
$string['messageprovider:manualaward'] = 'Level up! Punkte manuell zugewiesen';
$string['missingpermssionsmessage'] = 'Sie haben nicht die erforderlichen Berechtigungen, um auf diesen Inhalt zuzugreifen.';
$string['mylevel'] = 'Mein Level';
$string['navgroupladder'] = 'Gruppenrangliste';
$string['points'] = 'Punkte';
$string['previewmore'] = 'Mehr anzeigen';
$string['privacy:metadata:log'] = 'Speichert ein Protokoll der Ereignisse';
$string['privacy:metadata:log:points'] = 'Die für das Ereignis vergebenen Punkte';
$string['privacy:metadata:log:signature'] = 'Einige Ereignisdaten';
$string['privacy:metadata:log:time'] = 'Das Datum, an dem es passiert ist';
$string['privacy:metadata:log:type'] = 'Der Ereignis-Typ';
$string['privacy:metadata:log:userid'] = 'Wer die Punkte erhalten hat';
$string['progressbarmode'] = 'Zeige Fortschritt bis:';
$string['progressbarmode_help'] = 'Bei Einstellung auf _Das nächste Level_ zeigt der Fortschrittsbalken den Fortschritt auf dem Weg zum nächsten Level an.

Bei Einstellung auf _Das höchste Level_ zeigt der Fortschrittsbalken den Prozentsatz des Fortschritts zum allerletzten Level an, den Teilnehmer/innen erreichen können.

In beiden Fällen bleibt der Fortschrittsbalken voll, wenn das letzte Level erreicht ist.';
$string['progressbarmodelevel'] = 'Das nächste Level';
$string['progressbarmodeoverall'] = 'Das höchste Level';
$string['ruleactivitycompletion'] = 'Aktivitätsabschluss';
$string['ruleactivitycompletion_help'] = 'Diese Bedingung ist erfüllt, wenn eine Aktivität gerade als abgeschlossen markiert wurde, solange die Beendigung nicht als fehlgeschlagen markiert wurde.

Gemäß den Standardeinstellungen für die Fertigstellung von Moodle-Aktivitäten haben Trainer/innen die volle Kontrolle über die Bedingungen, die erforderlich sind, um eine Aktivität zu _vervollständigen_. Diese können für jede Aktivität im Kurs individuell eingestellt werden und basieren auf einem Datum, einer Note, etc. Es ist auch möglich, dass Teilnehmer/innen die Aktivitäten manuell als erledigt markieren.

Diese Bedingung wird Teilnehmer/innen nur einmal belohnen.';
$string['ruleactivitycompletiondesc'] = 'Eine Aktivität oder ein Inhalt wurde erfolgreich abgeschlossen.';
$string['ruleactivitycompletioninfo'] = 'Diese Bedingung trifft zu, wenn Teilnehmer/innen eine Aktivität oder ein Material abschließen.';
$string['rulecmname'] = 'Aktivitätsname';
$string['rulecmname_help'] = 'Diese Bedingung ist erfüllt, wenn das Ereignis in einer Aktivität auftritt, die wie angegeben benannt ist.

Hinweise:

- Beim Vergleich wird die Groß-/Kleinschreibung nicht beachtet.
- Ein leerer Wert wird niemals übereinstimmen.
- Erwägen Sie die Verwendung von **contains**, wenn der Name der Aktivität [multilang](https://docs.moodle.org/en/Multi-language_content_filter)-Tags enthält.';
$string['rulecmnamedesc'] = 'Der Aktivitätsname {$a->compare} \'{$a->value}\'.';
$string['rulecmnameinfo'] = 'Gibt den Namen der Aktivitäten oder Materialien an, in denen die Aktion stattfinden muss.';
$string['rulecourse'] = 'Kurs';
$string['rulecourse_help'] = 'Diese Bedingung ist erfüllt, wenn das Ereignis im angegebenen Kurs auftritt.

Sie ist nur verfügbar, wenn das Plugin für die gesamte Website verwendet wird. Wenn das Plugin pro Kurs verwendet wird, wird diese Bedingung unwirksam.';
$string['rulecoursecompletion'] = 'Kursabschluss';
$string['rulecoursecompletion_help'] = 'Diese Regel wird erfüllt, wenn ein Kurs von Teilnehmer/innen absolviert wird.

__Hinweis:__ Die Teilnehmer/innen erhalten ihre Punkte nicht sofort, es dauert eine Weile, bis Moodle die Kursabschlüsse bearbeitet hat.';
$string['rulecoursecompletioncoursemodedesc'] = 'Der Kurs wurde abgeschlossen';
$string['rulecoursecompletiondesc'] = 'Ein Kurs wurde abgeschlossen';
$string['rulecoursecompletioninfo'] = 'Diese Regel wird erfüllt, wenn ein Kurs von Teilnehmer/innen abgeschlossen wird.';
$string['rulecoursedesc'] = 'Der Kurs ist: {$a}';
$string['rulecourseinfo'] = 'Diese Bedingung erfordert es, dass das Ereignis im angegebenen Kurs auftritt.';
$string['rulegradeitem'] = 'Spezifisches Bewertungsobjekt';
$string['rulegradeitem_help'] = 'Diese Bedingung ist erfüllt, wenn eine Note für das angegebene Notenelement vergeben wird.';
$string['rulegradeitemdesc'] = 'Das Bewertungsobjekt ist \'{$a->gradeitemname}\'';
$string['rulegradeitemdescwithcourse'] = 'Das Bewertungsobjekt ist: \'{$a->gradeitemname}\' in \'{$a->coursename}\'';
$string['rulegradeiteminfo'] = 'Diese Bedingung ist erfüllt, wenn das Notenelement von einem bestimmten Typ ist.';
$string['rulegradeitemtype'] = 'Bewertungstyp';
$string['rulegradeitemtype_help'] = 'Diese Bedingung ist erfüllt, wenn das Notenelement vom gewünschten Typ ist. Wenn ein Aktivitätstyp ausgewählt ist, würde jede Note, die von diesem Aktivitätstyp stammt, passen.';
$string['rulegradeitemtypedesc'] = 'Die Bewertung ist eine \'{$a}\' Bewertung';
$string['rulegradeitemtypeinfo'] = 'Diese Bedingung ist erfüllt, wenn das Notenelement vom gewünschten Typ ist.';
$string['ruleusergraded'] = 'Erhaltene Note';
$string['ruleusergraded_help'] = 'Diese Bedingung ist erfüllt, wenn:

* die Note in einer Aktivität erhalten wurde
* die Aktivität eine Bestehensnote vorgab
* die Note die Bestehensnote erfüllte
* die Note _nicht_ auf Bewertungen basiert (z. B. in Foren)
* die Note punktbasiert ist, nicht skalenbasiert Diese Bedingung belohnt Teilnehmer/innen nur einmal.';
$string['ruleusergradeddesc'] = 'Die oder der Teilnehmer/in hat bestanden.';
$string['sendawardnotification'] = 'Nachricht für Belohnung verschicken';
$string['sendawardnotification_help'] = 'Wenn diese Funktion aktiviert ist, erhalten Teilnehmer/innen eine Benachrichtigung. Die Nachricht enthält ihren Namen, die Anzahl der Punkte und den Namen des Kurses, falls vorhanden.';
$string['shortcode:xpteamladder'] = 'Zeige einen Teil der Gruppenrangliste';
$string['shortcode:xpteamladder_help'] = 'Standardmäßig wird ein Teil der Gruppenrangliste angezeigt, die die/den aktuelle/n Teilnehmer/in umgibt.

```
[xpteamladder]
```

Um die Top-5 Gruppen anstelle der Gruppen anzuzeigen, die denen der/des aktuellen Teilnehmer/in benachbart sind, setzen Sie den Parameter `top`. Sie können optional die Anzahl der anzuzeigenden Gruppen festlegen, indem Sie `top` einen Wert geben, etwa so: `top=20`.

```
[xpteamladder top]
[xpteamladder top=15]
```

Ein Link zur vollständigen Rangliste wird automatisch unter der Tabelle angezeigt, wenn mehr Ergebnisse angezeigt werden sollen. Wenn Sie einen solchen Link nicht anzeigen lassen wollen, fügen Sie das Argument `hidelink` hinzu.

```
[xpteamladder hidelink]
```

Standardmäßig enthält die Tabelle nicht die Fortschrittsspalte, die den Fortschrittsbalken anzeigt. Wenn eine solche Spalte in den Einstellungen der Rangliste ausgewählt wurde, können Sie das Argument `withprogress` verwenden, um sie anzuzeigen.

```
[xpteamladder withprogress]
```

Beachten Sie, dass das Plugin, wenn die/der aktuelle Teilnehmer/in zu mehreren Gruppen gehört, diejenige mit dem besten Rang als Referenz verwendet.';
$string['studentsearnpointsforgradeswhen'] = 'Teilnehmer/innen bekommen Punkte wenn:';
$string['themestandard'] = 'Standard';
$string['theyleftthefollowingmessage'] = 'Sie haben die folgende Nachricht hinterlassen:';
$string['timeformaxpoints'] = 'Zeitraum für max. Punkte';
$string['timeformaxpoints_help'] = 'Der Zeitraum (in Sekunden), in dem Teilnehmer/innen nicht mehr als eine bestimmte Anzahl von Punkten erhalten können.';
$string['unabletoidentifyuser'] = 'Teilnehmer/in wurde nicht gefunden.';
$string['unknowngradeitemtype'] = 'Unbekannter Typ ({$a})';
$string['uptoleveln'] = 'Bis zu Level {$a}';
$string['visualsintro'] = 'Erscheinungsbild von Leveln und Punkten anpassen.';
