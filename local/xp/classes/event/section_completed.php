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
 * Section completed event.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Section completed event class.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_completed extends \core\event\base {

    /** @var bool Whether this event is XP compatible. */
    public $isxpcompatible = true;

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return 'The user with ID ' . $this->relateduserid . ' completed the section ' . $this->other['sectionnum'] . '.';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_section_completed', 'local_xp');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/report/progress/index.php', ['course' => $this->courseid]);
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Data validation.
     *
     * @return void
     */
    protected function validate_data() {
        if (empty($this->relateduserid)) {
            throw new \coding_exception('The related user ID must be set.');
        }
        if (!isset($this->other['sectionnum'])) {
            throw new \coding_exception('The sectionnum must be set in $other.');
        }
    }

}
