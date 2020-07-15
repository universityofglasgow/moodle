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
 * Synchronise auto enrolments
 *
 * @package    enrol_gudatabase
 * @copyright  2017 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/gusync/lib.php');

$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/gudatabase:config', $context);

$PAGE->set_url('/enrol/gudatabase/sync.php', array('courseid' => $courseid));
$PAGE->set_title(get_string('sync', 'enrol_gudatabase'));
echo $OUTPUT->header();
echo '<div class="alert alert-warning">' . get_string('patience', 'enrol_gudatabase') . '</div>';
echo "<pre>";

// Get the enrolment plugin.
$plugin = enrol_get_plugin('gudatabase');

if ($instances = $DB->get_records('enrol', array('courseid' => $courseid, 'enrol' => 'gudatabase'))) {
    foreach ($instances as $instance) {
        echo get_string('processinginstance', 'enrol_gudatabase', $plugin->get_instance_name($instance)) . "\n";
        if (!$plugin->enrolment_possible($course, $instance)) {
            echo get_string('enrolmentnotpossible', 'enrol_gudatabase') . "\n";
        }
        echo get_string('syncusers', 'enrol_gudatabase') . "\n";
        $plugin->enrol_course_users($course, $instance);
        echo get_string('syncgroups', 'enrol_gudatabase') . "\n";
        $plugin->sync_groups($course, $instance);
    }
}
cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($course->id));

// Export enrolments to 'Sharepoint' database.
echo get_string('syncexport', 'enrol_gudatabase') . "\n";
local_gusync_sync_one($course->id);
echo "</pre>";

$conurl = new moodle_url('/course/view.php', array('id' => $course->id));
echo $OUTPUT->continue_button($conurl);
echo $OUTPUT->footer();

//redirect(new moodle_url('/course/view.php', array('id' => $courseid)));

