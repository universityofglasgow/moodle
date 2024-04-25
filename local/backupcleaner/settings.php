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
 * GUID Enrolment sync
 *
 * @package    local_backupcleaner
 * @copyright  2021 Alex Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage(
            'local_backupcleaner', get_string('pluginname', 'local_backupcleaner'));
    $ADMIN->add('localplugins', $settings);
    
    $name = 'local_backupcleaner/min_age';                                                                                                   
    $title = get_string('min_backup_age', 'local_backupcleaner');                                                                                   
    $description = get_string('min_backup_age_desc', 'local_backupcleaner');
    
    $choices = Array(
        '31' => get_string('age_30_days', 'local_backupcleaner'),
        '91' => get_string('age_90_days', 'local_backupcleaner'),
        '183' => get_string('age_180_days', 'local_backupcleaner'),
        '365' => get_string('age_365_days', 'local_backupcleaner'),
        '730' => get_string('age_730_days', 'local_backupcleaner'),
        '1095' => get_string('age_1095_days', 'local_backupcleaner'),
        '1825' => get_string('age_1825_days', 'local_backupcleaner'),
        '3650' => get_string('age_3650_days', 'local_backupcleaner'),
    );                                                                     
    $default = '3650';
    
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                                                                                                                                                                     
    $settings->add($setting);
    
    $name = 'local_backupcleaner/max_delete';                                                                                                   
    $title = get_string('max_delete', 'local_backupcleaner');                                                                                   
    $description = get_string('max_delete_desc', 'local_backupcleaner');
    
    $choices = Array(
        '1' => get_string('max_delete_1', 'local_backupcleaner'),
        '5' => get_string('max_delete_5', 'local_backupcleaner'),
        '10' => get_string('max_delete_10', 'local_backupcleaner'),
        '25' => get_string('max_delete_25', 'local_backupcleaner'),
        '50' => get_string('max_delete_50', 'local_backupcleaner'),
        '100' => get_string('max_delete_100', 'local_backupcleaner'),
        '250' => get_string('max_delete_250', 'local_backupcleaner'),
        '500' => get_string('max_delete_500', 'local_backupcleaner'),
        '1000' => get_string('max_delete_1000', 'local_backupcleaner'),
    );                                                                     
    $default = '10';
    
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                                                                                                                                                                     
    $settings->add($setting);
    
        

}
