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

$string['activitycompleted'] = 'Activité achevée';
$string['anonymousgroup'] = 'Une autre équipe';
$string['anonymousiomadcompany'] = 'Une autre société';
$string['anonymousiomaddepartment'] = 'Un autre département';
$string['badgetheme'] = 'Thème des badges';
$string['badgetheme_help'] = 'Un thème de badge défini l\'apparence par défaut des badges de niveau.';
$string['clicktoselectcourse'] = 'Cliquer pour choisir un cours';
$string['courseselector'] = 'Selecteur de cours';
$string['currencysign'] = 'Symbole des points';
$string['currencysign_help'] = 'Ce paramètre permet de change la signification des points. Il sera affiché à côté des points des utilisateurs à la place du traditionnel _points d\'expérience_.

Par exemple, vous pouvez utiliser l\'image d\'une carotte de manière à ce que les utilisateurs reçoivent des carottes comme récompense pour leurs actions.';
$string['currencysignformhelp'] = 'L\'image uploadée ici sera affichée à côté des points à la place de la référence aux points d\'expérience. La taille recommandée est de 18 pixels.';
$string['displaygroupidentity'] = 'Afficher l\'identité des équipes';
$string['enablecheatguard'] = 'Activer la mise en garde sur la triche';
$string['enablecheatguard_help'] = 'La mise en garde sur la triche empêche les étudiants d\'être récompensés une fois qu\'ils atteignent une certaine limite.';
$string['errorunknowncourse'] = 'Erreur: cours inconnu';
$string['for2weeks'] = 'Pour 2 semaines';
$string['for3months'] = 'Pour 3 mois';
$string['gradereceived'] = 'Note reçue';
$string['groupanonymity'] = 'Anonymat';
$string['groupanonymity_help'] = 'Ce paramètre contrôle si les participants peuvent voir le nom des équipes dont ils ne font pas partie.';
$string['groupladder'] = 'Echelle d\'équipe';
$string['groupladdersource'] = 'Grouper les édudiants par';
$string['groupladdersource_help'] = 'L\'échelle d\'équipe affiche un classement des points combinés des étudiants d\'une même équipe.
La valeur choisie détermine ce que _Level up!_ utilise pour grouper les étudiants.
Quand la valeur est _Rien_ l\'échelle d\'équipe n\'est pas active.';
$string['groupsourcecohorts'] = 'Cohortes';
$string['groupsourcecoursegroups'] = 'Groupes de cours';
$string['groupsourceiomadcompanies'] = 'Les entreprises de IOMAD';
$string['groupsourceiomaddepartments'] = 'Les départements de IOMAD';
$string['groupsourcenone'] = 'Rien, l\'échelle n\'est pas active';
$string['hidegroupidentity'] = 'Cacher l\'identité des équipes';
$string['keeplogsdesc'] = 'L\'historique joue un rôle important. Il est utilisé pour
la mise en garde sur la triche, pour afficher les récentes récompenses, et pour plusieurs
autres choses. Réduire le temps pour lequel l\'historique est gardé peut influencer la façon
dont les points sont distribués au fil du temps et doit être traitée avec soin.';
$string['levelbadges'] = 'Remplacer les badges de niveau';
$string['levelbadges_help'] = 'Uploader une image pour remplacer l\'apparence du thème de badge.';
$string['levelup'] = 'Level up!';
$string['maxpointspertime'] = 'Nombre max de points par intervalle de temps';
$string['maxpointspertime_help'] = 'Le nombre maximum de points qui peuvent être accumulés pendant la période de temps. Quand cette valeur est vide, ou égale à zéro, cette règle ne s\'applique pas.';
$string['missingpermssionsmessage'] = 'Vous n\'avez pas les permissions requises pour accéder à ce contenu.';
$string['mylevel'] = 'Mon niveau';
$string['navgroupladder'] = 'Echelle d\'équipe';
$string['pluginname'] = 'Level up! Plus';
$string['points'] = 'Points';
$string['privacy:metadata:log'] = 'Contient l\'historique des évènements';
$string['privacy:metadata:log:points'] = 'Les points attribués pour cet évènement';
$string['privacy:metadata:log:signature'] = 'Des informations liées à l\'évènement';
$string['privacy:metadata:log:time'] = 'La date à laquelle l\'évènement s\'est produit';
$string['privacy:metadata:log:type'] = 'Le type d\'évènement';
$string['privacy:metadata:log:userid'] = 'L\'utilisateur ayant reçu les points';
$string['progressbarmode'] = 'Afficher le progrès vers';
$string['progressbarmode_help'] = 'Avec _Le niveau suivant_, la barre de progression affiche le pourcentage de progression vers le niveau suivant.

Avec _Le niveau ultime_, la barre de progression affiche le pourcentage de progression vers le dernier niveau qu\'un étudiant peut atteindre.

Dans les deux cas, la barre de progression restera pleine lorsque le dernier niveau est atteint.';
$string['progressbarmodelevel'] = 'Le niveau suivant';
$string['progressbarmodeoverall'] = 'Le niveau ultime';
$string['ruleactivitycompletion'] = 'Achèvement d\'activité';
$string['ruleactivitycompletion_help'] = 'Cette règle est remplie lorsque qu\'une activité est marquée achevée, pour autant qu\'elle ne soit pas indiquée comme étant échouée.

Conformément aux paramètres standards pour l\'achèvement des activités, les enseignants ont le contrôle
total des conditions requises pour _achever_ une activité. Ces dernières peuvent être personnalisé individuellement
pour chaque activité dans un cours et être basée sur une date, une note, etc... Il est aussi possible d\'autoriser
les étudiants à manuellement indiquer les activités comme étant achevées.

Cette règle ne récompensera l\'étudiant qu\'une seule fois.';
$string['ruleactivitycompletion_link'] = 'Activity_completion';
$string['ruleactivitycompletiondesc'] = 'Une activité ou ressource a été achevée avec succès';
$string['rulecourse'] = 'Cours';
$string['rulecourse_help'] = 'Cette condition est remplie lorsqu\'un événement se produit dans le cours indiqué.

Elle n\'est disponible que lorsque le plugin est utilisé pour tout le site. Quand le plugin est utilisé par cours, cette condition n\'a aucun effet.';
$string['rulecoursecompletion'] = 'Achèvement de cours';
$string['rulecoursecompletion_help'] = 'Cette règle est remplie lorsqu\'un cours est achevé par un étudiant.

__Note:__ Les étudiants ne recevront pas leurs points instantanément, cela peut prendre quelques temps pour que Moodle traite les achèvement de cours. En d\'autres termes, cela requiert _cron_.';
$string['rulecoursecompletion_link'] = 'Course_completion';
$string['rulecoursecompletioncoursemodedesc'] = 'Le cours a été achevé';
$string['rulecoursecompletiondesc'] = 'Un cours a été achevé';
$string['rulecoursedesc'] = 'Le cours est : {$a}';
$string['ruleusergraded'] = 'Note reçue';
$string['ruleusergraded_help'] = 'Cette condition est remplie lorsque:

* La note a été reçue pour une activité
* L\'activité a défini une note pour passer
* La note atteint la note pour passer
* La note _n\'est pas_ basée sur les évaluations (e.g. dans les forums)
* La note utilise des points, et non pas un barème

Cette règle ne récompensera l\'étudiant qu\'une seule fois.';
$string['ruleusergradeddesc'] = 'L\'étudiant a reçu une note atteignant la note pour passer';
$string['themestandard'] = 'Standard';
$string['timeformaxpoints'] = 'Temps pour un nombre max de points';
$string['timeformaxpoints_help'] = 'Le laps de temps pendant lequel un utilisateur ne peut pas recevoir plus qu\'un certain nombre de points.';
$string['uptoleveln'] = 'Jusqu\'au niveau {$a}';
$string['visualsintro'] = 'Personnaliser l\'apparence des niveaux, et des points.';

// Deprecated.
$string['enablegroupladder'] = 'Activer l\'échelle de groupe';
$string['enablegroupladder_help'] = 'Une fois activée, les étudiants peuvent accéder à un classement des groupes. Les points des groupes sont calculés à partir des points accumulés par leurs membres. Pour l\'instant cette fonctionnalité n\'est disponible que lorsque le plugin est utilisé par cours, et non pas pour tout le site.';
