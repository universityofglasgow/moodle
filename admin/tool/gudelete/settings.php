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
 * TODO describe file settings
 *
 * @package    tool_gudelete
 * @copyright  2024 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**defined('MOODLE_INTERNAL') || die();

 if ($hassiteconfig) {
  global $DB;
  $settings = new admin_settingpage('tool_gudelete_settings', new lang_string('pluginname', 'tool_gudelete'));
  $settings->add(new admin_setting_heading('tool_gudelete_header', '', get_string('configure_description', 'tool_gudelete')));

  $options_timestamp = array('startdate' => get_string('startdate'),
        'last_activity' => get_string('last_activity', 'tool_gudelete'));
    $settings->add(new admin_setting_configselect('targettimestamp',
            get_string('targettimestamp', 'tool_gudelete'),
            '', null, $options_timestamp));

    $settings->add(new admin_setting_configcheckbox('run_cron', get_string('run_cron', 'tool_gudelete'), '', 0));

    $categories = $DB->get_records('course_categories');
    $options = array();
    foreach ($categories as $category) {
      $options[$category->id] = $category->name;
    }

    $settings->add(new admin_setting_configmultiselect('sourcecat', get_string('sourcecat', 'tool_gudelete'), '', null, $options));
    $options = array(get_string('choose')) + $options;

    $settings->add(new admin_setting_configcheckbox('include_subcategories', get_string('include_subcategories', 'tool_gudelete'), '', 0));

    $settings->add(new admin_setting_configselect('targetcat', get_string('targetcat', 'tool_gudelete'), '', null, $options));
    $settings->add(new admin_setting_configtext('days', get_string('days', 'tool_gudelete'), '', '365', PARAM_RAW, '10', '1'));

  $ADMIN->add('tools', $settings);

 }
*/

defined('MOODLE_INTERNAL') || die();
if ($hassiteconfig) {
  $settings = new admin_settingpage('tool_gudelete', get_string('pluginname', 'tool_gudelete'));
  $ADMIN->add('tools', $settings);

  global $DB;
  $configs = array();
  //$configs[] = new admin_setting_heading('tool_gudelete_header', '', get_string('configure_description', 'tool_gudelete'));

  $options_timestamp = array('timemodified' => get_string('timemodified', 'tool_gudelete'), 'last_activity' => get_string('last_activity', 'tool_gudelete'));
  $configs[] = new admin_setting_configselect('targettimestamp', get_string('targettimestamp', 'tool_gudelete'), '', null, $options_timestamp);
  $configs[] = new admin_setting_configcheckbox('run_cron', get_string('run_cron', 'tool_gudelete'), '', 0);
  $categories = $DB->get_records('course_categories');
  $options = array();
  foreach ($categories as $category) {
    $options[$category->id] = $category->name;
  }

  $configs[] = new admin_setting_configmultiselect('sourcecat', get_string('sourcecat', 'tool_gudelete'), '', null, $options);
  $options = array(get_string('choose')) + $options;

  $configs[] = new admin_setting_configcheckbox('include_subcategories', get_string('include_subcategories', 'tool_gudelete'), '', 0);

  $configs[] = new admin_setting_configselect('targetcat', get_string('targetcat', 'tool_gudelete'), '', null, $options);
  $configs[] = new admin_setting_configtext('days', get_string('days', 'tool_gudelete'), '', '365', PARAM_RAW, '10', '1');

  foreach ($configs as $config) {
    $config->plugin = 'tool_gudelete';
    $settings->add($config);
  }

}
    