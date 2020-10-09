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
 * Test files iterator.
 *
 * @package   tool_ally
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_ally\files_iterator;
use tool_ally\local;
use tool_ally\role_assignments;
use tool_ally\local_file;
use tool_ally\file_validator;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/abstract_testcase.php');

/**
 * Test files iterator.
 *
 * @package   tool_ally
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_ally_files_iterator_testcase extends tool_ally_abstract_testcase {
    /**
     * Test get_files.
     */
    public function test_get_files() {
        global $DB;

        $this->resetAfterTest();

        $course    = $this->getDataGenerator()->create_course();
        $user      = $this->getDataGenerator()->create_user();
        $roleid    = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        $managerid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);

        $this->getDataGenerator()->enrol_user($user->id, $course->id, $roleid);
        $this->setUser($user);

        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course->id)->id,
            'component' => 'mod_notwhitelisted',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test_has_userid.txt',
            'userid' => $user->id,
            'modified' => time()
        );
        $teststring = 'moodletest';
        $file1 = $fs->create_file_from_string($filerecord, $teststring);

        $filerecord = array(
            'contextid' => context_course::instance($course->id)->id,
            'component' => 'mod_notwhitelisted',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test_no_userid.txt',
            'userid' => null,
            'modified' => time()
        );
        $teststring = 'moodletest';
        $file2 = $fs->create_file_from_string($filerecord, $teststring);

        $hashes    = [$file1->get_pathnamehash(), $file2->get_pathnamehash()];

        // Check that if a role or user did not make content, that we only get files with null user ID.
        $validator = new file_validator([get_admin()->id], new role_assignments([$managerid]));
        $files = new files_iterator($validator);
        foreach ($files as $file) {
            $this->assertStoredFileEquals($file2, $file);
            $this->assertNull($file->get_userid());
        }

        // Ensure user role works.
        $validator = new file_validator([], new role_assignments([$roleid]));
        $files = new files_iterator($validator);
        foreach ($files as $file) {
            $this->assertContains($file->get_pathnamehash(), $hashes);
        }

        // Ensure user ID works.
        $validator = new file_validator([$user->id]);
        $files = new files_iterator($validator);
        foreach ($files as $file) {
            $this->assertContains($file->get_pathnamehash(), $hashes);
        }
    }

    /**
     * Test get_files when there are no files to fetch.
     */
    public function test_get_no_files() {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $validator = new file_validator(local::get_adminids(), new role_assignments(local::get_roleids()));
        $files = new files_iterator($validator);
        $this->assertEmpty(iterator_to_array($files));
    }

    /**
     * Test get_files using the since parameter.
     */
    public function test_get_files_since() {
        global $DB;

        $this->resetAfterTest();

        $course    = $this->getDataGenerator()->create_course();
        $user      = $this->getDataGenerator()->create_user();
        $roleid    = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);

        $this->getDataGenerator()->enrol_user($user->id, $course->id, $roleid);
        $this->setUser($user);

        $resource1 = $this->getDataGenerator()->create_module('resource', ['course' => $course->id]);
        $resource2 = $this->getDataGenerator()->create_module('resource', ['course' => $course->id]);
        $resource3 = $this->getDataGenerator()->create_module('resource', ['course' => $course->id]);
        $file1     = $this->get_resource_file($resource1);
        $file2     = $this->get_resource_file($resource2);
        $file3     = $this->get_resource_file($resource3);

        $datetime = new \DateTimeImmutable('October 21 2015', new \DateTimeZone('UTC'));
        $earlier  = $datetime->sub(new \DateInterval('P2D'));
        $later    = $datetime->add(new \DateInterval('P2D'));

        $file1->set_timemodified($earlier->getTimestamp());
        $file2->set_timemodified($datetime->getTimestamp());
        $file3->set_timemodified($later->getTimestamp());

        // Make sure only files with times modified since the $since param, i.e. only $file3 here.
        $validator = new file_validator();
        $files = new files_iterator($validator);
        $files->since($datetime->getTimestamp());
        foreach ($files as $file) {
            $this->assertStoredFileEquals($file3, $file);
        }
    }

    /**
     * Simplified unenrolment of user from course using default options.
     *
     * It is strongly recommended to use only this method for 'manual' and 'self' plugins only!!!
     *
     * @param int $userid
     * @param int $courseid
     * @param string $enrol name of enrol plugin,
     *     there must be exactly one instance in course,
     *     it must support enrol_user() method.
     * @return bool success
     */
    private function unenrol_user($userid, $courseid, $enrol = 'manual') {
        global $DB;

        if (!$plugin = enrol_get_plugin($enrol)) {
            return false;
        }

        $instances = $DB->get_records('enrol', array('courseid' => $courseid, 'enrol' => $enrol));
        if (count($instances) != 1) {
            return false;
        }
        $instance = reset($instances);

        $plugin->unenrol_user($instance, $userid);
        return true;
    }

    public function test_white_listing() {
        global $DB;

        $this->resetAfterTest();

        $dg = $this->getDataGenerator();
        $course = $dg->create_course();
        $user = $this->getDataGenerator()->create_user();
        $dg->enrol_user($user->id, $course->id, 'editingteacher');
        $this->setUser($user);

        $now = time();
        $resource = $this->getDataGenerator()->create_module('resource', ['course' => $course->id]);
        $file     = $this->get_resource_file($resource);
        $this->assertEquals('content', $file->get_filearea());
        $DB->update_record('files',  (object) ['id' => $file->get_id(), 'userid' => $user->id]);

        $files = local_file::iterator();
        $files->since($now - DAYSECS);
        $fcount = 0;
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertStoredFileEquals($file, $filetocheck);
        }
        $this->assertEquals(1, $fcount);

        // Now unenrol user from course - whitelisting should still allow mod_resource file through.
        $this->unenrol_user($user->id, $course->id);
        $files = local_file::iterator();
        $files->since($now - DAYSECS);
        $fcount = 0;
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertStoredFileEquals($file, $filetocheck);
        }
        // This should still be 1 because even though the user has been unenrolled, the resource "content" area is
        // whitelisted for the resource module.
        $this->assertEquals(1, $fcount);
    }

    /**
     * Make sure a file created within a course of a whitelisted module is accessible when created by
     * someone with a teacher role but not when a student.
     */
    public function test_role_validation() {

        $this->resetAfterTest();

        $now = time();

        $dg = $this->getDataGenerator();
        $course1 = $dg->create_course();
        $course2 = $dg->create_course();
        $user = $this->getDataGenerator()->create_user();
        $dg->enrol_user($user->id, $course1->id, 'editingteacher');
        $dg->enrol_user($user->id, $course2->id, 'student');
        $this->setUser($user);

        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course1->id)->id,
            'component' => 'mod_assign',
            'filearea' => 'intro',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest';
        $testfile1 = $fs->create_file_from_string($filerecord, $teststring);

        $files = local_file::iterator();
        $files->since($now - DAYSECS);
        $fcount = 0;
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertStoredFileEquals($testfile1, $filetocheck);
        }
        $this->assertEquals(1, $fcount);

        // Add another file in course where user is teacher.
        // Make sure files iterator includes it.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course1->id)->id,
            'component' => 'mod_assign',
            'filearea' => 'intro',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test2.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest2';
        $testfile2 = $fs->create_file_from_string($filerecord, $teststring);
        $files = local_file::iterator();
        $files->since($now - WEEKSECS);
        $fcount = 0;
        $testfiles = [$testfile1, $testfile2];
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertTrue(in_array($filetocheck, $testfiles));
        }
        $this->assertEquals(2, $fcount);

        // Add another file in course where user is teacher but this time with an file area not in CHECKROLE_WHITELIST
        // or TEACHER_WHITELIST.
        // In this case, even though the file was created by a teacher, it should NOT be included.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course1->id)->id,
            'component' => 'mod_assign',
            'filearea' => 'notwhitelisted',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test3.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest3';
        $testfile3 = $fs->create_file_from_string($filerecord, $teststring);
        $files = local_file::iterator();
        $files->since($now - WEEKSECS);
        $fcount = 0;
        $testfiles = [$testfile1, $testfile2];
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertTrue(in_array($filetocheck, $testfiles));
        }
        foreach ($files as $filetocheck) {
            $this->assertNotEquals($filetocheck, $testfile3);
        }
        $this->assertEquals(2, $fcount);
        // Add a file in course where user is not a teacher but the file area is white listed as a teacher only area.
        // Make sure files iterator DOES include it.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course2->id)->id,
            'component' => 'mod_assign',
            'filearea' => 'intro',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test4.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest3';
        $testfile4 = $fs->create_file_from_string($filerecord, $teststring);
        $files = local_file::iterator();
        $files->since($now - WEEKSECS);
        $fcount = 0;
        $testfiles = [$testfile1, $testfile2, $testfile4];
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertTrue(in_array($filetocheck, $testfiles));
        }
        $this->assertEquals(3, $fcount); // Count should be 3 as file $testfile3 is whitelisted as a teacher only file.
        // Add a file in course where user is not a teacher AND the file area is not white listed as a teacher only area.
        // Make sure files iterator does NOT include it.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course2->id)->id,
            'component' => 'mod_assign',
            'filearea' => 'notwhitelisted',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test5.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest5';
        $testfile5 = $fs->create_file_from_string($filerecord, $teststring);
        $files = local_file::iterator();
        $files->since($now - WEEKSECS);
        $fcount = 0;
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertFalse($filetocheck->get_pathnamehash() === $testfile5->get_pathnamehash());
        }
        // Count should be 3 as file $testfile4 was not created by teacher and is not whitelisted.
        $this->assertEquals(3, $fcount);
    }

    /**
     * Test records paging.
     */
    public function test_files_paging() {
        global $DB;

        $this->resetAfterTest();

        $course    = $this->getDataGenerator()->create_course();
        $user      = $this->getDataGenerator()->create_user();
        $roleid    = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);

        $this->getDataGenerator()->enrol_user($user->id, $course->id, $roleid);
        $this->setUser($user);

        $fs = get_file_storage();
        $teststring = 'moodletest_';
        $hashes = [];

        $pagesize = 5;
        $filecount = 100;
        for ($i = 0; $i < $filecount; $i++) {
            $filerecord = array(
                'contextid' => context_course::instance($course->id)->id,
                'component' => 'mod_assign',
                'filearea' => 'intro',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => "test_file_$i.txt",
                'userid' => $user->id,
                'modified' => time()
            );
            $file = $fs->create_file_from_string($filerecord, $teststring.$i);
            $hashes[] = $file->get_pathnamehash();
        }

        // Review if all path name hashes are the same with paging turned on.
        $validator = new file_validator([], new role_assignments([$roleid]));
        $files = new files_iterator($validator);
        $files->set_page_size($pagesize);
        $queriedhashes = [];
        foreach ($files as $file) {
            $queriedhashes[] = $file->get_pathnamehash();
        }

        $this->assertSameSize($hashes, $queriedhashes);

        foreach ($hashes as $hash) {
            $this->assertContains($hash, $queriedhashes);
        }
    }

    /**
     * Test using the iterator with validation disabled.
     */
    public function test_files_without_valid_filter() {
        $this->resetAfterTest();

        $now = time();

        $dg = $this->getDataGenerator();
        $course1 = $dg->create_course();
        $course2 = $dg->create_course();
        $user = $this->getDataGenerator()->create_user();
        $dg->enrol_user($user->id, $course1->id, 'editingteacher');
        $dg->enrol_user($user->id, $course2->id, 'student');
        $this->setUser($user);

        // Add a file in course where user is a teacher.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course1->id)->id,
            'component' => 'mod_notwhitelisted',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest';
        $testfile1 = $fs->create_file_from_string($filerecord, $teststring);

        // Add a file in course where user is not a teacher.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course2->id)->id,
            'component' => 'mod_notwhitelisted',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test2.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest2';
        $testfile2 = $fs->create_file_from_string($filerecord, $teststring);

        $files = local_file::iterator();
        $files->since($now - DAYSECS)->with_valid_filter(false);
        $fcount = 0;
        $testfiles = [$testfile1, $testfile2];
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertTrue(in_array($filetocheck, $testfiles));
        }

        // Should be getting $fcount === 2 because student files are now also included.
        $this->assertEquals(2, $fcount);
    }

    /**
     * Test comparing curent and previous validators.
     */
    public function test_all_valid_files() {
        $this->resetAfterTest();

        $now = time();

        $dg = $this->getDataGenerator();
        $course1 = $dg->create_course();
        $course2 = $dg->create_course();
        $user = $this->getDataGenerator()->create_user();
        $dg->enrol_user($user->id, $course1->id, 'editingteacher');
        $dg->enrol_user($user->id, $course2->id, 'student');
        $this->setUser($user);

        // Create a file with a not whitelisted module.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course1->id)->id,
            'component' => 'mod_notwhitelisted',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest';
        $testfile1 = $fs->create_file_from_string($filerecord, $teststring);

        // Old iterator should take in account this file.
        $files = local_file::iterator();
        $files->with_retrieve_valid_files(false);
        $files->since($now - DAYSECS);
        $fcount = 0;
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertStoredFileEquals($testfile1, $filetocheck);
        }
        $this->assertEquals(1, $fcount);

        // New iterator should not have take in account this file.
        $files = local_file::iterator();
        $files->since($now - DAYSECS);
        $fcount = 0;
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertStoredFileEquals($testfile1, $filetocheck);
        }
        $this->assertEquals(0, $fcount);

        // Add another file in course where user is teacher but with a whitelisted module.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_course::instance($course1->id)->id,
            'component' => 'mod_assign',
            'filearea' => 'intro',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test2.txt',
            'userid' => $user->id,
            'modified' => $now
        );
        $teststring = 'moodletest2';
        $testfile2 = $fs->create_file_from_string($filerecord, $teststring);

        // Total amount of files should not change on the old validator because new file is included by the new validator.
        $files = local_file::iterator();
        $files->with_retrieve_valid_files(false);
        $files->since($now - DAYSECS);
        $fcount = 0;
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertStoredFileEquals($testfile1, $filetocheck);
        }
        $this->assertEquals(1, $fcount);

        $files = local_file::iterator();
        $files->since($now - WEEKSECS);
        $fcount = 0;
        $testfiles = [$testfile1, $testfile2];
        foreach ($files as $filetocheck) {
            $fcount++;
            $this->assertTrue(in_array($filetocheck, $testfiles));
        }
        $this->assertEquals(1, $fcount);
    }

    /**
     * Test records paging using $CFG->tool_ally_optimize_iteration_for_db = true.
     */
    public function test_files_paging_optimized_for_db() {
        global $DB, $CFG;

        $this->resetAfterTest();

        $CFG->tool_ally_optimize_iteration_for_db = true;

        $course    = $this->getDataGenerator()->create_course();
        $user      = $this->getDataGenerator()->create_user();
        $roleid    = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);

        $this->getDataGenerator()->enrol_user($user->id, $course->id, $roleid);
        $this->setUser($user);

        $fs = get_file_storage();
        $teststring = 'moodletest_';
        $hashes = [];

        $filecount = 100;
        for ($i = 0; $i < $filecount; $i++) {
            $filerecord = array(
                'contextid' => context_course::instance($course->id)->id,
                'component' => 'mod_assign',
                'filearea' => 'intro',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => "test_file_$i.txt",
                'userid' => $user->id,
                'modified' => time()
            );
            $file = $fs->create_file_from_string($filerecord, $teststring.$i);
            $hashes[] = $file->get_pathnamehash();
        }

        // Review if all path name hashes are the same with paging turned on.
        $validator = new file_validator([], new role_assignments([$roleid]));
        $files = new files_iterator($validator);
        $queriedhashes = [];
        foreach ($files as $file) {
            $queriedhashes[] = $file->get_pathnamehash();
        }

        $this->assertSameSize($hashes, $queriedhashes);

        foreach ($hashes as $hash) {
            $this->assertContains($hash, $queriedhashes);
        }
    }
}
