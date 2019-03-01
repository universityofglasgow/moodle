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
 * Task to execute rollover/archive
 *
 * @package    local_rollover
 * @copyright  2018 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rollover\task;

use local_rollover\locallib;

class rollover extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('rollovertask', 'local_rollover');
    }

    public function execute() {
        
        $config = get_config('local_rollover');

        // If not enabled then there's nothing to do
        if (locallib::get_state() == ROLLOVER_STATE_DISABLED) {
            mtrace('rollover: Skipping rollover. Not currently enabled');

            return;
        }

        // Raft of sanity checks.
        if (!$config->destinationcategory) {
            mtrace('rollover: Cannot execute rollover. Destination category not defined');
            return;
        }
        if (!$config->appendtext && !$config->prependtext) {
            mtrace('rollover: Cannot execute rollover. One of prepend/append text must be defined');
            return;
        }
        if (!$config->shortprependtext) {
            mtrace('rollover: Cannot execute rollover. Short name prepend text not defined');
            return;
        }

        // What to do?
        $state = locallib::get_state();
        if ($state == ROLLOVER_STATE_START) {
            mtrace('rollver: Starting rollover. Building course table');
            locallib::build_course_table();
        }
    }
}
