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
 * Execute logs download
 *
 * @package    report_gudata
 * @copyright  2020 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_gudata;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/csvlib.class.php');

class logsdownload {

    private $course;

    private $data;

    private $contexts = [];

    private $modinfo;

    public function __construct($course) {
        $this->course = $course;

        $contextvalues = [
            CONTEXT_SYSTEM,
            CONTEXT_USER,
            CONTEXT_COURSECAT,
            CONTEXT_COURSE,
            CONTEXT_MODULE,
            CONTEXT_BLOCK,
        ];
        foreach($contextvalues as $contextvalue) {
            $this->contexts[$contextvalue] = get_component_string('moodle', $contextvalue);
        }

        $this->modinfo = get_fast_modinfo($course->id);
    }

    /**
     * Set data from form
     * @param array $data
     */
    public function set_data($data) {
        $this->data = $data;
    }

    /** 
     * trim the last part off the event
     * @param string $event
     * @return string
     */
    private function trim_event($event) {
        $parts = explode('\\', $event);

        return end($parts);
    }

    /**
     * Get the context related stuff
     * @param object $log 
     * @return array [$context]
     */
    private function get_context($log) {
        global $DB; 

        if ($log->contextlevel) {
            $context = $this->contexts[$log->contextlevel];
        } else {
            $context = '';
        }

        $detail = '';
        $moduletype = '';
        if ($log->contextlevel == CONTEXT_MODULE) {
            $cm = $this->modinfo->get_cm($log->contextinstanceid);
            $detail = $cm->get_formatted_name();
            $moduletype = $cm->get_module_type_name();
        }

        return [$context, $detail, $moduletype];
    }

    /**
     * Get related user
     * @param object $log
     * @return string userid
     */
    private function get_relateduser($log) {
        global $DB;

        if (!$log->relateduserid) {
            return '';
        }
        if ($user = $DB->get_record('user', ['id' => $log->relateduserid])) {
            return $user->username;
        }
        return '';
    }

    /**
     * Execute the download
     */
    public function execute() {
        global $DB;

        $context = \context_course::instance($this->course->id);

        // filters
        $from = $this->data->logstart;
        $to = $this->data->logend;

        // Get raw log data
        $sql = 'SELECT lsl.id, lsl.timecreated, u.username, eventname, lsl.courseid, cc.fullname, contextlevel, contextinstanceid, relateduserid
            FROM {logstore_standard_log} lsl
            JOIN {course} cc ON lsl.courseid = cc.id
            JOIN {user} u ON lsl.userid = u.id
            WHERE lsl.courseid = :courseid ';
        $params = ['courseid' => $this->course->id];
        if ($from) {
            $sql .= 'AND lsl.timecreated >= :from ';
            $params['from'] = $from;
        }
        if ($to) {
            $sql .= 'AND lsl.timecreated <= :to ';
            $params['to'] = $to;
        }
        $logs = $DB->get_records_sql($sql, $params);

        // Make logs more human readable.
        foreach ($logs as $log) {
            $log->eventname = $this->trim_event($log->eventname);
            list($log->context, $log->detail, $log->moduletype) = $this->get_context($log);
            $log->relateduser = $this->get_relateduser($log);
        }
        //echo "<pre>"; var_dump($logs); die;

        // Export
        $csv = new \csv_export_writer();
        $csv->set_filename($this->course->shortname);

        // Headers
        $header = [
            'id',
            'date',
            'username',
            'event',
            'course id',
            'course name',
            'context level',
            'module type',
            'context name',
            'related user',
        ];
        $csv->add_data($header);

        // Data
        foreach ($logs as $log) {
            $row = [
                $log->id,
                userdate($log->timecreated),
                $log->username,
                $log->eventname,
                $log->courseid,
                $log->fullname,
                $log->context,
                $log->moduletype,
                $log->detail,
                $log->relateduser,
            ];
            $csv->add_data($row);
        }

        $csv->download_file();
    }

}