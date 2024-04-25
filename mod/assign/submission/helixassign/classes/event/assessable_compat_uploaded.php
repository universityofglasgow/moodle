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
 * The assignsubmission_helixassign assessable uploaded event with legacy log compatibility.
 *
 * @package    assignsubmission_helixassign
 * @copyright  2013 Frédéric Massart, 2018 Tim Williams
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_helixassign\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The assignsubmission_helixassign assessable uploaded event class with legacy log compatibility.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string format: (optional) content format.
 * }
 *
 * @package    assignsubmission_helixassign
 * @since      Moodle 2.6
 * @copyright  2013 Frédéric Massart, 2018 Tim Williams
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assessable_compat_uploaded extends assessable_uploaded {

    /**
     * Legacy event data if get_legacy_eventname() is not empty.
     *
     * @return stdClass
     */
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->contextinstanceid;
        $eventdata->itemid = $this->objectid;
        $eventdata->courseid = $this->courseid;
        $eventdata->userid = $this->userid;
        $eventdata->content = $this->other['content'];
        if ($this->other['pathnamehashes']) {
            $eventdata->pathnamehashes = $this->other['pathnamehashes'];
        }
        return $eventdata;
    }

    /**
     * Return the legacy event name.
     *
     * @return string
     */
    public static function get_legacy_eventname() {
        return 'assessable_content_uploaded';
    }
}
