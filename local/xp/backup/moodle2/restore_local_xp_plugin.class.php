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

        // Define each path. Note that, this structure may not always be defined. If the backup is merging
        // into an existing course, and the setting 'Overwrite course configuration' is set to 'No' (the default),
        // then the restore_course_structure_step is not included, and thus this structure is not defined. This
        // can lead to data loss if users are not aware of this. This also means that delete & merge backups
        // without the overwrite setting enabled, will not have their data deleted.
        $paths[] = new restore_path_element($this->get_namefor('config'), $this->get_pathfor('/xp_config'));
        $paths[] = new restore_path_element($this->get_namefor('drop'), $this->get_pathfor('/xp_drops/xp_drop'));

        // This path is a legacy one to ensure that older backups still work. We had to change the name of the
        // key where the config was stored because Moodle requires keys to be unique across plugins, hence the
        // new name 'xp_config'. The previous key structure is identical, and as such we should always send it
        // to the same restore method. Though because we can't have two restore path with the same name, we
        // had to declare another method. Note that when the config isn't found, nothing is done. See MDL-45441.
        $paths[] = new restore_path_element($this->get_namefor('config_legacy_key'), $this->get_pathfor('/config'));

        return $paths;
    }

    /**
     * Pre-processing.
     *
     * This method must be called from the first restore path element as we cannot simulate the presence
     * of another path if there is no associated data in the backup about it.
     *
     * @return void
     */
    private function pre_process_local_xp() {
        global $DB;

        $target = $this->task->get_target();
        $courseid = $this->task->get_courseid();

        // The backup target expects that all content is first being removed. Since deleting the block
        // instance does not delete the data itself, we must manually delete everything.
        if ($target == backup::TARGET_CURRENT_DELETING || $target == backup::TARGET_EXISTING_DELETING) {
            $this->task->log('local_xp: deleting all data in target course', backup::LOG_DEBUG);

            // Removing associated data.
            $conditions = ['courseid' => $courseid];
            $DB->delete_records('local_xp_config', $conditions);
            $DB->delete_records('local_xp_drops', $conditions);
            $DB->delete_records('local_xp_log', ['contextid' => $this->task->get_contextid()]);
        }
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

        // Call the pre-process.
        $this->pre_process_local_xp();

        $data['courseid'] = $this->task->get_courseid();
        if ($DB->record_exists('local_xp_config', ['courseid' => $data['courseid']])) {
            $this->task->log('local_xp: config not restored, existing config was found', backup::LOG_DEBUG);
            return;
        }
        $DB->insert_record('local_xp_config', $data);
    }

    /**
     * Process drop.
     *
     * @param array $data Data.
     * @return void
     */
    public function process_local_xp_drop($data) {
        global $DB;

        $oldid = $data['id'];
        $data['courseid'] = $this->task->get_courseid();

        // When the secret is already found, we cannot proceed with the restore. It usually means that
        // the secret is being restored in the same site as the original, either by duplicating the course
        // or by merge. This is not a perfect solution as the content will still be restored, which would
        // render another drop.
        if ($DB->record_exists('local_xp_drops', ['secret' => $data['secret']])) {
            $this->task->log("local_xp: drop '{$data['name']}' ({$data['id']}) not restored, " .
                "secret already used", backup::LOG_INFO);
            return;
        }

        $newid = $DB->insert_record('local_xp_drops', $data);
        $this->set_mapping('local_xp_drop', $oldid, $newid);
    }

    /**
     * After execute.
     *
     * @return void
     */
    public function after_execute_course() {
        $this->add_related_files('local_xp', 'currency', null, $this->task->get_old_contextid());
    }

    /**
     * Define decode contents.
     *
     * @return array
     */
    public static function define_decode_contents() {
        return [];
    }
}
