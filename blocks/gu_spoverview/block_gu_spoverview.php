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
 * Contains the class for the UofG Assessments Overview block.
 *
 * @package    block_gu_spoverview
 * @copyright  2021 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once('querylib.php');

class block_gu_spoverview extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_gu_spoverview');
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
        global $USER, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content = new stdClass;

        $iscurrentlyenrolled = ($this->return_enrolledcourses($USER->id)) ? true : false;

        if($iscurrentlyenrolled) {
            $courses = $this->return_enrolledcourses($USER->id);
            $courseids = implode(', ', $courses);
            $count = return_assessments_count($USER->id, $courseids);

            $submitted_str = ($count->submitted == 1) ? get_string('assessment', 'block_gu_spoverview').
                                                        get_string('submitted', 'block_gu_spoverview') :
                                                        get_string('assessments', 'block_gu_spoverview').
                                                        get_string('submitted', 'block_gu_spoverview');
            $marked_str = ($count->marked == 1) ? get_string('assessment', 'block_gu_spoverview').
                                                  get_string('marked', 'block_gu_spoverview') :
                                                  get_string('assessments', 'block_gu_spoverview').
                                                  get_string('marked', 'block_gu_spoverview');

            $assessments_submitted_icon = $OUTPUT->image_url('assessments_submitted', 'theme');
            $assessments_tosubmit_icon = $OUTPUT->image_url('assessments_tosubmit', 'theme');
            $assessments_overdue_icon = $OUTPUT->image_url('assessments_overdue', 'theme');
            $assessments_marked_icon = $OUTPUT->image_url('assessments_marked', 'theme');

            $templatecontext = (object)[
                'assessments_submitted'        => $count->submitted,
                'assessments_tosubmit'         => $count->tosubmit,
                'assessments_overdue'          => $count->overdue,
                'assessments_marked'           => $count->marked,
                'assessments_submitted_icon'   => $assessments_submitted_icon,
                'assessments_tosubmit_icon'    => $assessments_tosubmit_icon,
                'assessments_overdue_icon'     => $assessments_overdue_icon,
                'assessments_marked_icon'      => $assessments_marked_icon,
                'assessments_submitted_str'    => $submitted_str,
                'assessments_tosubmit_str'     => get_string('tobesubmitted', 'block_gu_spoverview'),
                'assessments_overdue_str'      => get_string('overdue', 'block_gu_spoverview'),
                'assessments_marked_str'       => $marked_str,
            ];

            $this->content->text = $OUTPUT->render_from_template('block_gu_spoverview/spoverview', $templatecontext);
        }else{
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

        $courseids = array();
        $fields = "c.id";
        $customfieldjoin = "JOIN {customfield_field} cff
                            ON cff.shortname = 'show_on_studentdashboard'
                            JOIN {customfield_data} cfd
                            ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)";

        $customfieldwhere = "cfd.value = 1";
        $enrolmentselect = "SELECT DISTINCT e.courseid FROM {enrol} e
                            JOIN {user_enrolments} ue
                            ON (ue.enrolid = e.id AND ue.userid = ?)";
        $enrolmentjoin = "JOIN ($enrolmentselect) en ON (en.courseid = c.id)";
        $sql = "SELECT $fields FROM {course} c $customfieldjoin $enrolmentjoin
                WHERE $customfieldwhere";
        $params = array($userid);
        $results = $DB->get_records_sql($sql, $params);

        if($results) {
            foreach($results as $courseid => $courseobject) {
                if($this->return_isstudent($courseid)) {
                    array_push($courseids, $courseid);
                }
            }
        }
    
        return $courseids;
    }

    /**
     * Checks if user has capability of a student
     * 
     * @param string $courseid
     * @param string $userid
     * @return boolean has_capability
     */
    public function return_isstudent($courseid) {
        $context = context_course::instance($courseid);
        return has_capability('moodle/grade:view', $context, null, false);
    }
}
