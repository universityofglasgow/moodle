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
 * Version information
 *
 * @package    qtype
 * @subpackage multinumerical
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Multinumérique';
$string['answer'] = 'Votre réponse : {$a}';
$string['pleaseenterananswer'] = 'Veuillez entrer une réponse';

$string['pluginname_link'] = 'question/type/multinumerical';
$string['pluginnameadding'] = 'Ajouter une questoin Multinumérique';
$string['pluginnameediting'] = 'Edition d\'une question multinumérique';
$string['pluginnamesummary'] = 'Permet de créer une question dont les réponses correctes peuvent être multiples, et sont régies par des équations.';
$string['parameters'] = 'Paramètres';
$string['conditions'] = 'Contraintes';
$string['feedbackperconditions'] = 'Feedback par contrainte';
$string['badfeedbackperconditionsyntax'] = 'Chaque ligne doit être de la forme : &quot;Feedback si la condition est vérifiée | Feedback si la condition n\'est pas vérifiée&quot;';
$string['badnumfeedbackperconditions'] = 'Le nombre de feedbacks par contrainte ne peut être supérieur au nombre de contraintes';
$string['noncomputable'] = '(réponses correctes non calculables)';
$string['onlyforcalculations'] = 'Seulement pour les calculs';
$string['usecolorforfeedback'] = 'Utiliser des couleurs pour le feedback par contraintes';
$string['binarygrade'] = 'Calcul de la note';
$string['gradebinary'] = 'Tout ou rien';
$string['gradefractional'] = 'Fraction';
$string['qtypeoptions'] = 'Options spécifiques à ce type de question';
$string['conditionnotverified'] = 'Contrainte non vérifiée';
$string['conditionverified'] = 'Contrainte vérifiée';
$string['displaycalc'] = 'Afficher le résultat du calcul';
$string['helponquestionoptions'] = 'Pour plus d\'informations, veuillez cliquer sur le bouton d\'aide en haut de cette page.';
$string['pluginname_help'] = '
<h2>Principe de fonctionnement</h2>
<p>Une question de type &quot;multinumérique&quot; permet de poser une question dont l\'étudiant doit calculer la réponse, cette réponse étant composée de plusieurs paramètres (numériques).</p>
<p><strong>Exemple de question :</strong> entrer <span style="font-family:monospace">X</span> et <span style="font-family:monospace">Y</span> tels que</p>
<ul><li>X + Y &lt; 20</li><li>X * Y &gt; 35</li></ul>
<p>Il existe <em>a priori</em> plusieurs solutions à ce problème, et n\'importe quelle
réponse répondant à ces deux conditions devrait pouvoir être considérée comme correcte.</p>
<p>Ce type de question permet donc de définir les paramètres demandés (ici, <span style="font-family:monospace">X</span> et <span style="font-family:monospace">Y</span>) et les contraintes auxquelles ces paramètres doivent répondre.</p>
<h2>Utilisation par l\'enseignant</h2>
<ul>
	<li>Entrer les paramètres à demander, séparés par des virgules (dans notre exemple,
	on entrerait &quot;<span style="font-family:monospace">X,Y</span>&quot;).<br />
	<strong>Note :</strong> on peut entrer des unités après chaque paramètre, soit par exemple
	&quot;<span style="font-family:monospace">X [m],Y [h]</span>&quot; (mettre un espace
	entre le nom du paramètre et son unité).</li>
	<li>Entrer les contraintes, séparées par un retour à la ligne ; dans notre exemple,
	on entrerait : <pre>X + Y &lt; 20
X * Y &gt; 35</pre>(les lignes vides seront ignorées)
    <p>Les opérateurs disponibles sont : <ul>
        <li>&quot;<span style="font-family:monospace">=</span>&quot; (égalité)</li>
        <li>&quot;<span style="font-family:monospace">&lt;</span>&quot; (inférieur à)</li>
        <li>&quot;<span style="font-family:monospace">&lt;=</span>&quot; (inférieur ou égal à)</li>
        <li>&quot;<span style="font-family:monospace">&gt;</span>&quot; (supérieur à)</li>
        <li>&quot;<span style="font-family:monospace">&gt;=</span>&quot; (supérieur ou égal à)</li>
        <li>l\'opérateur d\'intervalle :
            <pre><span style="font-family:monospace">X = [1;5]</span></pre> signifie que
            <span style="font-family:monospace">X</span> doit se trouver entre 1 et 5 compris, et
            <pre><span style="font-family:monospace">X = ]1;5[</span></pre> signifie que
            <span style="font-family:monospace">X</span> doit se trouver entre 1 et 5 non compris,
            (se référer à la définition des intervalles en mathématiques).
        </li>
    </ul></p></li>
	<li>Entrer si désiré un feedback pour chaque contrainte. Dans notre exemple, on
	pourrait par exemple entrer :
    <pre>OK : X + Y &lt; 20 | Non, X + Y &gt;= 20 !
OK : X * Y &gt; 35 | Non, X + Y &lt;= 35 !</pre>
    <p>Les lignes ne correspondant à aucune condition sont ignorées.</p>
    </li>
    <li>L\'option &quot;Afficher le résultat du calcul&quot; permet de définir si le feedback
    par contrainte doit contenir une évaluation numérique de chacune des contraintes.
    L\'affichage de cette évaluation numérique n\'a lieu que si le feedback par contrainte
    (positif ou négatif, suivant la réponse de l\'apprenant) contient du texte.<br />
    Si on choisit ici &quot;Seulement pour les calculs&quot;, ceci ne s\'affichera pas pour les
    contraintes non calculées (de type <span style="font-family:monospace">X&nbsp;>&nbsp;5</span>), afin
    de ne pas donner la solution à l\'apprenant.</li>
    <li>L\'option &quot;Calcul des points&quot; permet de définir si une réponse
    partiellement correcte (remplissant une partie des contraintes seulement) doit obtenir
    une partie des points, ou aucun point.</li>
</ul>
';