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
 * LDAP authentication plugin tests.
 *
 * NOTE: in order to execute this test you need to set up
 *       CoreHR test web server credentials in config.php or phpunit.xml
 *
 define('TEST_LOCAL_COREHR_WSDLTRAINING', 'https://....');
 define('TEST_LOCAL_COREHR_WSDLEXTRACT', 'https://....');
 define('TEST_LOCAL_COREHR_USERNAME', 'fred');
 define('TEST_LOCAL_COREHR_PASSWORD', 'orange'); 
 define('TEST_LOCAL_COREHR_VALID_COURSECODE', 'XYZ');
 define('TEST_LOCAL_COREHR_INVALID_COURSECODE', 'ABC');
 define('TEST_LOCAL_COREHR_VALID_PERSONNELNO', '12345');
 define('TEST_LOCAL_COREHR_PERSONNELNO_STUDENT', '1234567');
 define('TEST_LOCAL_COREHR_INVALID_PERSONNELNO', '1');
 define('TEST_LOCAL_COREHR_VALID_GUID', 'ab23c');
 define('TEST_LOCAL_COREHR_INVALID_GUID', 'xx');
 *
 * @package    local_corehr
 * @category   phpunit
 * @copyright  2019 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_corehr_testcase extends advanced_testcase {

    public function test_corehr_add() {
        global $CFG, $DB;

        // Check Soap extension is installed
        if (!extension_loaded('soap')) {
            $this->markTestSkipped('Soap extension is not loaded.');
        }

        // skip test if no WSDL defined
        if (!defined('TEST_LOCAL_COREHR_WSDLTRAINING')) {
            $this->markTestSkipped('No WSDLTRAINING defined');
        }

        $this->resetAfterTest();

        // Setup config
        set_config('wsdltraining', TEST_LOCAL_COREHR_WSDLTRAINING, 'local_corehr');
        set_config('username', TEST_LOCAL_COREHR_USERNAME, 'local_corehr');
        set_config('password', TEST_LOCAL_COREHR_PASSWORD, 'local_corehr');

        // Create test course and test user 
        $course1 = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user([
            'idnumber' => TEST_LOCAL_COREHR_VALID_PERSONNELNO,
        ]);

        // Set/check code for created course
        \local_corehr\api::savecoursecode($course1->id, TEST_LOCAL_COREHR_VALID_COURSECODE);
        $corehr = $DB->get_record('local_corehr', ['courseid' => $course1->id]);
        $this->assertNotEmpty($corehr, 'api::savecoursecode did not add record for course');
        if ($corehr) {
            $this->assertEquals($corehr->coursecode, TEST_LOCAL_COREHR_VALID_COURSECODE, 'incorrect coursecode found');
        }

        // Fake entry in course_completions
        $completion = new stdClass();
        $completion->userid = $user->id;
        $completion->course = $course1->id;
        $DB->insert_record('course_completions', $completion);

        // Test course completion call
        \local_corehr\api::course_completed($course1->id, $user->id);
        $status = $DB->get_record('local_corehr_status', ['userid' => $user->id, 'courseid' => $course1->id]);
        $this->assertNotEmpty($status, 'api::course_completed did not store record');

        // Test sending above combination - might exist, which is fine.
        $message = \local_corehr\api::send($status);
        $this->assertContains($message, ['OK', 'RECORD_ALREADY_EXISTS'], 'valid user/coursecode does not return OK or RECORD_ALREADY_EXISTS');

        // Test invalid person number
        $status->personnelno = TEST_LOCAL_COREHR_INVALID_PERSONNELNO;
        $DB->update_record('local_corehr_status', $status);
        $message = \local_corehr\api::send($status);
        $this->assertEquals($message, 'PERSON_NUMBER_NOT_VALID');

        // Test student id
        $status->personnelno = TEST_LOCAL_COREHR_PERSONNELNO_STUDENT;
        $DB->update_record('local_corehr_status', $status);
        $message = \local_corehr\api::send($status);
        $this->assertEquals($message, 'PERSON_IS_STUDENT');

        // Test removing course code
        \local_corehr\api::savecoursecode($course1->id, '');
        $corehr = $DB->get_record('local_corehr', ['courseid' => $course1->id]);
        $this->assertEmpty($corehr, 'api::savecoursecode did not delete record for course'); 

        // Check 'send' scheduled task routine with failed login
        \local_corehr\api::savecoursecode($course1->id, TEST_LOCAL_COREHR_VALID_COURSECODE);
        $status->personnelno = TEST_LOCAL_COREHR_VALID_PERSONNELNO;
        $DB->update_record('local_corehr_status', $status);
        set_config('password', 'xxxx', 'local_corehr'); // invalid password to generate non fatal error
        $send = new \local_corehr\task\send();
        $send->execute();
        $status = $DB->get_record('local_corehr_status', ['userid' => $user->id, 'courseid' => $course1->id]);
        $this->assertNotEmpty($status, 'send task - no status record');
        if ($status) {
            $this->assertEquals($status->status, 'pending', 'send task - pending status not recorded');
            $this->assertEquals($status->error, 'FAILED_LOGIN', 'send task - failed login error not recorded');
            $this->assertEquals($status->retrycount, 1, 'send task - retry count not incremented');
        }
    }


    public function test_corehr_extract() {
        global $CFG, $DB;

        // Check Soap extension is installed
        if (!extension_loaded('soap')) {
            $this->markTestSkipped('Soap extension is not loaded.');
        }

        // skip test if no WSDL defined
        if (!defined('TEST_LOCAL_COREHR_WSDLEXTRACT')) {
            $this->markTestSkipped('No WSDLTRAINING defined');
        }

        $this->resetAfterTest();

        // Setup config
        set_config('wsdlextract', TEST_LOCAL_COREHR_WSDLEXTRACT, 'local_corehr');
        set_config('username', TEST_LOCAL_COREHR_USERNAME, 'local_corehr');
        set_config('password', TEST_LOCAL_COREHR_PASSWORD, 'local_corehr');

        // Test extract for (valid) GUID
        $result = \local_corehr\api::extract(TEST_LOCAL_COREHR_VALID_GUID);
        $this->assertNotEmpty($result, 'no result returned for valid guid');
        $this->assertNotEmpty($result->forename, 'missing forename');
        $this->assertNotEmpty($result->surname, 'missing surname');

        // Test extract for invalid GUID
        $result = \local_corehr\api::extract(TEST_LOCAL_COREHR_INVALID_GUID);
        $this->assertEmpty($result, 'invalid guid did not produce empty result');

        // Test storing extracted data
        $user = $this->getDataGenerator()->create_user([
            'username' => TEST_LOCAL_COREHR_VALID_GUID,
        ]);
        $result = \local_corehr\api::extract(TEST_LOCAL_COREHR_VALID_GUID);
        \local_corehr\api::store_extract_guid(TEST_LOCAL_COREHR_VALID_GUID, $result);
        $extract = $DB->get_record('local_corehr_extract', ['userid' => $user->id]);
        $this->assertNotEmpty($extract, 'extract record missing for user');

    }
}
