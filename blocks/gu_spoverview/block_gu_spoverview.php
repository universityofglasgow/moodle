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

    public function hide_header() {
        return TRUE;
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
     * @todo Create a renderer for assessments block.
     *       See myoverview plugin for reference.
     */
    public function get_content() {
        global $USER, $DB, $PAGE, $OUTPUT;


        $PAGE->requires->css('/blocks/gu_spoverview/styles.css');

        $student_id = $USER->id;
        $current_yr = date("Y");

        // Query for all the assignments whose status was submitted
        $assigment_submitted    = $DB->get_records_sql("SELECT * FROM `mdl_assign_submission` 
                                                        WHERE FROM_UNIXTIME(timecreated, '%Y') = $current_yr 
                                                        AND userid = $student_id AND status='submitted'");

        // Query for all assignments for a specific student
        $assignments            = $DB->get_records_sql("SELECT DISTINCT i.itemname 
                                                        FROM `mdl_course_modules` cm 
                                                        INNER JOIN `mdl_modules` m ON m.id = cm.module 
                                                        INNER JOIN `mdl_grade_items` i ON i.courseid = cm.course
                                                        INNER JOIN 	`mdl_assign` ass ON ass.name = i.itemname 
                                                        INNER JOIN `mdl_grade_grades` g ON g.itemid = i.id 
                                                        INNER JOIN `mdl_user` u ON u.id = g.userid 
                                                        WHERE m.name IN ('assign') 
                                                        AND i.itemmodule IS NOT NULL AND u.id = $student_id 
                                                        AND FROM_UNIXTIME(i.timemodified, '%Y') = $current_yr"); 

        // Query for all the assignments that were not yet submit and overdue
        $assignment_duedate     = $DB->get_records_sql("SELECT  DISTINCT i.itemname
                                                        FROM `mdl_course_modules` cm 
                                                        INNER JOIN `mdl_modules` m ON m.id = cm.module 
                                                        INNER JOIN `mdl_grade_items` i ON i.courseid = cm.course 
                                                        INNER JOIN `mdl_grade_grades` g ON g.itemid = i.id
                                                        INNER JOIN `mdl_user` u ON u.id = g.userid
                                                        INNER JOIN 	`mdl_assign` ass ON ass.name = i.itemname
                                                        INNER JOIN `mdl_grade_grades` gg ON gg.userid = $student_id
                                                        INNER JOIN `mdl_assign_submission` asub ON (asub.assignment = ass.id AND asub.status <> 'submitted')  
                                                        WHERE m.name IN ('assign') 
                                                        AND i.itemmodule IS NOT NULL AND u.id = $student_id
                                                        AND FROM_UNIXTIME(i.timemodified, '%Y') = $current_yr 
                                                        AND FROM_UNIXTIME(ass.duedate) < NOW()
                                                        AND gg.finalgrade IS NULL");

        // Query for all the assignments, with a grade and with feedback
        $assign_marked_assessment_count = $DB->get_records_sql("SELECT DISTINCT i.itemname 
                                                                FROM `mdl_course_modules` cm 
                                                                INNER JOIN `mdl_modules` m ON m.id = cm.module 
                                                                INNER JOIN `mdl_grade_items` i ON i.courseid = cm.course 
                                                                INNER JOIN `mdl_assign` ass ON ass.name = i.itemname 
                                                                INNER JOIN `mdl_grade_grades` g ON g.itemid = i.id 
                                                                INNER JOIN `mdl_user` u ON u.id = g.userid 
                                                                WHERE m.name IN ('assign') 
                                                                AND i.itemmodule IS NOT NULL AND u.id = $student_id 
                                                                AND FROM_UNIXTIME(i.timemodified, '%Y') = $current_yr 
                                                                AND g.finalgrade IS NOT NULL 
                                                                AND g.feedback IS NOT NULL");

        // Query for all the quiz, with a grade and with feedback
        $quiz_marked_assessment_count = $DB->get_records_sql("SELECT DISTINCT i.itemname 
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

        if($this->content !== NULL) {
            return $this->content;
        }

        $templatecontext = (object)[
            'assignment_submitted_count' => sizeof($assigment_submitted),
            'to_be_submitted'            => (int)sizeof($assignments) - (int)sizeof($assigment_submitted),
            'due_date'                   => (int)sizeof($assignment_duedate),
            'marked_assessment_count'    => (int)sizeof($assign_marked_assessment_count) + (int)sizeof($quiz_marked_assessment_count),
            'assignment_svg_path'        => '../blocks/gu_spoverview/pix/assignment-submitted.svg',
            'to_be_submitted_svg_path'   => '../blocks/gu_spoverview/pix/to-be-submitted.svg',
            'overdue_svg_path'           => '../blocks/gu_spoverview/pix/overdue.svg',
            'assessment_marked_svg_path' => '../blocks/gu_spoverview/pix/assessment-marked.svg'
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
