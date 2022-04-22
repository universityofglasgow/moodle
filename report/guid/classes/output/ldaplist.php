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

/**
 * Class contains data for report_enhance elist
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ldaplist implements renderable, templatable {

    protected $config;

    protected $results;

    protected $users;

    /**
     * Constructor
     * @param array $results ldap search results
     * @param array $users existing Moodle users
     */
    public function __construct($config, $results, $users) {
        $this->config = $config;
        $this->results = $results;
        $this->users = $users;
    }

    /**
     * Format ldap data for display
     * @param array $results
     * @return array formatted data
     */
    protected function format_results($results) {
        global $DB;

        $formatted = [];
        foreach ($results as $cn => $result) {
            $guid = $result[$this->config->user_attribute];

            // Check that this isn't an array (it shouldn't be).
            if (is_array($guid)) {
                $guid = \report_guid\lib::array_to_guid($guid);
            }

            $mailinfo = \report_guid\lib::get_email($result);
            $mail = $mailinfo['mail'];
            if (!$mailinfo['primary']) {
                $mail = "<i>$mail</i>";
                $externalmail = true;
            }
            if ($user = $DB->get_record('user', ['username' => strtolower($guid)])) {
                $userlink = new \moodle_url('/user/view.php', ['id' => $user->id, 'course' => 1]);
                $username = '<a class="btn btn-success" href="' . $userlink . '">' . $guid . '</a>';
            } else {
                $username = $guid;
            }
            if ($username) {
                $link = new \moodle_url('/report/guid/index.php', ['guid' => $guid, 'action' => 'more']);
                $createbutton = '<a class="btn btn-primary" href="' . $link->out(true) . '">'.
                        get_string('more', 'report_guid') . '</a>';
                if (!$user) {
                    $createlink = new \moodle_url('/report/guid/index.php', ['action' => 'create', 'guid' => $guid, 'sesskey' => sesskey()]);
                    $createbutton .= ' <a class="btn btn-info" href="' . $createlink->out(true, ['sesskey' => sesskey()]) . '">' .
                        get_string('createbutton', 'report_guid') . '</a>';
                }
                $formatted[] = (object)[
                    'username' => $username,
                    'firstname' => $result[$this->config->field_map_firstname],
                    'lastname' => $result[$this->config->field_map_lastname],
                    'mail' => $mail,
                    'buttons' => $createbutton,
                ];
            }
        }

        return $formatted;
    }

    /**
     * Format list of existing Moodle users
     * @param array $users
     * @return array formatted list
     */
    public function format_users($users) {
        global $DB;

        $context = \context_system::instance();

        $formatted = [];
        foreach ($users as $userid => $user) {

            $buttons = '';
            if (has_capability('moodle/user:delete', $context)) {
                $link = new \moodle_url('/report/guid/index.php', ['delete' => $userid, 'sesskey' => sesskey()]);
                $buttons .= '<a class="btn btn-danger" href="' . $link . '">' . get_string('delete', 'report_guid') . '</a> ';
            }
            if (has_capability('moodle/user:update', $context)) {
                $link = new \moodle_url('/report/guid/userupdate.php', ['userid' => $userid, 'sesskey' => sesskey()]);
                $buttons .= '<a class="btn btn-warning" href="' .
                    $link . '">' . get_string('changeusername', 'report_guid') . '</a> ';
            }
            $link = new \moodle_url('/report/guid/sync.php', ['userid' => $userid, 'sesskey' => sesskey()]);
            $buttons .= '<a class="btn btn-success" href="' .
                $link . '">' . get_string('syncuser', 'report_guid') . '</a> ';

            $userlink = new \moodle_url('/user/view.php', ['id' => $user->id, 'course' => 1]);
            $username = '<a class="btn btn-success" href="' . $userlink . '">' . $user->username . '</a>';
            $formatted[] = (object)[
                'username' => $username,
                'auth' => $user->auth,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'enrolcount' => $user->enrolcount,
                'lastlogin' => $user->lastlogin ? userdate($user->lastlogin) : get_string('never'),
                'buttons' => $buttons,
            ];
        }

        return $formatted;
    }

    public function export_for_template(renderer_base $output) {
        return [
            'ldapresultsempty' => empty($this->results),
            'toomanyldapresults' => count($this->results) > MAXIMUM_RESULTS,
            'results' => $this->format_results($this->results),
            'resultcount' => count($this->results),
            'users' => $this->format_users($this->users),
            'toomanyuserresults' => count($this->users) > MAXIMUM_RESULTS,
            'userresultsempty' => empty($this->users),
            'usercount' => count($this->users),
        ];
    }
}