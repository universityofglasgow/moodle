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
/**
 * The language strings for component 'qtype_gapfill', language 'en' 
 *    
 * @copyright &copy; 2012 Marcus Green
 * @author marcusavgreen@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package qtype
 * @subpackage gapfill
 */
$string['casesensitive'] = 'Case Sensitive';
$string['casesensitive_help'] = 'When this is checked, if the correct answer is CAT, cat will be flagged as a wrong answer';
$string['casesensitive_text'] = 'An answer of CAT will will be treated as different to cat';
$string['noduplicates'] = 'No Duplicates';
$string['noduplicates_help'] = 'When checked, each answer must be unique, useful where each field has a | operator, i.e. what are the colours of the Olympic medals and each field has [gold|silver|bronze], if the student enters gold in every field only the first will get a mark (the others will still get a tick though). It is really more like discard duplicate answers for marking purposes';


$string['delimitchars'] = 'Delimit characters';
$string['pluginnameediting'] = 'Editing Gap Fill.';
$string['pluginnameadding'] = 'Adding a Gap Fill Question.';

$string['gapfill'] = 'Gapfill.';

$string['displaygapfill'] = 'gapfill';
$string['displaydropdown'] = 'dropdown';
$string['displaydragdrop'] = 'dragdrop';

$string['pluginname'] = 'Gapfill';
$string['pluginname_help'] = 'Place the words to be completed within square brackets e.g. The [cat] sat on the [mat].  If mat or rug are acceptable use [mat|rug]. Dropdown and Dragdrop modes allows for a shuffled list of answers to be displayed which can include optional wrong/distractor answers.';

$string['pluginname_link'] = 'question/type/gapfill';
$string['pluginnamesummary'] = 'A fill in the gaps style question. Allows drag drop or dropdown answers with distractors. Very easy to learn syntax';
$string['questionsmissing'] = 'You have not included any fields in your question text';
$string['delimitchars_help'] = 'Change the characters that delimit a field from the default [ ], useful for programming language questions';
$string['answerdisplay'] = 'Display Answers';
$string['answerdisplay_help'] = 'Dragdrop shows a list of words that can be dragged into the gaps, gapfill shows gaps with no word options, dropdown shows the same list of correct (and possibly incorrect) answers for each field';
$string['pleaseenterananswer'] = 'Please enter an answer.';
$string['duplicatepartialcredit'] = 'Credit is partial because you have duplicate answers';
$string['disableregex'] = 'Disable Regex';
$string['disableregex_help'] = 'Disable regular expression processing and perform a standard string comparison. This can be useful for html quesitons where the angle brackets (&lt; and &gt;) should be treated literally and maths where symbols such as * should be seen literally rather than as expressions';
$string['disableregexset_text'] = 'Disable regular expression processing of responses';
$string['fixedgapsize'] = 'Fixed Gap Size';
$string['fixedgapsize_help'] = 'When attempting the question all gaps will be set to the same size as the largest gap. This removes gap size as a clue to the correct answer, e.g. if the gaps are [red] and [yellow] it would be clear that the yellow went in the biggest gap';
$string['fixedgapsizeset_text'] = 'Sets the size of every gap to that of the biggest gap';
$string['delimitset'] = 'Delimit Chars';
$string['moreoptions'] = 'More Options.';
$string['blank'] = 'blank';
$string['or'] = ' or ';
$string['delimitset_text'] = 'Sets the delimiters for gaps, so you could add % % for The %cat% sat on the %mat%';
$string['wronganswers'] = 'Distractors.';
$string['wronganswers_help'] = 'List of incorrect words designed to distract from the correct answers. Each word is separated by commas, only applies in dragdrop/dropdowns mode';
$string['yougotnrightcount'] = 'Your number of correctly filled in gaps is {$a->num}.';
$string['correctanswer'] = 'Correct answer';
$string['coursenotfound'] = 'Course not found, check the course shortname';
$string['questioncatnotfound'] = 'Question category not found, click click <a href={$a}>here</a> to initialise, then the browser back button';
$string['import'] = 'Import';
$string['cannotimport'] = 'cannotimport';
$string['course'] = 'Import help';
$string['courseshortname'] = 'Course Shortname';
$string['visitquestions'] = 'Click <a href={$a}>here</a> to visit the questions';
$string['courseshortname_help'] = 'Enter the shortname of the course to import question to. This does a standard xml question import from the '
        . 'file example_questions.xml in the gapfill question type folder.';
$string['importexamples'] = "Import Examples";
