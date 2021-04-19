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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_kuracloud;
    define('NO_OUTPUT_BUFFERING', true);
require_once(dirname(__FILE__).'/../../config.php');

require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->libdir.'/grade/grade_item.php');

$courseid = required_param('course', PARAM_INT);
$confirmed = optional_param('confirmed', false, PARAM_BOOL);

// Access checks.
require_login($courseid, false);

$context = \context_course::instance($courseid);
require_capability('block/kuracloud:syncgrades', $context);

$url = new \moodle_url('/blocks/kuracloud/syncgrades.php', array('course' => $courseid));

$coursemappingstr = get_string('syncgrades', 'block_kuracloud');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($coursemappingstr);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($coursemappingstr, $url);

$course = (new courses)->get_course($courseid);

if ($course->is_deleted()) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid, get_string('remotecoursedeleted', 'block_kuracloud'));
}
$output = $PAGE->get_renderer('block_kuracloud');
echo $output->header();

echo $output->heading($coursemappingstr);

if ($confirmed && confirm_sesskey()) {

    $progress = 1;

    $progressbar = new \core\progress\display(get_string('syncgrades', 'block_kuracloud'));
    $progressbar->start_progress(get_string('syncgrades', 'block_kuracloud'), 10);

    $callback = function($loops) {
        global $progress, $progressbar;
        $progress++;
        $progressbar->progress($progress);
    };

    $remotegrades = $course->get_grades($callback);
    $progressbar->progress(9);

    $allgradeitems = \grade_item::fetch_all(array(
            'courseid' => $courseid,
            'itemtype' => 'manual',
            'itemmodule' => 'kuracloud',
            ));


    foreach ($remotegrades as $id => $remotegrade) {

        list($lessonid, $revisionid) = explode('-', $id);
        $item = array(
            'itemname' => $remotegrade->itemname,
            'idnumber' => $remotegrade->idnumber,
            'grademin' => $remotegrade->grademin,
            'grademax' => $remotegrade->grademax,
            'gradetype' => GRADE_TYPE_VALUE,
        );
        $grades = array();

        // The $grade param doesn't support blocks - the docs are somewhat incorrect.
        grade_update('block/kuracloud', $courseid, 'manual', 'kuracloud', $lessonid, $revisionid, null, $item);

        $gradeitem = \grade_item::fetch(array(
            'courseid' => $courseid,
            'itemtype' => 'manual',
            'itemmodule' => 'kuracloud',
            'iteminstance' => $lessonid,
            'itemnumber' => $revisionid,
            ));

        unset($allgradeitems[$gradeitem->id]);

        foreach ($remotegrade->grades as $userid => $grade) {
            $gradeitem->update_final_grade($userid, $grade);
        }

    }

    // Delete any grade items not in kuraCloud.
    if (!empty($allgradeitems)) {
        foreach ($allgradeitems as $gradeitem) {
            $gradeitem->delete();
        }
    }
    $progressbar->end_progress();

    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid, get_string('gradesynccomplete', 'block_kuracloud'));
} else {

    // Check for user sync issues.
    list($toadd, $toupdate, $todelete, $torestore) = $course->get_usersync_changes(true);

    if (!empty($toadd) || !empty($todelete) || !empty($torestore)) {
        redirect($CFG->wwwroot.'/blocks/kuracloud/syncusers.php?course='.$courseid, get_string('needusersync', 'block_kuracloud'));
    }

    echo $output->confirm(get_string('confirmgradesync', 'block_kuracloud'), 'syncgrades.php?course='.$courseid.'&confirmed=true',
        $CFG->wwwroot.'/course/view.php?id='.$courseid);

}
echo $output->footer();