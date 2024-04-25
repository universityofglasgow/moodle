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
$string['adddrop'] = 'Ajouter un drop';
$string['afterimport'] = 'Après l\'importation';
$string['anonymousgroup'] = 'Une autre équipe';
$string['anonymousiomadcompany'] = 'Une autre société';
$string['anonymousiomaddepartment'] = 'Un autre département';
$string['anysection'] = 'N\'importe quelle section';
$string['awardpoints'] = 'Donner des points';
$string['badgetheme'] = 'Thème des badges';
$string['badgetheme_help'] = 'Un thème de badge défini l\'apparence par défaut des badges de niveau.';
$string['categoryn'] = 'Catégorie : {$a}';
$string['clicktoselectcourse'] = 'Cliquer pour choisir un cours';
$string['clicktoselectgradeitem'] = 'Cliquer pour choisir un élément d\'évaluation';
$string['courseselector'] = 'Selecteur de cours';
$string['csvfieldseparator'] = 'Séparateur de champs CSV';
$string['csvfile'] = 'Fichier CSV';
$string['csvfile_help'] = 'Le fichier CSV doit contenir les colonnes __user__ et __points__. La colonne __message__ est optionelle et peut être utilisée quand les notifications sont activées. Notez que la colonne __user__ comprend les IDs d\'utilisateur, addresses email et noms d\'utilisateur.';
$string['csvisempty'] = 'Le fichier CSV est vide';
$string['csvline'] = 'Ligne';
$string['csvmissingcolumns'] = 'Les colonnes suivantes sont manquantes dans le CSV : {$a}';
$string['currencysign'] = 'Symbole des points';
$string['currencysign_help'] = 'Ce paramètre permet de changer la signification des points. Il sera affiché à côté des points des utilisateurs à la place du traditionnel _points d\'expérience_.

Par exemple, vous pouvez utiliser l\'image d\'une carotte de manière à ce que les utilisateurs reçoivent des carottes comme récompense pour leurs actions.';
$string['currencysignformhelp'] = 'L\'image déposée ici sera affichée à côté des points à la place de la référence aux points d\'expérience. La taille recommandée est de 18 pixels.';
$string['currencysignoverride'] = 'Remplacement du symbole de points';
$string['currencysignxp'] = 'XP (Points d\'expérience)';
$string['currentpoints'] = 'Points actuels';
$string['custom'] = 'Personnalisé';
$string['displayfirstnameinitiallastname'] = 'Afficher le prénom et la première lettre du nom (exemple : Sam H.)';
$string['displaygroupidentity'] = 'Afficher l\'identité des équipes';
$string['dropcollected'] = 'Drop récolté';
$string['dropenabled'] = 'Activé';
$string['dropenabled_help'] = 'Un drop ne donnera aucun point sauf s\'il est activé.';
$string['dropherea'] = 'Drop: {$a}';
$string['dropname'] = 'Nom';
$string['dropname_help'] = 'Le nom du drop pour votre référence. Ceci n\'est pas affiché aux utilisateurs.';
$string['droppoints'] = 'Points';
$string['droppoints_help'] = 'Le nombre de points à donner quand ce drop est trouvé.';
$string['drops'] = 'Drops';
$string['drops_help'] = '"Dans les jeux vidéo, certains personnages peuvent _lâcher_ (""drop"" en anglais) des objets ou des points d\'expérience sur le sol pour que le joueur les ramasse. Ces objets et points sont communément appelés ""drops"".

Dans Level Up XP, les drops sont des codes courts (par exemple `[xpdrop abcdef]`) que un instructeur peut placer dans le contenu Moodle régulier. Lorsqu\'ils sont rencontrés par un utilisateur, ces drops seront ramassés et un certain nombre de points seront attribués.

Pour l\'instant, les drops sont invisibles pour l\'utilisateur et attribuent passivement des points la première fois qu\'ils sont rencontrés.

Les drops peuvent être utilisés pour attribuer astucieusement des points lorsque certains types de contenu sont consommés par un étudiant. Voici quelques idées :

- Placer un drop dans les commentaires d\'un quiz uniquement visible pour les scores parfaits
- Placer un drop dans un contenu approfondi pour récompenser sa consommation
- Placer un drop dans une discussion de forum intéressante
- Placer un drop dans une page difficile à atteindre dans un module de cours

[Plus d\'informations](https://docs.levelup.plus/xp/docs/how-to/use-drops?ref=localxp_help)"';
$string['dropsintro'] = 'Les drops sont des morceaux de code directement placés dans le contenu qui attribuent des points lorsqu\'ils sont rencontrés par un utilisateur.';
$string['editdrop'] = 'Modifier le drop';
$string['enablecheatguard'] = 'Activer la mise en garde sur la triche';
$string['enablecheatguard_help'] = 'La mise en garde sur la triche empêche les étudiants d\'être récompensés une fois qu\'ils atteignent une certaine limite.

[Plus d\'information (anglais)](https://docs.levelup.plus/xp/docs/getting-started/cheat-guard?ref=localxp_help)';
$string['enablegroupladder'] = 'Activer l\'échelle de groupe';
$string['enablegroupladder_help'] = 'Une fois activée, les étudiants peuvent accéder à un classement des groupes. Les points des groupes sont calculés à partir des points accumulés par leurs membres. Pour l\'instant cette fonctionnalité n\'est disponible que lorsque le plugin est utilisé par cours, et non pas pour tout le site.';
$string['errorunknowncourse'] = 'Erreur : cours inconnu';
$string['errorunknowngradeitem'] = 'Erreur : élément d\'évaluation inconnu';
$string['event_section_completed'] = 'Section complétée';
$string['filtergradeitems'] = 'Filtrer les éléments d\'évaluation';
$string['filtershortcodesrequiredfordrops'] = 'Le plugin [Shortcodes]({$a->url}) doit être installé et activé pour utiliser les drops, il est disponible gratuitement sur [moodle.org]({$a->url}). Ce plugin débloquera également les [codes courts de Level Up XP]({$a->shortcodesdocsurl}).';
$string['for2weeks'] = 'Pour 2 semaines';
$string['for3months'] = 'Pour 3 mois';
$string['gradeitemselector'] = 'Sélecteur d\'éléments d\'évaluation';
$string['gradeitemtypeis'] = 'L\'élément d\'évaluation est de type {$a}';
$string['gradereceived'] = 'Note reçue';
$string['gradesrules'] = 'Les règles d\'éléments d\'évaluation';
$string['gradesrules_help'] = 'Les règles ci-dessous déterminent quand les étudiants reçoivent des points pour les notes qu\'ils reçoivent.

Les étudiants recevront autant de points que leurs notes.
Une note de 5/10, et une note de 5/100 donneront toutes deux 5 points à l\'étudiant.
Quand la note d\'un étudiant change plusieurs fois, ils recevront les points égaux à la note maximale qu\'ils ont obtenu.
Les points d\'un étudiant ne sont jamais diminués, et les notes négatives sont ignorées.

Exemple : Alice soumet un devoir et reçoit une note de 40/100. Dans _Level Up XP_, Alice reçoit 40 points pour sa note.
Alice soumet une autre version de son devoir, mais cette fois sa note est diminuée à 25/100. Les points d\'Alice dans _Level Up XP_ ne changent pas.
Pour sa dernière tentative, Alice reçoit une note de 60/100, elle gagne 20 points additionels dans _Level Up XP_, le total de points qu\'elle a obtenu est de 60.

[Plus sur la documentation de _Level Up XP_ (anglais)](https://docs.levelup.plus/xp/docs/how-to/grade-based-rewards?ref=localxp_help)';
$string['groupanonymity'] = 'Anonymat';
$string['groupanonymity_help'] = 'Ce paramètre contrôle si les participants peuvent voir le nom des équipes dont ils ne font pas partie.';
$string['groupladder'] = 'Echelle d\'équipe';
$string['groupladdercols'] = 'Colonnes de l\'échelle';
$string['groupladdercols_help'] = 'Ce paramètre détermine quelles colonnes sont affichées en plus des noms et rangs des équipes.

La colonne __Points__ affiche les points de l\'équipe.
La valeur peut avoir été compensée en function de la _Stratégie de classement_ choisie.

La colonne __Progrès__ affiche la progression globale de l\'équipe vers l\'obtention du niveau ultime par tous ses membres.
En d\'autres termes, 100% de progression ne peut être atteint que lorsque tous les membres de l\'équipe atteignent le dernier niveau. Notez que le nombre de points restants, affichés à côté de la barre de progression, peut prêter à confusion quand les équipes sont déséquilibrées et que les points ne sont pas compensés car les équipes avec plus de membres auront plus de points que les autres, bien que leurs progressions puissent être similaires.

Appuyez sur la touche CTRL ou CMD en cliquant pour sélectionner plus d\'une colonne, ou pour désélectionner une colonne.';
$string['groupladdersource'] = 'Grouper les étudiants par';
$string['groupladdersource_help'] = 'L\'échelle d\'équipe affiche un classement des points combinés des étudiants d\'une même équipe.
La valeur choisie détermine ce que _Level Up XP_ utilise pour grouper les étudiants.
Quand la valeur est _Rien_ l\'échelle d\'équipe n\'est pas active.';
$string['groupname'] = 'Nom de l\'équipe';
$string['grouporderby'] = 'Stratégie de classement';
$string['grouporderby_help'] = 'Détermine la méthode de classement des équipes.

Quand __Points__ est choisi, les équipes sont classées en fonction de la somme des points de ses membres.

Quand __Points (avec compensation)__ est choisi, les points des équipes avec moins de membres que d\'autres sont compensés en utilisant la moyenne par membre de l\'équipe. Par exemple, si une équipe a 3 membres de moins, elle recevra des points égaux à trois fois la moyenne de ses membres. Ceci créer un classement équilibré où toutes les équipes ont des chances égales.

Quand __Progrès__ est choisi, les équipes sont classées sur base de leur progression vers l\'obtention du niveau ultime par tous ses membres, sans compenser leurs points. Vous pourriez trouver utile d\'utiliser __Progrès__ lorsque les équipes sont déséquilibrées, par exemple quand quelques équipes ont beaucoup plus de membres que d\'autres.';
$string['grouppoints'] = 'Points';
$string['grouppointswithcompensation'] = 'Points (avec compensation)';
$string['groupsourcecohorts'] = 'Cohortes';
$string['groupsourcecoursegroups'] = 'Groupes de cours';
$string['groupsourceiomadcompanies'] = 'Les entreprises de IOMAD';
$string['groupsourceiomaddepartments'] = 'Les départements de IOMAD';
$string['groupsourcenone'] = 'Rien, l\'échelle n\'est pas active';
$string['hidegroupidentity'] = 'Cacher l\'identité des équipes';
$string['importcsvintro'] = 'Utilisez le formulaire ci-dessous pour importer des points depuis un fichier CSV. Cet import peut être utilisé pour _augmenter_ les points des étudiants, ou pour remplacer leurs points par les valeurs soumises. Notez que l\'importation __n\'utilise pas__ le même format que l\'exportation du rapport. Le format requis est décris dans la [documentation (anglais)]({$a->docsurl}), un fichier d\'exemple est aussi disponible [ici]({$a->sampleurl}).';
$string['importpoints'] = 'Importer des points';
$string['importpointsaction'] = 'Action d\'importation';
$string['importpointsaction_help'] = 'Détermine quoi faire avec les points inclus dans le fichier CSV.

**Définir comme total**

Les points remplaceront les points courants de l\'étudiant, ce qui en fait leur nouveau total. Les utilistaeurs ne recevront pas de notification, et il n\'y aura pas d\'entrées dans le journal.

**Augmenter**

Les points représent le nombre de points à donner à l\'étudiant. Quand activé, une notification contenant le _message_ optionel du fichier CSV sera envoyé au bénéficiaire. Une nouvelle entrée _Récompense manuelle_ sera aussi ajoutée au journal.';
$string['importpreview'] = 'Aperçu de l\'import';
$string['importpreviewintro'] = 'Voici un aperçu de l\'importation affichant les {$a} première(s) entrée(s) parmis toutes celles à importer. Veuillez revoir et confirmer que vous êtes prêt à tout importer.';
$string['importresults'] = 'Résultat d\'importation';
$string['importresultsintro'] = 'Un total de **{$a->successful} entrées** sur **{$a->total}** ont été importées avec succès. Si certaines entrées n\'ont pas pu être importées, leurs détails seront affichés ci-dessous.';
$string['importsettings'] = 'Paramètres d\'importation';
$string['increaseby'] = 'Augmenter de';
$string['increaseby_help'] = 'Le nombre de points à donner à l\'étudiant';
$string['increasemsg'] = 'Message optionnel';
$string['increasemsg_help'] = 'Quand un message est fourni, il sera ajouté à la notification.';
$string['invalidpointscannotbenegative'] = 'Les points ne peuvent pas être négatifs.';
$string['keeplogsdesc'] = 'L\'historique joue un rôle important. Il est utilisé pour
la mise en garde sur la triche, pour afficher les récentes récompenses, et pour plusieurs
autres choses. Réduire le temps pour lequel l\'historique est gardé peut influencer la façon
dont les points sont distribués au fil du temps et doit être traitée avec soin.';
$string['levelbadges'] = 'Remplacer les badges de niveau';
$string['levelbadges_help'] = 'Déposer une image pour remplacer l\'apparence du thème des badges.';
$string['levelup'] = 'Progressez !';
$string['manualawardnotification'] = 'Vous avez reçu une récompense de {$a->points} points par {$a->fullname}.';
$string['manualawardnotificationwithcourse'] = 'Vous avez reçu une récompense de {$a->points} points par {$a->fullname} dans le cours {$a->coursename}.';
$string['manualawardsubject'] = 'Vous avez reçu une récompense de {$a->points} points.';
$string['manuallyawarded'] = 'Récompense manuelle';
$string['maxn'] = 'Max. : {$a}';
$string['maxpointspertime'] = 'Nombre max de points par intervalle de temps';
$string['maxpointspertime_help'] = 'Le nombre maximum de points qui peuvent être accumulés pendant la période de temps. Quand cette valeur est vide, ou égale à zéro, cette règle ne s\'applique pas.';
$string['messageprovider:manualaward'] = 'Level Up XP points manuellement récompensés';
$string['missingpermssionsmessage'] = 'Vous n\'avez pas les permissions requises pour accéder à ce contenu.';
$string['mylevel'] = 'Mon niveau';
$string['navdrops'] = 'Drops';
$string['navgroupladder'] = 'Echelle d\'équipe';
$string['pluginname'] = 'Level Up XP+';
$string['points'] = 'Points';
$string['previewmore'] = 'Augmenter l\'aperçu';
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
$string['reallyedeletedrop'] = 'Etes-vous sûr de vouloir supprimer ce drop? Cette action est irréversible.';
$string['reason'] = 'Raison';
$string['reasonlocation'] = 'Endroit';
$string['reasonlocationurl'] = 'URL de l\'endroit';
$string['ruleactivitycompletion'] = 'Achèvement d\'activité';
$string['ruleactivitycompletion_help'] = 'Cette règle est remplie lorsqu\'une activité est marquée comme étant achevée, pour autant qu\'elle ne soit pas indiquée comme étant échouée.

Conformément aux paramètres standards pour l\'achèvement des activités, les enseignants ont le contrôle
total des conditions requises pour _achever_ une activité. Ces dernières peuvent être personnalisé individuellement
pour chaque activité dans un cours et être basée sur une date, une note, etc... Il est aussi possible d\'autoriser
les étudiants à indiquer manuellement les activités comme étant achevées.

Cette règle ne récompensera l\'étudiant qu\'une seule fois.';
$string['ruleactivitycompletion_link'] = 'Activity_completion';
$string['ruleactivitycompletiondesc'] = 'Une activité ou ressource a été achevée avec succès';
$string['ruleactivitycompletioninfo'] = 'Cette condition est remplie losque l\'étudiant achève un cours ou une activité.';
$string['rulecmname'] = 'Nom de l\'activité';
$string['rulecmname_help'] = 'Cette condition est remplie quand l\'événement a lieu dans une activité nommée tel que specifié.

Notes:

- La comparaison n\'est pas sensible à la casse.
- Une valeur vide ne correspondra jamais.
- Considérez utiliser **contient** quand le nom d\'une activité inclus des tags [multilingue](https://docs.moodle.org/fr/Contenu_multilingue).';
$string['rulecmnamedesc'] = 'Le nom de l\'activité {$a->compare} \'{$a->value}\'.';
$string['rulecmnameinfo'] = 'Defini le nom des activités ou ressources dans lesquelles l\'action a eu lieu.';
$string['rulecourse'] = 'Cours';
$string['rulecourse_help'] = 'Cette condition est remplie lorsqu\'un événement se produit dans le cours indiqué.

Elle n\'est disponible que lorsque le plugin est utilisé pour tout le site. Quand le plugin est utilisé par cours, cette condition n\'a aucun effet.';
$string['rulecoursecompletion'] = 'Achèvement de cours';
$string['rulecoursecompletion_help'] = 'Cette règle est remplie lorsqu\'un cours est achevé par un étudiant.

__Note:__ Les étudiants ne recevront pas leurs points instantanément, cela peut prendre quelques temps pour que Moodle traite les achèvement de cours. En d\'autres termes, cela requiert _cron_.';
$string['rulecoursecompletion_link'] = 'Course_completion';
$string['rulecoursecompletioncoursemodedesc'] = 'Le cours a été achevé';
$string['rulecoursecompletiondesc'] = 'Un cours a été achevé';
$string['rulecoursecompletioninfo'] = 'Cette condition est remplie lorsqu\'un étudiant achève un cours.';
$string['rulecoursedesc'] = 'Le cours est : {$a}';
$string['rulecourseinfo'] = 'Cette condition est remplie lorsque l\'action a lieu dans un cours.';
$string['rulegradeitem'] = 'Elément d\'évaluation spécifique';
$string['rulegradeitem_help'] = 'La condition est remplie lorsque la note est donnée pour l\'élément d\'évaluation spécifié.';
$string['rulegradeitemdesc'] = 'L\'élément d\'évaluation est \'{$a->gradeitemname}\'';
$string['rulegradeitemdescwithcourse'] = 'L\'élément d\'évaluation est : \'{$a->gradeitemname}\' dans \'{$a->coursename}\'';
$string['rulegradeiteminfo'] = 'La condition est remplie pour les notes reçues pour un élément d\'évaluation particulier.';
$string['rulegradeitemtype'] = 'Type d\'élément d\'évaluation';
$string['rulegradeitemtype_help'] = 'La condition est remplie quand l\'élément d\'évaluation est du type requis. Quand un type d\'activité est sélectionné, toute note provenant de cette activité remplira la condition.';
$string['rulegradeitemtypedesc'] = 'L\'élément d\'évaluation est de type \'{$a}\'';
$string['rulegradeitemtypeinfo'] = 'Cette condition est remplie lorsque l\'élément d\'évaluation est du type requis.';
$string['rulesectioncompletion'] = 'Achèvement de section';
$string['rulesectioncompletion_help'] = 'Cette condition est remplie quand une activité est achevée et que cette activité est la dernière activité à achever dans une section.';
$string['rulesectioncompletiondesc'] = 'La section à achever est \'{$a->sectionname}\'';
$string['rulesectioncompletioninfo'] = 'Cette condition est remplie quand un étudiant achève toutes les activités d\'une section.';
$string['ruleusergraded'] = 'Note reçue';
$string['ruleusergraded_help'] = 'Cette condition est remplie lorsque:

* La note a été reçue pour une activité
* L\'activité a défini une note pour passer
* La note atteint la note pour passer
* La note _n\'est pas_ basée sur les évaluations (e.g. dans les forums)
* La note utilise des points, et non pas un barème

Cette règle ne récompensera l\'étudiant qu\'une seule fois.';
$string['ruleusergradeddesc'] = 'L\'étudiant a reçu une note atteignant la note pour passer';
$string['sectioncompleted'] = 'Section achevée';
$string['sectiontocompleteis'] = 'La section à achever est {$a}';
$string['sendawardnotification'] = 'Envoyer une notification de récompense';
$string['sendawardnotification_help'] = 'Quand activé, l\'étudiant recevra une notification qu\'il a obtenu des points. Le message contiendra votre nom, le nombre de points et le nom du cours le cas échéant.';
$string['shortcode:xpteamladder'] = 'Afficher une partie de l\'échelle d\'équipe';
$string['shortcode:xpteamladder_help'] = 'Par défaut, une partie de l\'échelle des équipes voisines de l\'utilisateur courant est affichée.

```
[xpteamladder]
```

Pour afficher les 5 premières équipes au lieu des équipes voisines, ajouter le paramètre `top`. Vous pouvez optionnellement définir le nombre d\'équipes à afficher en donner une valeur à `top`, comme ceci : `top=20`.

```
[xpteamladder top]
[xpteamladder top=15]
```

Un lien vers l\'échelle totale sera automatiquement affichée en dessous du tableau si il y a plus de résultats à afficher. Si vous ne souhaitez pas afficher ce lien, rajouter l\'argument `hidelink`.

```
[xpteamladder hidelink]
```

Par défaut, la table n\'inclus pas la colonne de progrès qui affiche la barre de progression. Si cette colonne a été sélectionne dans les colonnes additionnelles des paramètres d\'échelle, vous pouvez utiliser l\'argument `withprogress` pour l\'afficher.

```
[xpteamladder withprogress]
```

Notez que lorsque l\'utilisateur courant est membre de plusieurs équipes, le plugin utilisateur celle avec le meilleur rang comme référence.';
$string['studentsearnpointsforgradeswhen'] = 'Les étudiants reçoivent des points pour leurs notes quand :';
$string['team'] = 'Équipe';
$string['teams'] = 'Équipes';
$string['themestandard'] = 'Standard';
$string['theyleftthefollowingmessage'] = 'Il/elle ont laissé le message suivant :';
$string['timeformaxpoints'] = 'Temps pour un nombre max de points';
$string['timeformaxpoints_help'] = 'Le laps de temps pendant lequel un utilisateur ne peut pas recevoir plus qu\'un certain nombre de points.';
$string['unabletoidentifyuser'] = 'Impossible d\'identifier l\'utilisateur.';
$string['unknowngradeitemtype'] = 'Type inconnu ({$a})';
$string['unknownsectiona'] = 'Section inconnue ({$a})';
$string['uptoleveln'] = 'Jusqu\'au niveau {$a}';
$string['visualsintro'] = 'Personnaliser l\'apparence des niveaux, et des points.';
