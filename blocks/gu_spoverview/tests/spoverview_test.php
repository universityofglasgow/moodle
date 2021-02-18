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
 * @package    block_gu_spoverview
 * @category   test
 * @copyright  2020 Alejandro De Guzman <a.g.de.guzman@accenture.com>
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('config.php');
require_once($CFG->dirroot .'/blocks/moodleblock.class.php');
require_once($CFG->dirroot .'/blocks/gu_spoverview/block_gu_spoverview.php');
require_once($CFG->dirroot .'/blocks/gu_spoverview/querylib.php');
require_once($CFG->dirroot .'/local/gugcat/locallib.php');

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

        // Set the current user to student
        $this->setUser($this->student);
    }   

    public function test_applicable_formats(){
        $returned = $this->spoverview->applicable_formats();

        $this->assertEquals($returned, array('my' => true));
    }

    public function test_return_enrolledcourses(){
        // Since show student dashboard is disabled first, return courses is empty
        $returned = $this->spoverview->return_enrolledcourses($this->student->id);
        $this->assertEmpty($returned);

        // Enable the display assessment on dashboard
        $this->enable_show_student_dashboard();
                
        $returned = $this->spoverview->return_enrolledcourses($this->student->id);
        // Assert returned is not empty and contains the current course id
        $this->assertNotEmpty($returned);
        $this->assertContains($this->course->id,$returned);
    }

    public function test_get_content(){
        // Enable the display assessment on dashboard
        $this->enable_show_student_dashboard();
        
        $returned = $this->spoverview->get_content();
        // Content is in class object that has 'text' key attribute
        $this->assertObjectHasAttribute('text', $returned);

        $text = $returned->text;

        // Assert returned text has student dashboard labels
        $this->assertStringContainsString('Assessment submitted', $text);
        $this->assertStringContainsString('To be submitted / attended', $text);
        $this->assertStringContainsString('Overdue', $text);
        $this->assertStringContainsString('Assessments marked', $text);
    }    

    public function enable_show_student_dashboard(){
        $contextid = context_course::instance($this->course->id)->id;
        $instanceid = $this->course->id;

        // Add custom field using the gcat function, then enable the display assessment on dashboard
        $switchdisplay = local_gugcat::switch_display_of_assessment_on_student_dashboard($instanceid, $contextid);
        
        // Show on student dashboard is enabled
        $this->assertEquals(1, $switchdisplay);
    }

    public function test_return_isstudent(){
        $returned = $this->spoverview->return_isstudent($this->course->id);

        $this->assertTrue($returned);
    }

    public function test_querylib(){
        $returned = return_assessments_count($this->student->id, $this->course->id);
        // Check return object has key attributes
        $this->assertObjectHasAttribute('submitted', $returned);
        $this->assertObjectHasAttribute('tosubmit', $returned);
        $this->assertObjectHasAttribute('overdue', $returned);
        $this->assertObjectHasAttribute('marked', $returned);

        // Assert 1 submitted assigment, and 0 to submit, overdue, marked
        $this->assertEquals(1, $returned->submitted);
        $this->assertEquals(0, $returned->tosubmit);
        $this->assertEquals(0, $returned->overdue);
        $this->assertEquals(0, $returned->marked);
    }
}
