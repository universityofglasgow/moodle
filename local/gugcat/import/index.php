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
 * Index file.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_gugcat\grade_capture;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
require_once($CFG->dirroot. '/local/gugcat/classes/form/uploadimportform.php');
require_once($CFG->libdir . '/csvlib.class.php');

$courseid = required_param('id', PARAM_INT);
$activityid = required_param('activityid', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$childactivityid = optional_param('childactivityid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$iid = optional_param('iid', null, PARAM_INT);
$download = optional_param('download', null, PARAM_INT);

require_login($courseid);
$urlparams = array('id' => $courseid, 'activityid' => $activityid, 'page' => $page);
$url = new moodle_url('/local/gugcat/import/index.php', $urlparams);
(!is_null($categoryid) && $categoryid != 0) ? $url->param('categoryid', $categoryid) : null;
$indexurl = new moodle_url('/local/gugcat/index.php', $urlparams);

$modid = $activityid;
if (!is_null($childactivityid) && $childactivityid != 0) {
    $url->param('childactivityid', $childactivityid);
    $indexurl->param('childactivityid', $childactivityid);
    $modid = $childactivityid;
}

$PAGE->navbar->add(get_string('navname', 'local_gugcat'), $indexurl);
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));

$PAGE->requires->css('/local/gugcat/styles/gugcat.css');
$PAGE->requires->css('/local/gugcat/styles/import-loading.css');
$PAGE->requires->js_call_amd('local_gugcat/main', 'init');

$course = get_course($courseid);
$coursecontext = context_course::instance($courseid);
require_capability('local/gugcat:view', $coursecontext);

$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);
$PAGE->set_url($url);

// Logs for upload import grades.
$params = array(
    'context' => context_course::instance($courseid),
    'other' => array(
        'courseid' => $courseid,
        'activityid' => $modid,
        'categoryid' => $categoryid,
        'page' => $page
    )
);

// Retrieve the activity.
$module = local_gugcat::get_activity($courseid, $modid);
$renderer = $PAGE->get_renderer('local_gugcat');

// Download template is clicked.
if ($download == 1) {
    grade_capture::download_template_csv($module);
}

// If assessment is assignment, create participant no. for each students.
if ($module->modname == 'assign') {
    assign::allocate_unique_ids($module->instance);
}

// Set up the upload import form.
$mform = new uploadform(null, array('acceptedtypes' => array('.csv'), 'activity' => $module));
if (!$iid) {
    // If the upload form has been submitted.
    if ($formdata = $mform->get_data()) {
        echo $OUTPUT->header();
        $text = $mform->get_file_content('userfile');
        $csvimport = new gradeimport_csv_load_data();
        $csvimport->load_csv_content($text, 'UTF-8', 'comma', 10);
        $csvimporterror = $csvimport->get_error();
        if (!empty($csvimporterror)) {
            foreach (array($csvimporterror) as $error) {
                echo $OUTPUT->notification($error);
            }
            echo $renderer->display_empty_form();
            echo $mform->display();
            echo $OUTPUT->footer();
            die();
        }
        $headers = ($formdata->ignorerow == 1) ? array() : $csvimport->get_headers();
        $iid = $csvimport->get_iid(); // Go to import options form.

        echo $renderer->display_import_preview($headers, $csvimport->get_previewdata(), $module->modname == 'assign');
    } else {
        // Display the standard upload file form.
        echo $OUTPUT->header();
        echo $renderer->display_empty_form();
        echo $mform->display();
        echo $OUTPUT->footer();
        die();
    }
}

// Data has already been submitted so we can use the $iid to retrieve it.
$csvimportdata = new csv_import_reader($iid, 'grade');

// Import form to be able to choose reason.
$mform2 = new importform(null, array('iid' => $iid));

// Here, if we have data, we process the fields and enter the information into the database.
if ($formdata = $mform2->get_data()) {
    // Populate static $grades scales.
    if ($isconverted = $module->is_converted) {
        local_gugcat::set_grade_scale(null, $isconverted);
    } else {
        local_gugcat::set_grade_scale($module->gradeitem->scaleid);
    }
    // Populate static provisional grade id.
    local_gugcat::set_prv_grade_id($courseid, $module);

    $gradereason = ($formdata->reasons == 9) ? $formdata->otherreason : local_gugcat::get_reasons()[$formdata->reasons];

    list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
    if ($status && count($errors) == 0) {
        local_gugcat::notify_success('successimportupload');
        (!is_null($categoryid) && $categoryid != 0) ? $indexurl->param('categoryid', $categoryid) : null;
        (!is_null($childactivityid) && $childactivityid != 0) ? $indexurl->param('childactivityid', $childactivityid) : null;
        $event = \local_gugcat\event\upload_import_grade::create($params);
        $event->trigger();
        redirect($indexurl);
        exit;
    } else {
        echo $OUTPUT->header();
        $iid = null;
        $errors[] = get_string('importfailed', 'grades');
        foreach ($errors as $error) {
            echo $OUTPUT->notification($error);
        }
        // Display the standard upload file form.
        echo $renderer->display_empty_form();
        echo $mform->display();
        echo $OUTPUT->footer();
    }
} else if ($mform2->is_cancelled()) {
    $iid = null;
    redirect($PAGE->url);
} else {
    // If data hasn't been submitted then display the choose reason form.
    $mform2->display();
    echo $OUTPUT->footer();
}
