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
 * Main class for course listing
 *
 * @package    report_guid
 * @copyright  2019 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guid\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;
use context;
use context_course;
use moodle_url;

/**
 * Class contains data for report_enhance single user report
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class single implements renderable, templatable {

    protected $ldapconfigured;

    protected $username;

    protected $result;

    public function __construct($ldapconfigured, $username, $result) {
        $this->ldapconfigured = $ldapconfigured;
        $this->username = $username;
        $this->result = $result;
    }

    /**
     * Get all the info about this user
     * @param object $output
     * @param object $result
     */
    public function get_user_data($output, $result) {
        global $DB;
        global $USER;

        if ($this->ldapconfigured && $result) {
            $config = \report_guid\lib::settings();

            $fullname = ucwords(strtolower($result['givenname'] . ' ' . $result['sn']));

            // Student?
            $dn = $result['dn'];
            $isstudent = strpos($dn, 'ou=student') !== false;

            // Do they have an email.
            $mailinfo = \report_guid\lib::get_email($result);
            $noemail = empty($mailinfo['mail']);

            // Do they have a moodle account?
            $createlink = '';
            $ua = $config->user_attribute;
            $username = $result[$ua];
            if (is_array($username)) {
                $username = \report_guid\lib::array_to_guid($username);
            }
            if ($user = $DB->get_record('user', ['username' => strtolower($username)])) {
                $userlink = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => 1]);
                $displayname = '<a href="' . $userlink . '">' . $fullname . '</a>';
                $create = '';
            } else {

                // Set the link to create the user in Moodle.
                $displayname = $fullname;
                if (!empty( $mailinfo['mail'] )) {
                    $createlink = new moodle_url('/report/guid/index.php', ['action' => 'create', 'guid' => $username, 'sesskey' => sesskey()]);
                }

                // Save the record in case we want to create the user.
                $USER->report_guid_ldap = $result;
            }
        } else {
            $user = $DB->get_record('user', ['username' => strtolower($this->username)], '*', MUST_EXIST);
            $fullname = fullname($user);
            $userlink = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => 1]);
            $displayname = '<a href="' . $userlink . '">' . $fullname . '</a>';
            $create = '';
            $createlink = '';
            $isstudent = strpos($user->email, 'student') !== false;
            $noemail = false;
        }

        // Is there a user picture?
        if (!empty($user)) {
            $picture = $output->user_picture( $user, array('size' => 100) );
        } else {
            $picture = null;
        }

        // If we have a $user object, synchronise their enrolments.
        $gudatabase = enrol_get_plugin('gudatabase');
        $gudatabaseerror = !$gudatabase->is_configured();
        if ($user && !$gudatabaseerror) {
            $gudatabase->sync_user_enrolments($user);
        }

        // Check for entries in enrollments.
        if ($user) {
            $enrolments = \report_guid\lib::get_all_enrolments($user);
            $noenrolments = empty($enrolments);
            $formattedenrolments = \report_guid\lib::format_enrolments($user->id, $enrolments);
        } else {
            $formattedenrolments = [];
            $noenrolments = true;
        }

        // Find mycampus enrolment data.
        if (!$gudatabaseerror) {
            $courses = $gudatabase->get_user_courses($user->username);
            $formattedcourses = \report_guid\lib::format_mycampus($courses, $username, $enrolments);
        } else {
            $formattedcourses = [];
        }

        // Find CoreHR data.
        if (!$isstudent) {
            $corehr = \local_corehr\api::get_extract($user->username);
            $iscorehr = $corehr !== false;        
        } else {
            $corehr = null;
            $iscorehr = null;
        }

        // Reformat time
        if ($iscorehr) {
            $corehr->timemodified = date('r', $corehr->timemodified);
        }

        // Get turnitin results
        if ($user) {
            $tiifiles = \report_guid\lib::get_turnitin($user->id, $user->username);
        } else {
            $tiifiles = [];
        }

        // TII Eula.
        if ($user) {
            $tiieula = \report_guid\lib::get_tii_eula($user->id);
        } else {
            $tiieula = false;
        }

        // Get CoreHR completion
        if ($user) {
            $corehrcompletion = \report_guid\lib::get_corehr_completion($user->id);
        } else {
            $corehrcompletion = [];
        }

        // Portal courses
        if ($user) {
            $portalcourses = \report_guid\lib::get_portal_courses($user->username);
        } else {
            $portalcourses = [];
        }

        return [
            'ldapconfigured' => $this->ldapconfigured,
            'fullname' => $fullname,
            'displayname' => $displayname,
            'picture' => $picture,
            'createlink' => $createlink,
            'noemail' => $noemail,
            'formattedldap' => \report_guid\lib::array_prettyprint($result),
            'gudatabaseerror' => $gudatabaseerror,
            'noenrolments' => $noenrolments,
            'formattedenrolments' => $formattedenrolments,
            'nocourses' => empty($courses),
            'formattedcourses' => $formattedcourses,
            'isstudent' => $isstudent,
            'iscorehr' => $iscorehr,
            'corehr' => \report_guid\lib::array_prettyprint((array)$corehr),
            'notiifiles' => empty($tiifiles),
            'tiifiles' => $tiifiles,
            'tiieula' => $tiieula,
            'corehrcompletion' => $corehrcompletion,
            'nocorehrcompletion' => empty($corehrcompletion),
            'portalcourses' => $portalcourses,
            'noportalcourses' => empty($portalcourses),
            'backlink' => new \moodle_url('/report/guid'),
        ];
    }

    public function export_for_template(renderer_base $output) {
        $userdata = $this->get_user_data($output, $this->result);
        return $userdata;
    }

}
