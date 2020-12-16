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

global $CFG;

require_once('config.php');
require_once($CFG->dirroot .'/blocks/moodleblock.class.php');
require_once($CFG->dirroot .'/blocks/gu_spoverview/block_gu_spoverview.php');

class block_gu_spoverview_testcase extends advanced_testcase {

    /**
     * Setup test data.
     */
    public function setUp(){
        global $DB;

        $this->spoverview = new block_gu_spoverview();
    
        $this->resetAfterTest(true);

        $gen = $this->getDataGenerator();

        $this->student = $gen->create_user();
        $this->teacher = $gen->create_user();

        $this->category = $gen->create_category();
        $this->course = $this->getDataGenerator()->create_course(array('name'=>'Some course', 'category'=>$this->category->id));

        $this->assign1 = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id, 'duedate' => time() - 10));
        $this->assign2 = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id, 'duedate' => time() + 60 * 60));
        $this->assign3 = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id, 'duedate' => time() + 30 * 30));

        $gen->enrol_user($this->student->id, $this->course->id, 'student');
        $gen->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');

        $DB->insert_record('assign_submission', array(
            'assignment' => $this->assign1->id,
            'userid' => $this->student->id,
            'attemptnumber' => 0,
            'status' => 'new'
        ));

        $DB->insert_record('assign_submission', array(
            'assignment' => $this->assign2->id,
            'userid' => $this->student->id,
            'attemptnumber' => 0,
            'status' => 'new'
        ));

        $DB->insert_record('assign_submission', array(
            'assignment' => $this->assign3->id,
            'userid' => $this->student->id,
            'attemptnumber' => 0,
            'status' => 'submitted'
        ));
    }   

    public function test_to_get_count_of_assignment(){
        $assignments_submitted = 0;
        $assignments_tosubmit = 0;
        $assignments_overdue = 0;
        
        $courses = enrol_get_all_users_courses($this->student->id, true);
        $courseids = array_column($courses, 'id');

        $assignments = (count($courseids) > 0) ? $this->spoverview->get_user_assignments($this->student->id, $courseids) : 0;

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
        $this->assertGreaterThanOrEqual(1, $assignments_submitted);

        $this->assertContains(
            $this->course->id, 
            $courseids, 
            "courseids array doesn't contains same course id"
        );
    }

    public function test_check_marked_assignment(){
        $courses = enrol_get_all_users_courses($this->student->id, true);
        $courseids = array_column($courses, 'id');
        $assessments_marked = (count($courseids) > 0) ? $this->spoverview->get_user_assessments_count($this->student->id, $courseids) : 0;

        $this->assertNotEmpty($courses, 'empty');
        $this->assertGreaterThanOrEqual(0, $assessments_marked);

        $this->assertContains(
            $this->course->id, 
            $courseids, 
            "courseids array doesn't contains same course id"
        );
    }

    public function test_applicable_formats(){
        $returned = $this->spoverview->applicable_formats();

        $this->assertEquals($returned, array('my' => true));
    }

    public function test_get_content(){
        $lang = 'block_gu_spoverview';

        $assignments_submitted = 0;
        $assignments_tosubmit = 0;
        $assignments_overdue = 0;
        
        $courses = enrol_get_all_users_courses($this->student->id, true);
        $courseids = array_column($courses, 'id');

        $assignments = (count($courseids) > 0) ? $this->spoverview->get_user_assignments($this->student->id, $courseids) : 0;
        $assessments_marked = (count($courseids) > 0) ? $this->spoverview->get_user_assessments_count($this->student->id, $courseids) : 0;

        $assignment_str = ($assignments_submitted == 1) ? get_string('assignment', $lang) : get_string('assignments', $lang);
        $assessment_str = ($assessments_marked == 1) ? get_string('assessment', $lang) : get_string('assessments', $lang);

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

        $html = $this->spoverview->get_content();

        $this->assertSame(get_string('pluginname', 'block_gu_spoverview'), $this->spoverview->title);
        $this->assertStringContainsString((string)$assignments_submitted, $html->text);
        $this->assertStringContainsString((string)$assignments_tosubmit, $html->text);
        $this->assertStringContainsString((string)$assignments_overdue, $html->text);
        $this->assertStringContainsString((string)$assessments_marked, $html->text);

        $this->assertStringContainsString($assignment_str, $html->text);
        $this->assertStringContainsString($assessment_str, $html->text);
    }    
}
