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
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @codingStandardsIgnoreFile
 */

defined('MOODLE_INTERNAL') || die();

$string['activitycompleted'] = 'Veikla užbaigta';
$string['adddrop'] = 'Pridėti numestuką';
$string['afterimport'] = 'Po importo';
$string['anysection'] = 'Bet kuris skyrius';
$string['anonymousgroup'] = 'Kita komanda';
$string['anonymousiomadcompany'] = 'Kita įmonė';
$string['anonymousiomaddepartment'] = 'Kitas departamentas';
$string['awardpoints'] = 'Suteikti taškų';
$string['badgetheme'] = 'Lygių ženkliukų tema';
$string['badgetheme_help'] = 'Ženkliukų tema apibrėžia numatytąją ženkliukų išvaizdą.';
$string['categoryn'] = 'Kategorija: {$a}';
$string['clicktoselectcourse'] = 'Spustelėkite norėdami pasirinkti kursą';
$string['clicktoselectgradeitem'] = 'Spustelėkite , kad pasirinktumėte vertinimo elementą';
$string['courseselector'] = 'Kursų parinkiklis';
$string['csvisempty'] = 'CSV failas yra tuščias.';
$string['csvline'] = 'Eilutė';
$string['csvfieldseparator'] = 'CSV laukų skyriklis';
$string['csvfile'] = 'CSV failas';
$string['csvfile_help'] = 'CSV faile turi būti stulpeliai __user__ ir __points__. Stulpelis __message__ yra neprivalomas ir gali būti naudojamas, kai įjungti pranešimai. Atkreipkite dėmesį, kad stulpelyje __user__ suprantami naudotojo ID, el. pašto adresai ir naudotojų vardai.';
$string['csvmissingcolumns'] = 'CSV trūksta šio (-ių) stulpelio (-ių): {$a}.';
$string['currentpoints'] = 'Dabartiniai taškai';
$string['currencysign'] = 'Taškų simbolis';
$string['currencysign_help'] = 'Naudodami šį nustatymą galite pakeisti taškų reikšmę. Jis bus rodomas šalia kiekvieno naudotojo turimų taškų skaičiaus kaip nuorodos į _experience points_ pakaitalas.

Pasirinkite vieną iš pateiktų simbolių arba įkelkite savo!';
$string['currencysignformhelp'] = 'Rekomenduojamas vaizdo aukštis yra 18 pikselių.';
$string['currencysignoverride'] = 'Taškų simbolio pakeitimas';
$string['currencysignxp'] = 'XP (patirties taškai)';
$string['custom'] = 'Pasirinktinis';
$string['dropcollected'] = 'Numestukas surinktas';
$string['dropherea'] = 'Numestukas: {$a}';
$string['dropenabled'] = 'Įjungta';
$string['dropenabled_help'] = 'Numestukas nesuteiks jokių taškų, jei jis nebus įjungtas.';
$string['dropname'] = 'Pavadinimas';
$string['dropname_help'] = 'Numestuko pavadinimas jūsų nuorodai. Jis nerodomas naudotojams.';
$string['droppoints'] = 'Taškai';
$string['droppoints_help'] = 'Taškų skaičius, kurį reikia skirti, kai randamas šis numestukas.';
$string['drops'] = 'Numestukai';
$string['dropsintro'] = 'Numestukai - tai tiesiogiai į turinį patalpinti kodo fragmentai, kurie suteikia taškų, kai su jais susiduria naudotojas.';
$string['drops_help'] = '
Vaizdo žaidimuose kai kurie veikėjai gali _drop_ daiktus ar patirties taškus ant žemės, kad žaidėjas galėtų juos pasiimti. Šie daiktai ir taškai paprastai vadinami numestukais.

Level Up XP sistemoje numestukai yra trumpieji kodai (pvz., `[xpdrop id=1 secret=abcdef]`), kuriuos dėstytojas gali įterpti į įprastą "Moodle" turinį. Naudotojui susidūrus su šiais numestukais, jie bus _picked up_ ir už juos bus skiriama tam tikra taškų suma.

Šiuo metu numestukai yra nematomi naudotojui ir už juos pasyviai skiriami taškai pirmą kartą su jais susidūrus.

Numestukai gali būti naudojami gudriai suteikiant taškus, kai mokinys naudoja tam tikro tipo turinį. Štai keletas idėjų:

- Į viktorinos atsiliepimus įterpkite numestuką, matomą tik už puikius rezultatus.
- Padėkite numestuką giliame turinyje, kad atlygintumėte už jo vartojimą
- Įdėkite numestuką į įdomią forumo diskusiją
- Įdėkite numestuką į sunkiai pasiekiamą pamokos modulio puslapį.

[More info](https://docs.levelup.plus/xp/docs/how-to/use-drops?ref=localxp_help)
';
$string['displaygroupidentity'] = 'Display teams identity';
$string['displayfirstnameinitiallastname'] = 'Display first name and initial (e.g. Sam H.)';
$string['editdrop'] = 'Edit drop';
$string['enablecheatguard'] = 'Enable cheat guard';
$string['enablecheatguard_help'] = 'The cheat guard prevents students from being rewarded once they reach certain limits.

[More info](https://docs.levelup.plus/xp/docs/getting-started/cheat-guard?ref=localxp_help)
';
$string['errorunknowncourse'] = 'Klaida: nežinomas kursas';
$string['errorunknowngradeitem'] = 'Klaida: nežinomas įvertinimo elementas';
$string['event_section_completed'] = 'Sekcija baigta';
$string['filtergradeitems'] = 'Filtruoti vertinamus elementus';
$string['filtershortcodesrequiredfordrops'] = 'Norint naudoti numestukus, reikia įdiegti ir įjungti įskiepį [Shortcodes]({$a->url}), kuris yra laisvai prieinamas iš [moodle.org]({$a->url}). Šis įskiepis taip pat atrakins [Level Up XP\'s shortcodes]({$a->shortcodesdocsurl}).';
$string['keeplogsdesc'] = 'Žurnalai atlieka svarbų vaidmenį įskiepyje. Jie naudojami
sukčiavimo apsaugai, naujausių apdovanojimų paieškai ir kai kuriems kitiems dalykams. Laiko mažinimas
kurį saugomi žurnalai, gali turėti įtakos taškų pasiskirstymui laike, todėl reikėtų elgtis atsargiai.';
$string['gradeitemselector'] = 'Vertinamojo objekto parinkiklis';
$string['gradeitemtypeis'] = 'Įvertis yra: {$a}';
$string['gradereceived'] = 'Gautas įvertis';
$string['gradesrules'] = 'Įverčių taisyklės';
$string['gradesrules_help'] = '
Toliau pateiktos taisyklės nustato, kada mokiniai gauna taškus už gautus įvertinimus.

Mokiniai gaus tiek taškų, kokie yra jų įvertinimai.
Už įvertinimą 5/10 ir 5/100 mokinys gaus 5 taškus.
Kai mokinio įvertinimas keičiasi kelis kartus, jis gaus taškų, lygių didžiausiam gautam įvertinimui.
Taškai iš mokinių niekada neatimami, o į neigiamus įvertinimus neatsižvelgiama.

Pavyzdys: Alisa pateikia užduotį ir gauna 40/100 balų. Programoje _Level Up XP_ Alisa gauna 40 taškų už savo įvertinimą.
Alisa dar kartą bando atlikti užduotį, tačiau šį kartą jos įvertinimas sumažinamas iki 25/100. Alisos taškai programoje _Level Up XP_ nepasikeičia.
Už paskutinį bandymą Alisa gauna 60/100 balų, ji gauna 20 papildomų taškų į _Level Up XP_, iš viso jos surinktų taškų suma yra 60.

[More at _Level Up XP_ documentation](https://docs.levelup.plus/xp/docs/how-to/grade-based-rewards?ref=localxp_help)
';
$string['groupanonymity'] = 'Anonimiškumas';
$string['groupanonymity_help'] = 'Šiuo nustatymu reguliuojama, ar dalyviai gali matyti komandų, kurioms jie nepriklauso, pavadinimus.';
$string['groupladder'] = 'Komandos lyderiai';
$string['groupladdercols'] = 'Stulpeliai';
$string['groupladdercols_help'] = 'Šis nustatymas pasako, kurie stulpeliai bus rodomi be komandų rangų ir pavadinimų.

__Points__ stulpelis vaizduoja komandos taškus.
Ši vertė galėjo būti kompensuota priklausomai nuo pasirinktos _Ranking strategy_.

Stulpelyje __Progress__ rodomas bendras komandos progresas, kai visi jos nariai pasiekia aukščiausią lygį.
Kitaip tariant, pažanga gali siekti 100 % tik tada, kai visi komandos nariai yra pasiekę aukščiausią lygį. Atkreipkite dėmesį, kad
likusių taškų skaičius, rodomas šalia pažangos stulpelio, gali būti klaidinantis, kai komandos nesubalansuotos ir taškai nekompensuojami,
nes komandoms, turinčioms daugiau narių, liks daugiau taškų nei kitoms, nors jų pažanga gali būti panaši.

Paspauskite CTRL arba CMD klavišą, jei norite pasirinkti daugiau nei vieną stulpelį arba nužymėti pasirinktą stulpelį.';
$string['groupladdersource'] = 'Sudarykite komandą naudojant';
$string['groupladdersource_help'] = 'Komandos lyderių lentoje rodomas mokinių taškų sumos reitingas.
Pasirinkta reikšmė lemia tai, pagal ką _Level Up XP_ grupuoja dalyvius.
Nustačius parinktį _Nothing_, komandų lyderių lentelė nebus pasiekiama.

Norėdami apriboti lyderių lentoje rodomas _Course groups_, galite sukurti naują grupavimą, apimantį atitinkamas grupes, ir kurso nustatymuose nustatyti, kad ši grupė būtų _Default grouping_.';
$string['groupname'] = 'Komandos pavadinimas';
$string['grouporderby'] = 'Reitingavimo strategija';
$string['grouporderby_help'] = 'Nustato, kuo remiantis sudaromas komandų reitingas.

Nustačius __Points__, komandos reitinguojamos pagal jų narių surinktų taškų sumą.

Nustačius __Points (with compensation)__, komandų, turinčių mažiau narių nei kitos, taškai kompensuojami pagal jų komandos narių vidurkį. Pavyzdžiui, jei komandai trūksta 3 narių, ji gauna taškus, lygius tris kartus didesniam jos narių vidurkiui. Taip sudaromas subalansuotas reitingas, kuriame visos komandos turi vienodas galimybes.

Nustačius __Progress__, komandos reitinguojamos pagal jų bendrą pažangą, kad visi jos nariai pasiektų aukščiausią lygį, nekompensuojant jų taškų. Galite norėti naudoti _Progress_, kai komandos yra nesubalansuotos, pavyzdžiui, kai vienos komandos turi daug daugiau narių nei kitos.';
$string['grouppoints'] = 'Taškai';
$string['grouppointswithcompensation'] = 'Taškai (su kompensavimu)';
$string['groupsourcecoursegroups'] = 'Kurso grupės';
$string['groupsourcecohorts'] = 'Kohortos';
$string['groupsourceiomadcompanies'] = 'IOMAD kompanijos';
$string['groupsourceiomaddepartments'] = 'IOMAD departamentai';
$string['groupsourcenone'] = 'Nieko, lyderių lenta išjungta';
$string['hidegroupidentity'] = 'Slėpti komandos identitetą';
$string['importcsvfile_help'] = '';
$string['importcsvintro'] = 'Norėdami importuoti taškus iš CSV failo, naudokite toliau pateiktą formą. Importas gali būti naudojamas taškams _increase_ arba pakeisti juos pateikta verte. Atkreipkite dėmesį, kad importuojant nenaudojamas tas pats formatas kaip eksportuojamoje ataskaitoje. Reikalaujamas formatas aprašytas [documentation]({$a->docsurl}), be to, [here]({$a->sampleurl}) galima rasti pavyzdinį failą.';
$string['importpreview'] = 'Importo peržiūra';
$string['importpreviewintro'] = 'Čia pateikiama peržiūra, kurioje rodomi pirmieji {$a} įrašai iš visų importuotinų. Peržiūrėkite ir patvirtinkite, kai būsite pasiruošę viską importuoti.';
$string['importpoints'] = 'Importo taškai';
$string['importpointsaction'] = 'Taškų importo veiksmas';
$string['importpointsaction_help'] = 'Nustato, ką daryti su CSV faile rastais taškais.

**Set as total**

Šie taškai pakeis dabartinius mokinio taškus ir taps naujuoju taškų skaičiumi. Vartotojai nebus informuojami ir žurnaluose nebus jokių įrašų.

**Increase**

Taškai rodo, kiek taškų reikia skirti mokiniui. Kai ši funkcija įjungta, gavėjams bus siunčiamas pranešimas su pasirenkamuoju _message_ iš CSV failo. Į žurnalus taip pat bus įtrauktas įrašas _Manual award_.
';
$string['importresults'] = 'Importo rezultatai';
$string['importresultsintro'] = 'Sėkmingai **importuota {$a->successful} įrašų** iš viso **{$a->total}**. Jei kai kurių įrašų nepavyko importuoti, toliau bus rodoma išsami informacija.';
$string['importsettings'] = 'Importo nustatymai';
$string['increaseby'] = 'Padidinti ';
$string['increaseby_help'] = 'Mokiniui skiriamų taškų skaičius.';
$string['increasemsg'] = 'Neprivalomas pranešimas';
$string['increasemsg_help'] = 'Pateikus pranešimą, jis įtraukiamas į žinutes.';
$string['invalidpointscannotbenegative'] = 'Taškai negali būti neigiami.';
$string['levelbadges'] = 'Lygių ženkliukų pakeitimas';
$string['levelbadges_help'] = 'Įkelkite vaizdus, kad pakeistumėte ženkliukų temos pateiktus dizainus.';
$string['levelup'] = 'Pakelk lygį!'; // The action, not the brand!
$string['manualawardsubject'] = 'Jūs gavote {$a->taškai} taškų!';
$string['manualawardnotification'] = '{$a->points} taškų jums skyrė {$a->fullname}.';
$string['manualawardnotificationwithcourse'] = 'Kurse {$a->coursename} {$a->fullname} jums buvo suteikta {$a->points} taškų.';
$string['manuallyawarded'] = 'Skirta rankiniu būdu';
$string['maxn'] = 'Maksimaliai: {$a}';
$string['maxpointspertime'] = 'Maksimalus taškų skaičius per laikotarpį';
$string['maxpointspertime_help'] = 'Maksimalus taškų skaičius, kurį galima surinkti per nurodytą laikotarpį. Kai ši reikšmė yra tuščia arba lygi nuliui, ji netaikoma.';
$string['messageprovider:manualaward'] = 'XP taškai suteikti rankiniu būdu';
$string['missingpermssionsmessage'] = 'Neturite reikiamų leidimų, kad galėtumėte pasiekti šį turinį.';
$string['mylevel'] = 'Mano lygis';
$string['navdrops'] = 'Numestukai';
$string['navgroupladder'] = 'Komandos lyderiai';
$string['pluginname'] = 'Level Up XP+';
$string['points'] = 'Taškai';
$string['previewmore'] = 'Peržiūrėti daugiau';
$string['privacy:metadata:log'] = 'Saugo įvykių žurnalą';
$string['privacy:metadata:log:points'] = 'Už įvykį skiriami taškai';
$string['privacy:metadata:log:signature'] = 'Kai kurie įvykių duomenys';
$string['privacy:metadata:log:time'] = 'Įvykio data';
$string['privacy:metadata:log:type'] = 'Įvykio tipas';
$string['privacy:metadata:log:userid'] = 'Taškus surinkęs naudotojas';
$string['progressbarmode'] = 'Rodyti pažangą siekiant';
$string['progressbarmode_help'] = '
Kai nustatyta reikšmė _The next level_, pažangos juostoje rodoma naudotojo pažanga pereinant į kitą lygį.

Nustačius parametrą _The ultimate level_, pažangos juostoje bus rodoma, kiek procentų naudotojas pasiekė paskutinį galimą lygį.

Bet kuriuo atveju, pasiekus paskutinį lygį, pažangos juosta liks pilna.';
$string['progressbarmodelevel'] = 'Kitas lygis';
$string['progressbarmodeoverall'] = 'Aukščiausias lygis';
$string['reallyedeletedrop'] = 'Ar tikrai norite ištrinti šį numestuką? Šis veiksmas negrįžtamas.';
$string['reason'] = 'Priežastis';
$string['reasonlocation'] = 'Vieta';
$string['reasonlocationurl'] = 'Nuorodos URL';
$string['ruleactivitycompletion'] = 'Veiklos užbaigimas';
$string['ruleactivitycompletion_help'] = '
Ši sąlyga tenkinama, kai veikla ką tik buvo pažymėta kaip užbaigta, jei užbaigimas nebuvo pažymėtas kaip nesėkmingas.

Pagal standartinius "Moodle" veiklos užbaigimo nustatymus mokytojai gali visiškai kontroliuoti sąlygas.
reikalingų veiklai _complete_. Jas galima nustatyti atskirai kiekvienai kurso veiklai ir
būti pagrįstos data, įvertinimu ir t. t. Taip pat galima leisti mokiniams rankiniu būdu pažymėti veiklą
kaip užbaigtą.

Už šią sąlygą mokinys bus apdovanotas tik vieną kartą.';
$string['ruleactivitycompletion_link'] = 'Activity_completion';
$string['ruleactivitycompletiondesc'] = 'Veikla arba resursas buvo sėkmingai užbaigti';
$string['ruleactivitycompletioninfo'] = 'Ši sąlyga sutampa, kai mokinys baigia veiklą ar resursą.';
$string['rulecmname'] = 'Veiklos pavadinimas';
$string['rulecmname_help'] = 'Ši sąlyga įvykdoma, kai įvykis įvyksta nurodytu pavadinimu pavadintoje veikloje.

Pastabos:

- Lyginant neatsižvelgiama į mažąsias ir didžiąsias raides.
- Tuščia reikšmė niekada nesutaps.
- Apsvarstykite galimybę naudoti **contains**, kai veiklos pavadinime yra [multilang](https://docs.moodle.org/en/Multi-language_content_filter) tags.';
$string['rulecmnamedesc'] = 'Veiklos pavadinimas {$a->compare} \'{$a->value}\'.';
$string['rulecmnameinfo'] = 'Nurodomas veiklų arba resursų, kuriuose turi būti atliekamas veiksmas, pavadinimas.';
$string['rulecoursecompletion'] = 'Kurso užbaigimas';
$string['rulecoursecompletion_help'] = 'Ši taisyklė įvykdoma, kai studentas baigia kursą.

__Note:__ Mokiniai taškus gaus ne iš karto, "Moodle" užtrunka šiek tiek laiko, kol "Moodle" apdoroja kurso užbaigimą. Kitaip tariant, tam reikia paleisti _cron_.';
$string['rulecoursecompletion_link'] = 'Course_completion';
$string['rulecoursecompletiondesc'] = 'Kursas buvo baigtas';
$string['rulecoursecompletioncoursemodedesc'] = 'Kursas buvo baigtas';
$string['rulecoursecompletioninfo'] = 'Ši sąlyga sutampa, kai mokinys baigia kursą.';
$string['rulecourse'] = 'Kursas';
$string['rulecourse_help'] = 'Ši sąlyga patenkinama, kai įvykis įvyksta nurodytame kurse.

Jis prieinamas tik tada, kai įskiepis naudojamas visai svetainei. Kai įskiepis naudojamas vienam kursui, ši sąlyga tampa neveiksminga.';
$string['rulecoursedesc'] = 'Kursas yra: {$a}';
$string['rulecourseinfo'] = 'Ši sąlyga reikalauja, kad veiksmas vyktų tam tikru metu.';
$string['rulegradeitem'] = 'Vertinamas elementas';
$string['rulegradeitem_help'] = 'Ši sąlyga įvykdoma, kai pateikiamas nurodyto vertinimo elemento įvertinimas.';
$string['rulegradeitemdesc'] = 'Vertinamas elementas yra \'{$a->gradeitemname}\'';
$string['rulegradeitemdescwithcourse'] = 'Vertinamas elementas yra: \'{$a->gradeitemname}\', \'{$a->coursename}\'';
$string['rulegradeiteminfo'] = 'Ši sąlyga sutampa su konkretaus elemento gautais įvertinimais.';
$string['rulegradeitemtype'] = 'Vertinimo tipas';
$string['rulegradeitemtype_help'] = 'Ši sąlyga tenkinama, kai vertinimo elementas yra reikalaujamo tipo. Pasirinkus veiklos tipą, atitiktų bet koks iš šio veiklos tipo kilęs įvertinimas.';
$string['rulegradeitemtypedesc'] = 'Įvertis yra: \'{$a}\'';
$string['rulegradeitemtypeinfo'] = 'Ši sąlyga atitinka, kai vertinimo elementas yra reikalaujamo tipo.';
$string['rulesectioncompletion'] = 'Skyriaus užbaigimas';
$string['rulesectioncompletion_help'] = 'Ši sąlyga yra tenkinama, jei veikla yra baigta ir ši veikla yra paskutinė veikla, kuri turi būti baigta skyriuje.';
$string['rulesectioncompletioninfo'] = 'Ši sąlyga tenkinama, kai mokinys atlieka visas skyriaus užduotis.';
$string['rulesectioncompletiondesc'] = 'Skyrius, kurį reikia užbaigti, yra toks \'{$a->sectionname}\'';
$string['ruleusergraded'] = 'Gautas įvertis';
$string['ruleusergraded_help'] = 'Ši sąlyga tenkinama, kai:

* Įvertinimas gautas už veiklą
* Už veiklą nurodytas teigiamas įvertinimas
* Įvertinimas atitiko teigiamą įvertinimą
* Įvertinimas _not_ paremtas reitingais (pvz., forumuose)
* Įvertinimas pagrįstas taškais, o ne skale

Už šią sąlygą mokinys bus apdovanotas tik vieną kartą.';
$string['ruleusergradeddesc'] = 'Besimokantysis gavo teigiamą įvertinimą';
$string['sendawardnotification'] = 'Siųsti pranešimą apie apdovanojimą';
$string['sendawardnotification_help'] = 'Įjungus šią funkciją, besimokantieji gaus pranešimą, kad jiems buvo skirti taškai. Pranešime bus nurodytas jūsų vardas ir pavardė, taškų suma ir kurso pavadinimas, jei toks yra.';
$string['shortcode:xpdrop'] = 'Į turinį įtraukite numestuką.';
$string['shortcode:xpteamladder'] = 'Parodykite dalį komandos reitingų.';
$string['shortcode:xpteamladder_help'] = '
Pagal numatytuosius nustatymus bus rodoma komandos lyderių lentelės dalis aplink dabartinį naudotoją.

```
[xpteamladder]
```

Jei norite, kad būtų rodomos 5 geriausios komandos, o ne komandos, esančios šalia dabartinio naudotojo, nustatykite parametrą `top`. Galite pasirinktinai nustatyti rodomų komandų skaičių, suteikdami `top` reikšmę, pvz: `top=20`.

```
[xpteamladder top]
[xpteamladder top=15]
```

Po lentele automatiškai bus rodoma nuoroda į visą lyderių lentelę, jei bus rodoma daugiau rezultatų; jei tokios nuorodos rodyti nenorite, pridėkite argumentą `hidelink`.

```
[xpteamladder hidelink]
```

Pagal numatytuosius nustatymus lentelėje nėra pažangos stulpelio, kuriame rodoma pažangos juosta. Jei toks stulpelis pasirinktas papildomų stulpelių sąraše lyderių lentelės nustatymuose, galite naudoti argumentą `withprogress`, kad jis būtų rodomas.

```
[xpteamladder withprogress]
```

Atkreipkite dėmesį, kad jei dabartinis naudotojas priklauso kelioms komandoms, įskiepis kaip nuorodą naudos geriausią reitingą turinčią komandą.
';
$string['sectioncompleted'] = 'Užbaigtas skyrius';
$string['sectiontocompleteis'] = 'Skyrius, kurį reikia užpildyti, yra toks: {$a}';
$string['studentsearnpointsforgradeswhen'] = 'Besimokantieji gauna taškų už pažymius, kai:';
$string['unabletoidentifyuser'] = 'Nepavyksta nustatyti naudotojo.';
$string['unknowngradeitemtype'] = 'Nežinomas tipas ({$a})';
$string['unknownsectiona'] = 'Nežinomas skyrius ({$a})';
$string['uptoleveln'] = 'Iki lygio {$a}';
$string['team'] = 'Komanda';
$string['teams'] = 'Komandos';
$string['themestandard'] = 'Standartas';
$string['theyleftthefollowingmessage'] = 'Jie paliko tokią žinutę:';
$string['timeformaxpoints'] = 'Maksimalaus taškų skaičiaus siekimo laikotarpis';
$string['timeformaxpoints_help'] = 'Laikotarpis (sekundėmis), per kurį naudotojas negali gauti daugiau nei tam tikrą taškų skaičių.';
$string['visualsintro'] = 'Pritaikykite lygių ir taškų išvaizdą.';

// Deprecated since v1.7.
$string['enablegroupladder'] = 'Įjungti grupių reitingą';
$string['enablegroupladder_help'] = 'Kai ši funkcija įjungta, mokiniai gali peržiūrėti kurso grupių lyderių lentelę. Grupės taškai apskaičiuojami pagal kiekvienos grupės narių surinktus taškus. Šiuo metu ši funkcija taikoma tik tada, kai įskiepis naudojamas vienam kursui, o ne visai svetainei.';

// Deprecated since v1.10.2.
$string['for2weeks'] = '2 savaitėms';
$string['for3months'] = '3 mėnesiams';
