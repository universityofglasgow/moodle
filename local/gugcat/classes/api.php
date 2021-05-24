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
 * Class containing helper methods for processing data requests.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_gugcat;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/gugcat/locallib.php');

use context_course;
use local_gugcat;

/**
 * Class containing helper methods for processing data requests.
 *
 * @copyright  2020
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Toggles the settings of assessment display on student dashboard.
     *
     * @param int $id The course id
     * @return boolean
     */
    public static function display_assessments($id) {
        $currentstate = local_gugcat::switch_display_of_assessment_on_student_dashboard($id, context_course::instance($id)->id);
        $logparams = array (
            'context' => context_course::instance($id),
            'other' => array(
                'courseid' => $id,
                'status' => ($currentstate == 1) ? 'on' : 'off'
            )
        );
        $event = \local_gugcat\event\toggle_assessments_display::create($logparams);
        $event->trigger();
        return ($currentstate == 1) ? true : false;
    }

    /**
     * Retrieve grade conversion template data.
     *
     * @param int $templateid Template id
     * @return mixed || false
     */
    public static function get_converter_template_data($templateid) {
        global $DB, $USER;
        $template = $DB->get_record('gcat_converter_templates', array('id' => $templateid, 'userid' => $USER->id));
        ($template) ? $template->conversion = $DB->get_records('gcat_grade_converter', array('templateid' => $templateid)) : null;
        return ($template) ? json_encode($template) : false;
    }
}
