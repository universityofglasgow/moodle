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
 * Execute user download
 *
 * @package    report_gudata
 * @copyright  2020 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_gudata;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/csvlib.class.php');

class userdownload {

    private $course;

    public function __construct($course) {
        $this->course = $course;
    }

    /**
     * Get list of group names for student/course
     * @param int $courseid
     * @param int $userid
     * @return array [groupname]
     */
    private function get_groups($courseid, $userid) {
        global $DB;

        $sql = 'SELECT gm.id, gg.name FROM {groups_members} gm
            JOIN {groups} gg ON gg.id = gm.groupid
            WHERE userid = :userid
            AND courseid = :courseid';
        $groups = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'courseid' => $courseid,
        ]);
        $names = [];
        foreach ($groups as $group) {
            $names[] = $group->name;
        }

        return $names;
    }

    /**
     * Execute the download
     */
    public function execute() {
        global $DB;

        $context = \context_course::instance($this->course->id);
        $fields = 'u.id, u.username, u.firstname, u.lastname, u.email, u.idnumber';
        $users = get_enrolled_users($context, '', 0, $fields);

        // Find groups
        $maxgroup = 0;
        foreach ($users as $user) {
            $user->names = $this->get_groups($this->course->id, $user->id);
            $count = count($user->names);
            $maxgroup = max($count, $maxgroup);
        }

        // Export
        $csv = new \csv_export_writer();
        $csv->set_filename($this->course->shortname);

        // Headers
        $header = [
            'idnumber',
            'username',
            'firstname',
            'lastname',
            'email',
            'course1',
        ];
        for ($i=1; $i<=$maxgroup; $i++) {
            $header[] = 'group' . $i;
        }
        $csv->add_data($header);

        // Data
        foreach ($users as $user) {
            $row = [
                $user->idnumber,
                $user->username,
                $user->firstname,
                $user->lastname,
                $user->email,
                $this->course->shortname,
            ];
            foreach ($user->names as $name) {
                $row[] = $name;
            }
            $csv->add_data($row);
        }

        $csv->download_file();
    }

}