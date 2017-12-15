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
 * Settings for RecordingsBN
 *
 * @package   mod_recordingsbn
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @copyright 2011-2014 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

defined('MOODLE_INTERNAL') || die;

global $BIGBLUEBUTTONBN_CFG;

require_once(dirname(__FILE__).'/locallib.php');

if ($ADMIN->fulltree) {
    if (!isset($BIGBLUEBUTTONBN_CFG->recordingsbn_ui_html_default) ||
        !isset($BIGBLUEBUTTONBN_CFG->recordingsbn_ui_html_editable)) {
            $settings->add( new admin_setting_heading('recordingsbn_config_general',
                    get_string('config_general', 'recordingsbn'),
                    get_string('config_general_description', 'recordingsbn')));
    }
    if (!isset($BIGBLUEBUTTONBN_CFG->recordingsbn_ui_html_default) ) {
        $settings->add(new admin_setting_configcheckbox('recordingsbn_ui_html_default',
                get_string('config_feature_ui_html_default', 'recordingsbn'),
                get_string('config_feature_ui_html_default_description', 'recordingsbn'),
                1));
    }
    if (!isset($BIGBLUEBUTTONBN_CFG->recordingsbn_ui_html_editable)) {
        // UI for 'recording' feature.
        $settings->add(new admin_setting_configcheckbox('recordingsbn_ui_html_editable',
                get_string('config_feature_ui_html_editable', 'recordingsbn'),
                get_string('config_feature_ui_html_editable_description', 'recordingsbn'),
                0));
    }
    if (!isset($BIGBLUEBUTTONBN_CFG->recordingsbn_include_deleted_activities_default)) {
        $settings->add(new admin_setting_configcheckbox('recordingsbn_include_deleted_activities_default',
                get_string('config_feature_include_deleted_activities_default', 'recordingsbn'),
                get_string('config_feature_include_deleted_activities_default_description', 'recordingsbn'),
                1));
    }
    if (!isset($BIGBLUEBUTTONBN_CFG->recordingsbn_include_deleted_activities_editable)) {
        // UI for 'recording' feature.
        $settings->add(new admin_setting_configcheckbox('recordingsbn_include_deleted_activities_editable',
                get_string('config_feature_include_deleted_activities_editable', 'recordingsbn'),
                get_string('config_feature_include_deleted_activities_editable_description', 'recordingsbn'),
                0));
    }
}
