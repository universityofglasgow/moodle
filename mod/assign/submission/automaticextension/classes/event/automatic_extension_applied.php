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
 * Automatic extension applied event.
 *
 * @package    assignsubmission_automaticextension
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_automaticextension\event;

/**
 * Automatic extension applied event.
 *
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class automatic_extension_applied extends \core\event\base {
    /**
     * {@inheritDoc}
     * @see \core\event\base::init()
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'assign';
    }

    /**
     * Returns the name of the event.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_automatic_extension_applied', 'assignsubmission_automaticextension');
    }

    /**
     * {@inheritDoc}
     * @see \core\event\base::get_description()
     */
    public function get_description() {
        $obj = new \stdClass();
        $obj->userid = $this->userid;
        $obj->contextinstanceid = $this->contextinstanceid;
        $obj->extensionduedate = userdate($this->other['extensionduedate'], get_string('strftimedaydatetime', 'langconfig'));

        return get_string('event_automatic_extension_applied_desc', 'assignsubmission_automaticextension', $obj);
    }
}
