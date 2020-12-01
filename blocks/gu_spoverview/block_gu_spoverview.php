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
 * @author     Alejandro de Guzman <a.g.de.guzman@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_gu_spoverview extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_gu_spoverview');
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $USER, $DB, $PAGE, $OUTPUT;

        $PAGE->requires->css('/blocks/gu_spoverview/styles.css');

        $student_id = $USER->id;
        $tpl = 'block_gu_spoverview';
        $current_yr = date("Y");

        // Query to retrieve all assignments where status is submitted
        $assignments_submitted = $DB->get_records_sql("SELECT * FROM `mdl_assign_submission` 
                                                     WHERE FROM_UNIXTIME(timecreated, '%Y') = $current_yr 
                                                     AND userid = $student_id AND status='submitted'");

        // Query to retrieve all assignments of a specific student
        $assignments = $DB->get_records_sql("SELECT DISTINCT i.itemname 
                                            FROM `mdl_course_modules` cm 
                                            INNER JOIN `mdl_modules` m ON m.id = cm.module 
                                            INNER JOIN `mdl_grade_items` i ON i.courseid = cm.course
                                            INNER JOIN 	`mdl_assign` asn ON asn.name = i.itemname 
                                            INNER JOIN `mdl_grade_grades` g ON g.itemid = i.id 
                                            INNER JOIN `mdl_user` u ON u.id = g.userid 
                                            WHERE m.name IN ('assign') 
                                            AND i.itemmodule IS NOT NULL AND u.id = $student_id 
                                            AND FROM_UNIXTIME(i.timemodified, '%Y') = $current_yr"); 

        // Query to retrieve all assignments that are not yet submitted and due date is past current date
        $assignments_overdue = $DB->get_records_sql("SELECT  DISTINCT i.itemname
                                                    FROM `mdl_course_modules` cm 
                                                    INNER JOIN `mdl_modules` m ON m.id = cm.module 
                                                    INNER JOIN `mdl_grade_items` i ON i.courseid = cm.course 
                                                    INNER JOIN `mdl_grade_grades` g ON g.itemid = i.id
                                                    INNER JOIN `mdl_user` u ON u.id = g.userid
                                                    INNER JOIN 	`mdl_assign` asn ON asn.name = i.itemname
                                                    INNER JOIN `mdl_grade_grades` gg ON gg.userid = $student_id
                                                    INNER JOIN `mdl_assign_submission` asub ON (asub.assignment = asn.id AND asub.status <> 'submitted')  
                                                    WHERE m.name IN ('assign') 
                                                    AND i.itemmodule IS NOT NULL AND u.id = $student_id
                                                    AND FROM_UNIXTIME(i.timemodified, '%Y') = $current_yr 
                                                    AND FROM_UNIXTIME(asn.duedate) < NOW()
                                                    AND gg.finalgrade IS NULL");

        // Query to retrieve all assignments with grade and feedback
        $assignments_marked = $DB->get_records_sql("SELECT DISTINCT i.itemname 
                                                    FROM `mdl_course_modules` cm 
                                                    INNER JOIN `mdl_modules` m ON m.id = cm.module 
                                                    INNER JOIN `mdl_grade_items` i ON i.courseid = cm.course 
                                                    INNER JOIN `mdl_assign` asn ON asn.name = i.itemname 
                                                    INNER JOIN `mdl_grade_grades` g ON g.itemid = i.id 
                                                    INNER JOIN `mdl_user` u ON u.id = g.userid 
                                                    WHERE m.name IN ('assign') 
                                                    AND i.itemmodule IS NOT NULL AND u.id = $student_id 
                                                    AND FROM_UNIXTIME(i.timemodified, '%Y') = $current_yr 
                                                    AND g.finalgrade IS NOT NULL 
                                                    AND g.feedback IS NOT NULL");

        // Query to retrieve all quizzes with grade and feedback
        $quizzes_marked = $DB->get_records_sql("SELECT DISTINCT i.itemname 
                                                FROM `mdl_course_modules` cm 
                                                INNER JOIN `mdl_modules` m ON m.id = cm.module 
                                                INNER JOIN `mdl_grade_items` i ON i.courseid = cm.course 
                                                INNER JOIN `mdl_quiz` q ON q.name = i.itemname 
                                                INNER JOIN `mdl_grade_grades` g ON g.itemid = i.id 
                                                INNER JOIN `mdl_user` u ON u.id = g.userid 
                                                WHERE m.name IN ('quiz') 
                                                AND i.itemmodule IS NOT NULL AND u.id = $student_id 
                                                AND FROM_UNIXTIME(i.timemodified, '%Y') = $current_yr 
                                                AND g.finalgrade IS NOT NULL 
                                                AND g.feedback IS NOT NULL");

        // Set assignment/assessment count variables
        $assign_submitted_count = (int) sizeof($assignments_submitted);
        $assign_tosubmit_count = (int) sizeof($assignments) - (int) sizeof($assignments_submitted);
        $assign_overdue_count = (int) sizeof($assignments_overdue);
        $assess_marked_count = (int) sizeof($assignments_marked) + (int) sizeof($quizzes_marked);

        // Set singular/plural strings for Assignment and Assessment
        $assign_str = ($assign_submitted_count == 1) ? get_string('assignment_sn', $tpl) : get_string('assignment_pl', $tpl);
        $assess_str = ($assess_marked_count == 1) ? get_string('assessment_sn', $tpl) : get_string('assessment_pl', $tpl);

        $templatecontext = (object)[
            'assess_submitted_count'        => $assign_submitted_count,
            'assess_tosubmit_count'         => $assign_tosubmit_count,
            'assess_overdue_count'          => $assign_overdue_count,
            'assess_marked_count'           => $assess_marked_count,
            'assess_submitted_icon'         => '../blocks/gu_spoverview/pix/assessments_submitted.svg',
            'assess_tosubmit_icon'          => '../blocks/gu_spoverview/pix/assessments_tosubmit.svg',
            'assess_overdue_icon'           => '../blocks/gu_spoverview/pix/assessments_overdue.svg',
            'assess_marked_icon'            => '../blocks/gu_spoverview/pix/assessments_marked.svg',
            'assess_submitted_str'          => $assign_str.get_string('submitted', $tpl),
            'assess_tosubmit_str'           => get_string('tobesubmitted', $tpl),
            'assess_overdue_str'            => get_string('overdue', $tpl),
            'assess_marked_str'             => $assess_str.get_string('marked', $tpl),
        ];

        $this->content = new stdClass;
        $this->content->text = $OUTPUT->render_from_template('block_gu_spoverview/spoverview', $templatecontext);

        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }
}
