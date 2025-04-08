<?php
// This file is part of Moodle
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
 * Defines restore_local_gugrades class.
 *
 * @package     local_gugrades
 * @author      Howard Miller
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore plugin class.
 *
 * @package    local_gugrades
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_gugrades_plugin extends restore_local_plugin {

    /**
     * Return the paths to be handled by the plugin (course level)
     */
    protected function define_course_plugin_structure() {

        $paths = [];
        $coursepath = $this->get_pathfor('/');

        $paths[] = new restore_path_element('gugrades_config', $coursepath . '/gugrades_config');
        $paths[] = new restore_path_element('gugrades_map', $coursepath . '/gugrades_maps/gugrades_map');
        $paths[] = new restore_path_element('gugrades_map_value', $coursepath . '/gugrades_map_values/gugrades_map_value/gm_values/gm_value');

        return $paths;
    }

    /**
     * Process config data
     */
    public function process_gugrades_config($data) {
        global $DB;

        $data = (object) $data;
        $data->courseid = $this->task->get_courseid();
        $data->gradeitemid = 0;

        $DB->insert_record('local_gugrades_config', $data);
    }

    /**
     * Process conversion map
     */
    public function process_gugrades_map($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newid = $DB->insert_record('local_gugrades_map', $data);
        $this->set_mapping('gugrades_map', $oldid, $newid);
    }

    /**
     * Process conversion map items
     */
    public function process_gugrades_map_value($data) {
        global $DB;

        $data = (object) $data;
        $data->mapid = $this->get_mappingid('gugrades_map', $data->mapid);

        $DB->insert_record('local_gugrades_map_value', $data);
    }

    /**
     * Update ids that we didn't know.
     */
    protected function after_restore_course() {
        global $DB;


    }

}