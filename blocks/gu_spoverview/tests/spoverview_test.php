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
 * Events tests.
 *
 * @package    block_spoverview
 * @category   test
 * @copyright  2020 Alejandro De Guzman <a.g.de.guzman@accenture.com>
 */

defined('MOODLE_INTERNAL') || die();

class block_gu_spoverview_testcase extends advanced_testcase {

    /**
     * Setup test data.
     */
    public function setUp(){
        $this->resetAfterTest(true);

        $gen = $this->getDataGenerator();

        $this->student = $gen->create_user();
        $this->teacher = $gen->create_user();

        $this->category = $gen->create_category(array('id' => 1));

        $this->course = $gen->create_course(array(
                                    'id'       => 1, 
                                    'name'     => 'Sample course', 
                                    'category' => $this->category->id
                                )
                        );

        $this->coursecontext = context_course::instance($this->course->id);

        $gen->enrol_user($this->student->id, $this->course->id, 'student');
        $gen->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');

        $this->assign1 = $gen->create_module('assign', 
            array(
                'course' => $this->course->id,
                'duedate' => time() * 30 * 30,
                'status' => 'new'
            )
        );

        $modulecontext1 = context_module::instance($this->assign1->cmid);
        $assign1 = new assign($modulecontext1, false, false);

        $this->assign2 = $gen->create_module('assign', 
            array(
                'course' => $this->course->id,
                'duedate' => time() - 10,
                'status' => 'new'
            )
        );

        $modulecontext2 = context_module::instance($this->assign2->cmid);
        $assign2 = new assign($modulecontext2, false, false);
    }   


    public function test_to_get_different_type_of_assignment(){
        $assignments_submitted = 0;
        $assignments_tosubmit = 0;
        $assignments_overdue = 0;
        
        $courses = enrol_get_all_users_courses($this->student->id, true);
        $courseids = array_column($courses, 'id');

        $assignments = (count($courseids) > 0) ? self::get_user_assignments($this->student->id, $courseids) : 0;

        foreach ($assignments as $assignment) {
            if($assignment->status != 'submitted') {
                if(time() > $assignment->startdate) {
                    if(($assignment->duedate > 0 && time() <= $assignment->duedate)
                        || ($assignment->cutoffdate > 0 && time() <= $assignment->cutoffdate)
                        || (($assignment->extensionduedate > 0 || !is_null($assignment->extensionduedate))
                            && time() <= $assignment->extensionduedate)) {
                        $assignments_tosubmit++;
                    }else{
                        if($assignment->duedate != 0 || $assignment->cutoffdate != 0
                            || $assignment->extensionduedate != 0) {
                            $assignments_overdue++;
                        }
                    }
                }
            }else{
                $assignments_submitted++;
            }
        }

        $this->assertNotEmpty($courses, 'empty');
        $this->assertNotEmpty($assignments, 'empty');

        $this->assertGreaterThanOrEqual(1, $assignments_tosubmit);
        $this->assertGreaterThanOrEqual(1, $assignments_overdue);
        $this->assertGreaterThanOrEqual(0, $assignments_submitted);

        $this->assertContains(
            $this->course->id, 
            $courseids, 
            "courseids array doesn't contains same course id"
        );
    }

    public function test_check_marked_assignment(){
        $courses = enrol_get_all_users_courses($this->student->id, true);
        $courseids = array_column($courses, 'id');
        $assessments_marked = (count($courseids) > 0) ? self::get_user_assessments_count($this->student->id, $courseids) : 0;

        $this->assertNotEmpty($courses, 'empty');
        $this->assertGreaterThanOrEqual(0, $assessments_marked);
        $this->assertContains(
            $this->course->id, 
            $courseids, 
            "courseids array doesn't contains same course id"
        );
    }

    public function test_check_user_assignments(){
        $courses = enrol_get_all_users_courses($this->student->id, true);
        $courseids = array_column($courses, 'id');
        $assignments = (count($courseids) > 0) ? self::get_user_assignments($this->student->id, $courseids) : array();

        $this->assertNotEmpty($assignments , 'empty');
        $this->assertNotEmpty($courses, 'empty');
        $this->assertContains(
            $this->course->id, 
            $courseids, 
            "courseids array doesn't contains same course id"
        );
    }

    public function test_user_interface_content(){
        global $OUTPUT;

        $lang = 'block_gu_spoverview';
        $assignments_submitted = 0;
        $assignments_tosubmit = 0;
        $assignments_overdue = 0;
        
        $courses = enrol_get_all_users_courses($this->student->id, true);
        $courseids = array_column($courses, 'id');

        $assignments = (count($courseids) > 0) ? self::get_user_assignments($this->student->id, $courseids) : array();
        $assessments_marked = (count($courseids) > 0) ? self::get_user_assessments_count($this->student->id, $courseids) : 0;

        foreach ($assignments as $assignment) {
            if($assignment->status != 'submitted') {
                if(time() > $assignment->startdate) {
                    if(($assignment->duedate > 0 && time() <= $assignment->duedate)
                        || ($assignment->cutoffdate > 0 && time() <= $assignment->cutoffdate)
                        || (($assignment->extensionduedate > 0 || !is_null($assignment->extensionduedate))
                            && time() <= $assignment->extensionduedate)) {
                        $assignments_tosubmit++;
                    }else{
                        if($assignment->duedate != 0 || $assignment->cutoffdate != 0
                            || $assignment->extensionduedate != 0) {
                            $assignments_overdue++;
                        }
                    }
                }
            }else{
                $assignments_submitted++;
            }
        }

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

        $expectedui  = file_get_contents("./blocks/gu_spoverview/tests/expected_ui.html");
   
        $actualui = $OUTPUT->render_from_template('block_gu_spoverview/spoverview', $templatecontext);

        $this->assertEquals($expectedui, $actualui);
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
        global $DB, $CFG;

        $params = array($userid, $userid);
        $incourseids = implode(',', $courseids);

        $sql = "SELECT ma.name, ma.allowsubmissionsfromdate as `startdate`,
                ma.duedate, ma.cutoffdate, mas.status, mauf.extensionduedate
                FROM `{$CFG->prefix}assign` ma
                LEFT JOIN `{$CFG->prefix}assign_submission` mas ON ma.id = mas.assignment 
                AND mas.userid = ?
                LEFT JOIN `{$CFG->prefix}assign_user_flags` mauf ON ma.id = mauf.assignment 
                AND mauf.userid = ?
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
        global $DB, $CFG;
        $params = array($userid, 'mod');
        $incourseids = implode(',', $courseids);

        $sql = "SELECT mgi.itemname, mgg.finalgrade
                FROM `{$CFG->prefix}grade_items` mgi
                JOIN `{$CFG->prefix}grade_grades` mgg ON mgi.id = mgg.itemid 
                AND mgg.userid = ? 
                AND mgg.finalgrade IS NOT NULL
                WHERE mgi.itemtype = ? 
                AND mgi.courseid IN (".$incourseids.")";

        $assessments = ($assessments = $DB->get_records_sql($sql, $params)) ? $assessments : array();
        $assessments_count = count($assessments);
        return $assessments_count;
    }
}
