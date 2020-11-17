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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud;

defined('MOODLE_INTERNAL') || die();

/**
 * All available kuraCloud courses and mapping associations
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses {

    /**
     * All available API endpoints
     *
     * @var endpoint[]
     */
    public $endpoints;

    /**
     * Populate endpoints variable
     */
    public function __construct() {
        $this->endpoints = endpoints::get_all();
    }

    /**
     * Get all kuraCloud courses
     *
     * @return stdClass[]
     */
    public function get_all() {

        $course = array();
        $return = array();

        foreach ($this->endpoints as $endpoint) {
            $courses = $endpoint->api->get_courses();

            foreach ($courses as $course) {
                $course->instanceId = $endpoint->instanceid;
                $course->instanceName = $endpoint->name;
                $return[$course->instanceId.'-'.$course->courseId] = $course;
            }
        }

        return $return;
    }

    /**
     * Get all unmapped kuraCloud courses
     *
     * @param integer $includecourseid moodle course id to include in the list of unmapped courses
     * @return \stdClass[]
     */
    public function get_all_unmapped($includecourseid=null) {
        global $DB;

        $courses = $this->get_all();
        if (is_null($includecourseid)) {
            $mappedcourses = $DB->get_records_select("block_kuracloud_courses");
        } else {
            $mappedcourses = $DB->get_records_select("block_kuracloud_courses", "courseid != ?", array($includecourseid));
        }

        $map = array();

        foreach ($mappedcourses as $mappedcourse) {
            $map[$mappedcourse->remote_instanceid][$mappedcourse->remote_courseid] = $mappedcourse;
        }

        $unmappedcourses = array();
        foreach ($courses as $course) {
            if (!isset($map[$course->instanceId][$course->courseId])) {
                $unmappedcourses[] = $course;
            }
        }

        return $unmappedcourses;
    }

    /**
     * Get all mapped courses
     *
     * @param boolean $checkremote check status of mapping with kuraCloud instance (slower)
     * @return \stdClass[]
     */
    public function get_all_mapped($checkremote=false) {
        global $DB;

        $mappings = $DB->get_records('block_kuracloud_courses');

        if ($checkremote) {

            $allfailed = false;

            try {
                $remotecourses = $this->get_all();
            } catch (\Exception $e) {
                $allfailed = true;
                $problem = $e->getMessage();
            }

            foreach ($mappings as $key => $mapping) {
                $mappings[$key]->status_ok = false;

                if ($allfailed) {
                    $mappings[$key]->status_message = $problem;
                    continue;
                }

                if (isset($remotecourses[$mapping->remote_instanceid.'-'.$mapping->remote_courseid])) {
                    $remotecourse = $remotecourses[$mapping->remote_instanceid.'-'.$mapping->remote_courseid];

                    if ($remotecourse->lmsEnabled == true) {
                        $mappings[$key]->status_ok = true;
                        $mappings[$key]->status_message = '';
                    } else {
                        $mappings[$key]->status_message = get_string('notlmsenabled', 'block_kuracloud');
                    }
                } else {
                    $mappings[$key]->status_message = get_string('remotecoursemissing', 'block_kuracloud');
                }
            }
        }

        return $mappings;
    }

    /**
     * Get a course mapping record from the DB
     *
     * @param integer $courseid Moodle course id
     * @return \stdClass
     */
    public static function get_mapping($courseid) {
        global $DB;

        return $DB->get_record('block_kuracloud_courses', array('courseid' => $courseid));
    }

    /**
     * Get a course mapping as a course object
     *
     * @param integer $courseid Moodle course id
     * @return course|boolean
     */
    public function get_course($courseid) {

        if ($mapping = $this->get_mapping($courseid)) {
            return new course($mapping, $this->endpoints[$mapping->remote_instanceid]);
        } else {
            return false;
        }
    }

    /**
     * Save a course mapping to the DB
     *
     * @param integer $courseid Moodle course id
     * @param string $remoteinstanceid kuraCloud instanceid
     * @param integer $remotecourseid kuraCloud course id
     * @return boolean
     */
    public function save_mapping($courseid, $remoteinstanceid, $remotecourseid) {
        global $DB;

        if ($mapping = $DB->get_record('block_kuracloud_courses', array('courseid' => $courseid))) {
            if ($mapping->remote_courseid != $remotecourseid) {
                $this->delete_mapping($mapping);
            } else {
                return true;
            }
        }
        $endpoint = $this->endpoints[$remoteinstanceid];
        $course = $endpoint->api->edit_course(array(
            'courseId' => $remotecourseid,
            'lmsEnabled' => true
        ));

        $mapping = new \stdClass();
        $mapping->courseid = $courseid;
        $mapping->remote_courseid = $remotecourseid;
        $mapping->remote_instanceid = $remoteinstanceid;
        $mapping->remote_name = $course->name;

        return $DB->insert_record('block_kuracloud_courses', $mapping);
    }

    /**
     * Delete a course mapping
     *
     * @param \stdClass $mapping Mapping record from the DB
     * @return return boolean
     */
    public function delete_mapping($mapping) {
        global $DB;
        $endpoint = $this->endpoints[$mapping->remote_instanceid];

        try {
            $endpoint->api->edit_course(array(
                'courseId' => $mapping->remote_courseid,
                'lmsEnabled' => false,
            ));
        } catch (\Exception $e) {
            // Continue on failure as user may be dealing with a deleted course.
            $e->getMessage();
        }

        return $DB->delete_records('block_kuracloud_courses', array('courseid' => $mapping->courseid));
    }

    /**
     * Delete all mappings for a kuraCloud instance
     *
     * @param string $instanceid kuraCloud instance id
     * @return void
     */
    public function delete_all_mappings($instanceid) {
        global $DB;

        $mappings = $DB->get_records('block_kuracloud_courses', array('remote_instanceid' => $instanceid));

        foreach ($mappings as $mapping) {
            $this->delete_mapping($mapping);
        }
    }

}