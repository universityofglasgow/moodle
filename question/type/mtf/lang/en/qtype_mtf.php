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
 * Strings for component 'qtype_mtf', language 'en'
 *
 * @package     qtype_mtf
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowdeduction'] = 'Allow penalty deductions';
$string['allowdeduction_help'] = 'If enabled, teachers can configure their questions to have deductions for wrong answers.
        If disabled, this option will not be available, e. g. because the institution does not want penalties in this question type. This option cannot be disabled, if "Subpoints with deduction" is set as the default scoring method.';
$string['answernumbering'] = 'Number the options?';
$string['answernumbering123'] = '1., 2., 3., ...';
$string['answernumberingabc'] = 'a., b., c., ...';
$string['answernumberingABCD'] = 'A., B., C., ...';
$string['answernumberingiii'] = 'i., ii., iii., ...';
$string['answernumberingIIII'] = 'I., II., III., ...';
$string['answernumberingnone'] = 'No numbering';
$string['cannotusedeductions'] = 'Please set a valid scoring method.';
$string['clearrow'] = 'Clear answer for row {$a}';
$string['configintro'] = 'Default values for Multiple True/False questions.';
$string['configscoringmethod'] = 'Default scoring method for Multiple True/False questions.';
$string['configshuffleanswers'] = 'Default setting for option shuffling in Multiple True/False questions.';
$string['deduction'] = 'Deduction if wrong';
$string['deduction_help'] = 'Penalty to be deducted for wrong answers, as a fraction of the points the item would get. Only useful if using subpoints scoring method.';
$string['deletedchoice'] = 'This choice was deleted after the attempt was started.';
$string['enterfeedbackhere'] = 'Enter feedback here.';
$string['entergeneralfeedbackhere'] = 'Enter general feedback here.';
$string['enteroptionhere'] = '';
$string['enterstemhere'] = 'Enter the stem or question prompt here.';
$string['false'] = 'False';
$string['feedbackforoption'] = 'Feedback for';
$string['generalfeedback'] = 'General Feedback.';
$string['generalfeedback_help'] = 'The same general feedback is displayed regardless of the answer chosen. <br />Use general feedback e.g. to explain the correct answers or give students a link to additional information.';
$string['invaliddeduction'] = 'Deduction must be a float between 0 and 1 (inclusive)';
$string['maxpoints'] = 'Max. points';
$string['mustsupplyresponses'] = 'You must supply values for all responses.';
$string['mustsupplyvalue'] = 'You must supply a value here.';
$string['optionno'] = 'Option {$a}';
$string['oneanswerperrow'] = 'Please answer all parts of the question.';
$string['pluginname'] = 'Multiple True False (ETH)';
$string['pluginname_help'] = 'In response to a question prompt candidates rate options according to the criteria provided, e.g. "true"/"false".';
$string['pluginname_link'] = 'question/type/mtf';
$string['pluginnameadding'] = 'Adding a Multiple True/False question';
$string['pluginnameediting'] = 'Editing a Multiple True/False question';
$string['pluginnamesummary'] = 'In Multiple True/False ("Type X") questions a number of options have to be correctly rated as "true" or "false".';
$string['privacy:metadata'] = 'The MTF question type plugin does not store any personal data.';
$string['responsedesc'] = 'The text used as a default for response {$a}.';
$string['responseno'] = 'Response {$a}';
$string['responsetext'] = 'Response Text {$a}';
$string['responsetext1'] = 'True';
$string['responsetext2'] = 'False';
$string['responsetexts'] = 'Judgement options';
$string['save'] = 'Save';
$string['scoringmtfonezero'] = 'MTF1/0';
$string['scoringmtfonezero_help'] = 'The student receives full points if all responses are correct, and zero points otherwise.';
$string['scoringsubpointdeduction'] = 'Subpoints with deduction';
$string['scoringsubpointdeduction_help'] = 'The student is awarded subpoints for each correct response, but also deductions for wrong answers.';
$string['scoringsubpoints'] = 'Subpoints';
$string['scoringsubpoints_help'] = 'The student is awarded subpoints for each correct response.';
$string['showscoringmethod'] = 'Show scoringmethod';
$string['showscoringmethod_help'] = 'If enabled, students will see the scoringmethod in tests.';
$string['scoringmethod'] = 'Scoring method';
$string['scoringmethod_help'] = 'There are three alternative scoring methods. <br /><strong>Subpoints</strong> (recommended): The student is awarded subpoints for each correct response.<br /><strong>Subpoints with deduction</strong>: The student is awarded subpoints for each correct response, but also deductions for wrong answers. This method needs prior activation by the administrator.<br/><strong>MTF1/0</strong>: The student receives full points if all responses are correct, and zero points otherwise.';
$string['shuffleanswers'] = 'Shuffle options';
$string['shuffleanswers_help'] = 'If enabled, the order of the options is randomly shuffled for each attempt,
         provided that "Shuffle within questions" in the activity settings is also enabled.';
$string['stem'] = 'Stem';
$string['tasktitle'] = 'Task title';
$string['true'] = 'Correct';
$string['optionsandfeedback'] = 'Options and Feedback';
$string['correctresponse'] = 'Correct Response';
$string['incorrect'] = 'Incorrect';
$string['answersingleno'] = 'Multiple answers';
$string['numberofrows'] = 'Number of options';
$string['numberofrows_help'] = 'Specify the number of options.  When changing to fewer options, surplus options will be deleted once the item is saved.';
$string['deleterawswarning'] = 'When lowering the number of options surplus options will be deleted. Are you sure you want to proceed?';
$string['mustdeleteextrarows'] = 'Max allowed options in MTF are 5 options. {$a} option(s) will be deleted. If you cancel editing without saving, the surplus options will remain.';
$string['notenoughanswers'] = 'This type of question requires at least {$a} option';
$string['numberchoicehaschanged'] = 'Number of options has changed. Can not regrade the question attempt.';
