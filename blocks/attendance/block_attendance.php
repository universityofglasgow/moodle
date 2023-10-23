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
 * Attendance Block
 *
 * @package    block_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Displays information about Attendance Module in this course.
 *
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_attendance extends block_base {

    /**
     * Set the initial properties for the block
     */
    public function init() {
        $this->title = get_string('blockname', 'block_attendance');
    }

    /**
     * Gets the content for this block
     *
     * @return object $this->content
     */
    public function get_content() {
        global $CFG, $USER, $COURSE, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = '';

        $attendances = get_all_instances_in_course('attendance', $COURSE, null, true);
        if (count($attendances) == 0) {
             $this->content->text = get_string('needactivity', 'block_attendance');;
             return $this->content;
        }

        require_once($CFG->dirroot.'/mod/attendance/locallib.php');
        require_once($CFG->dirroot.'/mod/attendance/renderhelpers.php');

        foreach ($attendances as $attinst) {
            $cmid = $attinst->coursemodule;
            $cm  = get_coursemodule_from_id('attendance', $cmid, $COURSE->id, false, MUST_EXIST);
            if (!empty($cm->deletioninprogress)) {
                // Don't display if this attendance is in recycle bin.
                continue;
            }
            $context = context_module::instance($cmid, MUST_EXIST);
            $attendance = $DB->get_record('attendance', ['id' => $cm->instance], '*', MUST_EXIST);

            $att = new mod_attendance_structure($attendance, $cm, $COURSE, $context);

            $this->content->text .= html_writer::link($att->url_view(), html_writer::tag('b', format_string($att->name)));
            $this->content->text .= html_writer::empty_tag('br');

            // Link to attendance.

            if (has_capability('mod/attendance:takeattendances', $context) or
                has_capability('mod/attendance:changeattendances', $context)) {
                $this->content->text .= html_writer::link($att->url_manage(array('from' => 'block')),
                                                                           get_string('takeattendance', 'attendance'));
                $this->content->text .= html_writer::empty_tag('br');
            }
            if (has_capability('mod/attendance:manageattendances', $context)) {
                $url = $att->url_sessions(array('action' => mod_attendance_sessions_page_params::ACTION_ADD));
                $this->content->text .= html_writer::link($url, get_string('add', 'attendance'));
                $this->content->text .= html_writer::empty_tag('br');
            }
            if (has_capability('mod/attendance:viewreports', $context)) {
                $this->content->text .= html_writer::link($att->url_report(), get_string('report', 'attendance'));
                $this->content->text .= html_writer::empty_tag('br');
            }

            if (has_capability('mod/attendance:canbelisted', $context, null, false) &&
                has_capability('mod/attendance:view', $context)) {
                $this->content->text .= construct_full_user_stat_html_table($attinst, $USER);
            }
            $this->content->text .= "<br />";
        }
        if ($COURSE->id !== SITEID) { // Don't show course categories on site homepage.
            $categorycontext = context_coursecat::instance($COURSE->category);
            if (has_capability('mod/attendance:viewsummaryreports', $categorycontext)) {
                $url = new moodle_url('/mod/attendance/coursesummary.php',
                    array('category' => $COURSE->category, 'fromcourse' => $COURSE->id));
                $this->content->text .= html_writer::link($url, get_string('categoryreport', 'attendance'));
                $this->content->text .= html_writer::empty_tag('br');
            }
        }

        return $this->content;
    }

    /**
     * Set the applicable formats for this block
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true, 'my' => false, 'admin' => false, 'tag' => false);
    }
}
