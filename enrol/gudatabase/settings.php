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
 * Database enrolment plugin settings and presets.
 *
 * @package    enrol
 * @subpackage gudatabase
 * @copyright  2012 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $average = get_config('enrol_gudatabase', 'average');
    $average = empty($average) ? '-' : $average;
    $settings->add(new admin_setting_heading('gudatabase_average', '',
            get_string('displayaverage', 'enrol_gudatabase',
            $average)));

    $settings->add(new admin_setting_heading('enrol_gudatabase_settings', '',
        get_string('pluginname_desc', 'enrol_database')));

    $settings->add(new admin_setting_heading('enrol_gudatabase_exdbheader',
        get_string('settingsheaderdb', 'enrol_database'), ''));

    $options = array(
        '',
        "access",
        "ado_access",
        "ado",
        "ado_mssql",
        "borland_ibase",
        "csv",
        "db2",
        "fbsql",
        "firebird",
        "ibase",
        "informix72",
        "informix",
        "mssql",
        "mssql_n",
        "mssqlnative",
        "mysql",
        "mysqli",
        "mysqlt",
        "oci805",
        "oci8",
        "oci8po",
        "odbc",
        "odbc_mssql",
        "odbc_oracle",
        "oracle",
        "postgres64",
        "postgres7",
        "postgres",
        "proxy",
        "sqlanywhere",
        "sybase",
        "vfp",
    );
    $options = array_combine($options, $options);
    $settings->add(new admin_setting_configselect('enrol_gudatabase/dbtype',
        get_string('dbtype', 'enrol_database'), get_string('dbtype_desc', 'enrol_database'), '', $options));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/dbhost',
        get_string('dbhost', 'enrol_database'), get_string('dbhost_desc', 'enrol_database'), 'localhost'));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/dbuser',
        get_string('dbuser', 'enrol_database'), '', ''));

    $settings->add(new admin_setting_configpasswordunmask('enrol_gudatabase/dbpass',
        get_string('dbpass', 'enrol_database'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/dbname',
        get_string('dbname', 'enrol_database'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/dbencoding',
        get_string('dbencoding', 'enrol_database'), '', 'utf-8'));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/dbsetupsql',
        get_string('dbsetupsql', 'enrol_database'), get_string('dbsetupsql_desc', 'enrol_database'), ''));

    $settings->add(new admin_setting_configcheckbox('enrol_gudatabase/dbsybasequoting',
        get_string('dbsybasequoting', 'enrol_database'), get_string('dbsybasequoting_desc', 'enrol_database'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_gudatabase/debugdb',
        get_string('debugdb', 'enrol_database'), get_string('debugdb_desc', 'enrol_database'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_gudatabase/enforceenddate',
        get_string('enforceenddate', 'enrol_gudatabase'), get_string('enforceenddate_desc', 'enrol_gudatabase'), 1));

    $settings->add(new admin_setting_configcheckbox('enrol_gudatabase/allowunenrol',
        get_string('allowunenrol', 'enrol_gudatabase'), get_string('allowunenrol_desc', 'enrol_gudatabase'), 0));

    $settings->add(new admin_setting_heading('enrol_gudatabase_remoteheader',
        get_string('settingsheaderremote', 'enrol_database'), ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/remoteenroltable',
        get_string('remoteenroltable', 'enrol_database'), get_string('remoteenroltable_desc', 'enrol_database'), ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/remotecoursefield',
        get_string('remotecoursefield', 'enrol_database'), get_string('remotecoursefield_desc', 'enrol_database'), ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/remoteuserfield',
        get_string('remoteuserfield', 'enrol_database'), get_string('remoteuserfield_desc', 'enrol_database'), ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/remoterolefield',
        get_string('remoterolefield', 'enrol_database'), get_string('remoterolefield_desc', 'enrol_database'), ''));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_gudatabase/defaultrole',
            get_string('defaultrole', 'enrol_database'), get_string('defaultrole_desc', 'enrol_database'), $student->id, $options));
    }

    $settings->add(new admin_setting_configcheckbox('enrol_gudatabase/ignorehiddencourses',
        get_string('ignorehiddencourses', 'enrol_database'), get_string('ignorehiddencourses_desc', 'enrol_database'), 0));

    $options = array(ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
                     ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'));
    $settings->add(new admin_setting_configselect('enrol_gudatabase/unenrolaction',
        get_string('extremovedaction', 'enrol'), get_string('extremovedaction_help', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL, $options));

    $unenrolguardoptions = [
        0        => get_string('daysoff', 'enrol_gudatabase'),
        2592000  => get_string('days30', 'enrol_gudatabase'),
        5184000  => get_string('days60', 'enrol_gudatabase'),
        7776000  => get_string('days90', 'enrol_gudatabase'),
        15228000 => get_string('days180', 'enrol_gudatabase'),
        23328000 => get_string('days270', 'enrol_gudatabase'),
    ];
    $settings->add(new admin_setting_configselect('enrol_gudatabase/unenrolguard',
        get_string('unenrolguard', 'enrol_gudatabase'), get_string('unenrolguard_help', 'enrol_gudatabase'),
        7776000, $unenrolguardoptions));

    /*
    $enrolguardoptions = [
        0        => get_string('daysoff', 'enrol_gudatabase'),
        2592000  => get_string('days30', 'enrol_gudatabase'),
        5184000  => get_string('days60', 'enrol_gudatabase'),
        7776000  => get_string('days90', 'enrol_gudatabase'),
        15228000 => get_string('days180', 'enrol_gudatabase'),
        23328000 => get_string('days270', 'enrol_gudatabase'),
        31536000 => get_string('days365', 'enrol_gudatabase'),
    ];
    $settings->add(new admin_setting_configselect('enrol_gudatabase/enrolguard',
        get_string('enrolguard', 'enrol_gudatabase'), get_string('enrolguard_help', 'enrol_gudatabase'),
        15228000, $enrolguardoptions));
    */

    $settings->add(new admin_setting_configtext('enrol_gudatabase/enrolguardpercent',
        get_string('enrolguardpercent', 'enrol_gudatabase'), get_string('enrolguardpercent_help', 'enrol_gudatabase'), 50));

    $settings->add(new admin_setting_heading('enrol_gudatabase_codesheader',
        get_string('settingsheadercodes', 'enrol_gudatabase'), ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/codesenroltable',
        get_string('codesenroltable', 'enrol_gudatabase'), get_string('codesenroltable_desc', 'enrol_gudatabase'), ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/timelimit',
        get_string('timelimit', 'enrol_gudatabase'), get_string('timelimit_desc', 'enrol_gudatabase'), 30));

    $settings->add(new admin_setting_heading('enrol_gudatabase_classlistheader',
        get_string('settingsclasslist', 'enrol_gudatabase'), ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/classlisttable',
        get_string('classlisttable', 'enrol_gudatabase'), get_string('classlisttable_desc', 'enrol_gudatabase'), ''));

    $settings->add(new admin_setting_configtext('enrol_gudatabase/programtable',
        get_string('programtable', 'enrol_gudatabase'), get_string('programtable_desc', 'enrol_gudatabase'), ''));

    $settings->add(new admin_setting_configtextarea('enrol_gudatabase/schoolsjson',
        get_string('schoolsjson', 'enrol_gudatabase'), get_string('schoolsjson_desc', 'enrol_gudatabase'), ''));

    $settings->add(new admin_setting_heading('enrol_gudatabase_newaccountheader',
        get_string('settingsnewuser', 'enrol_gudatabase'), ''));
        
    $plugins = core_component::get_plugin_list('auth');
    $pluginselect = [];
    foreach ($plugins as $pluginname => $pluginpath) {
        $pluginselect[$pluginname] = $pluginname;
    }
    $settings->add(new admin_setting_configselect('enrol_gudatabase/newuserauth',
    get_string('newuserauth', 'enrol_gudatabase'), get_string('newuserauth_help', 'enrol_gudatabase'),
    'guid', $pluginselect));
}
