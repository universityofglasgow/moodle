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
 * The local_gugcat release provisional grade event.
*
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_gugcat\event;
defined('MOODLE_INTERNAL') || die();

class release_prv_grade extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }
 
    public static function get_name() {
        return get_string('eventreleaseprvgrades', 'local_gugcat');
    }
 
    public function get_description() {
        return "The user with id {$this->userid} have released provisional grades in the course with id of {$this->courseid}.";
    }
 
    public function get_url() {
        $url = new \moodle_url('local/gugcat/index.php', array('id' => $this->courseid, 'page'=>$this->other['page']));
        if(!is_null($this->other['activityid']))
            $url->param('activityid', $this->other['activityid']);
        if(!is_null($this->other['categoryid']))
            $url->param('categoryid', $this->other['categoryid']);

        return $url;
    }
 
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'local_gugcat', 'release_prv_grade',
            '...........',
            '....', $this->contextinstanceid);
    }
 
    protected function get_legacy_eventdata() {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->userid = $this->relateduserid;
        return $data;
    }
}