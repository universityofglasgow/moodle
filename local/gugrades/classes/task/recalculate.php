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
 * Event observers
 *
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\task;

class recalculate extends \core\task\adhoc_task {

    /**
     * Create the task
     * @param int $courseid
     * @param int $gradecategoryid
     * @return \local_gugrades\task\recalculate
     */
    public static function instance(int $courseid, int $gradecategoryid): self {
        $task = new self();
        $task->set_custom_data((object) [
            'courseid' => $courseid,
            'gradecategoryid' => $gradecategoryid,
        ]);

        return $task;
    }

    /**
     * Execute the ad-hoc task
     */
    public function execute() {
        $data = $this->get_custom_data();
        $courseid = $data->courseid;
        $gradecategoryid = $data->gradecategoryid;
        mtrace('Recalculating MyGrades aggregation data. CourseID = ' . $courseid . ', GradeCategoryID = ' . $gradecategoryid);

        \local_gugrades\api::recalculate($courseid, $gradecategoryid);
    }
}