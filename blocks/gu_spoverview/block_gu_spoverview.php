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
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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
        global $USER, $DB, $PAGE, $OUTPUT;

        $PAGE->requires->css('/blocks/gu_spoverview/styles.css');
        $lang = 'block_gu_spoverview';
        $userid = $USER->id;

        $courses = enrol_get_all_users_courses($userid, true);
        $courseids = array_column($courses, 'id');

        $assignments = (count($courseids) > 0) ? self::get_user_assignments($userid, $courseids) : array();
        $assignments_submitted = 0;
        $assignments_tosubmit = 0;
        $assignments_overdue = 0;
        $assessments_marked = (count($courseids) > 0) ? self::get_user_assessments_count($userid, $courseids) : 0;

        foreach ($assignments as $assignment) {
            if($assignment->status != 'submitted') {
                if(time() > $assignment->startdate) {
                    if(($assignment->duedate > 0 && time() <= $assignment->duedate)
                        || ($assignment->cutoffdate > 0 && time() <= $assignment->cutoffdate)
                        || (($assignment->extensionduedate > 0 || !is_null($assignment->extensionduedate))
                             && time() <= $assignment->extensionduedate)) {
                        $assignments_tosubmit++;
                    }else{
                        $assignments_overdue++;
                    }
                }
            }else{
                $assignments_submitted++;
            }
        }

        // Set singular/plural strings for Assignment and Assessment
        $assignment_str = ($assignments_submitted == 1) ? get_string('assignment', $lang) : get_string('assignments', $lang);
        $assessment_str = ($assessments_marked == 1) ? get_string('assessment', $lang) : get_string('assessments', $lang);

        $templatecontext = (object)[
            'assessments_submitted'        => $assignments_submitted,
            'assessments_tosubmit'         => $assignments_tosubmit,
            'assessments_overdue'          => $assignments_overdue,
            'assessments_marked'           => $assessments_marked,
            'assessments_submitted_icon'   => '../blocks/gu_spoverview/pix/assessments_submitted.svg',
            'assessments_tosubmit_icon'    => '../blocks/gu_spoverview/pix/assessments_tosubmit.svg',
            'assessments_overdue_icon'     => '../blocks/gu_spoverview/pix/assessments_overdue.svg',
            'assessments_marked_icon'      => '../blocks/gu_spoverview/pix/assessments_marked.svg',
            'assessments_submitted_str'    => $assignment_str.get_string('submitted', $lang),
            'assessments_tosubmit_str'     => get_string('tobesubmitted', $lang),
            'assessments_overdue_str'      => get_string('overdue', $lang),
            'assessments_marked_str'       => $assessment_str.get_string('marked', $lang),
        ];

        $this->content = new stdClass;
        $this->content->text = $OUTPUT->render_from_template('block_gu_spoverview/spoverview', $templatecontext);

        return $this->content;
    }

    /**
     * Returns all user assignments including submission status and extension due date.
     * 
     * @param int $userid
     * @param array $courseids
     * @return stdClass Assignment objects if records are returned,
     *  otherwise return empty object
     */
    public static function get_user_assignments($userid, $courseids) {
        global $DB;
        $params = array($userid, $userid);
        $incourseids = implode(',', $courseids);

        $sql = "SELECT ma.name, ma.allowsubmissionsfromdate as `startdate`,
                ma.duedate, ma.cutoffdate, mas.status, mauf.extensionduedate
                FROM `mdl_assign` ma
                LEFT JOIN `mdl_assign_submission` mas ON ma.id = mas.assignment AND mas.userid = ?
                LEFT JOIN `mdl_assign_user_flags` mauf ON ma.id = mauf.assignment AND mauf.userid = ?
                WHERE ma.course IN (".$incourseids.")";

        $assignments = ($assignments = $DB->get_records_sql($sql, $params)) ? $assignments : new stdClass;
        return $assignments;
    }

    /**
     * Returns count of graded assessments (activity modules)
     * 
     * @param int $userid
     * @param array $courseids
     * @return int Count of Assessment records
     */
    public static function get_user_assessments_count($userid, $courseids) {
        global $DB;
        $params = array($userid, 'mod');
        $incourseids = implode(',', $courseids);

        $sql = "SELECT mgi.itemname, mgg.finalgrade
                FROM `mdl_grade_items` mgi
                JOIN `mdl_grade_grades` mgg ON mgi.id = mgg.itemid AND mgg.userid = ? AND mgg.finalgrade IS NOT NULL
                WHERE mgi.itemtype = ? AND mgi.courseid IN (".$incourseids.")";

        $assessments = ($assessments = $DB->get_records_sql($sql, $params)) ? $assessments : array();
        $assessments_count = count($assessments);
        return $assessments_count;
    }
}
