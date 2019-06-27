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
 * Library functions
 *
 * @package    report_enhance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_enhance;

defined('MOODLE_INTERNAL') || die();

class lib {

    /**
     * Get voting status for this user/request
     * @param object $request
     * @return array [$count, $ownrequest, $voted]
     */
    public static function getvotes($request) {
        global $DB, $USER;

        // Get the possible votes for this request
        $votes = $DB->get_records('report_enhance_vote', ['enhanceid' => $request->id]);

        $count = count($votes);
        $ownrequest = $request->userid == $USER->id;
        $voted = !empty(array_filter($votes, function($vote) {
            global $USER;
            return $vote->userid == $USER->id;
            }));

        return [$count, $ownrequest, $voted];
    }

    /**
     * Set/remove vote for current user
     * @param object $request
     * @param bool $vote true = vote, false = remove vote
     * @return bool success
     */
    public static function vote($request, $vote) {
        global $DB, $USER;

        // You can't vote on your own request
        if ($request->userid == $USER->id) {
            return false;
        }

        if ($vote) {
            if (!$DB->get_record('report_enhance_vote', ['enhanceid' => $request->id, 'userid' => $USER->id])) {
                $enhancevote = new \stdClass;
                $enhancevote->enhanceid = $request->id;
                $enhancevote->userid = $USER->id;
                $DB->insert_record('report_enhance_vote', $enhancevote);
            }
        } else {
            $DB->delete_records('report_enhance_vote', ['enhanceid' => $request->id, 'userid' => $USER->id]);
        }

        return true;
    }

    /**
     * Get classes for list cards
     * @param object $request
     * @return string
     */
    public static function cardclasses($request) {
        global $USER;

        $requestClasses = [];
        $requestClasses[] = 'filter-status-' . $request->status;
        if($request->userid == $USER->id) {
            $requestClasses[] = 'filter-me';
        }

        return implode(" ", $requestClasses);
    }

    /**
     * Get list of priorities
     * @param $html if true add html formatting (default)
     * @return array
     */
    public static function getpriorities($html = true) {
        if ($html) {
            return [
                0 => '<i class="text-success fa fa-arrow-down"></i> ' . get_string('lowpriority', 'report_enhance'),
                1 => '<i class="text-warning fa fa-arrow-right"></i> ' . get_string('mediumpriority', 'report_enhance'),
                2 => '<i class="text-danger fa fa-arrow-up"></i> ' . get_string('highpriority', 'report_enhance'),
            ];
        } else {
            return [
                0 => get_string('lowpriority', 'report_enhance'),
                1 => get_string('mediumpriority', 'report_enhance'),
                2 => get_string('highpriority', 'report_enhance'),
            ];
        }
    }

    /**
     * Fix navigation - make the breadcrumbs work
     * @param string $name optional breadcrumb
     * @param string $url optional breadcrumb
     */
    public static function fixnavigation($name = '', $url = '') {
        global $PAGE;

        $PAGE->navbar->ignore_active();
        $PAGE->navbar->add(get_string('vleenhancements', 'report_enhance'), new \moodle_url('/report/enhance'));
        if ($name) {
            $PAGE->navbar->add($name, $url);
        }
    }

    /**
     * Format requests for export
     * @param array $requests
     * @return array
     */
    protected static function format_for_export($requests) {
        global $DB;

        $status = new \report_enhance\status;
        $priorities = self::getpriorities(false);

        foreach ($requests as $request) {
            $user = $DB->get_record('user', ['id' => $request->userid], '*', MUST_EXIST);
            $request->username = fullname($user);
            $request->userdate = userdate($request->timecreated);
            $request->statusformatted = $status->getStatus($request->status);
            $request->priorityformatted = $priorities[$request->priority];
            list($request->votes) = \report_enhance\lib::getvotes($request);
        }

        return $requests;
    }

    /**
     * Export to Excel
     * @param string $filename
     * @param array $requests
     */
    public static function export($filename, $requests) {
        global $CFG;

        require_once($CFG->dirroot . '/lib/excellib.class.php');

        // Format the requests for output
        $requests = self::format_for_export($requests);

        $workbook = new \MoodleExcelWorkbook("-");

        // Sending HTTP headers.
        $workbook->send($filename);

        // Adding the worksheet.
        $myxls = $workbook->add_worksheet(get_string('workbook', 'report_enhance'));

        // Headers.
        $i = 0;
        $myxls->write_string(1, $i++, get_string('requestnumber', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('submittedby', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('submittedon', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('department', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('status', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('votes', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('priority', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('description', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('benefits', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('desirability', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('impact', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('viability', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('result', 'report_enhance'));
        $myxls->write_string(1, $i++, get_string('reviewernotes', 'report_enhance'));

        // Add some data.
        $row = 2;
        foreach ($requests as $request) {
            $i = 0;
            $myxls->write_string($row, $i++, $request->id);
            $myxls->write_string($row, $i++, $request->username);
            $myxls->write_string($row, $i++, $request->userdate);
            $myxls->write_string($row, $i++, $request->department);
            $myxls->write_string($row, $i++, $request->statusformatted);
            $myxls->write_number($row, $i++, $request->votes);
            $myxls->write_string($row, $i++, $request->priorityformatted);
            $myxls->write_string($row, $i++, html_to_text($request->description, 0, false));
            $myxls->write_string($row, $i++, html_to_text($request->benefits, 0, false));
            $myxls->write_string($row, $i++, html_to_text($request->desirability, 0, false));
            $myxls->write_string($row, $i++, html_to_text($request->impact, 0, false));
            $myxls->write_string($row, $i++, html_to_text($request->viability, 0, false));
            $myxls->write_string($row, $i++, html_to_text($request->result, 0, false));
            $myxls->write_string($row, $i++, html_to_text($request->reviewernotes, 0, false));

            $row++;
        }
        $workbook->close();
    }

}
