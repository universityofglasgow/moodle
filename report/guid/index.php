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

define('MAXIMUM_RESULTS', 12);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

// Get settings.
$config = report_guid\lib::settings();

// Renderer.
$context = context_system::instance();
$PAGE->set_context($context);
$output = $PAGE->get_renderer('report_guid');
$output->set_guid_config($config);

// Configuration.
$ldaphost = $config->host_url;
$dn = $config->contexts;

// Get paramters.
$firstname = optional_param('firstname', '', PARAM_TEXT);
$lastname = optional_param('lastname', '', PARAM_TEXT);
$email = optional_param('email', '', PARAM_CLEAN);
$guid = optional_param('guid', '', PARAM_ALPHANUM);
$idnumber = optional_param('idnumber', '', PARAM_ALPHANUM);
$action = optional_param('action', '', PARAM_ALPHA);
$resetbutton = optional_param('resetbutton', '', PARAM_ALPHA);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_TEXT);

// Has form been reset?
if ($resetbutton) {
    redirect(new moodle_url('/report/guid'));
}

// Start the page.
admin_externalpage_setup('reportguid', '', null, '', array('pagelayout' => 'report'));
echo $output->header();

echo $output->heading(get_string('heading', 'report_guid'));

// Check we have ldap.
if (!function_exists( 'ldap_connect' )) {
    print_error(get_string('ldapnotloaded', 'report_guid'));
}

// Check for user create.
if (($action == 'create') && confirm_sesskey()) {
    if ($guid) {
        $results = report_guid\lib::filter($output, '', '', $guid, '', '');
        $result = array_shift($results);
        $user = report_guid\lib::create_user_from_ldap($result);
        $link = new moodle_url('/report/guid/index.php', ['guid' => $guid, 'action' => 'more']);
        notice(get_string('usercreated', 'report_guid', fullname($user)), $link);
    }
}

// Check for delete.
if ($delete) {
    require_sesskey();
    require_capability('moodle/user:delete', $context);
    $user = $DB->get_record('user', ['id' => $delete], '*', MUST_EXIST);

    if ($confirm != md5($user->id)) {

        // Confirm message.
        $output->confirmdelete($user);
        echo $output->footer();
        die;
    } else {
        delete_user($user);
        \core\session\manager::gc(); // Remove stale sessions.
        $link = new moodle_url('/report/guid/index.php');
        notice(get_string('deleted', 'report_guid', fullname($user, true)), $link);
    }
}

// Was 'more' button pressed?
if ($guid && ($action == 'more')) {
    $results = report_guid\lib::filter($output, '', '', $guid, '', '');
    $result = array_shift($results);
    $single = new report_guid\output\single($config, $result);
    echo $output->render_single($single);

    echo $output->footer();
    die;
}

// Url for errors and stuff.
$linkback = new moodle_url( '/report/guid/index.php' );

// Form.
$mform = new \report_guid\forms\filter(null, null, 'get');
$mform->display();

// Link to upload script.
$output->mainlinks();

if ($mform->is_cancelled()) {
    redirect( "index.php" );
} else if ($data = $mform->get_data()) {
    $result = report_guid\lib::filter($output, $data->firstname, $data->lastname, $data->guid, $data->email, $data->idnumber);
    $users = report_guid\lib::user_search($data->firstname, $data->lastname, $data->guid, $data->email, $data->idnumber);
    report_guid\lib::add_enrol_counts($users);

    // Display ldap search results.
    if (($action == 'more') && (count($result) == 1)) {
        $result = array_shift($results);
        $output->single_ldap($result);
    } else {
        //$output->ldap_results($result);
        //$output->user_results($users);
        $ldaplist = new report_guid\output\ldaplist($config, $result, $users);
        echo $output->render_ldaplist($ldaplist);
    }
}

echo $output->footer();

