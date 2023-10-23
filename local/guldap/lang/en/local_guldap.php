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
 * @package    local_guldap
 * @copyright  2022 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['binddn'] = 'Distinguished name';
$string['binddn_help'] = 'If you want to use bind-user to search users, specify it here. Something like \'cn=ldapuser,ou=public,o=org\'';
$string['bindpw'] = 'Bind password';
$string['bindpw_help'] = 'Password for bind-user.';
$string['bindsettings'] = 'Bind settings';
$string['closed'] = 'LDAP closed';
$string['connected'] = 'LDAP connected';
$string['contexts'] = 'Contexts';
$string['contexts_help'] = 'List of contexts where users are located. Separate different contexts with \';\'. For example: \'ou=users,o=org; ou=others,o=org\'';
$string['email'] = 'Email';
$string['email_help'] = 'LDAP field name containing email';
$string['firstname'] = 'Firstname';
$string['firstname_help'] = 'LDAP field name containing firstname.';
$string['homeemailaddress'] = 'Home email address';
$string['homeemailaddress_help'] = 'LDAP field name containg home email address';
$string['hosturl'] = 'Host URL';
$string['hosturl_help'] = 'Specify LDAP host in URL-form like \'ldap://ldap.myorg.com/\' or \'ldaps://ldap.myorg.com/\'. Separate multiple servers with \';\' to get failover support.';
$string['idnumber'] = 'ID number';
$string['idnumber_help'] = 'LDAP field name containing ID number / Workforce ID';
$string['lastname'] = 'Lastname';
$string['lastname_help'] = 'LDAP field name containing lastname.';
$string['ldapencoding'] = 'LDAP encoding';
$string['ldapencoding_help'] = 'Encoding used by the LDAP server, most likely utf-8. If LDAP v2 is selected, Active Directory uses its configured encoding, such as cp1252 or cp1250.';
$string['loginhook'] = 'Login hook enabled';
$string['loginhook_help'] = 'Enable user profile processing immediately after login.';
$string['loginsettings'] = 'Login settings';
$string['mappings'] = 'LDAP mappings';
$string['noemail'] = 'You cannot log into Moodle as you do not have an email address. Please contact the Help Desk and ask for an email to be added to your GUID.';
$string['numberofresults'] = 'Number of results returned is {$a}';
$string['optderef'] = 'Dereference aliases?';
$string['optderef_help'] = 'Determines how aliases are handled during search. Select one of the following values: "No" (LDAP_DEREF_NEVER) or "Yes" (LDAP_DEREF_ALWAYS)';
$string['pagesize'] = 'Page size';
$string['pagesize_help'] = 'Make sure this value is smaller than your LDAP server result set size limit (the maximum number of entries that can be returned in a single query)';
$string['pluginname'] = 'UofG LDAP API';
$string['searchsub'] = 'Search subcontexts?';
$string['searchsub_help'] = 'Search users from subcontexts.';
$string['serversettings'] = 'LDAP server settings';
$string['userattribute'] = 'User attribute';
$string['userattribute_help'] = 'Optional: Overrides the attribute used to name/search users. Usually \'cn\'.';
$string['usersettings'] = 'User lookup settings';
$string['usertype'] = 'User type';
$string['usertype_help'] = 'Select how users are stored in LDAP. This setting also specifies how login expiry, grace logins and user creation will work.';
$string['version'] = 'Version';
$string['version_help'] = 'The version of the LDAP protocol your server is using.';

