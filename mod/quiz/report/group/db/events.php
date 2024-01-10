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
 * This file defines the observers for the quiz group.
 *
 * @package   quiz_group
 * @copyright 2017 Camille Tardy, University of Geneva
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


// Catch attempt started.
$observers = array(
    array(
        'eventname' => '\mod_quiz\event\attempt_started',
        'callback' => 'quiz_group_observer::attempt_started',
    ),

    // Catch attempt submitted.
    array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => 'quiz_group_observer::attempt_submitted',
    ),

    // Catch attempt abandoned.
    array(
        'eventname' => '\mod_quiz\event\attempt_abandoned',
        'callback' => 'quiz_group_observer::attempt_abandoned',
    ),
    // Catch attempt deleted.
    array(
        'eventname' => '\mod_quiz\event\attempt_deleted',
        'callback' => 'quiz_group_observer::attempt_deleted',
    ),

    // Group event and course reset.
    array(
        'eventname' => '\core\event\course_reset_started',
        'callback' => 'quiz_group_observer::course_reset_started',
    ),
    array(
        'eventname' => '\core\event\course_reset_ended',
        'callback' => 'quiz_group_observer::course_reset_ended',
    ),
    array(
        'eventname' => '\core\event\group_deleted',
        'callback' => 'quiz_group_observer::group_deleted'
    )

);

