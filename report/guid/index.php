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
 * GUID report
 *
 * @package    report_guid
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once(dirname(__FILE__).'/lib.php');

require_login();

// Configuration.
$config = report_guid_settings();
$ldaphost = $config->host_url;
$dn = $config->contexts;

// Get paramters.
$firstname = optional_param('firstname', '', PARAM_TEXT);
$lastname = optional_param('lastname', '', PARAM_TEXT);
$email = optional_param('email', '', PARAM_CLEAN);
$guid = optional_param('guid', '', PARAM_ALPHANUM);
$idnumber = optional_param('idnumber', '', PARAM_ALPHANUM);
$action = optional_param('action', '', PARAM_ALPHA);


// Start the page.
admin_externalpage_setup('reportguid', '', null, '', array('pagelayout' => 'report'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('heading', 'report_guid'));

// Check we have ldap.
if (!function_exists( 'ldap_connect' )) {
    error(get_string('ldapnotloaded', 'report_guid'));
}

// Check for user create.
if (($action == 'create') and confirm_sesskey()) {
    if (!empty($USER->report_guid_ldap)) {
        $result = $USER->report_guid_ldap;
        if ($guid == $result[$config->user_attribute]) {
            $user = report_guid_create_user_from_ldap($result);
            notice(get_string('usercreated', 'report_guid', fullname($user)));
        }
    }
}

// Url for errors and stuff.
$linkback = new moodle_url( '/report/guid/index.php' );

// Form.
$mform = new guidreport_form(null, null, 'get');
$mform->display();

// Link to upload script.
echo "<p><a class=\"btn btn-primary\" href=\"{$CFG->wwwroot}/report/guid/upload.php\">".get_string('uploadguid', 'report_guid')."</a></p>";

if ($mform->is_cancelled()) {
    redirect( "index.php" );
} else if ($data = $mform->get_data()) {
    if (!$filter = report_guid_build_filter($data->firstname, $data->lastname, $data->guid, $data->email, $data->idnumber)) {
        notice(get_string('filtererror', 'report_guid'), $linkback );
        echo $OUTPUT->footer();
        die;
    }
    $result = report_guid_ldapsearch( $config, $filter );
    if (is_string( $result )) {
        notice(get_string('searcherror', 'report_guid', $result), $linkback );
        die;
    }
    if ($result === false) {
        echo '<p class="alert alert-error">' . get_string('ldapsearcherror', 'report_guid') . '</p>\n';
        echo $OUTPUT->footer();
        die;
    }
    // Build url for paging.
    $url = new moodle_url($CFG->wwwroot.'/report/guid/index.php',
        array(
            'firstname' => $data->firstname,
            'lastname' => $data->lastname,
            'email' => $data->email,
            'idnumber' => $data->idnumber,
            'guid' => $data->guid,
            'submitbutton' => $data->submitbutton,
            '_qf__guidreport_form' => 1,
        ));
    report_guid_print_results( $result, $url );
}

echo $OUTPUT->footer();

