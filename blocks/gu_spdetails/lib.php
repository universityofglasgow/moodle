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
 * Lang strings for the UofG Assessments Details block.
 *
 * @package    block_gu_spdetails
 * @copyright  2020 Accenture
 * @author     Jose Maria C. Abreu <jose.maria.abreu@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class block_gu_spdetails_lib {
    
    public static function get_all_users_courses_gradable_activities($userid){
        global $DB;

        $params = array('siteid' => SITEID, 'userid' => $userid, 'contextlevel' => CONTEXT_COURSE,
                        'active' => ENROL_USER_ACTIVE, 'enabled' => ENROL_INSTANCE_ENABLED, 'gradetype' => GRADE_TYPE_NONE);

        $fields = array('id as courseid', 'category', 'sortorder',
                    'shortname', 'fullname', 'idnumber',
                    'startdate', 'enddate', 'visible',
                    'defaultgroupingid',
                    'groupmode', 'groupmodeforce');

        $coursefields = 'c.' .join(',c.', $fields);
        $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $gradegetselect = ", cm.*, md.name as modname";
        $gradegetjoin = "JOIN {course_modules} cm ON (cm.course = c.id)
                         JOIN {modules} md ON (md.id = cm.module)
                         JOIN {grade_items} gi ON (gi.iteminstance = cm.instance AND gi.courseid = c.id AND gi.itemmodule = md.name)";
        $gradegetwhere = "AND gi.itemtype = 'mod'
                          AND gi.itemnumber = 0
                          AND gi.gradetype != :gradetype";

        $sql = "SELECT cm.id, $coursefields $ccselect $gradegetselect
                    FROM {course} c
                    $gradegetjoin
                    JOIN (SELECT DISTINCT e.courseid
                            FROM {enrol} e
                            JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                    $subwhere
                        ) en ON (en.courseid = c.id)
                $ccjoin
                WHERE c.id <> :siteid
                $gradegetwhere";

        return $DB->get_records_sql($sql, $params);
    }
}