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
 * Local XP backup.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Local XP backup.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_xp_plugin extends restore_local_plugin {

    /**
     * Define structure.
     */
    protected function define_course_plugin_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('users');

        // Define each path.
        $paths[] = new restore_path_element($this->get_namefor('config'), $this->get_pathfor('/xp_config'));

        // This path is a legacy one to ensure that older backups still work. We had to change the name of the
        // key where the config was stored because Moodle requires keys to be unique across plugins, hence the
        // new name 'xp_config'. The previous key structure is identical, and as such we should always send it
        // to the same restore method. Though because we can't have two restore path with the same name, we
        // had to declare another method. Note that when the config isn't found, nothing is done. See MDL-45441.
        $paths[] = new restore_path_element($this->get_namefor('config_legacy_key'), $this->get_pathfor('/config'));

        return $paths;
    }

    /**
     * Process the legacy key of config.
     *
     * @param array $data Data.
     * @return void
     */
    public function process_local_xp_config_legacy_key($data) {
        $this->process_local_xp_config($data);
    }

    /**
     * Process config.
     *
     * @param array $data Data.
     * @return void
     */
    public function process_local_xp_config($data) {
        global $DB;
        $data['courseid'] = $this->task->get_courseid();
        if ($DB->record_exists('local_xp_config', ['courseid' => $data['courseid']])) {
            $this->log('local_xp: config not restored, existing config was found', backup::LOG_DEBUG);
            return;
        }
        $DB->insert_record('local_xp_config', $data);
    }

    /**
     * After execute.
     *
     * @return void
     */
    public function after_execute_course() {
        $this->add_related_files('local_xp', 'currency', null, $this->task->get_old_contextid());
    }

}
