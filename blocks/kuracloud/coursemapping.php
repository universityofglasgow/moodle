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
require_once('coursemapping_form.php');

$courseid = required_param('course', PARAM_INT);

// Access checks.
require_login($courseid, false);

$context = \context_course::instance($courseid);
require_capability('block/kuracloud:mapcourses', $context);

$url = new \moodle_url('/blocks/kuracloud/coursemapping.php');

$coursemappingstr = get_string('coursemapping', 'block_kuracloud');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($coursemappingstr);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($coursemappingstr, $url);

$output = $PAGE->get_renderer('block_kuracloud');

$mform = new coursemapping_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
} else if ($fromform = $mform->get_data()) {
    $course = new courses;
    list($instanceid, $remotecourseid) = explode('-', $fromform->remotecourse);
    $course->save_mapping($courseid, $instanceid, $remotecourseid);

    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
} else {
    $course = new courses;
    $data = array('course' => $courseid);

    if ($mapping = $course->get_mapping($courseid)) {
        $data['remotecourse'] = $mapping->remote_instanceid.'-'.$mapping->remote_courseid;
    }

    $mform->set_data($data);

    echo $output->header();
    echo $output->heading($coursemappingstr);
    $mform->display();
     echo $output->footer();
}