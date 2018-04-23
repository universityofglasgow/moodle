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
 * This file contains functions used by the participation report
 *
 * @package    report
 * @subpackage gucode
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('GLA_COURSE_CODE_URL', 'http://www.gla.ac.uk/coursecatalogue/course/?code=');

class report_gucode_renderer extends plugin_renderer_base {

    public function print_table($courses) {
        global $CFG;

        if (!$courses) {
            echo '<div class="alert">'.get_string('nocourses', 'report_gucode').'</div>';
            return;
        }
        $table = new html_table();
        $table->head[] = get_string('code', 'report_gucode');
        $table->head[] = get_string('course');
        $table->head[] = get_string('localname', 'report_gucode');
        $table->head[] = get_string('subject', 'report_gucode');
        $table->head[] = get_string('number', 'report_gucode');
        $table->head[] = get_string('added', 'report_gucode');
        $table->head[] = get_string('isvisible', 'report_gucode');
        foreach ($courses as $course) {
            $row = new html_table_row();
            if ($course->visible == 'hidden') {
                $row->style = 'font-style: italic';
            }
            if (strlen($course->fullname) > 25) {
                $fullname = substr($filename, 0, 25) . '...';
            } else {
                $fullname = $course->fullname;
            }    
            $codelink = GLA_COURSE_CODE_URL . $course->code;
            $row->cells[] = "<a href=\"$codelink\">$course->code</a>";
            $courselink = new moodle_url('/course/view.php', array('id' => $course->courseid));
            $row->cells[] = $course->missing ? $course->coursename : "<a href=\"$courselink\">{$course->coursename}</a>";
            $row->cells[] = $course->missing ? $fullname: "<a href=\"$courselink\"  title=\"{$course->fullname}\">{$fullname}</a>";
            $row->cells[] = $course->subjectname;
            $row->cells[] = $course->subjectnumber;
            $row->cells[] = userdate($course->timeadded, get_string('strftimedatetimeshort'), $CFG->timezone);
            $row->cells[] = $course->visible;
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    }

}
