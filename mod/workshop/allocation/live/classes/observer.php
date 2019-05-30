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
 * Definition of event observers.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopallocation_live
 */

namespace workshopallocation_live;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/workshop/locallib.php');
require_once($CFG->dirroot . '/mod/workshop/allocation/lib.php');
require_once($CFG->dirroot . '/mod/workshop/allocation/random/lib.php');

/**
 * Definition of event observers.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopallocation_live
 */
class observer {

    /**
     * Allocates assessments randomly after each submission.
     *
     * @param \core\event\assessable_uploaded $event
     * @return bool
     */
    public static function assessable_uploaded(\core\event\assessable_uploaded $event) {
        global $DB;

        $cm = get_coursemodule_from_id('workshop', $event->contextinstanceid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $instance = $DB->get_record('workshop', ['id' => $cm->instance], '*', MUST_EXIST);
        $workshop = new \workshop($instance, $cm, $course);

        $record = $DB->get_record('workshopallocation_live', ['workshopid' => $workshop->id]);

        if ($workshop->phase == \workshop::PHASE_ASSESSMENT and $record and $record->enabled) {
            $randomallocator = $workshop->allocator_instance('random');
            $settings = \workshop_random_allocator_setting::instance_from_text($record->settings);
            $result = new \workshop_allocation_result($randomallocator);
            $randomallocator->execute($settings, $result);
        }

        return true;
    }
}
