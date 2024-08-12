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
 * Define view dashboard event
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\event;

/**
 * view_dashboard event
 */
class view_dashboard extends \core\event\base {

    /**
     * Initialise event
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'course';
    }

    /**
     * Get event name
     * @return string
     */
    public static function get_name() {
        return get_string('event_view_dashboard', 'block_newgu_spdetails');
    }

    /**
     * Get event description
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' viewed the MyGrades Student Dashboard.";
    }

    /**
     * Get event URL
     * @return string
     */
    public function get_url() {
        return new \moodle_url('/blocks/newgu_spdetails/index.php', ['id' => $this->objectid]);
    }
}
