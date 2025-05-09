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
 * Update all admingrades after settings have been changed
 *
 * @package    local_gugrades
 * @copyright  2025
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\task;

class update_admingrades extends \core\task\adhoc_task {

    /**
     * Create the task
     * @param string $name
     * @return \local_gugrades\task\update_admingrades
     */
    public static function instance(string $name): self {
        $task = new self();
        $task->set_custom_data((object) [
            'name' => $name,
        ]);

        return $task;
    }

    /**
     * Execute the ad-hoc task
     */
    public function execute() {
        $data = $this->get_custom_data();
        $name = $data->name;
        mtrace('Updating admingrades for name = "' . $name . '"');

        \local_gugrades\admingrades::update_displaynames($name);
    }
}