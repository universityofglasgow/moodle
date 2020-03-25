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
 * tool_rollover library
 *
 * @package    tool_rollover
 * @copyright  2019 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rollover;

defined('MOODLE_INTERNAL') || die();

// Overall rollover processing state.
define('ROLLOVER_STATE_DISABLED', 1); // Completely disabled
define('ROLLOVER_STATE_START', 2); // Just enabled but haven't done anything yet
define('ROLLOVER_STATE_BUILDTABLE', 3); // In the process of building course table

// Individual course processing state.
define('ROLLOVER_COURSE_WAITING', 1); // scheduled but not processed
define('ROLLOVER_COURSE_BACKUP', 2); // backup file has been created
define('ROLLOVER_COURSE_RESTORE', 3); // archive version has been restored

class lib {

    /**
     * Get current status
     * @return array
     */
    public static function get_current_status() {
        global $DB;

        // Count 'waiting'
        $waiting = $DB->count_records('tool_rollover', ['state' => ROLLOVER_COURSE_WAITING]);

        // Count 'backup'
        $backup = $DB->count_records('tool_rollover', ['state' => ROLLOVER_COURSE_BACKUP]);

        // Count 'restore'
        $restore = $DB->count_records('tool_rollover', ['state' => ROLLOVER_COURSE_RESTORE]);

        return [
            'waiting' => $waiting,
            'backup' => $backup,
            'restore' => $restore,
        ];
    }

    /**
     * Get rollover state
     * @return string
     */
    public static function get_state() {

        // If rollover is disabled then always reset state
        // TODO: might want to clear some db stuff too
        if (!get_config('tool_rollover', 'enable')) {
            set_config('state', ROLLOVER_STATE_DISABLED, 'tool_rollover');
            return ROLLOVER_STATE_DISABLED;
        }

        // If rollover is enabled then some state checks required
        $state = get_config('tool_rollover', 'state');
        if (!$state || ($state == ROLLOVER_STATE_DISABLED)) {
            set_config('state', ROLLOVER_STATE_START, 'tool_rollover');
        }

        return get_config('tool_rollover', 'state');
    }

    /**
     * Build the table of courses to be backed up
     * We should be able to do this in one go
     */
    public static function build_course_table() {
        global $DB;

        // set state to building table
        $excludedcategories = self::get_excluded_categories();
        $session = get_config('tool_rollover', 'session');

        // Keep some counts.
        $skipped = 0;
        $existing = 0;
        $added = 0;

        // Which courses?
        $sourcecategory = get_config('tool_rollover', 'sourcecategory');
        if ($sourcecategory) {
            $category = \coursecat::get($sourcecategory);
            $children = $category->get_all_children_ids();
            $categoryids = array_merge([$sourcecategory], $children);
            $rs = $DB->get_recordset_list('course', 'category', $categoryids);
        } else {

            // Every single course :-O
            $rs = $DB->get_recordset('course');
        }

        foreach ($rs as $course) {

            // If it's in an excluded category...
            if (in_array($course->category, $excludedcategories)) {

                // It may already have a table entry...
                $DB->delete_records('tool_rollover', ['session' => $session, 'courseid' => $course->id]);
                $skipped++;
                continue;
            }

            // Does this already have an entry
            if ($rollover = $DB->get_record('tool_rollover', ['session' => $session, 'courseid' => $course->id])) {
                $existing++;
                continue;
            }

            // Create the record
            $rollover = new \stdClass();
            $rollover->courseid = $course->id;
            $rollover->session = $session;
            $rollover->destinationcourseid = 0;
            $rollover->state = ROLLOVER_COURSE_WAITING;
            $rollover->filename = '';
            $rollover->timestarted = 0;
            $rollover->timecompleted = 0;
            $DB->insert_record('tool_rollover', $rollover);
            $added++;
        }
        $rs->close();
    }

    /**
     * Empty table
     */
    public static function delete_course_table() {
        global $DB;

        $DB->delete_records('tool_rollover');
    }

    /**
     * Get excluded category IDs (and all children).
     * Need to parse the 'categoryexclude' setting
     * @return array
     */
    public static function get_excluded_categories() {
        global $DB;

        // First, get the list and grab the list of top-level categories.
        $categoryexclude = get_config('tool_rollover', 'categoryexclude');
        $lines = explode(PHP_EOL, $categoryexclude);
        $categories = [];
        foreach ($lines as $line) {
            if (substr(ltrim($line), 0, 2) == '//') {
                continue;
            }
            $categories = array_merge($categories, preg_split('/[\ \,]+/', trim($line), -1, PREG_SPLIT_NO_EMPTY));
        }

        // Run through top-level and find all the children as well.
        $allcats = [];
        foreach ($categories as $key => $value) {
            if (!$category = \core_course_category::get((int)$value, IGNORE_MISSING)) {
                continue;
            }
            $children = $category->get_all_children_ids();
            $parentid = $category->id;
            $allcats[$parentid] = $parentid;
            foreach ($children as $childid) {
                $allcats[$childid] = $childid;
            }
        }

        return $allcats;
    }

}
