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
 * Course diagnostic settings
 *
 * @package    report_coursediagnositc
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $pluginname = new lang_string('pluginname', 'report_coursediagnostic');
    $settings = new admin_settingpage('course_diagnostic_settings', $pluginname);

    $settings->add(new admin_setting_heading('coursediagnostic_default_options',
        '', get_string('coursediagnosticdefaults_desc', 'report_coursediagnostic')));

    // General plugin settings.
    $name = new lang_string('coursediagnostichdr_text', 'report_coursediagnostic');
    $desc = '';
    $setting = new admin_setting_heading('coursediagnosticsettingshdr',
        $name,
        $desc);
    $settings->add($setting);

    $name = new lang_string('enablediagnostic', 'report_coursediagnostic');
    $desc = new lang_string('enablediagnostic_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/enablediagnostic',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('timelimit', 'report_coursediagnostic');
    $desc = new lang_string('timelimit_desc', 'report_coursediagnostic');
    $default = 30;
    $setting = new admin_setting_configtext('report_coursediagnostic/timelimit',
        $name,
        $desc,
        $default);
    // I don't think flushing the cache is needed for this setting.
    $settings->add($setting);

    // Course settings tests.
    $name = new lang_string('testsuite', 'report_coursediagnostic');
    $desc = '';
    $setting = new admin_setting_heading('coursediagnostichdr',
        $name,
        $desc);
    $settings->add($setting);

    $name = new lang_string('startdate', 'report_coursediagnostic');
    $desc = new lang_string('startdate_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/startdate',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('enddate', 'report_coursediagnostic');
    $desc = new lang_string('enddate_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/enddate',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('visibility', 'report_coursediagnostic');
    $desc = new lang_string('visibility_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/visibility',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('studentenrolment', 'report_coursediagnostic');
    $desc = new lang_string('studentenrolment_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/studentenrolment',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('inactivestaffenrolment', 'report_coursediagnostic');
    $desc = new lang_string('inactivestaffenrolment_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/inactivestaffenrolment',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('inactivestudentenrolment', 'report_coursediagnostic');
    $desc = new lang_string('inactivestudentenrolment_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/inactivestudentenrolment',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('groupmode', 'report_coursediagnostic');
    $desc = new lang_string('groupmode_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/groupmode',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('activitycompletion', 'report_coursediagnostic');
    $desc = new lang_string('activitycompletion_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/activitycompletion',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    // Course Size tests.
    $name = new lang_string('coursesize', 'report_coursediagnostic');
    $desc = '';
    $setting = new admin_setting_heading('coursesizehdr',
        $name,
        $desc);
    $settings->add($setting);

    $name = new lang_string('filesizelimit', 'report_coursediagnostic');
    $desc = new lang_string('filesizelimit_desc', 'report_coursediagnostic');
    $options = [
        1 => '100' . get_string('sizemb'),
        2 => '500' . get_string('sizemb'),
        3 => '1' . get_string('sizegb'),
        4 => '10' . get_string('sizegb'),
        5 => '100' . get_string('sizegb'),
        6 => '500' . get_string('sizegb'),
        7 => '1' . get_string('sizetb')
    ];
    $setting = new admin_setting_configselect('report_coursediagnostic/filesizelimit',
        $name,
        $desc,
        1,
        $options);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('coursefiles', 'report_coursediagnostic');
    $desc = new lang_string('coursefiles_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/coursefiles',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('coursevideo', 'report_coursediagnostic');
    $desc = new lang_string('coursevideo_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/coursevideo',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('courseaudio', 'report_coursediagnostic');
    $desc = new lang_string('courseaudio_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/courseaudio',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    // Course assignment tests.
    $name = new lang_string('courseassignment', 'report_coursediagnostic');
    $desc = '';
    $setting = new admin_setting_heading('courseassignmenthdr',
        $name,
        $desc);
    $settings->add($setting);

    $name = new lang_string('assignmentduedate', 'report_coursediagnostic');
    $desc = new lang_string('assignmentduedate_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/assignmentduedate',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    // Enrolment plugin tests.
    $name = new lang_string('enrolmentplugins', 'report_coursediagnostic');
    $desc = '';
    $setting = new admin_setting_heading('enrolmentpluginshdr',
        $name,
        $desc);
    $settings->add($setting);

    $name = new lang_string('enrolmentpluginsenabled', 'report_coursediagnostic');
    $desc = new lang_string('enrolmentplugins_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/enrolmentpluginsenabled',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('selfenrolmentkey', 'report_coursediagnostic');
    $desc = new lang_string('selfenrolmentkey_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/selfenrolmentkey',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    // Auto enrolment tests - applies only to UofG currently.
    // ...@todo - find a way to allow these tests to be loaded in separately.
    $name = new lang_string('autoenrolment', 'report_coursediagnostic');
    $desc = '';
    $setting = new admin_setting_heading('autoenrolmenthdr',
        $name,
        $desc);
    $settings->add($setting);

    $name = new lang_string('autoenrolment_studentdatadeletion', 'report_coursediagnostic');
    $desc = new lang_string('autoenrolment_studentdatadeletion_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/autoenrolment_studentdatadeletion',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    $name = new lang_string('mycampusenrolment', 'report_coursediagnostic');
    $desc = new lang_string('mycampusenrolment_desc', 'report_coursediagnostic');
    $default = 1;
    $setting = new admin_setting_configcheckbox('report_coursediagnostic/mycampusenrolment',
        $name,
        $desc,
        $default);
    $setting->set_updatedcallback('report_coursediagnostic\coursediagnostic::flag_cache_for_deletion');
    $settings->add($setting);

    if (\report_coursediagnostic\coursediagnostic::get_cache_deletion_flag()) {
        \report_coursediagnostic\coursediagnostic::purge_diagnostic_settings_cache();
    }
}
