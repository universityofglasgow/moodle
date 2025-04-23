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
 * The update_log event.
 *
 * @package   quiz_filedownloader
 * @copyright 2019 ETH Zurich
 * @author    Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_filedownloader\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The update_log event class.
 *
 * @since     Moodle 3.6.4
 * @copyright 2019 ETH Zurich
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class update_log extends \core\event\base {

    /**
     * Initializaion.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Returns name.
     * @return string
     */
    public static function get_name() {
        return get_string('eventupdate_log', 'quiz_filedownloader');
    }

    /**
     * Returns description.
     * @return string
     */
    public function get_description() {
        return "User with id '$this->userid' has downloaded quiz sumbissions.";
    }

    /**
     * Returns url.
     * @return string
     */
    public function get_url() {
        return new \moodle_url('/mod/quiz/report.php', array(
            'id' => $this->contextinstanceid,
            'mode' => 'filedownloader'
        ));
    }

    /**
     * Returns legacy log data.
     * @return array
     */
    public function get_legacy_logdata() {
        return array(
            $this->courseid,
            'course',
            'download quiz submissions',
            'report.php? id=' . $this->contextinstanceid . '&mode=filedownloader',
            $this->contextinstanceid
        );
    }
}
