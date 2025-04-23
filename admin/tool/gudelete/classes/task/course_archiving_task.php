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

namespace tool_gudelete\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class course_archiving_task
 *
 * @package    tool_gudelete
 * @copyright  2024 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class course_archiving_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for the task
     * 
     * @return string
     */
    public function get_name() {
        return get_string('course_archiving_task', 'tool_gudelete');
    }

    /**
     * Do the Job
     */
    public function execute() {
        global $CFG;
        $config = get_config('tool_gudelete');
        if ($config->run_cron) {
            $archivement = new \tool_gudelete\course_archiving_helper();
            $a = $archivement->process_archivment($config);
            mtrace(strip_tags(get_string('notice', 'tool_gudelete', $a)));
        }
    }
}
