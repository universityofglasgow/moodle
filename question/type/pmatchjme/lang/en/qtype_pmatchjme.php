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
 * OU pmatch with molecular editor question question type language strings.
 *
 * @package    qtype
 * @subpackage pmatchjme
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowanothertry'] = 'Allow another try';
$string['answeringoptions'] = 'Options for the molecular editor';
$string['atomcount'] = 'Atom count feedback';
$string['autoez'] = 'Automatic generation of SMILES with E,Z stereochemistry ("autoez")';
$string['enablejavascript'] = '<p>Loading the molecular editor...</p><p>If this message does not get replaced by the the molecular editor then you have not got javascript working in your browser.</p>';
$string['firstcorrectanswermustbestraightmatch'] = 'First correct answer must be a straight match of a SMILES string with no wild cards like match(SMILESEXPRESSION).';
$string['firstcorrectanswermustnotrequireatomcountfeedback'] = 'First correct answer can not require atom count feedback';
$string['nostereo'] = 'Stereochemistry not considered when creating SMILES ("nostereo")';
$string['pluginname'] = 'Pattern match with molecular editor';
$string['pluginname_help'] = 'In response to a question (that may include a image) the respondent uses the molecular editor to describe a molecular structure. There may be several possible correct answers, each with a different grade.';
$string['pluginname_link'] = 'question/type/pmatchjme';
$string['pluginnameadding'] = 'Adding a pattern match with molecular editor question';
$string['pluginnameediting'] = 'Editing a pattern match with molecular editor question';
$string['pluginnamesummary'] = 'Allows drawing a molecule using the molecular editor. The response is graded by comparing against various model answers, which are described using the OU\'s pattern match syntax.';
$string['smilesequal'] = 'You have the correct number of {$a}.';
$string['smilestoofew'] = 'You have too few {$a}.';
$string['smilestoomany'] = 'You have too many {$a}.';
$string['smiles_aromatic_c'] = 'aromatic carbon atoms';
$string['smiles_c'] = 'aliphatic carbon atoms';
$string['smiles_o'] = 'oxygen atoms';
$string['smiles_n'] = 'nitrogen atoms';
$string['smiles_s'] = 'sulfur atoms';
$string['smiles_f'] = 'fluorine atoms';
$string['smiles_cl'] = 'chlorine atoms';
$string['smiles_br'] = 'bromine atoms';
$string['smiles_i'] = 'iodine atoms';
$string['smiles_doublebond'] = 'double bonds';
$string['smiles_triplebond'] = 'triple bonds';
$string['smilestoofew'] = 'You have too few {$a}.';
$string['smilestoomany'] = 'You have too many {$a}.';
$string['smilescorrectcount'] = 'You have the correct molecular formula but not the correct structure.';

$string['privacy:metadata'] = 'The Pattern match with molecular editor plugin does not store any personal data.';
