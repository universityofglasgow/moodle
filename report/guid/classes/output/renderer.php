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
 * Version details.
 *
 * @package    report
 * @subpackage guid
 * @copyright  2012 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define('MAXIMUM_RESULTS', 8);

class report_guid_renderer extends plugin_renderer_base {

    protected $config;

    /**
     * Set the config settings
     * @param object $config
     */
    public function set_guid_config($config) {
        $this->config = $config;
    }

    /**
     * Display links on main page
     */
    public function mainlinks() {
        $uploadlink = new moodle_url('/report/guid/upload.php');
        echo '<div class="form-group">';
        echo '    <a class="btn btn-primary" href="' . $uploadlink . '">' . get_string('uploadguid', 'report_guid') . '</a>';
        echo '</div>';
    }

    /**
     * Print the list of LDAP results
     * We only print if there's a few as a big list is pointless
     * @param array $results
     */
    public function ldap_results($results) {
        global $CFG, $DB;

        $config = report_guid_search::settings();

        echo '<h3>' . get_string('ldapresults', 'report_guid') . '</h3>';

        // Check there are some.
        if (empty($results)) {
            echo '<div class="alert alert-warning">' . get_string('noresults', 'report_guid') . '</div>';
            return;
        }

        // Check for too many.
        if (count($results) > MAXIMUM_RESULTS) {
            echo '<div class="alert alert-warning">' . get_string('toomanyldap', 'report_guid') . '</div>';
            return;
        }

        // Note any external email addresses.
        $externalmail = false;

        // Add dn into data.
        foreach ($results as $dn => $result) {
            $results[$dn]['dn'] = $dn;
        }

        $table = new html_table();
        $table->head = array(
            get_string( 'username' ),
            get_string( 'firstname' ),
            get_string( 'lastname' ),
            get_string( 'email' ),
            ' ',
            );
        foreach ($results as $cn => $result) {
            $guid = $result[$config->user_attribute];

            // Check that this isn't an array (it shouldn't be).
            if (is_array($guid)) {
                $guid = report_guid_search::array_to_guid($guid);
            }

            $mailinfo = report_guid_search::get_email( $result );
            $mail = $mailinfo['mail'];
            if (!$mailinfo['primary']) {
                $mail = "<i>$mail</i>";
                $externalmail = true;
            }
            if ($user = $DB->get_record('user', array('username' => strtolower($guid)))) {
                $userlink = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => 1));
                $username = '<a class="btn btn-success" href="' . $userlink . '">' . $guid . '</a>';
            } else {
                $username = $guid;
            }
            if ($username) {
                $link = new moodle_url('/report/guid/index.php', array('guid' => $guid, 'action' => 'more'));
                $createbutton = '<a class="btn btn-primary" href="' . $link->out(true, array('sesskey' => sesskey())) . '">'.
                        get_string('more', 'report_guid') . '</a>';
                if (!$user) {
                    $createlink = new moodle_url('/report/guid/index.php', array('action' => 'create', 'guid' => $guid, 'sesskey' => sesskey()));
                    $createbutton .= ' <a class="btn btn-info" href="' . $createlink->out(true, array('sesskey' => sesskey())) . '">' .
                        get_string('createbutton', 'report_guid') . '</a>';
                }
                $table->data[] = array(
                    $username,
                    $result[$config->field_map_firstname],
                    $result[$config->field_map_lastname],
                    $mail,
                    $createbutton
                );
            }
        }
        echo html_writer::table($table);
        echo '<div class="alert alert-success">' . get_string('numberofresults', 'report_guid', count($results)) . '</div>';

        // If external emails - add note.
        if ($externalmail) {
            echo '<div class="alert alert-warning">' . get_string( 'externalmail', 'report_guid' ) . '</div>';
        }
    }

    /**
     * Print single, detailed result
     * @param object $result
     */
    public function single_ldap($result) {
        global $OUTPUT, $CFG, $USER, $DB;

        $config = report_guid_search::settings();

        $fullname = ucwords(strtolower($result['givenname'].' '.$result['sn']));

        // Do they have an email.
        $mailinfo = report_guid_search::get_email($result);

        // Do they have a moodle account?
        $username = $result[$config->user_attribute];
        if (is_array($username)) {
            $username = report_guid_search::array_to_guid($username);
        }
        if ($user = $DB->get_record( 'user', array('username' => strtolower($username)) )) {
            $userlink = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => 1));
            $displayname = '<a href="' . $userlink . '">' . $fullname . '</a>';
            $create = '';
        } else {
            $displayname = $fullname;
            $createlink = new moodle_url('/report/guid/index.php',
                array('action' => 'create', 'guid' => $username, 'sesskey' => sesskey()));
            if (!empty( $mailinfo['mail'] )) {
                $create = '<a class="btn btn-primary" href="' . $createlink . '" >' . get_string('create', 'report_guid')."</a>";
            } else {
                $create = '<i>' . get_string('noemail', 'report_guid') . '</i>';
            }

            // Save the record in case we want to create the user.
            $USER->report_guid_ldap = $result;
        }
        if (!empty($user)) {
            echo $OUTPUT->user_picture( $user, array('size' => 100) );
        }
        echo '<div class="alert alert-info">' . get_string( 'resultfor', 'report_guid') .
            ' ' .  $displayname . ' ' . $create . ' (' . $username . ')</div>';
        report_guid_search::array_prettyprint( $result );

        // If we have a $user object, synchronise their enrolments.
        if ($user) {
            $gudatabase = enrol_get_plugin('gudatabase');
            if ($gudatabase->is_configured()) {
                $gudatabase->sync_user_enrolments($user);
            } else {
                echo '<div class="alert alert-danger">' . get_string('nogudatabase', 'report_guid') . '</div>';
            }
        }

        // Check for entries in enrollments.
        $enrolments = report_guid_search::get_all_enrolments( $username );
        if (!empty($enrolments)) {
            report_guid_search::print_enrolments( $enrolments, $fullname, $username );
        } else if ($enrolments === false) {
            echo '<div class="alert alert-danger">' . get_string('noguenrol', 'report_guid') . '</div>';
        } else {
            echo '<div class="alert alert-warning">' . get_string('noenrolments', 'report_guid') . '</div>';
        }

        if ($enrolments !== false) {

            // Find mycampus enrolment data.
            $gudatabase = enrol_get_plugin('gudatabase');
            if (!$gudatabase->is_configured()) {
                echo '<div class="alert alert-danger">' . get_string('nogudatabase', 'report_guid') . '</div>';
            } else {
                $courses = $gudatabase->get_user_courses( $username );
                if (!empty($courses)) {
                    report_guid_search::print_mycampus($courses, $username);
                } else {
                    echo '<div class="alert alert-warning">' . get_string('nomycampus', 'report_guid') . '</div>';
                }
            }
        }
    }

    /**
     * Display ldap error and footer
     * @param string $message
     */
    public function ldap_error($message) {
        global $OUTPUT;

        echo '<div class="alert alert-danger">' . $message . '</div>';
        echo $OUTPUT->footer();
    }

    /**
     * Print the list of (interna) user results
     * We only print if there's a few as a big list is pointless
     * @param array $results
     */
    public function user_results($users) {
        global $CFG, $DB;

        $config = report_guid_search::settings();
        $context = context_system::instance();

        echo '<h3>' . get_string('userresults', 'report_guid') . '</h3>';

        // Check there are some.
        if (empty($users)) {
            echo '<div class="alert alert-warning">' . get_string('nouserresults', 'report_guid') . '</div>';
            return;
        }

        // Check for too many.
        if (count($users) > MAXIMUM_RESULTS) {
            echo '<div class="alert alert-warning">' . get_string('toomanyuser', 'report_guid') . '</div>';
            return;
        }

        $table = new html_table();
        $table->head = array(
            get_string('username'),
            get_string('authentication'),
            get_string('firstname'),
            get_string('lastname'),
            get_string('email'),
            get_string('enrolledcourses', 'report_guid'),
            get_string('lastlogin'),
            ' ',
            );
        foreach ($users as $userid => $user) {

            $buttons = '';
            if (has_capability('moodle/user:delete', $context)) {
                $link = new moodle_url('/report/guid/index.php', array('delete' => $userid, 'sesskey' => sesskey()));
                $buttons .= '<a class="btn btn-danger" href="' . $link . '">' . get_string('delete') . '</a>';
            }
            if (has_capability('moodle/user:update', $context)) {
                $link = new moodle_url('/report/guid/userupdate.php', array('userid' => $userid, 'sesskey' => sesskey()));
                $buttons .= '<a class="btn btn-warning" href="' .
                    $link . '">' . get_string('changeusername', 'report_guid') . '</a>';
            }

            $userlink = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => 1));
            $username = '<a class="btn btn-success" href="' . $userlink . '">' . $user->username . '</a>';
            $table->data[] = array(
                $username,
                $user->auth,
                $user->firstname,
                $user->lastname,
                $user->email,
                $user->enrolcount,
                $user->lastlogin ? userdate($user->lastlogin) : get_string('never'),
                $buttons,
                );
        }
        echo html_writer::table($table);
        echo '<div class="alert alert-success">' . get_string('numberofresults', 'report_guid', count($users)) . '</div>';
    }

    /**
     * Continue, back to main form
     */
    public function continue_button() {
        $link = new moodle_url('/report/guid/index.php');
        echo '<div>';
        echo '    <a class="btn btn-info" href="' . $link . '">' . get_string('continue') . '</a>';
        echo '</div>';
    }

    /**
     * User update confirmation
     * @param object $user
     */
    public function userupdate_confirm($user) {
        echo '<div class="alert alert-success">';
        echo get_string('updatesuccess', 'report_guid', fullname($user));
        echo '</div>';
    }

    /**
     * Display list of duplicate usernames
     * @param array $users
     */
    public function duplicates($users) {
        echo '<div class="alert alert-warning">';
        echo get_string('duplicateusers', 'report_guid');
        echo '<ul>';
        foreach ($users as $user) {
            $link = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => 1));
            echo '<li><a href="' . $link . '">' . fullname($user) . '</a></li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    /**
     * Confirm deletion of user
     * @param object $user
     */
    public function confirmdelete($user) {
        global $OUTPUT;

        $fullname = fullname($user, true);

        $optionsyes = array('delete' => $user->id, 'confirm' => md5($user->id), 'sesskey' => sesskey());
        $deleteurl = new moodle_url('/report/guid/index.php', $optionsyes);
        $cancelurl = new moodle_url('/report/guid/index.php');
        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deletecheckfull', '', "'$fullname'"), $deletebutton, $cancelurl);
    }

    /**
     * Deleted message
     * @param object $user
     */
    public function deleted($user) {
        $fullname = fullname($user, true);
        echo '<div class="alert alert-success">';
        echo get_string('deleted', 'report_guid', $fullname);
        echo '</div>';
    }

    /**
     * Display status note for course upload output
     * @param string $message language string
     * @param string $class Bootstrap alert/label class suffix (e.g., info, warning)
     * @param boolean $eol add end of line characters
     * @param string $a get_string extra
     */
    public function courseuploadnote($message, $class, $eol=false, $a = '') {
        $str = get_string($message, 'report_guid', $a);
        echo '&nbsp;<span class="label label-' . $class . '">' . $str . '</span>';
        if ($eol) {
            echo "<br />";
        }
    }
}
