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
 * CoreHR Boomi plugin tests.
 *
 * NOTE: in order to execute this test you need to set up
 *       CoreHR test web server credentials in config.php or phpunit.xml
 *
 define('TEST_LOCAL_COREHR_GETPERSONURL', 'https://....');
 define('TEST_LOCAL_COREHR_TRAININGRECORDURL', 'https://....');
 define('TEST_LOCAL_COREHR_BOOMIUSER', 'Moodle');
 define('TEST_LOCAL_COREHR_BOOMIPASSWORD', '*****');
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

class local_corehr_boomi_test extends advanced_testcase {

    private $course1;
    private $course2;

    private $user1;
    private $user2;

    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest(true);

        // Configure config settings
        set_config('getpersonurl', TEST_LOCAL_COREHR_GETPERSONURL, 'local_corehr');
        set_config('trainingrecordurl', TEST_LOCAL_COREHR_TRAININGRECORDURL, 'local_corehr');
        set_config('boomiuser', TEST_LOCAL_COREHR_BOOMIUSER, 'local_corehr');
        set_config('boomipassword', TEST_LOCAL_COREHR_BOOMIPASSWORD, 'local_corehr');

        // Create user profile fields.
        \report_guid\lib::check_create_userprofile();

        // Create test user
        $generator = $this->getDataGenerator();
        $this->user1 = $generator->create_user([
            'username' => TEST_LOCAL_COREHR_VALID_GUID,
            'idnumber' => TEST_LOCAL_COREHR_VALID_PERSONNELNO,
        ]);
        $this->user2 = $generator->create_user([
            'username' => TEST_LOCAL_COREHR_INVALID_GUID,
            'idnumber' => TEST_LOCAL_COREHR_INVALID_PERSONNELNO,
        ]);

        // Create test courses.
        $this->course1 = $generator->create_course();
        $this->course2 = $generator->create_course();

        // Add valid CoreHR code for course1.
        $corehr = (object)[
            'courseid' => $this->course1->id,
            'enable' => true,
            'coursecode' => TEST_LOCAL_COREHR_VALID_COURSECODE,
        ];
        $DB->insert_record('local_corehr', $corehr);
    }

    /**
     * Get userid for GUID
     * @param string $guid
     * @return int
     */
    private function get_userid(string $guid) {
        global $DB;

        $user = $DB->get_record('user', ['username' => $guid], '*', MUST_EXIST);

        return $user->id;
    }

    /**
     * Test GetPersonByGUID web service
     * 
     */
    public function test_getpersonbyguid(): void {
        global $DB;

        $boomi = new \local_corehr\boomi();

        // If boomi hasn't been configured, then there's nothing much to do. 
        if (!$boomi->is_getperson_configured()) {
            return;
        }

        // Try example valid GUID
        $payload = $boomi->getpersonbyguid(TEST_LOCAL_COREHR_VALID_GUID);
        $this->assertObjectHasProperty('jobTitle', $payload);
        $this->assertObjectHasProperty('school', $payload);

        // Get log
        $id = $boomi->get_lastlogid();
        $log = $DB->get_record('local_corehr_boomi_log', ['id' => $id], '*', MUST_EXIST);
        $this->assertEquals(0, $log->errorcode);

        // Write data for user.
        $userid = $this->get_userid(TEST_LOCAL_COREHR_VALID_GUID);
        \local_corehr\api::store_extract($userid, $payload);
        \local_corehr\api::write_profile($userid, $payload);

        // Check local_core_extract.
        $extract = $DB->get_record('local_corehr_extract', ['userid' => $userid]);
        $this->assertObjectHasProperty('surname', $extract);
        $this->assertObjectHasProperty('schooldesc', $extract);

        // Check user_info_data
        $infodata = $DB->get_records('user_info_data', ['userid' => $userid]);
        $this->assertCount(4, $infodata);
    }

    /**
     * Test GetPersonByGUID with invalid GUID
     */
    public function test_getpersonbyinvalidguid(): void {
        global $DB;

        $boomi = new \local_corehr\boomi();

        // If boomi hasn't been configured, then there's nothing much to do. 
        if (!$boomi->is_getperson_configured()) {
            return;
        }

        // Try example invalid GUID
        $payload = $boomi->getpersonbyguid(TEST_LOCAL_COREHR_INVALID_GUID);
        $this->assertNull($payload);

        // Get log
        $id = $boomi->get_lastlogid();
        $log = $DB->get_record('local_corehr_boomi_log', ['id' => $id], '*', MUST_EXIST);
        $this->assertEquals('null', $log->payload);
        $this->assertEquals(0, $log->errorcode);

        // Try no GUID at all.
        $payload = $boomi->getpersonbyguid('');
        $this->assertNull($payload);

        // Get log
        $id = $boomi->get_lastlogid();
        $log = $DB->get_record('local_corehr_boomi_log', ['id' => $id], '*', MUST_EXIST);
        $this->assertEquals('null', $log->payload);
        $this->assertEquals(400, $log->errorcode);
        $this->assertEquals("Missing or Empty personGUID query string parameter", $log->errormessage);
    }

    /**
     * Test update training record - with a broken URL 
     * (So it doesn't connect)
     */
    public function test_updatetraining_noconnect(): void {
        global $DB;

        // Set the endpoint to something invalid
        set_config('trainingrecordurl', 'https://xxx.yyy.zz/', 'local_corehr');

        $boomi = new \local_corehr\boomi();

        // If boomi hasn't been configured, then there's nothing much to do. 
        if (!$boomi->is_trainingrecord_configured()) {
            return;
        }

        // Check course code for course1.
        $coursecode = $boomi->get_course_code($this->course1->id);
        $this->assertEquals(TEST_LOCAL_COREHR_VALID_COURSECODE, $coursecode);

        // Check staff number for user1.
        $staffnumber = $boomi->get_staff_number($this->user1->id);
        $this->assertEquals(TEST_LOCAL_COREHR_VALID_PERSONNELNO, $staffnumber);

        // Update training record
        $boomi->trainingrecord($coursecode, $staffnumber, time());

        // Get log
        $id = $boomi->get_lastlogid();
        $log = $DB->get_record('local_corehr_boomi_log', ['id' => $id], '*', MUST_EXIST);
        $this->assertEquals("Could not resolve host: xxx.yyy.zz", $log->errormessage);
    }

    /**
     * Test update training record - with valid data 
     */
    public function test_updatetraining_valid(): void {
        global $DB;

        $boomi = new \local_corehr\boomi();

        // If boomi hasn't been configured, then there's nothing much to do. 
        if (!$boomi->is_trainingrecord_configured()) {
            return;
        }

        // Check course code for course1.
        $coursecode = $boomi->get_course_code($this->course1->id);
        $this->assertEquals(TEST_LOCAL_COREHR_VALID_COURSECODE, $coursecode);

        // Check staff number for user1.
        $staffnumber = $boomi->get_staff_number($this->user1->id);
        $this->assertEquals(TEST_LOCAL_COREHR_VALID_PERSONNELNO, $staffnumber);

        // Create random date stamp (as it has to be unique)
        // Sometime in last 10 years
        $startdate = time() - rand(0, 314360000);

        // Update training record
        $status = $boomi->trainingrecord($coursecode, $staffnumber, $startdate);
        $this->assertEquals('OK', $status);

        // Get log
        $id = $boomi->get_lastlogid();
        $log = $DB->get_record('local_corehr_boomi_log', ['id' => $id], '*', MUST_EXIST);
        $this->assertEquals('', $log->errormessage);

        // Do it again with the same date and we should get an error
        $status = $boomi->trainingrecord($coursecode, $staffnumber, $startdate);
        $this->assertEquals('RECORD_ALREADY_EXISTS', $status);

        // Send a student ID.
        $startdate = time() - rand(0, 314360000);
        $status = $boomi->trainingrecord($coursecode, '1234567', $startdate);
        $this->assertEquals('PERSON_IS_STUDENT', $status);
    }
}