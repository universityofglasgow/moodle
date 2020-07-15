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
 * Test for course files webservice.
 *
 * @package   tool_ally
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_ally\local;
use tool_ally\webservice\course_files;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/abstract_testcase.php');

/**
 * Test for course files webservice.
 *
 * @package   tool_ally
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_ally_webservice_course_files_testcase extends tool_ally_abstract_testcase {

    /**
     * @var stdClass
     */
    private $course;

    /**
     * @var stdClass
     */
    private $resource;

    /**
     * @var stored_file
     */
    private $resourcefile;

    public function setUp() {
        $this->resetAfterTest();
        $roleid = $this->assignUserCapability('moodle/course:view', context_system::instance()->id);
        $this->assignUserCapability('moodle/course:viewhiddencourses', context_system::instance()->id, $roleid);

        $dg = $this->getDataGenerator();

        $this->course       = $dg->create_course();
        $this->resource     = $dg->create_module('resource', ['course' => $this->course->id]);
        $this->resourcefile = $this->get_resource_file($this->resource);

        // Create a file outside of $this->course. This is to make sure it doesn't get included in counts.
        $course2 = $dg->create_course();
        $dg->create_module('resource', ['course' => $course2->id]);

    }
    /**
     * Test the web service.
     */
    public function test_service() {

        $files = course_files::service([$this->course->id]);
        $files = external_api::clean_returnvalue(course_files::service_returns(), $files);
        $this->assertCount(1, $files);
        $file = reset($files);

        $expectedfile = $this->resourcefile;
        $this->assertEquals($expectedfile->get_pathnamehash(), $file['id']);
        $this->assertEquals($this->course->id, $file['courseid']);
        $this->assertEquals($expectedfile->get_filename(), $file['name']);
        $this->assertEquals($expectedfile->get_mimetype(), $file['mimetype']);
        $this->assertEquals($expectedfile->get_contenthash(), $file['contenthash']);
        $this->assertEquals($expectedfile->get_timemodified(), local::iso_8601_to_timestamp($file['timemodified']));
    }

    public function test_service_section_deleted() {
        // Add file to a soon to be deleted section.
        $section      = $this->getDataGenerator()->create_course_section(
            ['section' => 1, 'course' => $this->course->id]);
        $coursectx    = \context_course::instance($this->course->id);
        $filename     = 'shouldbeanimage.jpg';
        $filecontents = 'image contents (not really)';
        // Add a fake inline image to the post.
        $filerecordinline = array(
            'contextid' => $coursectx->id,
            'component' => 'course',
            'filearea'  => 'section',
            'itemid'    => $section->id,
            'filepath'  => '/',
            'filename'  => $filename,
        );
        $fs = get_file_storage();
        // This file should not appear in the service returned files if section is deleted.
        $fs->create_file_from_string($filerecordinline, $filecontents);

        $files = course_files::service([$this->course->id]);
        $files = external_api::clean_returnvalue(course_files::service_returns(), $files);
        $this->assertCount(2, $files);

        // The time has come to delete the section.
        course_delete_section($this->course->id, 1, true);

        $files = course_files::service([$this->course->id]);
        $files = external_api::clean_returnvalue(course_files::service_returns(), $files);

        $this->assertCount(1, $files);
        $file = reset($files);
        // Make sure the file that is left was not the one in the deleted section.
        $expectedfile = $this->resourcefile;
        $this->assertEquals($expectedfile->get_pathnamehash(), $file['id']);
        $this->assertEquals($this->course->id, $file['courseid']);
        $this->assertEquals($expectedfile->get_filename(), $file['name']);
        $this->assertEquals($expectedfile->get_mimetype(), $file['mimetype']);
        $this->assertEquals($expectedfile->get_contenthash(), $file['contenthash']);
        $this->assertEquals($expectedfile->get_timemodified(), local::iso_8601_to_timestamp($file['timemodified']));
    }

    public function test_service_resource_soft_deleted() {
        global $DB;

        $cm = get_coursemodule_from_instance('resource', $this->resource->id);
        $cm->deletioninprogress = 1;
        $DB->update_record('course_modules', $cm);

        $files = course_files::service([$this->course->id]);
        $files = external_api::clean_returnvalue(course_files::service_returns(), $files);
        $this->assertCount(0, $files);
    }
}