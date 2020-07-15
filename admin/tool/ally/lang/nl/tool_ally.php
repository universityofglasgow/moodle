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
 * @copyright  Copyright (c) 2020 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['adminurl'] = 'Start-URL';
$string['adminurldesc'] = 'De start-URL van LTI om toegang te krijgen tot het toegankelijkheidsrapport.';
$string['allyclientconfig'] = 'Ally-configuratie';
$string['ally:clientconfig'] = 'Clientconfiguratie weergeven en bijwerken';
$string['ally:viewlogs'] = 'Ally-logboekweergave';
$string['clientid'] = 'Client-ID';
$string['clientiddesc'] = 'De Ally-client-ID';
$string['code'] = 'Code';
$string['contentauthors'] = 'Inhoudsauteurs';
$string['contentauthorsdesc'] = 'Geüploade cursusbestanden van beheerders en gebruikers die zijn toegewezen aan deze geselecteerde rollen worden geëvalueerd voor toegankelijkheid. De bestanden krijgen een toegankelijkheidsscore. Een lage score betekent dat het bestand moet worden gewijzigd om beter toegankelijk te zijn.';
$string['contentupdatestask'] = 'Taak voor bijwerken van inhoud';
$string['curlerror'] = 'cURL-fout: {$a}';
$string['curlinvalidhttpcode'] = 'Ongeldige HTTP-statuscode: {$a}';
$string['curlnohttpcode'] = 'Kan HTTP-statuscode niet verifiëren';
$string['error:invalidcomponentident'] = 'Ongeldige component-ID {$a}';
$string['error:pluginfilequestiononly'] = 'Alleen vraagcomponenten worden ondersteund voor deze url';
$string['error:componentcontentnotfound'] = 'Geen inhoud gevonden voor {$a}';
$string['error:wstokenmissing'] = 'Webservicetoken ontbreekt. Misschien moet een beheerder automatische configuratie uitvoeren?';
$string['filecoursenotfound'] = 'Het doorgegeven bestand hoort niet bij een cursus';
$string['fileupdatestask'] = 'Bestandsupdates naar Ally pushen';
$string['id'] = 'ID';
$string['key'] = 'Sleutel';
$string['keydesc'] = 'De sleutel van de LTI-consumer.';
$string['level'] = 'Niveau';
$string['message'] = 'Bericht';
$string['pluginname'] = 'Ally';
$string['pushurl'] = 'URL voor bestandsupdates';
$string['pushurldesc'] = 'Pushmeldingen over bestandsupdates naar deze URL.';
$string['queuesendmessagesfailure'] = 'Er is een fout opgetreden tijdens het verzenden van berichten naar de AWS SQS. Foutgegevens: $a';
$string['secret'] = 'Geheim';
$string['secretdesc'] = 'Het LTI-geheim.';
$string['showdata'] = 'Gegevens weergeven';
$string['hidedata'] = 'Gegevens verbergen';
$string['showexplanation'] = 'Toelichting weergeven';
$string['hideexplanation'] = 'Toelichting verbergen';
$string['showexception'] = 'Uitzondering weergeven';
$string['hideexception'] = 'Uitzondering verbergen';
$string['usercapabilitymissing'] = 'De opgegeven gebruiker heeft niet de mogelijkheid om dit bestand te verwijderen.';
$string['autoconfigure'] = 'Ally-webservice automatisch configureren';
$string['autoconfiguredesc'] = 'Automatsch webservicerol en -gebruiker maken voor Ally.';
$string['autoconfigureconfirmation'] = 'Automatisch webservicerol en -gebruiker maken voor Ally en webservice inschakelen. De volgende acties worden uitgevoerd: <ul><li>een rol met de titel \'ally_webservice\' maken en een gebruiker met de gebruikersnaam \'ally_webuser\'</li><li>de gebruiker \'ally_webuser\' toevoegen aan de rol \'ally_webservice\'</li><li>webservices inschakelen</li><li>het rest-webserviceprotocol inschakelen</li><li>de Ally-webservice inschakelen</li><li>een token maken voor de account \'ally_webuser\'</li></ul>';
$string['autoconfigsuccess'] = 'Gelukt. De Ally-webservice is automatisch geconfigureerd.';
$string['autoconfigtoken'] = 'Dit is het webservicetoken:';
$string['autoconfigapicall'] = 'Je kunt via de volgende url testen of de webservice werkt:';
$string['privacy:metadata:files:action'] = 'De actie die wordt uitgevoerd op het bestand, zoals: gemaakt, bijgewerkt of verwijderd.';
$string['privacy:metadata:files:contenthash'] = 'De hash van de inhoud van het bestand teneinde het bestand uniek te maken.';
$string['privacy:metadata:files:courseid'] = 'De ID van de cursus waarbij het bestand hoort.';
$string['privacy:metadata:files:externalpurpose'] = 'Voor integratie met Ally moeten er bestanden worden uitgewisseld met Ally.';
$string['privacy:metadata:files:filecontents'] = 'De inhoud van het bestand wordt naar Ally verzonden om deze te controleren op toegankelijkheid.';
$string['privacy:metadata:files:mimetype'] = 'Het MIME-type van het bestand, zoals: text/plain, image/jpeg, etc.';
$string['privacy:metadata:files:pathnamehash'] = 'De hash van de padnaam van het bestand om het pad uniek te identificeren.';
$string['privacy:metadata:files:timemodified'] = 'Het tijdstip waarop het veld het laatst is gewijzigd.';
$string['cachedef_request'] = 'Cache met Ally-filteraanvragen';
$string['pushfilessummary'] = 'Overzicht van Ally-bestandsupdates.';
$string['pushfilessummary:explanation'] = 'Overzicht van bestandsupdates verzonden naar Ally.';
$string['section'] = 'Sectie {$a}';
$string['lessonanswertitle'] = 'Antwoord voor les "{$a}"';
$string['lessonresponsetitle'] = 'Reactie voor les "{$a}"';
$string['logs'] = 'Ally-logboeken';
$string['logrange'] = 'Logboekbereik';
$string['loglevel:none'] = 'Geen';
$string['loglevel:light'] = 'Licht';
$string['loglevel:medium'] = 'Normaal';
$string['loglevel:all'] = 'Alle';
$string['logger:pushtoallysuccess'] = 'Pushen naar Ally-eindpunt gelukt';
$string['logger:pushtoallyfail'] = 'Pushen naar Ally-eindpunt mislukt';
$string['logger:pushfilesuccess'] = 'Pushen van bestand(en) naar Ally-eindpunt gelukt';
$string['logger:pushfileliveskip'] = 'Fout bij pushen van live bestand';
$string['logger:pushfileliveskip_exp'] = 'Het pushen van een of meer live bestanden wordt overgeslagen vanwege communicatieproblemen. Het pushen van live bestanden wordt hervat wanneer de taak voor bestandsupdates succesvol is. Controleer de configuratie.';
$string['logger:pushfileserror'] = 'Pushen naar Ally-eindpunt mislukt';
$string['logger:pushfileserror_exp'] = 'Fouten behorende bij pushen van inhoudsupdates naar Ally-services.';
$string['logger:pushcontentsuccess'] = 'Pushen van inhoud naar Ally-eindpunt gelukt';
$string['logger:pushcontentliveskip'] = 'Fout bij pushen van live inhoud';
$string['logger:pushcontentliveskip_exp'] = 'Het pushen van live inhoud wordt overgeslagen vanwege communicatieproblemen. Het pushen van live inhoud wordt hervat wanneer de taak voor inhoudsupdates succesvol is. Controleer de configuratie.';
$string['logger:pushcontentserror'] = 'Pushen naar Ally-eindpunt mislukt';
$string['logger:pushcontentserror_exp'] = 'Fouten behorende bij pushen van inhoudsupdates naar Ally-services.';
$string['logger:addingconenttoqueue'] = 'Inhoud toevoegen aan push-wachtrij';
$string['logger:annotationmoderror'] = 'Annoteren van inhoud van Ally-module mislukt.';
$string['logger:annotationmoderror_exp'] = 'Module is niet correct geïdentificeerd.';
$string['logger:failedtogetcoursesectionname'] = 'Fout bij ophalen van naam van cursussectie';
$string['logger:cmidresolutionfailure'] = 'Fout bij omzetten van ID van cursusmodule';
$string['courseupdatestask'] = 'Cursusgebeurtenissen naar Ally pushen';
$string['logger:pushcoursesuccess'] = 'Pushen van een of meer cursusgebeurtenissen naar Ally-eindpunt gelukt';
$string['logger:pushcourseliveskip'] = 'Fout bij pushen van live cursusgebeurtenis';
$string['logger:pushcourseerror'] = 'Fout bij pushen van live cursusgebeurtenis';
$string['logger:pushcourseliveskip_exp'] = 'Het pushen van cursusgebeurtenissen wordt overgeslagen vanwege communicatieproblemen. Het pushen van live cursusgebeurtenissen wordt hervat wanneer de taak voor updates van cursusgebeurtenissen succesvol is. Controleer de configuratie.';
$string['logger:pushcourseserror'] = 'Pushen naar Ally-eindpunt mislukt';
$string['logger:pushcourseserror_exp'] = 'Fouten behorende bij pushen van cursusupdates naar Ally-services.';
$string['logger:addingcourseevttoqueue'] = 'Cursusgebeurtenis toevoegen aan push-wachtrij';
$string['logger:cmiderraticpremoddelete'] = 'Cursusmodule-ID heeft problemen met vooraf verwijderen.';
$string['logger:cmiderraticpremoddelete_exp'] = 'De module is niet juist geïdentificeerd; de module bestaat niet vanwege sectieverwijdering of er is sprake van een andere factor die de verwijderings-hook heeft geactiveerd waardoor de module niet is gevonden.';
$string['logger:servicefailure'] = 'Mislukt tijdens het gebruik van de service.';
$string['logger:servicefailure_exp'] = '<br>Cursus: {$a->class}<br>Parameters: {$a->params}';
$string['logger:autoconfigfailureteachercap'] = 'Mislukt bij het toewijzen van een vaardigheid docentarchetype aan de rol ally_webservice.';
$string['logger:autoconfigfailureteachercap_exp'] = '<br>Vaardigheid: {$a->cap}<br>Machtiging: {$a->permission}';
