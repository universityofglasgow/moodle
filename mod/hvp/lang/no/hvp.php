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

$string['modulename'] = 'Interaktivt innhold';
$string['modulename_help'] = 'Aktivitetsmodulen H5P gjør deg istand til å lage interaktivt innhold som feks Interaktive videoer, spørsmåls-sett, dra og slipp, flervalg, presentasjoner og mye mer.

I tillegg til å være et forfatterverktøy for rikt innhold, gjør H5P det mulig å importere og eksportere H5P-filer for effektiv gjenbruk og deling av innhold.

Brukerinteraksjoner og poeng spores vha xAPI og er tilgjengelig gjennom Moodles karakterbok

Du kan legge til eksisterende interaktivt H5P-innhold fra andre nettsider ved å laste opp en .h5p-fil. Du kan lage og laste ned .h5p-filer på feks h5p.org';
$string['modulename_link'] = 'https://h5p.org/moodle-more-help';
$string['modulenameplural'] = 'Interaktivt innhold';
$string['pluginadministration'] = 'H5P';
$string['pluginname'] = 'H5P';
$string['intro'] = 'Introduksjon';
$string['h5pfile'] = 'H5P-fil';
$string['fullscreen'] = 'Fullskjermsvisning';
$string['disablefullscreen'] = 'Gå ut av fullskjermsvisning';
$string['download'] = 'Last ned';
$string['copyright'] = 'Opphavsrett';
$string['embed'] = 'Inkluder';
$string['showadvanced'] = 'Vis avanserte instillinger';
$string['hideadvanced'] = 'Skjul avanserte instillinger';
$string['resizescript'] = 'Inkluder dette scriptet på din nettside hvis du ønsker dynamisk endring av størrelse på inkludert innhold:';
$string['size'] = 'Størrelse';
$string['close'] = 'Lukk';
$string['title'] = 'Tittel';
$string['author'] = 'Forfatter';
$string['year'] = 'År';
$string['source'] = 'Kilde';
$string['license'] = 'Lisens';
$string['thumbnail'] = 'Miniatyrbilde';
$string['nocopyright'] = 'Informasjon om opphavsrett ikke tilgjengelig for dette innholdet.';
$string['downloadtitle'] = 'Last ned dette innholdet som en H5P-fil.';
$string['copyrighttitle'] = 'Informasjon om opphavsrett for dette innholdet.';
$string['embedtitle'] = 'Vis HTML-kode du kan bruke for å inkludere innholdet på en annen nettside.';
$string['h5ptitle'] = 'Besøk H5P.org for å se mer interaktivt innhold.';
$string['contentchanged'] = 'Dette innholdet har endret seg siden sist du brukte det.';
$string['startingover'] = 'Du starter på nytt.';
$string['confirmdialogheader'] = 'Bekreft handling';
$string['confirmdialogbody'] = 'Er du sikker på at du ønsker å gjøre dette? Handlingen er ikke reversibel.';
$string['cancellabel'] = 'Avbryt';
$string['confirmlabel'] = 'Bekreft';
$string['noh5ps'] = 'Der finnes ikke noe interaktivt innhold for dette kurset.';

// Update message email for admin
$string['messageprovider:updates'] = 'Varsel om tilgjengelige H5P-oppdateringer';
$string['updatesavailabletitle'] = 'Nye H5P-oppdateringer er tilgjengelig';
$string['updatesavailablemsgpt1'] = 'Der er tilgjengelige oppdateringer for H5P-innholdstypene du har i din Moodle-installasjon.';
$string['updatesavailablemsgpt2'] = 'Naviger til lenka under for ytterligere instruksjoner.';
$string['updatesavailablemsgpt3'] = 'Den seneste oppdateringen ble sluppet: {$a}';
$string['updatesavailablemsgpt4'] = 'Du kjører en versjon fra: {$a}';

$string['lookforupdates'] = 'Se etter H5P-oppdateringer';
$string['removetmpfiles'] = 'Fjern gamle midlertidige filer';
$string['removeoldlogentries'] = 'Fjern gamle H5P-loggmeldinger';

// Admin settings.
$string['displayoptionnevershow'] = 'Vis aldri';
$string['displayoptionalwaysshow'] = 'Vis alltid';
$string['displayoptionpermissions'] = 'Vis kun for brukere som har tilgang til å eksportere H5Per';
$string['displayoptionauthoron'] = 'Settes av forfatter, standard på';
$string['displayoptionauthoroff'] = 'Settes av forfatter, standard av';
$string['displayoptions'] = 'Visningsinnstillinger';
$string['enableframe'] = 'Vis handlingslinjen og rammen';
$string['enabledownload'] = 'Nedlastings-knapp';
$string['enableembed'] = 'Inkluder-knapp';
$string['enablecopyright'] = 'Opphavsretts-knapp';
$string['enableabout'] = 'Om H5P-knapp';

$string['externalcommunication'] = 'Ekstern kommuniksjon';
$string['externalcommunication_help'] = 'Bidra til utviklingen av H5P ved å bidra med anonym bruksdata. Ved å skru av denne innstillingen vil du hindre installasjonen i å hente de seneste H5P-oppdateringene. Du kan lese mer om <a {$a}>hvilke data som samles</a> på h5p.org.';
$string['enablesavecontentstate'] = 'Lagre tilstanden til innholdet';
$string['enablesavecontentstate_help'] = 'Automatisk lagring av hva brukeren har svart og hvor langt brukeren har kommet. Dette betyr brukeren kan fortsette der han avsluttet.';
$string['contentstatefrequency'] = 'Frekvens for tilstandslagring';
$string['contentstatefrequency_help'] = 'Hvor ofte skal man lagre tilstanden (i antall sekunder). Øk dette tallet hvis du har problemer med for mange ajax-forespørsler';
$string['enabledlrscontenttypes'] = 'Skru på LRS-avhengige innholdstyper';
$string['enabledlrscontenttypes_help'] = 'Gjør det mulig å bruke innholdstyper som er avhengig av en såkalt Learning Record Store for å virke, slik som Questionnaire-innholdstypen.';

// Admin menu.
$string['settings'] = 'H5P-innstillinger';
$string['libraries'] = 'H5P-bibliotek';

// Update libraries section.
$string['updatelibraries'] = 'Oppdater alle bibliotek';
$string['updatesavailable'] = 'Der finnes tilgjengelige oppdateringer for H5P-innholdstypene dine.';
$string['whyupdatepart1'] = 'Du kan lese mer om hvorfor det er viktig å holde seg oppdatert og godene ved å gjøre det på <a {$a}>Why Update H5P</a>-siden.';
$string['whyupdatepart2'] = 'Denne siden viser også en liste over de forskjellige endringene, hvor du kan lese om nye funksjoner som introduseres og problemer som er fikset.';
$string['currentversion'] = 'Du har';
$string['availableversion'] = 'Tilgjengelig oppdatering';
$string['usebuttonbelow'] = 'Du kan bruke knappen under for å automatisk laste ned og oppdatere alle innholdstypene dine.';
$string['downloadandupdate'] = 'Last ned & oppdater';
$string['missingh5purl'] = 'Mangler URL for H5P-fil';
$string['unabletodownloadh5p'] = 'Ute av stand til å laste ned H5P-fil';

// Upload libraries section.
$string['uploadlibraries'] = 'Last opp bilbliotek';
$string['options'] = 'Innstillinger';
$string['onlyupdate'] = 'Oppdater kun eksisterende bibliotek';
$string['disablefileextensioncheck'] = 'Slå av sjekk for fil-endelser';
$string['disablefileextensioncheckwarning'] = 'Advarsel! Det å slå av sjekk for fil-endelser kan ha alvorlige sikkerhetsimplikasjoner, siden man da tillater å laste opp php-filer. Dette kan gjøre det mulig for eksterne å kjøre ondsinnet kode på ditt nettsted. Gjør dette kun hvis du vet hva du holder på med.';
$string['upload'] = 'Last opp';

// Installed libraries section.
$string['installedlibraries'] = 'Installerte bibliotek';
$string['invalidtoken'] = 'Ufyldig sikkerhetsnøkkel.';
$string['missingparameters'] = 'Mangler parametre';

// H5P library list headers on admin page.
$string['librarylisttitle'] = 'Tittel';
$string['librarylistrestricted'] = 'Begrenset';
$string['librarylistinstances'] = 'Instanser';
$string['librarylistinstancedependencies'] = 'Instansavhengigheter';
$string['librarylistlibrarydependencies'] = 'Biblioteksavhengigheter';
$string['librarylistactions'] = 'Handlinger';

// H5P library page labels.
$string['addlibraries'] = 'Legg til bibliotek';
$string['installedlibraries'] = 'Installerte bibliotek';
$string['notapplicable'] = '--';
$string['upgradelibrarycontent'] = 'Oppgrader H5P-innhold';

// Upgrade H5P content page.
$string['upgrade'] = 'Oppgrader H5P';
$string['upgradeheading'] = 'Oppgrader {$a}-innhold';
$string['upgradenoavailableupgrades'] = 'Det finnes ingen oppgraderinger for dette biblioteket.';
$string['enablejavascript'] = 'Vær vennlig å slå på JavaScript-støtte i nettleseren din.';
$string['upgrademessage'] = 'Du er iferd med å oppgradere {$a} innholdsinstans(er). Men først må du velge hvilke versjon du ønsker å oppgradere til.';
$string['upgradeinprogress'] = 'Oppgraderer til %ver...';
$string['upgradeerror'] = 'En fil skjedde under prosesseringen:';
$string['upgradeerrordata'] = 'Klarte ikke å laste data for biblioteket %lib.';
$string['upgradeerrorscript'] = 'Klarte ikke å laste oppgraderingskoden til %lib.';
$string['upgradeerrorcontent'] = 'Klarte ikke å oppgradere innholdet med ID: %id:';
$string['upgradeerrorparamsbroken'] = 'Parameterne er ødelagt.';
$string['upgradedone'] = 'Du har nå oppgrader {$a} innholdsinstans(er).';
$string['upgradereturn'] = 'Gå tilbake';
$string['upgradenothingtodo'] = 'Det finnes ikke noe innhold å oppgradere';
$string['upgradebuttonlabel'] = 'Oppgrader';
$string['upgradeinvalidtoken'] = 'Feil: Ugyldig sikkerhetsnøkkel!';
$string['upgradelibrarymissing'] = 'Feil: Et bibliotek mangler!';

// Results / report page.
$string['user'] = 'Bruker';
$string['score'] = 'Poeng';
$string['maxscore'] = 'Max poeng';
$string['finished'] = 'Ferdig';
$string['loadingdata'] = 'Laster data.';
$string['ajaxfailed'] = 'Feilet ved lasting av data.';
$string['nodata'] = 'Det finnes ikke data som passer til kriteriene.';
$string['currentpage'] = 'Side $current av $total';
$string['nextpage'] = 'Neste side';
$string['previouspage'] = 'Forrige side';
$string['search'] = 'Søk';
$string['empty'] = 'Ingen resultater tilgjengelig';

// Editor
$string['javascriptloading'] = 'Venter på JavaScript...';
$string['action'] = 'Handling';
$string['upload'] = 'Laste opp';
$string['create'] = 'Lag ny';
$string['editor'] = 'Innholdstype';

$string['invalidlibrary'] = 'Ugyldig bibliotek';
$string['nosuchlibrary'] = 'Biblioteket finnes ikke';
$string['noparameters'] = 'Ingen parametre';
$string['invalidparameters'] = 'Ugyldige parametre';
$string['missingcontentuserdata'] = 'Feil: Kunne ikke finne innholdsbrukerdata';

$string['maximumgrade'] = 'Maximum grade';
$string['maximumgradeerror'] = 'Please enter a valid positive integer as the max points available for this activity';

// Capabilities
$string['hvp:addinstance'] = 'Legg til en ny H5P-aktivitet';
$string['hvp:restrictlibraries'] = 'Begrense et H5P-bibliotek';
$string['hvp:updatelibraries'] = 'Oppdatere versjonen til et H5P-bibliotek';
$string['hvp:userestrictedlibraries'] = 'Bruke begrenset H5P-bibliotek';
$string['hvp:savecontentuserdata'] = 'Lagre H5P-brukerdata';
$string['hvp:saveresults'] = 'Lagre resultater for H5P-innhold';
$string['hvp:viewresults'] = 'Vise resultater for H5P-innhold';
$string['hvp:getcachedassets'] = 'Tilgang til bufret H5P-innholdsressurser';
$string['hvp:getcontent'] = 'Tilgang til innholdet til H5P-fil i kurs';
$string['hvp:getexport'] = 'Tilgang til eksportfil fra H5P i kurs';
$string['hvp:updatesavailable'] = 'Få varsel når H5P-oppdateringer er tilgjengelige';

// Capabilities error messages
$string['nopermissiontoupgrade'] = 'Du har ikke tillatelse til å oppgradere bibliotek.';
$string['nopermissiontorestrict'] = 'Du har ikke tillatelse til å begrense tilgang til bibliotek.';
$string['nopermissiontosavecontentuserdata'] = 'Du har ikke tillatelse til å lagre brukerdata.';
$string['nopermissiontosaveresult'] = 'Du har ikke tillatelse til å lagre resultater for dette innholdet.';
$string['nopermissiontoviewresult'] = 'Du har ikke tillatelse til å se resultater for dette innholdet.';

// Editor translations
$string['noziparchive'] = 'PHP-versjonen du bruker støtter ikke ZipArchive.';
$string['noextension'] = 'Fila du lastet opp er ikke en gyldig H5P (Den har ikke .h5p som filendelse)';
$string['nounzip'] = 'Fila du lastet opp er ikke en gyldig H5P (Jeg klarer ikke å unzippe den)';
$string['noparse'] = 'Jeg klarte ikke å tolke h5p.json-fila';
$string['nojson'] = 'h5p.json-fila er ugyldig';
$string['invalidcontentfolder'] = 'Ugyldig innholdskatalog';
$string['nocontent'] = 'Kunne ikke finne eller tolke content.json-fila';
$string['librarydirectoryerror'] = 'Biblioteks-katalogavnet må være lik machineName eller machineName-majorVersion.minorVersion (fra library.json). (Katalog: {$a->%directoryName} , machineName: {$a->%machineName}, majorVersion: {$a->%majorVersion}, minorVersion: {$a->%minorVersion})';
$string['missingcontentfolder'] = 'Finner ingen gyldig innholdskatalog';
$string['invalidmainjson'] = 'Finner ingen gyldig h5p.json-fil';
$string['missinglibrary'] = 'Mangler et påkrevd bibliotek {$a->@library}';
$string['missinguploadpermissions'] = 'Vær oppmerksom på at bibliotekene kan skistere i den opplastede fila, men at du ikke tillates å laste opp nye bibliotek. Kontakt nettstedsadministratoren.';
$string['invalidlibraryname'] = 'Ugyldig biblioteksnavn: {$a->%name}';
$string['missinglibraryjson'] = 'Klarte ikke å finne en library.json-fil med gyldig json format for bibliotek {$a->%name}';
$string['invalidsemanticsjson'] = 'En ugyldig semantics.json-fil er inkludert i biblioteket {$a->%name}';
$string['invalidlanguagefile'] = 'En ugyldig språkfil {$a->%file} i biblioteket {$a->%library}';
$string['invalidlanguagefile2'] = 'En ugyldig språkfil {$a->%languageFile} er inkludert i biblioteket {$a->%name}';
$string['missinglibraryfile'] = 'Fila "{$a->%file}" mangler i biblioteket: "{$a->%name}"';
$string['missingcoreversion'] = 'Systemet kunne ikke installere <em>{$a->%component}</em>-komponenten fra pakken, den krever en nyere versjon av H5P-utvidelsen. Dette nettstedet kjører versjon {$a->%current}, mens påkrevd versjon er {$a->%required} eller høyere. Du bør vurdere å oppgradere for deretter å prøve på nytt.';
$string['invalidlibrarydataboolean'] = 'Ugyldig data angitt for {$a->%property} i {$a->%library}. Boolsk verdi forventet.';
$string['invalidlibrarydata'] = 'Ugyldig data angitt for {$a->%property} i {$a->%library}';
$string['invalidlibraryproperty'] = 'Kan ikke lese feltet {$a->%property} i {$a->%library}';
$string['missinglibraryproperty'] = 'Det obligatoriske feltet {$a->%property} finnes ikke i {$a->%library}';
$string['invalidlibraryoption'] = 'Ulovlig verdi {$a->%option} i {$a->%library}';
$string['addedandupdatelibraries'] = 'La til {$a->%new} nye H5P-bibliotek og oppdaterte {$a->%old} eksisterende.';
$string['addednewlibraries'] = 'La til {$a->%new} nye H5P-bibliotek.';
$string['updatedlibraries'] = 'Oppdaterte {$a->%old} eksisterende H5P-bibliotek.';
$string['missingdependency'] = 'Mangler avhengigheten {$a->@dep} som kreves av {$a->@lib}.';
$string['invalidstring'] = 'Angitt streng er ugyldig iforhold til det regulære uttrykket i semantikken. (verdi: \"{$a->%value}\", regexp: \"{$a->%regexp}\")';
$string['invalidfile'] = 'Fila "{$a->%filename}" er ikke tillatt. Bare filer med de følgende filendingene er tillatt: {$a->%files-allowed}.';
$string['invalidmultiselectoption'] = 'Ugyldig valg gjort i flervalg.';
$string['invalidselectoption'] = 'Ugyldig valg gjort.';
$string['invalidsemanticstype'] = 'Intern H5P-feil: ukjent innholdstype "{$a->@type}" i semantikken. Fjerner det aktuelle innholdet!';
$string['copyrightinfo'] = 'Opphavsrettsinformasjon';
$string['years'] = 'År';
$string['undisclosed'] = 'Undisclosed';
$string['attribution'] = 'Attribution 4.0';
$string['attributionsa'] = 'Attribution-ShareAlike 4.0';
$string['attributionnd'] = 'Attribution-NoDerivs 4.0';
$string['attributionnc'] = 'Attribution-NonCommercial 4.0';
$string['attributionncsa'] = 'Attribution-NonCommercial-ShareAlike 4.0';
$string['attributionncnd'] = 'Attribution-NonCommercial-NoDerivs 4.0';
$string['gpl'] = 'General Public License v3';
$string['pd'] = 'Public Domain';
$string['pddl'] = 'Public Domain Dedication and Licence';
$string['pdm'] = 'Public Domain Mark';
$string['copyrightstring'] = 'Opphavsrett';
$string['unabletocreatedir'] = 'Ikke istand til å opprette katalog.';
$string['unabletogetfieldtype'] = 'Ikke istand til å hente felt-type.';
$string['filetypenotallowed'] = 'Filtypen er ikke tillatt.';
$string['invalidfieldtype'] = 'Ugyldig felt-type.';
$string['invalidimageformat'] = 'Ugyldig bidlefilformat. Bruk jpg, png eller gif.';
$string['filenotimage'] = 'Fila er ikke et bilde.';
$string['invalidaudioformat'] = 'Ugyldig lydfilformat. Bruk mp3 eller wav.';
$string['invalidvideoformat'] = 'Ugyldig videofilformat. Bruk mp4 eller webm.';
$string['couldnotsave'] = 'Klarte ikke å lagre fila.';
$string['couldnotcopy'] = 'Klarte ikke å kopiere fila.';

// Welcome messages
$string['welcomeheader'] = 'Welcome to the world of H5P!';
$string['welcomegettingstarted'] = 'To get started with H5P and Moodle take a look at our <a {$a->moodle_tutorial}>tutorial</a> and check out the <a {$a->example_content}>example content</a> at H5P.org for inspiration.<br>The most popuplar content types have been installed for your convenience!';
$string['welcomecommunity'] = 'We hope you will enjoy H5P and get engaged in our growing community through our <a {$a->forums}>forums</a> and chat room <a {$a->gitter}>H5P at Gitter</a>';
$string['welcomecontactus'] = 'If you have any feedback, don\'t hesitate to <a {$a}>contact us</a>. We take feedback very seriously and are dedicated to making H5P better every day!';
$string['missingmbstring'] = 'PHP-utvidelsen mbstring mangler. H5P trenger denne for å kunne virke';
$string['wrongversion'] = 'En ugyldig versjon av H5P-biblioteket {$a->%machineName} er brukt i innholdet. Innholdet bruker {$a->%contentLibrary}, mens det skal bruke {$a->%semanticsLibrary}.';
$string['invalidlibrary'] = 'H5P-biblioteket {$a->%library} brukt i innholdet er ugyldig';
