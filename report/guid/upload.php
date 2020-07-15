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
 * @copyright  2013-19 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/csvlib.class.php');

// Configuration.
$config = report_guid\lib::settings();
$ldaphost = $config->host_url;
$dn = $config->contexts;

// Renderer.
$PAGE->set_context(context_system::instance());
$output = $PAGE->get_renderer('report_guid');
$output->set_guid_config($config);

// Start the page.
admin_externalpage_setup('reportguid', '', null, '', ['pagelayout' => 'report']);
echo $output->header();

echo $output->heading(get_string('heading', 'report_guid'));

// Check we have ldap.
if (!function_exists('ldap_connect')) {
    print_error(get_string('ldapnotloaded', 'report_guid'));
}

// Form definition.
$mform = new report_guid\forms\upload();
if ($mform->is_cancelled()) {
    redirect( 'index.php' );
    die;
} else if ($data = $mform->get_data()) {

    // Get the data from the file.
    $filedata = $mform->get_file_content('csvfile');
    $iid = csv_import_reader::get_new_iid('uploadguid');
    $cir = new csv_import_reader( $iid, 'uploadguid');
    $count = $cir->load_csv_content( $filedata, 'utf8', 'comma' );

    // Check for errors.
    if ($cir->get_error()) {
        $link = new moodle_url( '/report/guid/upload.php' );
        notice( 'Error reading CSV file - ' . $cir->get_error(), $link );
    }

    // Notify line count or error.
    if ($count > 0) {
        echo "<p><strong>" . get_string('numbercsvlines', 'report_guid', $count) . "</strong></p>";
    } else {
        echo $output->notification(get_string('emptycsv', 'report_guid') );
    }

    // Count created.
    $createdcount = 0;
    $errorcount = 0;
    $existscount = 0;

    // Iterate over lines in csv.
    $cir->init();
    while ($line = $cir->next()) {
        // Get the guid from first column.
        foreach ($line as $key => $item) {
            $item = trim( $item, '" ' );
            if ($key == 0) {
                $guid = $item;
            } else {

                // Don't care about rest of line.
                continue;
            }
        }

        // If no guid then carry on.
        if (empty($guid)) {
            continue;
        }

        // Notify...
        echo "<p><strong>'$guid'</strong> ";

        // Try to find or make an account.
        if (!$user = $DB->get_record( 'user', ['username' => strtolower($guid)] )) {

            // Need to find them in ldap.
            $result = report_guid\lib::ldapsearch( $config, "{$config->user_attribute}=$guid" );
            if (empty($result)) {
                echo "<span class=\"label label-warning\">" . get_string('nouser', 'report_guid') . "</span> ";
                $errorcount++;
                continue;
            }

            // Sanity check.
            if (count($result) > 1) {
                echo "<span class=\"label label-warning\">" . get_string('multipleresults', 'report_guid') . "</span>";
                $errorcount++;
                continue;
            }

            // Create account.
            $result = array_shift( $result );
            $user = report_guid\lib::create_user_from_ldap( $result );
            $link = new moodle_url( '/user/view.php', ['id' => $user->id]);
            echo "<span class=\"label label-success\">".
                get_string('accountcreated', 'report_guid', "<a href=\"$link\">" . fullname($user) . "</a>") . "</span>";
            $createdcount++;
        } else {
            $link = new moodle_url('/user/view.php', ['id' => $user->id]);
            echo "<span class=\"label label-warning\">".
                get_string('accountexists', 'report_guid', "<a href=\"$link\">" . fullname($user) . "</a>"). "</span";
            $existscount++;
        }

        echo "</p>";
    }
    echo "<ul class=\"label\">";
    echo "<li><strong>" . get_string('countnewaccounts', 'report_guid', $createdcount) . "</strong></li>";
    echo "<li><strong>" . get_string('countexistingaccounts', 'report_guid', $existscount) . "</strong></li>";
    echo "<li><strong>" . get_string('counterrors', 'report_guid', $errorcount) . "</strong></li>";
    echo "</ul>";
    $link = new moodle_url('/report/guid/index.php');
    echo $output->continue_button($link);
} else {
    $mform->display();
}

echo $output->footer();
