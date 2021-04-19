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
 * @copyright  2017-19 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once(dirname(__FILE__) . '/classes/parsecsv.lib.php');
require_once($CFG->dirroot . '/group/lib.php');

// Configuration.
$config = report_guid\lib::settings();

// Parameters.
$courseid = required_param('id', PARAM_INT);

// Security.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);
require_login($course);
require_capability('report/guid:courseupload', $context);

// Renderer.
$PAGE->set_context($context);
$output = $PAGE->get_renderer('report_guid');
$output->set_guid_config($config);
$url = new moodle_url('/report/guid/courseupload.php', ['id' => $courseid]);
$PAGE->set_url($url);

// Start the page.
$PAGE->set_title($course->shortname .': '. get_string('courseupload', 'report_guid'));
$PAGE->set_heading($course->fullname);
echo $output->header();

echo $output->heading(get_string('headingcourseupload', 'report_guid'));

// Get the list of roles current user may assign.
$roles = get_assignable_roles($context);
$studentrole = report_guid\lib::getstudentrole();

// Form definition.
$mform = new report_guid\forms\courseupload(null, [
    'id' => $courseid,
    'roles' => $roles,
    'studentroleid' => $studentrole ? $studentrole->id : 1,
]);
if ($mform->is_cancelled()) {
    redirect($url);
    die;
} else if ($data = $mform->get_data()) {

    // Upload settings.
    $roleid = $data->role;
    $firstcolumn = $data->firstcolumn;
    $addgroups = $data->addgroups;
    $action = $data->action;

    // Get the data from the file.
    $filedata = $mform->get_file_content('csvfile');
    $csv = new ParseCSV($filedata);

    // Check for errors.
    if ($csv->error > 1) {
        print_error('csverror', 'report_guid');
    }

    // Notify line count or error.
    $count = count($csv->data);
    if ($count > 0) {
        echo "<p><strong>".get_string('numbercsvlines', 'report_guid', $count)."</strong></p>";
    } else {
        echo $output->notification( get_string('emptycsv', 'report_guid') );
    }

    // Count created.
    $createdcount = 0;
    $errorcount = 0;
    $existscount = 0;
    $unenrolcount = 0;

    // Configuration.
    if (!$config = report_guid\lib::settings()) {
        notice('GUID enrol plugin is not configured');
    }

    // Iterate over lines in csv.
    foreach ($csv->data as $line) {

        // Get the username/guid from the first column,
        // and groups from any additional.
        $groups = [];
        $count = 0;
        foreach ($line as $item) {
            $item = trim( $item, '" ' );
            if ($count == 0) {
                $usermatch = $item;
            } else {
                $groups[] = $item;
            }
            $count++;
        }

        // If nothing in first column, possibly blank line?
        // Anyway, nothing else to be done.
        if (empty($usermatch)) {
            continue;
        }

        // Notify...
        echo "<p><strong>'$usermatch'</strong> ";

        // Attempt to find user
        $user = report_guid\lib::findmoodleuser($usermatch, $firstcolumn);

        // If action=unenrol then we'll remove them if they exist
        if ($action == 'unenrol') {
            if ($user) {
                $instances = $DB->get_records('enrol', ['courseid' => $courseid]);
                foreach ($instances as $instance) {
                    $plugin = enrol_get_plugin($instance->enrol);
                    $plugin->unenrol_user($instance, $user->id);
                }
                $output->courseuploadnote('unenrolled', 'info', true);
                $unenrolcount++;
            } else {
                $output->courseuploadnote('usernotfound', 'error', true);
                $errorcount++;
            }
            continue;
        }

        // Try to create user if they don't exist
        if (!$user) {

            // If they don't already exist then find in LDAP.
            if ($firstcolumn == 'guid') {
                $ldap = report_guid\lib::filter($output, '', '', $usermatch, '', '');
            } else {
                $ldap = report_guid\lib::filter($output, '', '', '', '', $usermatch);
            }

            if (!$ldap) {
                $output->courseuploadnote('usernotfound', 'error', true);
                $errorcount++;
                continue;
            }

            // Sanity check.
            if (count($ldap) > 1) {
                $output->courseuploadnote('multipleresults', 'error', true);
                $errorcount++;
                continue;
            }

            // Create the user profile from ldap if needed.
            $ldapuser = reset($ldap);
            $user = report_guid\lib::create_user_from_ldap($ldapuser);

            $output->courseuploadnote('userprofilecreated', 'success');
            $createdcount++;
        } else {
            $output->courseuploadnote('userexists', 'success');
            $existscount++;
        }

        // Enrol the user in the course (with the specified role)
        // Check user is permitted to assign this role too!
        $roleid = $data->role;
        if (array_key_exists($roleid, $roles)) {
            if (enrol_try_internal_enrol($courseid, $user->id, $roleid)) {
                $output->courseuploadnote('userenrolled', 'success');
            } else {
                $output->courseuploadnote('usernotenrolled', 'warning');
                continue;
            }
        }

        // Any remaining items on the line will be groups (if enabled).
        if ($groups && $addgroups) {
            foreach ($groups as $groupname) {
                $groupid = report_guid\lib::create_group($groupname, $courseid);
                if (groups_add_member($groupid, $user->id)) {
                    $output->courseuploadnote('groupadded', 'info', false, $groupname);
                } else {
                    $output->courseuploadnote('groupnotadded', 'warning', false, $groupname);
                }
            }
        }

        echo "</p>";
    }
    echo "<ul class=\"label\">";
    echo "<li><strong>".get_string('countnewaccounts', 'report_guid', $createdcount)."</strong></li>";
    echo "<li><strong>".get_string('countexistingaccounts', 'report_guid', $existscount)."</strong></li>";
    echo "<li><strong>".get_string('counterrors', 'report_guid', $errorcount)."</strong></li>";
    if ($action == 'unenrol') {
        echo "<li><strong>".get_string('countunenrol', 'report_guid', $errorcount)."</strong></li>";
    }
    echo "</ul>";

    $link = new moodle_url('/course/view.php', ['id' => $courseid]);
    echo $output->continue_button($link);
} else {
    $mform->display();
}

echo $output->footer();
