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

namespace mod_board\event;

/**
 * Add comment event handler.
 * @package     mod_board
 * @author      Bas Brands <bas@sonsbeekmedia.nl>
 * @copyright   2022 Brickfield Education Labs <https://www.brickfield.ie/>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_comment extends \core\event\base {
    /**
     * Init function.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'board';
    }

    /**
     * Get name.
     * @return \lang_string|string
     */
    public static function get_name() {
        return get_string('event_add_comment', 'mod_board');
    }

    /**
     * Get description.
     * @return \lang_string|string|null
     */
    public function get_description() {
        $obj = new \stdClass;
        $obj->userid = $this->userid;
        $obj->objectid = $this->objectid;
        $obj->content = $this->other['content'];
        $obj->noteid = $this->other['noteid'];
        return get_string('event_add_comment_desc', 'mod_board', $obj);
    }

    /**
     * Get legacy logdata.
     * @return array
     */
    public function get_legacy_logdata() {
        return array($this->courseid, 'mod_board', 'add_comment', null, $this->objectid,
                     $this->other['content'], $this->other['noteid']);
    }
}
