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
class backup_local_xp_plugin extends backup_local_plugin {

    /**
     * Define structure.
     */
    protected function define_course_plugin_structure() {
        $userinfo = $this->get_setting_value('users');

        $plugin = $this->get_plugin_element();
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($pluginwrapper);

        // Add config.
        $config = new backup_nested_element('xp_config', ['courseid'], [
            'maxpointspertime', 'timeformaxpoints', 'currencystate', 'badgetheme', 'enablegroupladder',
            'groupidentitymode', 'progressbarmode', 'groupladdercols', 'grouporderby'
        ]);
        $config->set_source_table('local_xp_config', ['courseid' => backup::VAR_COURSEID]);
        $pluginwrapper->add_child($config);

        // Add logs. Note that those will not be useful for the cheat guard any more, as
        // they will point to other resources, linked to another course. In fact, for now
        // we won't include it as it could have a negative effect on the teacher's perceptions
        // of what happens. The XP, and config will be backed up as normal, but the logs will
        // disappear.
        // $logs = new backup_nested_element('logs');
        // $log = new backup_nested_element('log', ['contextid'], [
        //     'userid', 'type', 'signature', 'points', 'time', 'hashkey',
        // ]);
        // $log->annotate_ids('user', 'userid');
        // $log->set_source_table('local_xp_log', ['contextid' => backup::VAR_CONTEXTID]);
        // if ($userinfo) {
        //     // We only add it if need be.
        //     $logs->add_child($log);
        // }
        // $pluginwrapper->add_child($logs);

        // Add currency.
        $pluginwrapper->annotate_files('local_xp', 'currency', null, context_course::instance($this->task->get_courseid())->id);

        return $plugin;
    }

}
