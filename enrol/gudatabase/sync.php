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

require(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/gusync/lib.php');

$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/gudatabase:config', $context);

// Get the enrolment plugin.
$plugin = enrol_get_plugin('gudatabase');

if ($instances = $DB->get_records('enrol', array('courseid' => $courseid, 'enrol' => 'gudatabase'))) {
    foreach ($instances as $instance) {
        $plugin->enrol_course_users($course, $instance);
        $plugin->sync_groups($course, $instance);
    }
}
cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($course->id));

// Export enrolments to 'Sharepoint' database.
local_gusync_sync_one($course->id);

redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
die;

