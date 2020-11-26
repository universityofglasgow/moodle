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

require_once(dirname(__FILE__).'/../../config.php');


$courseid = required_param('course', PARAM_INT);
$confirmed = optional_param('confirmed', false, PARAM_BOOL);

// Access checks.
require_login($courseid, false);

$context = \context_course::instance($courseid);
require_capability('block/kuracloud:syncusers', $context);

$url = new \moodle_url('/blocks/kuracloud/syncusers.php', array('course' => $courseid));

$coursemappingstr = get_string('syncusers', 'block_kuracloud');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($coursemappingstr);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($coursemappingstr, $url);

$course = (new courses)->get_course($courseid);

if ($course->is_deleted()) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid, get_string('remotecoursedeleted', 'block_kuracloud'));
}

list($toadd, $toupdate, $todelete, $torestore) = $course->get_usersync_changes(!$confirmed);


if ($confirmed && confirm_sesskey()) {
    if (!empty($toadd)) {

        $newusers = $course->add_users($toadd);

        foreach ($newusers as $newuser) {

            $select = $DB->sql_like('email', ':email', false);

            $local = $DB->get_record_select('user', $select, array('email' => strtolower($newuser->email)));

            $mapping = new \stdClass;
            $mapping->userid = $local->id;
            $mapping->remote_studentid = $newuser->studentId;
            $mapping->remote_instanceid = $course->remote_instanceid;
            $mapping->remote_courseid = $course->remote_courseid;

            $DB->insert_record('block_kuracloud_users', $mapping);
        }
    }

    if (!empty($torestore)) {
        $course->restore_users($torestore);
    }

    if (!empty($toupdate)) {
        $course->update_users($toupdate);
    }

    if (!empty($todelete)) {
        $course->delete_users($todelete);
    }

    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
}


$output = $PAGE->get_renderer('block_kuracloud');
echo $output->header();
echo $output->heading($coursemappingstr);
if (!$confirmed) {

    $syncusers = new \block_kuracloud\output\syncusers($toadd, $toupdate, $todelete, $torestore);

    echo $output->confirm($output->render($syncusers), "syncusers.php?confirmed=1&course=".$courseid,
        $CFG->wwwroot.'/course/view.php?id='.$courseid);
}

echo $output->footer();