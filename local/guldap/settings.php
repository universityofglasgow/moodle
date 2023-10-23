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
 * Admin settings and defaults.
 *
 * @package local_guldap
 * @copyright  2022 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    require_once($CFG->libdir . '/ldaplib.php');
    require_once($CFG->dirroot.'/auth/ldap/classes/admin_setting_special_lowercase_configtext.php');
    require_once($CFG->dirroot.'/auth/ldap/classes/admin_setting_special_contexts_configtext.php');
    require_once($CFG->dirroot.'/auth/ldap/classes/admin_setting_special_ntlm_configtext.php');

    $settings = new admin_settingpage(
        'local_guldap', new lang_string('pluginname', 'local_guldap'));
    $ADMIN->add('localplugins', $settings);

    // Login settings
    $settings->add(new admin_setting_heading('local_guldap/ldaploginsettings',
        new lang_string('loginsettings', 'local_guldap'), ''));

    // Enable login hook.
    $yesno = [
        new lang_string('no'),
        new lang_string('yes'),
    ];
    $settings->add(new admin_setting_configselect('local_guldap/loginhook',
            new lang_string('loginhook', 'local_guldap'),
            new lang_string('loginhook_help', 'local_guldap'), 0 , $yesno));        

    // LDAP server settings.
    $settings->add(new admin_setting_heading('local_guldap/ldapserversettings',
        new lang_string('serversettings', 'local_guldap'), ''));

    // Host.
    $settings->add(new admin_setting_configtext('local_guldap/host_url',
        new lang_string('hosturl', 'local_guldap'),
        new lang_string('hosturl_help', 'local_guldap'), '', PARAM_RAW_TRIMMED));

    // Version.
    $versions = [
        2 => '2',
        3 => '3',
    ];
    $settings->add(new admin_setting_configselect('local_guldap/ldap_version',
        new lang_string('version', 'local_guldap'),
        new lang_string('version_help', 'local_guldap'), 3, $versions));

    // Start TLS.
    $yesno = [
        new lang_string('no'),
        new lang_string('yes'),
    ];
    $settings->add(new admin_setting_configselect('local_guldap/start_tls',
            new lang_string('start_tls_key', 'auth_ldap'),
            new lang_string('start_tls', 'auth_ldap'), 0 , $yesno));     
            
    // Encoding.
    $settings->add(new admin_setting_configtext('local_guldap/ldapencoding',
            new lang_string('ldapencoding', 'local_guldap'),
            new lang_string('ldapencoding_help', 'local_guldap'), 'utf-8', PARAM_RAW_TRIMMED));

    // Page Size. (Hide if not available).
    $settings->add(new admin_setting_configtext('local_guldap/pagesize',
            new lang_string('pagesize_key', 'auth_ldap'),
            new lang_string('pagesize', 'auth_ldap'), '250', PARAM_INT));

    // Bind settings.
    $settings->add(new admin_setting_heading('local_guldap/ldapbindsettings',
            new lang_string('bindsettings', 'local_guldap'), ''));
            
    // User ID.
    $settings->add(new admin_setting_configtext('local_guldap/bind_dn',
            new lang_string('binddn', 'local_guldap'),
            new lang_string('binddn_help', 'local_guldap'), '', PARAM_RAW_TRIMMED));  
            
    // Password.
    $settings->add(new admin_setting_configpasswordunmask('local_guldap/bind_pw',
            new lang_string('bindpw', 'local_guldap'),
            new lang_string('bindpw_help', 'local_guldap'), ''));

    // User Lookup settings.
    $settings->add(new admin_setting_heading('local_guldap/ldapuserlookup',
            new lang_string('usersettings', 'local_guldap'), ''));

    // User Type.
    $settings->add(new admin_setting_configselect('local_guldap/user_type',
            new lang_string('usertype', 'local_guldap'),
            new lang_string('usertype_help', 'local_guldap'), 'default', ldap_supported_usertypes())); 
            
    // Contexts.
    $settings->add(new auth_ldap_admin_setting_special_contexts_configtext('local_guldap/contexts',
            new lang_string('contexts', 'local_guldap'),
            new lang_string('contexts_help', 'local_guldap'), '', PARAM_RAW_TRIMMED));  
            
    // Search subcontexts.
    $settings->add(new admin_setting_configselect('local_guldap/search_sub',
            new lang_string('searchsub', 'local_guldap'),
            new lang_string('searchsub_help', 'local_guldap'), 0 , $yesno));   
            
    // Dereference aliases.
    $optderef = [
        LDAP_DEREF_NEVER => new lang_string('no'),
        LDAP_DEREF_ALWAYS => new lang_string('yes'),
    ];
    $settings->add(new admin_setting_configselect('local_guldap/opt_deref',
            new lang_string('optderef', 'local_guldap'),
            new lang_string('optderef_help', 'local_guldap'), LDAP_DEREF_NEVER , $optderef));   
            
    // User attribute.
    $settings->add(new auth_ldap_admin_setting_special_lowercase_configtext('local_guldap/user_attribute',
            get_string('userattribute', 'local_guldap'),
            get_string('userattribute_help', 'local_guldap'), '', PARAM_RAW));

    // Mappings.
    $settings->add(new admin_setting_heading('local_guldap/ldapmappings',
            new lang_string('mappings', 'local_guldap'), ''));

    // Firstname
    $settings->add(new admin_setting_configtext('local_guldap/map_firstname',
            new lang_string('firstname', 'local_guldap'),
            new lang_string('firstname_help', 'local_guldap'), '', PARAM_RAW_TRIMMED));    

    // Lastname
    $settings->add(new admin_setting_configtext('local_guldap/map_lastname',
            new lang_string('lastname', 'local_guldap'),
            new lang_string('lastname_help', 'local_guldap'), '', PARAM_RAW_TRIMMED));         
            
    // Email
    $settings->add(new admin_setting_configtext('local_guldap/map_email',
            new lang_string('email', 'local_guldap'),
            new lang_string('email_help', 'local_guldap'), '', PARAM_RAW_TRIMMED));           
            
    // IDNumber
    $settings->add(new admin_setting_configtext('local_guldap/map_idnumber',
        new lang_string('idnumber', 'local_guldap'),
        new lang_string('idnumber_help', 'local_guldap'), '', PARAM_RAW_TRIMMED));    
        
    // HomeEmailAddress
    $settings->add(new admin_setting_configtext('local_guldap/map_homeemailaddress',
        new lang_string('homeemailaddress', 'local_guldap'),
        new lang_string('homeemailaddress_help', 'local_guldap'), '', PARAM_RAW_TRIMMED));         
}