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
 * This file contains the class that handles testing of the block assess frequency class.
 *
 * @package    assignsubmission_automaticextension
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_automaticextension;

use assign;
use context_module;

/**
 * This file contains the class that handles testing of the block assess frequency class.
 *
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \assignsubmission_automaticextension\automaticextension
 */
class automaticextension_class_test extends \advanced_testcase {

    /**
     * Initial set up.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setup();
        $this->resetAfterTest(true);

        $this->course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        \core\session\manager::set_user($this->student);
        $assigngenerator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $duedate = time() + 86400;
        $this->assignment = $assigngenerator->create_instance(['course' => $this->course, 'duedate' => $duedate]);
        list ($course, $cm) = get_course_and_cm_from_cmid($this->assignment->cmid, 'assign');
        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);

        // Enable the online text submission plugin.
        $assign = new assign($this->context, $this->cm, $this->course);
        $submissionplugins = $assign->get_submission_plugins();
        foreach ($submissionplugins as $plugin) {
            if ($plugin->get_type() === 'onlinetext') {
                $plugin->enable();
                break;
            }
        }
    }

    /**
     * Test the can_request_extension function.
     * @covers ::can_request_extension
     */
    public function test_can_request_extension() {
        global $DB;

        $assign = new assign($this->context, $this->cm, $this->course);

        // Test can_request_extension returns false when maximumrequests is set to 0.
        set_config('maximumrequests', 0, 'assignsubmission_automaticextension');
        set_config('extensionlength', 86400, 'assignsubmission_automaticextension');
        $automaticextension = new automaticextension($assign, $this->student->id);
        $canrequest = $automaticextension->can_request_extension();
        $this->assertFalse($canrequest);

        // Test can_request_extension returns false when extensionlength is set to 0.
        set_config('maximumrequests', 1, 'assignsubmission_automaticextension');
        set_config('extensionlength', 0, 'assignsubmission_automaticextension');
        $automaticextension = new automaticextension($assign, $this->student->id);
        $canrequest = $automaticextension->can_request_extension();
        $this->assertFalse($canrequest);

        // Test can_request_extension returns true when the configs are set and the due date hasn't been reached yet.
        set_config('maximumrequests', 1, 'assignsubmission_automaticextension');
        set_config('extensionlength', 86400, 'assignsubmission_automaticextension');
        $automaticextension = new automaticextension($assign, $this->student->id);
        $canrequest = $automaticextension->can_request_extension();
        $this->assertTrue($canrequest);

        // Test can_request_extension returns false when the user doesn't have the capability.
        $role = $DB->get_record('role', array('shortname' => 'student'));
        $cap = 'assignsubmission/automaticextension:requestextension';
        assign_capability($cap, CAP_PROHIBIT, $role->id, $assign->get_context()->id, true);
        $automaticextension = new automaticextension($assign, $this->student->id);
        $canrequest = $automaticextension->can_request_extension();
        $this->assertFalse($canrequest);

        assign_capability($cap, CAP_ALLOW, $role->id, $assign->get_context()->id, true);

        // Manually update the assignment due date to be half a day ago.
        $newduedate = time() - 43200;
        $DB->update_record('assign', (object) [
            'id' => $assign->get_instance()->id,
            'duedate' => $newduedate
        ]);
        $assign = new assign($this->context, $this->cm, $this->course);

        // Test can_request_extension returns false when the duedate has passed.
        $automaticextension = new automaticextension($assign, $this->student->id);
        $canrequest = $automaticextension->can_request_extension();
        $this->assertFalse($canrequest);

        // Set extension to be a day after due date (1 day/1 extension request).
        $extensionduedate = $newduedate + 86400;
        $flags = $assign->get_user_flags($this->student->id, true);
        $flags->extensionduedate = $extensionduedate;
        $assign->update_user_flags($flags);

        // Test can_request_extension returns false if extension hasn't passed but max requests has been reached.
        $automaticextension = new automaticextension($assign, $this->student->id);
        $canrequest = $automaticextension->can_request_extension();
        $this->assertFalse($canrequest);

        // Test can_request_extension returns true if extension hasn't passed and max requests hasn't been reached.
        set_config('maximumrequests', 2, 'assignsubmission_automaticextension');
        $automaticextension = new automaticextension($assign, $this->student->id);
        $canrequest = $automaticextension->can_request_extension();
        $this->assertTrue($canrequest);

        // Set extension to be 2 days after due date (2 requests).
        $extensionduedate = $newduedate + 172800;
        $flags = $assign->get_user_flags($this->student->id, true);
        $flags->extensionduedate = $extensionduedate;
        $assign->update_user_flags($flags);

        // Test can_request_extension returns false if extension hasn't passed but max requests (2 requests) has been reached.
        $automaticextension = new automaticextension($assign, $this->student->id);
        $canrequest = $automaticextension->can_request_extension();
        $this->assertFalse($canrequest);
    }

    /**
     * Test the apply_extension function.
     * @covers ::apply_extension
     */
    public function test_apply_extension() {
        global $DB;

        set_config('maximumrequests', 2, 'assignsubmission_automaticextension');
        set_config('extensionlength', 86400, 'assignsubmission_automaticextension');

        $assign = new assign($this->context, $this->cm, $this->course);
        $automaticextension = new automaticextension($assign, $this->student->id);

        // Test apply_extension sets the extensionduedate to 1 day after the due date.
        $sink = $this->redirectEvents();
        $result = $automaticextension->apply_extension();
        $events = $sink->get_events();
        $this->assertTrue($result);
        $expected = $this->assignment->duedate + 86400;
        $flags = $assign->get_user_flags($this->student->id, true);
        $this->assertEquals($expected, $flags->extensionduedate);

        // Test the correct events were triggered.
        $this->assertCount(2, $events);
        $event0 = $events[0];
        $expected = 'mod_assign\event\extension_granted';
        $this->assertEquals($expected, get_class($events[0]));
        $expected = 'assignsubmission_automaticextension\event\automatic_extension_applied';
        $this->assertEquals($expected, get_class($events[1]));

        // Test apply_extension a second time sets the extensionduedate to 2 days after the due date.
        $automaticextension->apply_extension();
        $expected = $this->assignment->duedate + 172800;
        $flags = $assign->get_user_flags($this->student->id, true);
        $this->assertEquals($expected, $flags->extensionduedate);

        // Test apply_extension will not exceed the maximum requests set and returns false.
        $result = $automaticextension->apply_extension();
        $this->assertFalse($result);
        $expected = $this->assignment->duedate + 172800;
        $flags = $assign->get_user_flags($this->student->id, true);
        $this->assertEquals($expected, $flags->extensionduedate);
    }
}
