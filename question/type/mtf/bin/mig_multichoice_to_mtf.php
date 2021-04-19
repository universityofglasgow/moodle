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
 * @package     qtype_mtf
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/question/type/mtf/lib.php');

// Getting parameters.
$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 1, PARAM_INT);
$migratesingle = optional_param('migratesingleanswer', 0, PARAM_INT);
$includesubcategories = optional_param('includesubcategories', 0, PARAM_INT);

@set_time_limit(0);
@ini_set('memory_limit', '3072M');

// General Page Setup.
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' .
    '<style>body{font-family: "Courier New", Courier, monospace; font-size: 12px; background: #ebebeb; color: #5a5a5a;}</style>' .
    '</head>';
echo "=========================================================================================<br/>\n";
echo "M I G R A T I O N :: Multichoice to MTF<br/>\n";
echo "=========================================================================================<br/>\n";


// Checking for permissions.
require_login();
if (!is_siteadmin()) {
    echo "<br/>[<font color='red'>ERR</font>] You are not a Website Administrator";
    die();
}

$starttime = microtime(1);
$fs = get_file_storage();

$sql = "SELECT  q.*
        FROM    {question} q
        WHERE   q.qtype = 'multichoice'
          AND   q.parent = 0";

$params = array();


// Showing information when either no or too many parameters are selected.
$numparameters = ($all == 0 ? 0 : 1) + ($courseid == 0 ? 0 : 1) + ($categoryid == 0 ? 0 : 1);
if (($all != 1 && $courseid <= 0 && $categoryid <= 0) || $numparameters > 1 ) {
    echo "
    <br/>\nParameters:<br/><br/>\n\n
    =========================================================================================<br/>\n
    You must specify certain parameters for this script to work: <br/><br/>\n\n
    Step 1: <b>NECESSARY </b> - Use ONE of the following three parameters-value pairs:
    <ul>
        <li><b>courseid</b> (values: <i>a valid course ID</i>)</li>
        <li><b>categoryid</b> (values: <i>a valid category ID</i>)</b></li>
        <li><b>all</b> (values: 1)</li>
    </ul>
    This parameter-value pairs define which MTF questions will be migrated.<br/><br/>\n\n
    Step 2: <b>IMPORTANT AND STRONGLY RECOMMENDED:</b><br/>\n
    <ul>
        <li><b>dryrun</b> (values: <i>0,1</i>)</li>
        <li><b>migratesingleanswer</b> (values: <i>0,1</i>)</li>
        <li><b>includesubcategories</b> (values: <i>0,1</i>)</li>
    </ul>
    The Dryrun Option is enabled (1) by default.<br/>\n
    With Dryrun enabled no changes will be made to the database.<br/>\n
    Use Dryrun to receive information about possible issues before migrating.<br/><br/>\n\n
    The MigrateSingleAnswer Option is disabled (0) by default.<br/>\n
    With migratesingleanswer enabled those Multichoice Questions with only one correct option<br/>\n
    are included into the Migration to MTF as well.<br/>\n
    The IncludeSubcategories Option also is disabled (0) by default.<br/>\n
    With includesubcategories enabled all subcategories will be included in the migration<br/>\n
    process, if the user chooses to migrate questions by selecting a certain category.<br/><br/>\n\n
    =========================================================================================<br/><br/>\n\n
    Examples:<br/><br/>\n\n
    =========================================================================================<br/>\n
    <ul>
        <li><strong>Migrate MTF Questions in a specific course</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?<b>courseid=55</b>
        <li><strong>Migrate MTF Questions in a specific category</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?<b>categoryid=1</b>
        <li><strong>Migrate all MTF Questions</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?<b>all=1</b>
        <li><strong>Disable Dryrun</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1<b>&dryrun=0</b>
        <li><strong>Enable MigrateSingleAnswer</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=0<b>&migratesingleanswer=1</b>
        <li><strong>Enable IncludeSubcategories</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=0<b>&includesubcategories=1</b>
    </ul>
    <br/>\n";
    die();
}

// Parameter Information.
echo "-----------------------------------------------------------------------------------------<br/><br/>\n\n";
echo ($dryrun == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "Dryrun: " . ($dryrun == 1 ? "NO changes to the database will be made!" : "Migration is being processed") . "<br/>\n";
echo ($migratesingle == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "MigrateSingleAnswer<br/>\n";
echo ($includesubcategories == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "IncludeSubcategories<br/><br/>\n\n";
echo "-----------------------------------------------------------------------------------------<br/>\n";
echo "=========================================================================================<br/>\n";


// Get the categories : Case 1.
if ($all == 1) {
    if ($categories = $DB->get_records('question_categories', array())) {
        echo "Migration of all MTF Questions<br/>\n";
    } else {
        echo "<br/>[<font color='red'>ERR</font>] Could not get categories<br/>\n";
        die();
    }
}

// Get the categories : Case 2.
if ($courseid > 0) {
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        echo "<br/>[<font color='red'>ERR</font>] Course with ID " . $courseid . " not found<br/>\n";
        die();
    }

    $coursecontext = context_course::instance($courseid);

    $categories = $DB->get_records('question_categories', array('contextid' => $coursecontext->id));

    $catids = array_keys($categories);

    if (!empty($catids)) {
        echo "Migration of MTF Questions within courseid " . $courseid . " <br/>\n";
        list($csql, $params) = $DB->get_in_or_equal($catids);
        $sql .= " AND category $csql ";
    } else {
        echo "<br/>[<font color='red'>ERR</font>] No question categories for course found.<br/>\n";
        die();
    }
}

// Get the categories : Case 3.
if ($categoryid > 0) {
    if ($categories[$categoryid] = $DB->get_record('question_categories', array('id' => $categoryid))) {

        $catids = [];

        if ($includesubcategories == 1) {
            $subcategories = get_subcategories($categoryid);
            $catids = array_column($subcategories, 'id');
            $catnames = array_column($subcategories, 'name');
        }

        array_push($catids, $categoryid);

        echo 'Migration of MTF questions within category "' . $categories[$categoryid]->name . "\"<br/>\n";

        if ($includesubcategories == 1) {
            echo "Also migrating subcategories:<br>\n";
            echo implode(",<br>", $catnames) . "<br>\n";
            echo "=========================================================================================<br/>\n";
        }

        list($csql, $params) = $DB->get_in_or_equal($catids);
        $sql .= " AND category $csql ";
    } else {
        echo "<br/>[<font color='red'>ERR</font>] Question category with ID " . $categoryid . " not found<br/>\n";
        die();
    }
}

// Get the questions based on the previous set parameters.
$sql .= " ORDER BY category ASC";
$questions = $DB->get_records_sql($sql, $params);
echo 'Questions found: ' . count($questions) . "<br/>\n";
echo "=========================================================================================<br/><br/>\n\n";
if (count($questions) == 0) {
    echo "<br/>[<font color='red'>ERR</font>] No questions found<br/>\n";
    die();
}

// Processing the single questions.
echo "Migrating questions...<br/>\n";
$nummigrated = 0;
$questionsnotmigrated = [];

foreach ($questions as $question) {
    set_time_limit(600);
    $question->oldid = $question->id;
    $question->oldname = $question->name;

    // Getting related question data from database.
    $multichoiceoptions = $DB->get_record('qtype_multichoice_options', array('questionid' => $question->oldid));
    $questionanswers = $DB->get_records('question_answers', array('question' => $question->oldid), ' id ASC ');
    $questionhints = $DB->get_records('question_hints', array('questionid' => $question->id), ' id ASC ');


    // Checking for possible errors before doing anyting.
    // Getting question weights in case of a complete record.
    if (!isset($questionanswers) || !isset($multichoiceoptions->single) || !isset($migratesingle) || !isset($questionhints)) {
        $questionweights = array("error" => true, "message" => "Database records incomplete.", "notices" => []);
    } else {
        $questionweights = get_weights($questionanswers, $multichoiceoptions->single, $migratesingle);
        $rownumber = $questionweights["rownumber"];
    }

    // If weights are not mapable, skip the question and continue; with the next iteration.
    if ($questionweights["error"]) {
        echo '[<font style="color:#ff0909;">ERR</font>] - question <i>"' . $question->oldname .
            '"</i> (ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $question->oldid .
            '" target="_blank">' . $question->oldid . '</a>) is not migratable: ' . $questionweights["message"];
        echo count($questionweights["notices"]) > 0 ? " ::: <b>Notices:</b> " . implode(" | ", $questionweights["notices"]) : null;
        echo "<br/>\n";
        array_push($questionsnotmigrated, array("id" => $question->oldid, "name" => $question->oldname));
        continue;
    } else {
        $nummigrated++;
    }

    // If Dryrun is disabled, changes to the database are made from this point on.
    if ($dryrun == 0) {
        try {
            $transaction = $DB->start_delegated_transaction();

            // Get contextid from question category.
            $contextid = $DB->get_field('question_categories', 'contextid', array('id' => $question->category));

            if (!isset($contextid)) {
                echo "<br/>[<font color='red'>ERR</font>] No context id found for this question.";
                continue;
            }

            // Duplicating mdl_question -> mdl_question.
            unset($question->id);

            $question->parent = 0;
            $question->name = substr($question->name . " (MTF " . date("Y-m-d H:i:s") . ")", 0, 255);
            $question->qtype = "mtf";
            $question->stamp = make_unique_id_code();
            $question->version = make_unique_id_code();
            $question->timecreated = time();
            $question->timemodified = time();
            $question->modifiedby = $USER->id;
            $question->createdby = $USER->id;
            $question->id = $DB->insert_record('question', $question);

            // Tansferring mdl_question_answers -> mdl_qtype_mtf_weights.
            foreach ($questionweights["message"] as $keyrow => $row) {
                foreach ($row as $keycolumn => $column) {
                    $entry = new stdClass();
                    $entry->questionid = $question->id;
                    $entry->rownumber = $keyrow;
                    $entry->columnnumber = $keycolumn;
                    $entry->weight = $column;
                    $DB->insert_record('qtype_mtf_weights', $entry);
                    unset($entry);
                }
            }

            // Tansferring mdl_question_answers -> mdl_qtype_mtf_rows.
            $iterator = 1;
            foreach ($questionanswers as $key => $row) {
                $entry = new stdClass();
                $entry->questionid = $question->id;
                $entry->number = $iterator++;
                $entry->optiontext = $questionanswers[$key]->answer;
                $entry->optiontextformat = FORMAT_HTML;
                $entry->optionfeedback = $questionanswers[$key]->feedback;
                $entry->optionfeedbackformat = FORMAT_HTML;
                $mtfrowid = $DB->insert_record('qtype_mtf_rows', $entry);
                unset($entry);

                // Copy images in the answer text.
                copy_files(
                    $fs,
                    $contextid,
                    $questionanswers[$key]->id,
                    $mtfrowid,
                    $questionanswers[$key]->answer,
                    "answer",
                    "qtype_mtf",
                    "optiontext");

                // Copy images in the answer feedback.
                copy_files(
                    $fs,
                    $contextid,
                    $questionanswers[$key]->id,
                    $mtfrowid, $questionanswers[$key]->feedback,
                    "answerfeedback",
                    "qtype_mtf",
                    "feedbacktext");
            }

            // Tansferring mdl_question_hints -> mdl_question_hints.
            foreach ($questionhints as $key => $row) {
                $entry = new stdClass();
                $entry->questionid = $question->id;
                $entry->hint = $row->hint;
                $entry->hintformat = $row->hintformat;
                $entry->shownumcorrect = $row->shownumcorrect;
                $entry->clearwrong = $row->clearwrong;
                $entry->options = $row->options;
                $DB->insert_record('question_hints', $entry);
                unset($entry);
            }

            // Tansferring  mdl_qtype_multichoice_options -> mdl_qtype_mtf_options.
            $entry = new stdClass();
            $entry->questionid = $question->id;

            if ($multichoiceoptions->single == 1) {
                $entry->scoringmethod = "mtfonezero";
            } else {
                $entry->scoringmethod = "subpoints";
            }
            $entry->shuffleanswers = $multichoiceoptions->shuffleanswers;
            $entry->numberofrows = count($questionanswers);
            $entry->numberofcolumns = 2;
            $entry->answernumbering = $multichoiceoptions->answernumbering;
            $DB->insert_record('qtype_mtf_options', $entry);

            unset($entry);

            // Creating  mdl_qtype_mtf_columns.
            for ($i = 1; $i <= 2; $i++) {
                $entry = new stdClass();
                $entry->questionid = $question->id;
                $entry->number = $i;
                $i == 1 ? $entry->responsetext = "True" : $entry->responsetext = "False";
                $entry->responsetextformat = FORMAT_MOODLE;
                $DB->insert_record('qtype_mtf_columns', $entry);
                unset($entry);
            }

            // Copy images in the questiontext to new itemid.
            copy_files(
                $fs,
                $contextid,
                $question->oldid,
                $question->id,
                $question->questiontext,
                "questiontext",
                "question",
                "questiontext");

            // Copy images in the general feedback to new itemid.
            copy_files(
                $fs,
                $contextid,
                $question->oldid,
                $question->id,
                $question->generalfeedback,
                "generalfeedback",
                "question",
                "generalfeedback");

            // Copy tags.
            $tags = $DB->get_records_sql(
                "SELECT * FROM {tag_instance} WHERE itemid = :itemid",
                array('itemid' => $question->oldid));

            foreach ($tags as $tag) {
                $entry = new stdClass();
                $entry->tagid = $tag->tagid;
                $entry->component = $tag->component;
                $entry->itemtype = $tag->itemtype;
                $entry->itemid = $question->id;
                $entry->contextid = $tag->contextid;
                $entry->tiuserid = $tag->tiuserid;
                $entry->ordering = $tag->ordering;
                $entry->timecreated = $tag->timecreated;
                $entry->timemodified = $tag->timemodified;
                $DB->insert_record('tag_instance', $entry);
            }

            // Save changes to the database.
            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    // Output: Question Migration Success.
    echo '[<font style="color:#228d00;">OK </font>] - question <i>"' . $question->oldname . '"</i> ' .
    '(ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $question->oldid .
    '" target="_blank">' . $question->oldid . '</a>) ';
    echo $dryrun == 0 ? ' > <i>"' . $question->name . '"</i> ' .
    '(ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $question->id .
    '" target="_blank">' . $question->id . '</a>)' : " is migratable";
    echo count($questionweights["notices"]) > 0 ? " ::: <b>Notices:</b> " . implode(" | ", $questionweights["notices"]) : null;
    echo "<br/>\n";
}

// Showing final summary.
echo "<br/>\n";
echo "=========================================================================================<br/>\n";
echo count($questionsnotmigrated) > 0 ? "Not Migrated: <br/>" : null;
foreach ($questionsnotmigrated as $entry) {
    echo '<a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $entry["id"] . '" target="_blank">' .
    $CFG->wwwroot . '/question/preview.php?id=' . $entry["id"] . "</a> - " . $entry["name"] . "<br/>\n";
}
echo "=========================================================================================<br/>\n";
echo "SCRIPT DONE: Time needed: " . round(microtime(1) - $starttime, 4) . " seconds.<br/>\n";
echo $nummigrated . "/" . count($questions) . " questions " . ($dryrun == 1 ? "would be " : null) . "migrated.<br/>\n";
echo "=========================================================================================<br/>\n";

// Getting the subcategories of a certain category.
function get_subcategories($categoryid) {
    global $DB;

    $subcategories = $DB->get_records('question_categories', array('parent' => $categoryid), 'id');

    foreach ($subcategories as $subcategory) {
        $subcategories = array_merge($subcategories, get_subcategories($subcategory->id));
    }

    return $subcategories;
}

// Mapping the multichoice fractions to mtf weights.
// This function checks for possible mapping problems.
function get_weights($fractions, $single, $migratesingle) {
    $rownumber = 1;
    $notices = [];
    $answers = [];
    $answers[$rownumber] = [];

    // Error - Case 1: No rows in mdl_question_answers.
    if (count($fractions) < 1) {
        return array(
            "error" => true,
            "message" => "Question has the wrong number of options",
            "notices" => $notices,
            "rownumber" => $rownumber);
    }

    // Error - Case 2: single in mdl_multichoice_options is neither 0 or 1.
    if ($single < 0 || $single > 1) {
        return array(
            "error" => true,
            "message" => "Question has the wrong number of responses",
            "notices" => $notices,
            "rownumber" => $rownumber);
    }

    // Error - Case 3: single answer is enabled (1) in mdl_multichoice_options.
    if ($single == 1) {
        if ($migratesingle == 0) {
            return array(
                "error" => true,
                "message" => "Question has only one correct answer (Solution: migratesingleanswer=1)",
                "notices" => $notices,
                "rownumber" => $rownumber);
        }
    }

    // All good.
    foreach ($fractions as $record) {
        if ($record->fraction > 0) {
            $answers[$rownumber][1] = 1.000;
            $answers[$rownumber][2] = 0.000;
        } else {
            $answers[$rownumber][1] = 0.000;
            $answers[$rownumber][2] = 1.000;
        }
        $rownumber++;
    }
    return array("error" => false, "message" => $answers, "notices" => $notices, "rownumber" => $rownumber);
}

function get_image_filenames($text) {
    $result = array();
    $strings = preg_split("/<img|<source/i", $text);

    foreach ($strings as $string) {
        $matches = array();
        if (preg_match('!@@PLUGINFILE@@/(.+)!u', $string, $matches) && count($matches) > 0) {
            $filename = mb_substr($matches[1], 0, mb_strpos($matches[1], '"'));
            $filename = urldecode($filename);
            $result[] = $filename;
        }
    }
    return $result;
}

// Copying files from one question to another.
function copy_files($fs, $contextid, $oldid, $newid, $text, $type, $component, $filearea) {
    $filenames = get_image_filenames($text);
    foreach ($filenames as $filename) {
        $file = $fs->get_file($contextid, 'question', $type, $oldid, '/', $filename);
        if ($file) {
            $newfile = new stdClass();
            $newfile->component = $component;
            $newfile->filearea = $filearea;
            $newfile->itemid = $newid;
            if (!$fs->get_file($contextid, $newfile->component, $newfile->filearea, $newfile->itemid, '/', $filename)) {
                $fs->create_file_from_storedfile($newfile, $file);
            }
        }
    }
}