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

require_once(__DIR__ . '/../../config.php');
require_once('locallib.php');

$courseid = required_param('id', PARAM_INT);
$activityid = optional_param('activityid', null, PARAM_INT);

require_login($courseid);
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/gugcat/index.php', array('id' => $courseid)));
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('navname', 'local_gugcat'), new moodle_url('/local/gugcat'));

$PAGE->requires->css('/local/gugcat/styles/gugcat.css');
$PAGE->requires->js_call_amd('local_gugcat/main', 'init');

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);

$coursecontext = context_course::instance($course->id);
$students = get_enrolled_users($coursecontext, 'mod/coursework:submit');
$activities = local_gugcat::get_activities($courseid, $activityid);
$mods = array_reverse($activities);
$selectedmodule = is_null($activityid) ? array_pop($mods) : $activities[$activityid];
$PAGE->set_cm($selectedmodule);

$scaleid = local_gugcat::get_scaleid($selectedmodule);
//populate $GRADES with scales
local_gugcat::set_grade_scale($scaleid);
//populate provisional grade id and set it to static
local_gugcat::set_prv_grade_id($courseid, $selectedmodule->id, $scaleid);

//---------on submit grade capture
if (!empty($_POST)){
    // release provisional grade
    if (isset($_POST['release']) && isset($_POST['grades'])){
        grade_capture::release_prv_grade($courseid, $selectedmodule, $_POST['grades']);
        unset($_POST);
        header("Location: ".$_SERVER['REQUEST_URI']);
        exit;
    }else if (isset($_POST['grades']) && !empty($_POST['reason'])){
        $grades = $_POST['grades'];
        $reason = $_POST['reason'];
        if(array_column($grades,'grade')){
            $gradeitemid = local_gugcat::add_grade_item($courseid, $reason, $selectedmodule->id, $scaleid);
            foreach ($grades as $item) {
                if(isset($item['grade'])){
                    $grade = array_search($item['grade'], local_gugcat::$GRADES);
                    local_gugcat::add_update_grades($item['id'], $gradeitemid, $grade);
                }
            }
            $message = get_string('successaddall', 'local_gugcat');
            \core\notification::add($message, \core\output\notification::NOTIFY_SUCCESS);
            unset($_POST);
            header("Location: ".$_SERVER['REQUEST_URI']);
            exit;
        }else{
            //no grades selected
            print_error('errorgraderequired', 'local_gugcat', $PAGE->url);
        }
    }else{
        print_error('errorrequired', 'local_gugcat', $PAGE->url);
    }
}

$rows = grade_capture::get_rows($course, $selectedmodule, $students);
$columns = grade_capture::get_columns($selectedmodule);

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_grade_capture($activities, $rows, $columns);
echo $OUTPUT->footer();
