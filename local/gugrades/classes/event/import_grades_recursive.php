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
 * Define import grade recursive event
 *
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\event;

/**
 * import_grades_users event
 */
class import_grades_recursive extends \core\event\base {

    /**
     * Initialise event
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'grade_items';
    }

    /**
     * Get event name
     * @return string
     */
    public static function get_name() {
        return get_string('eventimportgradesrecursive', 'local_gugrades');
    }

    /**
     * Get event description
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' imported recursive grades for grade item '$this->objectid'.";
    }
}
