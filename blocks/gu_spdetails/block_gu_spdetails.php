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
 * Contains the class for the UofG Assessments Details block.
 *
 * @package    block_gu_spdetails
 * @copyright  2021 Accenture
 * @author     Franco Louie Magpusao, Jose Maria Abreu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

class block_gu_spdetails extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_gu_spdetails');
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $USER, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        if (!empty($this->return_enrolledcourses($USER->id))) {
            // Call JS.
            $this->page->requires->js_call_amd('block_gu_spdetails/main', 'init');

            $templatecontext = (array)[
                'tab_current'       => get_string('tab_current', 'block_gu_spdetails'),
                'tab_past'          => get_string('tab_past', 'block_gu_spdetails'),
                'tab_asessments'    => get_string('returnallassessment', 'block_gu_spdetails'),
                'label_course'      => get_string('label_course', 'block_gu_spdetails'),
                'label_assessment'  => get_string('label_assessment', 'block_gu_spdetails'),
                'label_weight'      => get_string('label_weight', 'block_gu_spdetails'),
                'label_grade'       => get_string('label_grade', 'block_gu_spdetails')
            ];
            $this->content->text = $OUTPUT->render_from_template('block_gu_spdetails/spdetails', $templatecontext);
        } else {
            $this->content->text = null;
        }

        return $this->content;
    }

    /**
     * Returns enrolled courses with enabled 'show_on_studentdashboard' custom field
     *
     * @param string $userid
     * @param string $fields
     * @return array Array of Course IDs
     */
    public function return_enrolledcourses($userid) {
        global $DB;
        $fields = "c.id";
        $customfieldjoin = "JOIN {customfield_field} cff
                            ON cff.shortname = 'show_on_studentdashboard'
                            JOIN {customfield_data} cfd
                            ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)";
        $customfieldwhere = "cfd.value = 1 AND c.visible = 1 AND c.visibleold = 1";
        $enrolmentselect = "SELECT DISTINCT e.courseid FROM {enrol} e
                            JOIN {user_enrolments} ue
                            ON (ue.enrolid = e.id AND ue.userid = ?)";
        $enrolmentjoin = "JOIN ($enrolmentselect) en ON (en.courseid = c.id)";
        $sql = "SELECT $fields FROM {course} c $customfieldjoin $enrolmentjoin
                WHERE $customfieldwhere";
        $param = array($userid);
        $results = $DB->get_records_sql($sql, $param);

        if ($results) {
            $studentcourses = array();
            foreach ($results as $courseid => $courseobject) {
                if (assessments_details::return_isstudent($courseid, $userid)) {
                    array_push($studentcourses, $courseid);
                }
            }
            return $studentcourses;
        } else {
            return array();
        }
    }

}
