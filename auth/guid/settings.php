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
 * @package auth_guid
 * @copyright  2017 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    if (!function_exists('ldap_connect')) {
        $settings->add(new admin_setting_heading('auth_ldap_noextension', '', get_string('auth_ldap_noextension', 'auth_ldap')));
    } else {

        // We use a couple of custom admin settings since we need to massage the data before it is inserted into the DB.
        require_once($CFG->dirroot.'/auth/ldap/classes/admin_setting_special_lowercase_configtext.php');
        require_once($CFG->dirroot.'/auth/ldap/classes/admin_setting_special_contexts_configtext.php');

        // We need to use some of the Moodle LDAP constants / functions to create the list of options.
        require_once($CFG->dirroot.'/auth/guid/auth.php');

        // Introductory explanation.
        $settings->add(new admin_setting_heading('auth_guid/pluginname', '',
                new lang_string('auth_ldapdescription', 'auth_ldap')));

        // LDAP server settings.
        $settings->add(new admin_setting_heading('auth_guid/ldapserversettings',
                new lang_string('auth_ldap_server_settings', 'auth_ldap'), ''));

        // Host.
        $settings->add(new admin_setting_configtext('auth_guid/host_url',
                get_string('auth_ldap_host_url_key', 'auth_ldap'),
                get_string('auth_ldap_host_url', 'auth_ldap'), '', PARAM_RAW_TRIMMED));

        // Version.
        $versions = array();
        $versions[2] = '2';
        $versions[3] = '3';
        $settings->add(new admin_setting_configselect('auth_guid/ldap_version',
                new lang_string('auth_ldap_version_key', 'auth_ldap'),
                new lang_string('auth_ldap_version', 'auth_ldap'), 3, $versions));

        // Start TLS.
        $yesno = array(
            new lang_string('no'),
            new lang_string('yes'),
        );
        $settings->add(new admin_setting_configselect('auth_guid/start_tls',
                new lang_string('start_tls_key', 'auth_ldap'),
                new lang_string('start_tls', 'auth_ldap'), 0 , $yesno));


        // Encoding.
        $settings->add(new admin_setting_configtext('auth_guid/ldapencoding',
                get_string('auth_ldap_ldap_encoding_key', 'auth_ldap'),
                get_string('auth_ldap_ldap_encoding', 'auth_ldap'), 'utf-8', PARAM_RAW_TRIMMED));

        // Page Size. (Hide if not available).
        $settings->add(new admin_setting_configtext('auth_guid/pagesize',
                get_string('pagesize_key', 'auth_ldap'),
                get_string('pagesize', 'auth_ldap'), '250', PARAM_INT));

        // Bind settings.
        $settings->add(new admin_setting_heading('auth_guid/ldapbindsettings',
                new lang_string('auth_ldap_bind_settings', 'auth_ldap'), ''));

        // User ID.
        $settings->add(new admin_setting_configtext('auth_guid/bind_dn',
                get_string('auth_ldap_bind_dn_key', 'auth_ldap'),
                get_string('auth_ldap_bind_dn', 'auth_ldap'), '', PARAM_RAW_TRIMMED));

        // Password.
        $settings->add(new admin_setting_configpasswordunmask('auth_guid/bind_pw',
                get_string('auth_ldap_bind_pw_key', 'auth_ldap'),
                get_string('auth_ldap_bind_pw', 'auth_ldap'), ''));

        // User Lookup settings.
        $settings->add(new admin_setting_heading('auth_guid/ldapuserlookup',
                new lang_string('auth_ldap_user_settings', 'auth_ldap'), ''));

        // User Type.
        $settings->add(new admin_setting_configselect('auth_guid/user_type',
                new lang_string('auth_ldap_user_type_key', 'auth_ldap'),
                new lang_string('auth_ldap_user_type', 'auth_ldap'), 'default', ldap_supported_usertypes()));

        // Contexts.
        $settings->add(new auth_ldap_admin_setting_special_contexts_configtext('auth_guid/contexts',
                get_string('auth_ldap_contexts_key', 'auth_ldap'),
                get_string('auth_ldap_contexts', 'auth_ldap'), '', PARAM_RAW_TRIMMED));

        // Search subcontexts.
        $settings->add(new admin_setting_configselect('auth_guid/search_sub',
                new lang_string('auth_ldap_search_sub_key', 'auth_ldap'),
                new lang_string('auth_ldap_search_sub', 'auth_ldap'), 0 , $yesno));

        // Dereference aliases.
        $optderef = array();
        $optderef[LDAP_DEREF_NEVER] = get_string('no');
        $optderef[LDAP_DEREF_ALWAYS] = get_string('yes');

        $settings->add(new admin_setting_configselect('auth_guid/opt_deref',
                new lang_string('auth_ldap_opt_deref_key', 'auth_ldap'),
                new lang_string('auth_ldap_opt_deref', 'auth_ldap'), LDAP_DEREF_NEVER , $optderef));

        // User attribute.
        $settings->add(new auth_ldap_admin_setting_special_lowercase_configtext('auth_guid/user_attribute',
                get_string('auth_ldap_user_attribute_key', 'auth_ldap'),
                get_string('auth_ldap_user_attribute', 'auth_ldap'), '', PARAM_RAW));

        // Suspended attribute.
        $settings->add(new auth_ldap_admin_setting_special_lowercase_configtext('auth_guid/suspended_attribute',
                get_string('auth_ldap_suspended_attribute_key', 'auth_ldap'),
                get_string('auth_ldap_suspended_attribute', 'auth_ldap'), '', PARAM_RAW));

        // Member attribute.
        $settings->add(new auth_ldap_admin_setting_special_lowercase_configtext('auth_guid/memberattribute',
                get_string('auth_ldap_memberattribute_key', 'auth_ldap'),
                get_string('auth_ldap_memberattribute', 'auth_ldap'), '', PARAM_RAW));

        // Member attribute uses dn.
        $settings->add(new admin_setting_configtext('auth_guid/memberattribute_isdn',
                get_string('auth_ldap_memberattribute_isdn_key', 'auth_ldap'),
                get_string('auth_ldap_memberattribute_isdn', 'auth_ldap'), '', PARAM_RAW));

        // Object class.
        $settings->add(new admin_setting_configtext('auth_guid/objectclass',
                get_string('auth_ldap_objectclass_key', 'auth_ldap'),
                get_string('auth_ldap_objectclass', 'auth_ldap'), '', PARAM_RAW_TRIMMED));

        // Force Password change Header.
        $settings->add(new admin_setting_heading('auth_ldap/ldapforcepasswordchange',
                new lang_string('forcechangepassword', 'auth'), ''));

        // Force Password change.
        $settings->add(new admin_setting_configselect('auth_guid/forcechangepassword',
                new lang_string('forcechangepassword', 'auth'),
                new lang_string('forcechangepasswordfirst_help', 'auth'), 0 , $yesno));

        // Standard Password Change.
        $settings->add(new admin_setting_configselect('auth_guid/stdchangepassword',
                new lang_string('stdchangepassword', 'auth'), new lang_string('stdchangepassword_expl', 'auth') .' '.
                get_string('stdchangepassword_explldap', 'auth'), 0 , $yesno));

        // Password Type.
        $passtype = array();
        $passtype['plaintext'] = get_string('plaintext', 'auth');
        $passtype['md5']       = get_string('md5', 'auth');
        $passtype['sha1']      = get_string('sha1', 'auth');

        $settings->add(new admin_setting_configselect('auth_guid/passtype',
                new lang_string('auth_ldap_passtype_key', 'auth_ldap'),
                new lang_string('auth_ldap_passtype', 'auth_ldap'), 'plaintext', $passtype));

        // Password change URL.
        $settings->add(new admin_setting_configtext('auth_guid/changepasswordurl',
                get_string('auth_ldap_changepasswordurl_key', 'auth_ldap'),
                get_string('changepasswordhelp', 'auth'), '', PARAM_URL));

        // Password Expiration Header.
        $settings->add(new admin_setting_heading('auth_guid/passwordexpire',
                new lang_string('auth_ldap_passwdexpire_settings', 'auth_ldap'), ''));

        // Password Expiration.
        $expiration = array();
        $expiration['0'] = 'no';
        $expiration['1'] = 'LDAP';
        $settings->add(new admin_setting_configselect('auth_guid/expiration',
                new lang_string('auth_ldap_expiration_key', 'auth_ldap'),
                new lang_string('auth_ldap_expiration_desc', 'auth_ldap'), 0 , $expiration));

        // Password Expiration warning.
        $settings->add(new admin_setting_configtext('auth_guid/expiration_warning',
                get_string('auth_ldap_expiration_warning_key', 'auth_ldap'),
                get_string('auth_ldap_expiration_warning_desc', 'auth_ldap'), '', PARAM_RAW));

        // Password Expiration attribute.
        $settings->add(new auth_ldap_admin_setting_special_lowercase_configtext('auth_guid/expireattr',
                get_string('auth_ldap_expireattr_key', 'auth_ldap'),
                get_string('auth_ldap_expireattr_desc', 'auth_ldap'), '', PARAM_RAW));

        // Grace Logins.
        $settings->add(new admin_setting_configselect('auth_guid/gracelogins',
                new lang_string('auth_ldap_gracelogins_key', 'auth_ldap'),
                new lang_string('auth_ldap_gracelogins_desc', 'auth_ldap'), 0 , $yesno));

        // Grace logins attribute.
        $settings->add(new auth_ldap_admin_setting_special_lowercase_configtext('auth_guid/graceattr',
                get_string('auth_ldap_gracelogin_key', 'auth_ldap'),
                get_string('auth_ldap_graceattr_desc', 'auth_ldap'), '', PARAM_RAW));

        // Debug mode Header.
        $settings->add(new admin_setting_heading('auth_guid/guiddebugmode',
                new lang_string('auth_debugmode', 'auth_guid'), ''));
    
        // Debugmode
        $settings->add(new admin_setting_configselect('auth_guid/debugmode',
                new lang_string('auth_debugmode_key', 'auth_guid'),
                new lang_string('auth_guid_debugmode', 'auth_guid'), 0 , $yesno));
    }

    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin($this->name);
    $help  = get_string('auth_ldapextrafields', 'auth_ldap');
    $help .= get_string('auth_updatelocal_expl', 'auth');
    $help .= get_string('auth_fieldlock_expl', 'auth');
    $help .= get_string('auth_updateremote_expl', 'auth');
    $help .= '<hr />';
    $help .= get_string('auth_updateremote_ldap', 'auth');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
            $help, true, true, $authplugin->get_custom_user_profile_fields());
}
