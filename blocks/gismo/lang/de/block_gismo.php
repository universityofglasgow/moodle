<?php

/**
 * GISMO block DE translation file
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// block title
$string['pluginname'] = 'Gismo';
$string['gismo'] = 'Gismo';
//$string['gismo_report_launch'] = 'Werkzeug zur Berichterstattung';
$string['gismo_report_launch'] = 'Reporting Tool';

// capabilities
$string['gismo:trackuser'] = 'Gismo Student/in';
$string['gismo:trackteacher'] = 'Gismo Dozent/in';

// help
$string['gismo_help'] = "<p>Gismo funktioniert bei Kursen, welche die folgenden Anforderungen erfüllen:</p><ul><li> mindestens ein Student muss für den Kurs eingetragen sein</li><li>der Kurs muss mindestens eines der folgenden Module enthalten:<ul><li>Ressourcen</li><li>Aufgaben</li><li>Tests</li></ul></li></ul>";

// General
$string['page_title'] = "Gismo - ";
$string['file'] = 'Datei';
$string['options'] = 'Optionen';
$string['save'] = 'Diagramm als Bild exportieren';
$string['print'] = 'Drucken';
$string['exit'] = 'Programm verlassen';
$string['help'] = 'Hilfe';
$string['home'] = 'Gismo Home';
$string['close'] = 'Schliessen';

$string['users'] = 'User'; //************
$string['teachers'] = 'Dozent/innen'; //************
// Students
$string['students'] = 'Student/-innen';
$string['student_accesses'] = 'Zugriffe durch Student/-innen';
$string['student_accesses_chart_title'] = 'Student/-innen: Zugriffe durch Student/-innen';
$string['student_accesses_overview'] = 'Übersicht über Zugriffe';
$string['student_accesses_overview_chart_title'] = 'Student/-innen: Übersicht über Zugriffe';
$string['student_resources_overview'] = 'Übersicht über Zugriffe auf Ressourcen';
$string['student_resources_overview_chart_title'] = 'Student/-innen: Übersicht über Zugriffe auf Ressourcen';
$string['student_resources_details_chart_title'] = 'Student/-innen: Einzelheiten bezüglich Zugriffe auf Ressourcen';

// Resources
$string['resources'] = 'Ressourcen';
$string['detail_resources'] = 'Einzelheiten bezüglich Ressourcen';
$string['resources_students_overview'] = 'Übersicht über Student/-innen';
$string['resources_students_overview_chart_title'] = 'Ressourcen: Übersicht über Student/-innen';
$string['resources_access_overview'] = 'Übersicht über Zugriffe';
$string['resources_access_overview_chart_title'] = 'Ressourcen: Übersicht über Zugriffe';
$string['resources_access_detail_chart_title'] = 'Ressourcen: Einzelheiten bezüglich Zugriffe durch Student/-innen'; //**************
// Activities
$string['activities'] = 'Aktivitäten';
$string['assignments'] = 'Aufgaben';
$string['assignments_chart_title'] = 'Aktivitäten: Übersicht über Aufgaben';
$string['assignments22'] = 'Aufgaben 2.2';
$string['assignments22_chart_title'] = 'Aktivitäten: Übersicht über Aufgaben 2.2';
$string['chats'] = 'Chats';

$string['chats_over_time'] = 'Chats im Zeitablauf'; //************

$string['chats_chart_title'] = 'Aktivitäten: Übersicht über Chats';
$string['chats_ud_chart_title'] = 'Aktivitäten: Einzelheiten über Student/-innen in Chats';
$string['chats_over_time_chart_title'] = 'Aktivitäten: Chat-Beiträge im Zeitablauf';
$string['forums'] = 'Foren';

$string['forums_over_time'] = 'Foren im Zeitablauf'; //************

$string['forums_chart_title'] = 'Aktivitäten: Übersicht über Foren';
$string['forums_ud_chart_title'] = 'Aktivitäten: Einzelheiten über Student/-innen in Foren';
$string['forums_over_time_chart_title'] = 'Aktivitäten: Forenbeiträge im Zeitablauf';

$string['quizzes'] = 'Tests';
$string['quizzes_chart_title'] = 'Aktivitäten: Übersicht über Tests';

$string['wikis'] = 'Wikis';

$string['wikis_over_time'] = 'Wikis im Zeitablauf'; //************

$string['wikis_chart_title'] = 'Aktivitäten: Übersicht über Wikis';
$string['wikis_ud_chart_title'] = 'Aktivitäten: Einzelheiten über Student/-innen in Wikis';
$string['wikis_over_time_chart_title'] = 'Aktivitäten: Wiki-Beiträge im Zeitablauf';

// Help
$string['help'] = 'Hilfe';
$string['help_docs'] = 'Kurze Ubersicht';
$string['tutorial'] = 'Tutorial';
$string['about'] = 'Über Gismo';

$string['date'] = 'Datum';
$string['from'] = 'Von';
$string['to'] = 'Bis';

$string['show'] = 'Anzeigen'; //************
$string['list'] = 'Liste'; //************

$string['menu_hide'] = 'Menü ausblenden'; //************
$string['menu_show'] = 'Menü anzeigen'; //************
$string['detail_show'] = 'Einzelheiten anzeigen'; //************

$string['items'] = 'EINTRÄGE'; //************
$string['details'] = 'Details'; //************
$string['info_title'] = 'GISMO - Listen'; //************
$string['info_text'] = '<p>Um das Diagramm individuell zu gestalten, können Sie Elemente aus den Menüs auswählen/Auswahl aufheben.</p>";
        message += "<p>Anweisungen</p>";
        message += "<ul style=\'list-style-position: inside;\'>";
        message += "<li>Haupt-Kontrollkästchen: alle aufgelisteten Elemente auswählen/Auswahl aufheben.</li>";
        message += "<li>Klick auf das Element: das angeklickte Element auswählen/Auswahl aufheben.</li>";
        message += "<li>Element Alt+Klick: nur das angeklickte Element auswählen.</li>";
        message += "<li><img src=\'images/eye.png\'> Einzelheiten zu Elementen anzeigen</li>";
        message += "</ul>'; //************
// Errors
$string['err_course_not_set'] = 'Die Identifikation (ID) für den Kurs ist nicht eingerichtet!';
$string['err_block_instance_id_not_set'] = 'Die Block Instanz ID ist nicht eingerichtet!';
$string['err_authentication'] = 'Sie sind nicht authentifiziert. Es ist möglich, dass die Moodle-Session abgelaufen ist.<br /><br /><a href="">Einloggen</a>';
$string['err_access_denied'] = 'Zur Ausführung dieser Handlung sind Sie nicht befugt.';
$string['err_srv_data_not_set'] = 'Es fehlen ein oder mehrere erforderliche Kennwerte!';
$string['err_missing_parameters'] = 'Es fehlen ein oder mehrere erforderliche Kennwerte!';
$string['err_missing_course_students'] = 'Die Kursteilnehmer können nicht ausgewählt werden!';
$string['gismo:view'] = "GISMO - Autorisierung nicht möglich";


//OTHERS
$string['welcome'] = "Willkommen zu GISMO v. 3.3";
$string['processing_wait'] = "Die Daten werden aufbereitet, bitte warten Sie!";

//Graphs labels
$string['accesses'] = "Zugriffe";
$string['timeline'] = "Zeitplan";
$string['actions_on'] = "Handlungen von ";
$string['nr_submissions'] = "Anzahl Einträge";



//OPTIONS
$string['option_intro'] = 'In diesem Abschnitt können Sie bestimmte Applikations-Optionen individuell einstellen.';
$string['option_general_settings'] = 'Allgemeine Einstellungen';
$string['option_include_hidden_items'] = 'Verborgene Elemente mit einschliessen';
$string['option_chart_settings'] = 'Diagramm-Einstellungen';
$string['option_base_color'] = 'Grundfarbe';
$string['option_red'] = 'Rot';
$string['option_green'] = 'Grün';
$string['option_blue'] = 'Blau';
$string['option_axes_label_max_length'] = 'Achsenbeschriftung max. Länge (Zeichen)';
$string['option_axes_label_max_offset'] = 'Achsenbeschriftung max. Offset (Zeichen)';
$string['option_number_of_colors'] = 'Anzahl der Farben (Matrix-Diagramme)';
$string['option_other_settings'] = 'Andere Einstellungen';
$string['option_window_resize_delay_seconds'] = 'Fenstergrösse anpassen mit Verzögerungsfunktion (Sekunden)';
$string['save'] = 'Speichern';
$string['cancel'] = 'Abbrechen';


$string['export_chart_as_image'] = 'GISMO - Diagramm als Bild exportieren';
$string['no_chart_at_the_moment'] = 'Momentan existiert kein Diagramm!';


$string['about_gismo'] = 'Über GISMO';
$string['intro_information_about_gismo'] = 'Informationen zu dieser Version sind unten angezeigt:';
$string['gismo_version'] = 'Version ';
$string['release_date'] = 'Freigabedatum ';
$string['authors'] = 'Autoren ';
$string['contact_us'] = 'Bei Fragen oder wenn Sie irgendwelche Fehler melden möchten, wenden Sie sich bitte an die Autoren unter den nachstehenden Adressen: ';
$string['close'] = 'Schliessen';
$string['confirm_exiting'] = 'Möchten Sie Gismo wirklich verlassen?';


//Ende
$string['completion'] = 'Abschluss';
$string['completion_quiz_menu'] = 'Quizfragen';
$string['completion_quiz_chart_title'] = 'Abschluss der Quizfragen';
$string['completion_assignment_menu'] = 'Aufgaben';
$string['completion_assignment_chart_title'] = 'Abschluss der Aufgaben';
$string['completion_assignment22_menu'] = 'Aufgaben 2.2';
$string['completion_assignment22_chart_title'] = 'Abschluss der Aufgaben 2.2';
$string['completion_resource_menu'] = 'Ressourcen';
$string['completion_resource_chart_title'] = 'Abschluss der Ressourcen';
$string['completion_forum_menu'] = 'Forums';
$string['completion_forum_chart_title'] = 'Abschluss der Forums';
$string['completion_wiki_menu'] = 'Wikis';
$string['completion_wiki_chart_title'] = 'Abschluss der Wikis';
$string['completion_chat_menu'] = 'Chats';
$string['completion_chat_chart_title'] = 'Abschluss der Chats';
$string['completion_completed_on_tooltip'] = 'Beendet am ';
$string['completion_completed_on_tooltip_months'] = "['Jan','Feb','M&auml;r','Apr','Mai','Jun','Jul','Aug','Sep','Oct','Nov','Dez']";

// Fehlende Reihen hinzugefügt am 08.10.2013
$string['err_missing_data'] = 'Kann nicht mit der Analyse fortfahren, weil keine Daten zur Verf&uuml;gung stehen!';
$string['err_no_data'] = 'Keine Daten';
$string['err_cannot_extract_data'] = 'Kann nicht auf Serverdaten zugreifen!';
$string['err_unknown'] = 'Unbekannter Fehler!';

//Homepage Text
$string['homepage_title'] = 'Wilkommen bei GISMO';
$string['homepage_processing_data_wait'] = 'Datenverarbeitung, bitte warten!';
$string['homepage_processing_data'] = 'Datenverarbeitung';
$string['homepage_text'] = 'GISMO ist ein grafisches, interaktives System, das &uuml;ber ein Tool, Tracking-Daten aus dem Moodle Course Management-System extrahiert, mit denen Dozierende und Studierende ihre Aktivit&auml;ten &uuml;berwachen k&ouml;nnen.<br />
Um GISMO zu starten w&auml;hlen Sie ein Men&uuml; oben auf dieser Seite aus.<br />
Wenn Sie die Tutorials anschauen m&ouml;chten, klicken Sie bitte auf das Men&uuml; "Hilfe" > "Tutorials".';

$string['hide_menu'] = 'Menu verstecken';
$string['show_menu'] = 'Menu zeigen';
$string['show_details'] = 'Details zeigen';

$string['homepage_charts_preview_title'] = 'Diagramm Vorschau';
$string['homepage_chart_activities_assignments_overview'] = 'Aktivit&auml;ten: &Uuml;bersicht der Aufgaben';
$string['homepage_chart_resources_access_overview'] = 'Ressourcen: Zugriffs&uuml;bersicht';
$string['homepage_chart_resources_students_overview'] = 'Ressourcen: Studenten&uuml;bersicht';
$string['homepage_chart_students_access_overview_on_resources'] = 'Studenten: Studenentzugriffe und ressourcen';
$string['homepage_chart_students_access_overview'] = 'Studenten: Zugriffs&uuml;bersicht';
$string['homepage_chart_students_accesses_by_students'] = 'Studenten: Zugriffe durch Studenten';
?>
