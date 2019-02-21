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
 * @copyright  2017 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
//require_once($CFG->libdir . '/formslib.php');

// Configuration.
$config = report_guid_search::settings();

// Renderer.
$context = context_system::instance();
$PAGE->set_context($context);
$output = $PAGE->get_renderer('report_guid');
$output->set_guid_config($config);

// Parameters.
$userid = required_param('userid', PARAM_INT);

// Security.
require_login();
require_sesskey();
require_capability('moodle/user:update', $context);

// User details.
$user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

// Start the page.
admin_externalpage_setup('reportguid', '', null, '', array('pagelayout' => 'report'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('headingupdate', 'report_guid'));

// Form definition.
$mform = new \report_guid\forms\update(null, array('user' => $user));

if ($mform->is_cancelled()) {
    redirect( 'index.php' );
    die;
} else if ($data = $mform->get_data()) {
    $newusername = $data->newusername;

    // Check for duplicate.
    if ($users = report_guid_search::isduplicate($newusername)) {
        $output->duplicates($users);
    } else {
        $user->username = $newusername;
        $DB->update_record('user', $user);

        $output->userupdate_confirm($user);
    }
    $output->continue_button();
} else {
    $mform->display();
}

echo $OUTPUT->footer();
