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
require_capability('block/kuracloud:mapcourses', $context);

$url = new \moodle_url('/blocks/kuracloud/deletecoursemapping.php');

$coursemappingstr = get_string('coursemapping', 'block_kuracloud');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($coursemappingstr);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($coursemappingstr, $url);

$output = $PAGE->get_renderer('block_kuracloud');


$mapping = courses::get_mapping($courseid);

if (!empty($confirmed) && confirm_sesskey()) {
    $courses = new courses;

    $courses->delete_mapping($mapping);
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);

} else {
    echo $output->header();
    echo $output->heading($coursemappingstr);
    echo $output->confirm(get_string('confirmdeletemapping', 'block_kuracloud', $mapping->remote_name),
        "deletecoursemapping.php?confirmed=1&course=".$courseid, $CFG->wwwroot."/course/view.php?id=".$courseid);
    echo $output->footer();
}