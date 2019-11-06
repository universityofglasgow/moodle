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
 * Capabilities for database enrolment plugin.
 *
 * @package    enrol
 * @subpackage gudatabase
 * @copyright  2012 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$observers = array(

    array(
        'eventname' => '\core\event\course_reset_ended',
        'callback' => 'enrol_gudatabase_observer::course_reset_ended',
    ),

    // // Trigger a course created event.
    // $event = \core\event\course_created::create(array(
    //     'objectid' => $course->id,
    //     'context' => context_course::instance($course->id),
    //     'other' => array('shortname' => $course->shortname,
    //         'fullname' => $course->fullname)
    // ));

    [
        'eventname' => '\core\event\course_created',
        'callback' => 'enrol_gudatabase_observer::course_created',
    ],

);
