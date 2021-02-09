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
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao, Jose Maria Abreu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

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
        global $PAGE, $USER, $OUTPUT;
        $user = $USER;

        // call JS and CSS
        $PAGE->requires->css('/blocks/gu_spdetails/styles.css');
        $PAGE->requires->js_call_amd('block_gu_spdetails/main', 'init');

        $templatecontext = (array)[
            'tab_current'            => get_string('tab_current', 'block_gu_spdetails'),
            'tab_past'               => get_string('tab_past', 'block_gu_spdetails'),
        ];

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass();
        $this->content->text   = $this->return_enrolledcourses($USER->id) ? $OUTPUT->render_from_template('block_gu_spdetails/spdetails', $templatecontext) : null;

        return $this->content;
    }

    /**
     * Returns enrolled courses with custom field 'show_on_studentdashboard'
     * 
     * @param string $userid
     * @param string $fields
     * @return array SQL data
     */

    public function return_enrolledcourses($userid, $fields = "c.id"){
        global $DB;

        $customfieldjoin = "JOIN
                        {customfield_field} cff ON cff.shortname = 'show_on_studentdashboard'
                            JOIN
                        {customfield_data} cfd ON (cfd.fieldid = cff.id
                            AND cfd.instanceid = c.id)";

        $customfieldwhere = "cfd.value > 0";

        $enrolmentselect = "SELECT DISTINCT
                                e.courseid
                            FROM
                                {enrol} e
                            JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ?)";    

        $enrolmentjoin = "JOIN ($enrolmentselect) en ON (en.courseid = c.id)";

        $sql = "SELECT $fields FROM {course} c $customfieldjoin $enrolmentjoin WHERE $customfieldwhere";
        $param = array($userid);
        return $DB->get_records_sql($sql, $param);
    }
}
