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
 * @package    enrol_gudatabase
 * @copyright  2019 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_gudatabase\task;

defined('MOODLE_INTERNAL') || die;

class sync_course extends \core\task\adhoc_task {

    public function execute() {
        global $DB;

        // Get enrolment plugin
        $plugin = enrol_get_plugin('gudatabase');

        // Get custom data (and courseid)
        $data = $this->get_custom_data();
        $courseid = $data->courseid;
        $newcourse = $data->newcourse;
        if ($course = $DB->get_record('course', ['id' => $courseid])) {
            mtrace('enrol_gudatabase: processing course ' . $course->fullname);
            $plugin->process_course($newcourse, $course);
        } else {
            mtrace('enrol_gudatabase: warning, course no longer exists id=' . $courseid);
        }
    }

}
