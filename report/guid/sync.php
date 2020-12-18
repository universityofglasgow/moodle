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
 * Sync user
 *
 * @package    report_guid
 * @copyright  2020 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Configuration.
$config = report_guid\lib::settings();

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
$link = new moodle_url('/report/guid/index.php');

// User details.
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

// Start the page.
admin_externalpage_setup('reportguid', '', null, '', ['pagelayout' => 'report']);
echo $output->header();

echo $output->heading(get_string('headingsync', 'report_guid'));

echo "<pre>";
$gudatabase = enrol_get_plugin('gudatabase');
$gudatabaseerror = !$gudatabase->is_configured();
if ($user && !$gudatabaseerror) {
    $gudatabase->process_user_enrolments($user, true);
} else {
    echo "<b>enrol_gudatabase not configured</b>";
}
echo "</pre>";

echo $output->footer();
