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
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @codingStandardsIgnoreFile
 */

defined('MOODLE_INTERNAL') || die();

$string['activitycompleted'] = 'Aktiviteetin suorittaminen';
$string['adddrop'] = 'Lisää droppi';
$string['afterimport'] = 'Tuonnin jälkeen';
$string['anonymousgroup'] = 'Joku muu ryhmä';
$string['anonymousiomadcompany'] = 'Joku muu yritys';
$string['anonymousiomaddepartment'] = 'Joku muu osasto';
$string['anysection'] = 'Mikä tahansa osio';
$string['awardpoints'] = 'Myönnä pisteet';
$string['badgetheme'] = 'Tason teema';
$string['badgetheme_help'] = 'Teema määrittää tasomerkkien ulkoasun.';
$string['categoryn'] = 'Kategoria: {$a}';
$string['clicktoselectcourse'] = 'Klikkaa valitaksesi kurssi';
$string['clicktoselectgradeitem'] = 'Klikkaa valitaksesi aktiviteetti';
$string['copypastedropsnippet'] = 'Kopioi ja liitä seuraava koodinpätkä johonkin kurssialueen sisältöön.';
$string['courseselector'] = 'Kurssivalitsin';
$string['csvfieldseparator'] = 'Erotinmerkki';
$string['csvfile'] = 'CSV-tiedosto';
$string['csvfile_help'] = 'CSV-tiedoston tulee sisältää sarakkeet __user__ ja __points__. Sarake __message__ on valinnainen ja sitä voidaan käyttää, kun ilmoitukset ovat käytössä. Huomaa, että __user__-sarake ymmärtää sähköpostiosoitteet ja käyttäjätunnukset.';
$string['csvisempty'] = 'CSV-tiedosto on tyhjä.';
$string['csvline'] = 'Rivi';
$string['csvmissingcolumns'] = 'CSV-tiedostosta puuttuu seuraava sarake/seuraavat sarakkeet: {$a}';
$string['currencysign'] = 'Pistesymbolit';
$string['currencysignformhelp'] = 'Suositeltava kuvakorkeus on 18 pikseliä.';
$string['currencysignoverride'] = 'Pistesymbolit';
$string['currencysignoverride_help'] = 'Lataa itse tekemäsi pistesymboli.

Suositeltava kuvakorkeus on 18 pikseliä ja suositeltavat tiedostomuodot JPEG, PNG ja SVG.';
$string['currencysignxp'] = 'XP (Kokemuspisteet)';
$string['currencysign_help'] = 'Tällä asetuksella voit muuttaa pistesymbolin teeman. Pistesymboli näkyy kunkin käyttäjän pistemäärän oikealla puolella. Oletuksena käytössä on xp-merkintä (Experience points), joka viittaa kokemuspisteisiin. Voit korvata xp-merkinnän jollakin alasvetovalikosta löytyvällä symbolilla.';
$string['currentpoints'] = 'Nykyiset pisteet';
$string['custom'] = 'Mukautettu';
$string['displayfirstnameinitiallastname'] = 'Näytä etunimi ja sukunimen alkukirjain (esim. Oili O.)';
$string['displaygroupidentity'] = 'Näytä ryhmien identiteetti';
$string['dropcollected'] = 'Droppi poimittu';
$string['dropenabled'] = 'Käytössä';
$string['dropenabled_help'] = 'Dropeista ei myönnetä pisteitä, elleivät ne ole käytössä.';
$string['dropherea'] = 'Droppi: {$a}';
$string['dropname'] = 'Nimi';
$string['dropname_help'] = 'Dropin nimi. Tätä ei näytetä käyttäjille.';
$string['droppoints'] = 'Pisteet';
$string['droppoints_help'] = 'Pisteiden määrä, joka myönnetään, kun opiskelija tulee tämän dropin kohdalle.';
$string['drops'] = 'Dropit';
$string['dropsintro'] = 'Dropit ovat suoraan kurssialueen sisältöön sijoitettuja koodinpätkiä, jotka antavat pisteitä, kun käyttäjä osuu niiden kohdalle.';
$string['drops_help'] = 'Videopeleissä, hahmot voivat _pudottaa_ esineitä tai kokemuspisteitä maahan, jotka pelaaja voi poimia. Näitä esineitä ja pisteitä kutsutaan dropeiksi.

Level Up XP:ssä dropit ovat koodinpätkiä (esim. `[xpdrop id=1 secret=abcdef]`), jotka opettaja voi sijoittaa mihin tahansa kurssialueen sisältöön. Kun opiskelija osuus näiden droppien kohdalle, poimii hän ne ja saa niistä tietyn määrän pisteitä.

Dropit ovat opiskelijalta näkymättömissä ja pisteet myönnetään automaattisesti yhden kerran. Dropit myöntävät siis pisteitä, kun opiskelija lukee esimerkiksi jotakin sisältöä kurssialueella ja tulee tietyn luvun kohdalle.

Tässä pari esimerkkiä: Aseta droppi tentti-aktiviteetin välittömään palautteeseen, joka näytetään ainoastaan täydet tenttipisteet saaneille - Aseta droppi mielenkiintoiselle keskustelualueelle – Aseta droppi esimerkiksi Kirja-aktiviteetin viimeiseen lisätietolukuun.';
$string['editdrop'] = 'Muokkaa droppia';
$string['enablecheatguard'] = 'Ota huijauksenesto käyttöön';
$string['enablecheatguard_help'] = 'Huijauksenesto estää opiskelijoita saamasta palkkioita toistamalla samaa toimintoa useamman kerran peräkkäin lyhyen ajan sisällä.';
$string['errorunknowncourse'] = 'Virhe: Tuntematon kurssi';
$string['errorunknowngradeitem'] = 'Virhe: Tuntematon arvosanan kohde';
$string['event_section_completed'] = 'Osio suoritettu';
$string['filtergradeitems'] = 'Hae arvioitavia kohteita';
$string['for2weeks'] = 'Kahden viikon ajan';
$string['for3months'] = 'Kolmen kuukauden ajan';
$string['gradeitemselector'] = 'Aktiviteettivalitsin';
$string['gradeitemtypeis'] = 'Arvosanan vaativa aktiviteettityyppi on {$a}';
$string['gradereceived'] = 'Arvosana saatu';
$string['gradesrules'] = 'Arvosanasäännöt';
$string['gradesrules_help'] = 'Opiskelijat saavat saavuttamansa arvosanan verran pisteitä. Arvosana 5/10 ja arvosana 5/100 antavat molemmat 5 pistettä. Kun opiskelijan arvosana muuttuu, hän ansaitsee saamansa enimmäisarvosanan verran pisteitä. Pisteitä ei koskaan oteta pois opiskelijoilta, ja negatiiviset arvosanat jätetään huomiotta.

Esimerkki: Alice lähettää tehtävän ja saa arvosanan 40/100. Alice saa tällöin arvosanastaan 40 pistettä. Alice yrittää uudelleen tehtäväänsä, mutta tällä kertaa hänen arvosanansa on 25/100. Alicen pisteet eivät muutu. Viimeisestä yrityksestään Alice saa pisteet 60/100, joten hän ansaitsee 20 lisäpistettä. Hänen ansaittujen pisteiden yhteismäärä on tällöin 60.

[More at _Level Up XP_ documentation](https://docs.levelup.plus/xp/docs/how-to/grade-based-rewards?ref=blockxp_help)';
$string['groupanonymity'] = 'Anonymiteetti';
$string['groupanonymity_help'] = 'Tämä asetus määrittää, näkevätkö osallistujat niiden ryhmien nimet, joihin he eivät itse kuulu.';
$string['groupladder'] = 'Ryhmien tulostaulu';
$string['groupladdercols'] = 'Lisäsarakkeet';
$string['groupladdercols_help'] = 'Tämä asetus määrittää, mitkä lisäsarakkeet näytetään tulostaulusivulla.

”Pisteet”-sarake näyttää ryhmän pisteet. Tämä arvo on saatettu kompensoida asetusten kohdassa Ranking-strategia.

”Eteneminen”-sarake näyttää ryhmän yleisen etenemisen kohti viimeistä tasoa. Toisin sanoen ryhmä voi saavuttaa 100 % edistymisen vain, kun kaikki ryhmän jäsenet ovat saavuttaneet viimeisen tason.

Huom! Edistymispalkin vieressä näkyvä jäljellä olevien pisteiden määrä voi olla hämmentävää silloin, kun ryhmät ovat epätasapainossa ja pisteitä ei kompensoida. Ryhmillä, joissa on enemmän jäseniä, on enemmän pisteitä jäljellä kuin muilla, vaikka heidän etenemisensä voi olla samalla tasolla.

Paina CTRL tai CMD -painiketta samalla kun valitset tietoja, jos haluat valita useamman kuin yhden vaihtoehdon. Samalla tavalla CTRL tai CMD pohjassa voit poistaa valintoja.';
$string['groupladdersource'] = 'Käytä opiskelijoiden ryhmittelyyn';
$string['groupladdersource_help'] = 'Ryhmien tulostaulukossa näkyy osallistujien pisteiden yhteispistejärjestys. Alasvetovalikosta valitsemasi arvo määrittää, mitä LevelUp käyttää osallistujien ryhmittelyyn. Kun asetus on "Ei mitään", ryhmien tulostaulukko ei ole käytettävissä.

Voit rajoittaa tulostaulukossa näkyviä ryhmiä luomalla ryhmittelyn, joka sisältää haluamasi ryhmät, ja asettaa tämän ryhmittelyn oletusryhmittelyksi kurssialueen asetuksissa.';
$string['groupname'] = 'Ryhmän nimi';
$string['grouporderby'] = 'Ranking-strategia';
$string['grouporderby_help'] = 'Tämä asetus määrittää, mikä on ryhmien sijoituksen perusta.

Kun asetukseksi on valittu ”Pisteet”, ryhmien sijoitus määräytyy sen jäsenten pisteiden absoluuttisen summan perusteella.

Kun asetukseksi on valittu ”Pisteet (hyvityksellä)” saavat ryhmät, joissa on vähemmän jäseniä kuin muissa ryhmissä, kompensaatiopisteitä. Kompensaatiopisteet lasketaan ryhmän jäsenten pisteiden keskiarvon perusteella. Esimerkiksi, jos ryhmässä on kolme (3) jäsentä vähemmän kuin muissa ryhmissä, saa ryhmä lisäpisteitä kolme kertaa heidän pistekeskiarvonsa verran. Tämä luo tilanteen, jossa kaikilla ryhmillä on yhtäläiset mahdollisuudet kerätä pisteitä.

Kun asetukseksi on valittu ”Edistyminen”, ryhmät luokitellaan niiden yleisen etenemisen perusteella, jossa kaikki ryhmän jäsenet tavoittelevat viimeistä tasoa ilman pistekompensaatiota. Tämä toimii erityisesti silloin, kun joissakin ryhmissä on paljon enemmän jäseniä kuin toisissa.';
$string['grouppoints'] = 'Pisteet';
$string['grouppointswithcompensation'] = 'Pisteet (hyvityksellä)';
$string['groupsourcecohorts'] = 'Kohortteja (ei käytössä)';
$string['groupsourcecoursegroups'] = 'Kurssialueen ryhmiä';
$string['groupsourcenone'] = 'Ei mitään, ryhmien tulostaulua ei näytetä';
$string['hidegroupidentity'] = 'Piilota ryhmien identiteetti';
$string['importpoints'] = 'Tuo pisteet';
$string['importpointsaction'] = 'Tuotavien pisteiden toiminta';
$string['importpointsaction_help'] = 'Tämä asetus määrittää sen, mitä CSV-tiedostossa olevilla pisteillä tehdään.

 **Kokonaispisteet** (Set as total)

Pisteet ylikirjoittavat opiskelijan nykyiset pisteet, jolloin tiedostolla tuotavista pisteistä tulee opiskelijan uudet kokonaispisteet. Opiskelijoille ei ilmoiteta tästä muutoksesta, eikä lokeihin tule tästä merkintöjä.

 **Lisääntyvä** (Increase)

Pisteet eivät ylikirjoita opiskelijan nykyisiä pisteitä, vaan CSV-tiedoston pisteet lisätään olemassa olevien pisteiden päälle. Opiskelijoille ilmoitetaan tästä muutoksesta. Ilmoitus sisältää valinnaisen viestin . Tästä tulee merkintä myöskin lokitietoihin.';
$string['importpreview'] = 'Tuonnin esikatselu';
$string['importpreviewintro'] = 'Tässä esikatselussa näet kymmenen (10) ensimmäistä tietuetta kaikista tuotavista tietueista. Tarkista tiedot ja vahvista, kun olet valmis myöntämään tuotavat pisteet.';
$string['importresults'] = 'Tuonnin tulokset';
$string['importresultsintro'] = 'Onnistuneesti **tuotu {$a->successful} pisteytystä** kokonaismäärästä **{$a->total}**. Jos joitain pisteytyksiä ei voitu tuoda, niiden tiedot näytetään alla.';
$string['importsettings'] = 'Tuonnin asetukset';
$string['increaseby'] = 'Lisää';
$string['increaseby_help'] = 'Opiskelijalle myönnettävien pisteiden määrä';
$string['increasemsg'] = 'Valinnainen viesti';
$string['increasemsg_help'] = 'Kun viesti on kirjoitettu, se lisätään ilmoitukseen.';
$string['invalidpointscannotbenegative'] = 'Pisteet eivät voi olla negatiivisia.';
$string['manualawardnotification'] = 'Sinulle myönnettiin {$a->points} pistettä, myöntäjänä {$a->fullname}.';
$string['manualawardnotificationwithcourse'] = 'Sinulle myönnettiin {$a->points} pistettä, myöntäjänä {$a->fullname} kurssilla {$a->coursename}.';
$string['manualawardsubject'] = 'Sinulle myönnettiin {$a->points} pistettä!';
$string['manuallyawarded'] = 'Manuaalisesti myönnetty';
$string['maxn'] = 'Max: {$a}';
$string['maxpointspertime'] = 'Max. pisteet aikarajan sisällä';
$string['maxpointspertime_help'] = 'Pisteiden enimmäismäärä, joka voidaan ansaita määritellyn ajanjakson aikana. Kun tämä arvo on tyhjä tai yhtä suuri kuin nolla, sitä ei käytetä.';
$string['messageprovider:manualaward'] = 'Level Up XP pisteet manuaalisesti myönnetty';
$string['missingpermssionsmessage'] = 'Sinulla ei ole pääsyoikeutta tähän sisältöön.';
$string['mylevel'] = 'Minun tasoni';
$string['navdrops'] = 'Dropit';
$string['navgroupladder'] = 'Ryhmien tulostaulu';
$string['points'] = 'Pisteet';
$string['previewmore'] = 'Esikatsele lisää';
$string['progressbarmode'] = 'Näytä edistyminen kohti';
$string['progressbarmodelevel'] = 'Seuraavaa tasoa';
$string['progressbarmodeoverall'] = 'Viimeistä tasoa';
$string['progressbarmode_help'] = 'Kun asetuksesi on valittu ”Seuraavaa tasoa”, edistymispalkki näyttää käyttäjälle vaadittavan pistemäärän seuraavalle tasolle pääsemiseksi.

Kun asetukseksi on valittu ”Viimeistä tasoa”, edistymispalkki näyttää vaadittavan pistemäärän viimeisen tason saavuttamiseksi.

Kummassakin tapauksessa edistymispalkki tulee täyteen, kun viimeinen taso saavutetaan.';
$string['reallyedeletedrop'] = 'Oletko varma, että haluat poistaa tämän dropin? Tätä toimintoa ei voi peruuttaa.';
$string['ruleactivitycompletion'] = 'Aktiviteetin suorittaminen';
$string['ruleactivitycompletiondesc'] = 'Aktiviteetti suoritettiin onnistuneesti';
$string['ruleactivitycompletioninfo'] = 'Tämä ehto edellyttää, että opiskelija suorittaa tietyn aktiviteetin tai aineiston.';
$string['ruleactivitycompletion_help'] = 'Tämä ehto täyttyy, kun aktiviteetti on suoritettu kokonaan loppuun hyväksytysti. Opettajana määrittelet suorittamiseen vaadittavat ehdot, jotka voivat perustua päivämäärään, arvosanaan jne. Voit myös antaa opiskelijoille mahdollisuuden merkitä itse aktiviteetti tehdyksi. Tämä ehto antaa opiskelijalle pisteitä vain kerran.';
$string['rulecmname'] = 'Aktiviteetin nimi';
$string['rulecmnameinfo'] = 'Tämä ehto edellyttää, että toiminto tapahtuu tietyn nimisessä aktiviteetissa tai aineistossa.';
$string['rulecmname_help'] = 'Tämä ehto täyttyy, kun tapahtuma tapahtuu aktiviteetissa, joka on nimetty määritetyllä tavalla.

Huom!:

- Kirjainkoolla ei ole merkitystä
- Tyhjä arvo ei koskaan täsmää
- Harkitse **sisältää** käyttämistä, kun toiminto sisältää [multilang](https://docs.moodle.org/en/Multi-language_content_filter) tägejä.';
$string['rulecourse'] = 'Kurssi';
$string['rulecoursecompletion'] = 'Kurssin suorittaminen';
$string['rulecoursecompletioncoursemodedesc'] = 'Kurssi suoritettu';
$string['rulecoursecompletiondesc'] = 'Kurssi suoritettiin';
$string['rulecoursecompletioninfo'] = 'Tämä ehto edellyttää, että opiskelija suorittaa koko kurssin.';
$string['rulecoursecompletion_help'] = 'Tämä sääntö täyttyy, kun opiskelija suorittaa koko kurssin. __Huom:__ Opiskelijat eivät saa heti pisteitään, vaan kestää hetken, kun Moodle käsittelee suorituksia. Toisin sanoen tämä vaatii _cron_-ajon.';
$string['rulegradeitem'] = 'Tietty aktiviteetti, joka vaatii arvosanan';
$string['rulegradeitemdesc'] = 'Aktiviteetti on \'{$a->gradeitemname}\'';
$string['rulegradeiteminfo'] = 'Tämä ehto edellyttää arvosanan saamista tietystä arvioitavasta kohteesta.';
$string['rulegradeitemtype'] = 'Aktiviteettityyppi, joka vaatii arvosanan';
$string['rulegradeitemtypeinfo'] = 'Tämä ehto edellyttää arvosanan saamista tietystä aktiviteettityypistä.';
$string['rulegradeitemtype_help'] = 'Tämä ehto täyttyy, kun arviointikohde on vaadittua aktiviteettityyppiä. Kun aktiviteettityyppi valitaan, mikä tahansa tästä aktiviteettityypistä peräisin oleva arvosana antaa opiskelijalle pisteitä.';
$string['rulegradeitem_help'] = 'Tämä ehto täyttyy, kun opiskelija saa arvosanan tietystä arvioitavasta kohteesta.';
$string['rulesectioncompletion'] = 'Osion suorittaminen';
$string['rulesectioncompletiondesc'] = 'Osio suoritettu \'{$a->Osion nimi}\'';
$string['rulesectioncompletioninfo'] = 'Tämä ehto edellyttää, että opiskelija suorittaa kokonaisen osion.';
$string['rulesectioncompletion_help'] = 'Tämä ehto täyttyy, kun osion viimeinen aktiviteetti on suoritettu onnistuneesti.';
$string['ruleusergraded'] = 'Arvosana saatu';
$string['sectioncompleted'] = 'Osio suoritettu';
$string['sectiontocompleteis'] = 'Osio suoritettu {$a}';
$string['sendawardnotification'] = 'Lähetä ilmoitus pisteiden myöntämisestä';
$string['sendawardnotification_help'] = 'Kun tämä on käytössä, opiskelija saa ilmoituksen, että hänelle on myönnetty pisteitä. Viesti sisältää nimen, pisteiden määrän, kurssialueen nimen sekä valinnaisen CSV-tiedostossa olevan viestin.';
$string['studentsearnpointsforgradeswhen'] = 'Opiskelijat ansaitsevat pisteitä arvosanoista, kun:';
$string['team'] = 'Ryhmä';
$string['teams'] = 'Ryhmät';
$string['themestandard'] = 'Tähdet (oletus, suositeltu)';
$string['theyleftthefollowingmessage'] = 'Saamasi viesti on luettavissa alla:';
