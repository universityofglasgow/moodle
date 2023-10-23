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
 * the local_gugcat require resit event.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_gugcat\event;
defined('MOODLE_INTERNAL') || die();

class require_resit extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r'; // Valid values are c(reate), r(ead), u(pdate), d(elete).
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    public static function get_name() {
        return get_string('eventrequireresit', 'local_gugcat');
    }

    public function get_description() {
        return "The user with id {$this->userid} has {$this->other['status']} require resit for student with idnumber of ".
                "{$this->other['idnumber']} in the course with the id of {$this->courseid}";
    }

    public function get_url() {
        $url = new \moodle_url('/local/gugcat/overview/index.php', array('id' => $this->courseid, 'page' => $this->other['page']));
        if (!is_null($this->other['categoryid'])) {
            $url->param('categoryid', $this->other['categoryid']);
        }

        return $url;
    }

    public function get_legacy_logdata() {
        return array($this->courseid, 'local_gugcat', 'require_resit',
            '...........',
            '....', $this->contextinstanceid);
    }

    protected function get_legacy_eventdata() {
        $data = new \stdClass();
        $data->userid = $this->relateduserid;
        return $data;
    }
}