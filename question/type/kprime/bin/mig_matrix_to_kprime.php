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
 * @package     qtype_kprime
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @copyright   2014 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/question/type/kprime/lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 0, PARAM_INT);

require_login();

if (!is_siteadmin()) {
    echo 'You are not a Website Administrator!';
    die();
}

// Helper function to turn weight records from the database into an array
// indexed by rowid and columnid.
function weight_records_to_array($weightrecords) {
    $weights = array();
    foreach ($weightrecords as $weight) {
        if (!array_key_exists($weight->rowid, $weights)) {
            $weights[$weight->rowid] = array();
        }
        $weights[$weight->rowid][$weight->colid] = $weight;
    }

    return $weights;
}

$starttime = time();

$sql = "SELECT q.*
        FROM {question} q
        WHERE q.qtype = 'matrix'
        ";
$params = array();

if (!$all && (!($courseid > 0 || $categoryid > 0))) {
    echo "<br/><font color='red'>You should specify either the 'courseid'
    or the 'categoryid' parameter! Or set the parameter 'all' to 1.</font><br/>\n";
    echo "I'm not doing anything without restrictions!\n";
    die();
}

if ($courseid > 0) {
    if (!$course = $DB->get_record('course', array('id' => $courseid
    ))) {
        echo "<br/><font color='red'>Course with ID $courseid  not found...!</font><br/>\n";
        die();
    }
    $coursecontext = context_course::instance($courseid);
    $categories = $DB->get_records('question_categories',
    array('contextid' => $coursecontext->id
    ));

    $catids = array_keys($categories);

    if (!empty($catids)) {
        list($csql, $params) = $DB->get_in_or_equal($catids);
        $sql .= " AND category $csql ";
    } else {
        echo "<br/><font color='red'>No question categories for course found... weird!</font><br/>\n";
        echo "I'm not doing anything without restrictions!\n";
        die();
    }
}

if ($categoryid > 0) {
    if ($category = $DB->get_record('question_categories', array('id' => $categoryid
    ))) {
        echo 'Migration restricted to category "' . $category->name . "\".<br/>\n";
        $sql .= ' AND category = :category ';
        $params = array('category' => $categoryid
        );
    } else {
        echo "<br/><font color='red'>Question category with ID $categoryid  not found...!</font><br/>\n";
        die();
    }
}

$questions = $DB->get_records_sql($sql, $params);
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
echo 'Migrating ' . count($questions) . " Matrix/Kprime questions... <br/>\n";

if ($dryrun) {
    echo "***********************************************************<br/>\n";
    echo "*   Dry run: No changes to the database will be made! *<br/>\n";
    echo "***********************************************************<br/>\n";
}

$counter = 0;
$notmigrated = array();
foreach ($questions as $question) {
    set_time_limit(60);

    $transaction = $DB->start_delegated_transaction();

    $oldquestionid = $question->id;

    // Retrieve rows and columns and count them.
    $matrix = $DB->get_record('question_matrix', array('questionid' => $oldquestionid
    ));
    $rows = $DB->get_records('question_matrix_rows', array('matrixid' => $matrix->id
    ), ' id ASC ');
    $rowids = array_keys($rows);
    $columns = $DB->get_records('question_matrix_cols',
    array('matrixid' => $matrix->id
    ), ' id ASC ');

    if ($dryrun) {
        echo '--------------------------------------------------------------------------------' .
                 "<br/>\n";
        if (count($rows) != QTYPE_KPRIME_NUMBER_OF_OPTIONS) {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                     " would NOT migrated! It has the wrong number of options!<br/>\n";
            $notmigrated[] = $question;
        } else if (count($columns) != QTYPE_KPRIME_NUMBER_OF_RESPONSES) {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                     " would NOT migrated! It has the wrong number of responses!<br/>\n";
            $notmigrated[] = $question;
        } else {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                     " would be migrated!<br/>\n";
        }
        echo shorten_text($question->questiontext, 100, false, '...');
        continue;
    } else {
        echo '--------------------------------------------------------------------------------' .
                 "<br/>\n";
        echo 'Matrix Question: "' . $question->name . "\"<br/>\n";
    }

    // If the matrix question has got too manu options or responses, we ignore it.
    if (count($rows) != QTYPE_KPRIME_NUMBER_OF_OPTIONS) {
        echo "&nbsp;&nbsp; Question has the wrong number of options! Question is not migrated.<br/>\n";
        $notmigrated[] = $question;
        continue;
    }
    if (count($columns) != QTYPE_KPRIME_NUMBER_OF_RESPONSES) {
        echo "&nbsp;&nbsp; Question has the wrong number of responses! Question is not migrated.<br/>\n";
        $notmigrated[] = $question;
        continue;
    }

    // Create a new kprime question in the same category.
    unset($question->id);
    $question->qtype = 'kprime';
    $question->name = $question->name . ' (kprime)';
    $question->timecreated = time();
    $question->timemodified = time();
    $question->modifiedby = $USER->id;
    $question->createdby = $USER->id;
    // Get the new question ID.
    $question->id = $DB->insert_record('question', $question);

    echo 'New Kprime Question: "' . $question->name . '" with ID ' . $question->id . "<br/>\n";

    list($rowsql, $rowparams) = $DB->get_in_or_equal($rowids, SQL_PARAMS_NAMED, 'row');

    $weightsql = 'SELECT *
                    FROM {question_matrix_weights}
                   WHERE rowid ' . $rowsql;
    $weightrecords = $DB->get_records_sql($weightsql, $rowparams);
    $weights = weight_records_to_array($weightrecords);

    $rowcount = 1;
    foreach ($rows as $row) {
        // Create a new kprime row.
        $kprimerow = new stdClass();
        $kprimerow->questionid = $question->id;
        $kprimerow->number = $rowcount++;
        $kprimerow->optiontext = $row->shorttext;
        $kprimerow->optiontextformat = FORMAT_HTML;
        $kprimerow->optionfeedback = $row->feedback;
        $kprimerow->optionfeedbackformat = FORMAT_HTML;
        $kprimerow->id = $DB->insert_record('qtype_kprime_rows', $kprimerow);
    }

    $colcount = 1;
    foreach ($columns as $column) {
        // Create a new kprime column.
        $kprimecolumn = new stdClass();
        $kprimecolumn->questionid = $question->id;
        $kprimecolumn->number = $colcount++;
        $kprimecolumn->responsetext = $column->shorttext;
        $kprimecolumn->responsetextformat = FORMAT_MOODLE;
        $kprimecolumn->id = $DB->insert_record('qtype_kprime_columns', $kprimecolumn);
    }

    // Create kprime weight entries.
    $rowcount = 1;
    foreach ($rows as $row) {
        $colcount = 1;
        foreach ($columns as $column) {
            // Create a new weight entry.
            $kprimeweight = new stdClass();
            $kprimeweight->questionid = $question->id;
            $kprimeweight->rownumber = $rowcount;
            $kprimeweight->columnnumber = $colcount;
            if (isset($weights[$row->id][$column->id])) {
                $kprimeweight->weight = $weights[$row->id][$column->id]->weight;
            } else {
                $kprimeweight->weight = 0.0;
            }
            $kprimeweight->id = $DB->insert_record('qtype_kprime_weights', $kprimeweight);
            ++$colcount;
        }
        ++$rowcount;
    }

    // Create the kprime options.
    $kprime = new stdClass();
    $kprime->questionid = $question->id;
    $kprime->shuffleanswers = $matrix->shuffleanswers;
    $kprime->numberofrows = count($rows);
    $kprime->numberofcolumns = count($columns);

    // Translate the grading method.
    switch (strtolower(trim($matrix->grademethod))) {
        case 'all':
            $kprime->scoringmethod = 'subpoints';
            break;
        case 'kany':
            $kprime->scoringmethod = 'kprime';
            break;
        case 'kprime':
            $kprime->scoringmethod = 'kprimeonezero';
            break;
        default:
            $kprime->scoringmethod = 'kprime';
    }
    $kprime->id = $DB->insert_record('qtype_kprime_options', $kprime);

    $transaction->allow_commit();
}
echo '--------------------------------------------------------------------------------' . "<br/>\n";

$endtime = time();
$used = $endtime - $starttime;
$mins = round($used / 60);
$used = ($used - ($mins * 60));

echo "<br/>\n Done\n<br/>";
echo 'Time needed: ' . $mins . ' mins and ' . $used . " secs.<br/>\n<br/>\n";

echo "Questions that were not migrated:<br/>\n";
echo " ID &nbsp;&nbsp; ,  Question Name<br/>\n";
echo "----------------------------------------<br/>\n";
foreach ($notmigrated as $question) {
    echo $question->id . ' , ' . $question->name . "<br/>\n";
}
die();
