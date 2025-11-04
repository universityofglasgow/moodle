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
 * TODO describe file archiving_courses
 *
 * @package    tool_gudelete
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once('../../../course/lib.php');
global $DB, $CFG;

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);

$url = new moodle_url('/admin/tool/gudelete/archiving_courses.php', []);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');

require_once('archiving_courses_form.php');
$mform = new archiving_courses_form();

// Optional download of tables as Excel.
$download = optional_param('download', '', PARAM_ALPHA);
$format = optional_param('format', 'excel', PARAM_ALPHA);
if (!empty($download)) {
    $config = get_config('tool_gudelete');
    $archivement = new tool_gudelete\course_archiving_helper();
    $result = $archivement->check_courses($config);

    $columns = array('fullname', 'category', 'id', 'url');
    $data = array();
    if ($download === 'archive' && !empty($result->archive)) {
        foreach ($result->archive as $course) {
            $category = \core_course_category::get($course->category);
            $data[] = array(
                'fullname' => format_string($course->fullname),
                'category' => $category->get_nested_name(false),
                'id' => $course->id,
                'url' => (new moodle_url('/course/view.php', array('id' => $course->id)))->out(false)
            );
        }
        \core\dataformat::download_data('gudelete_archive_'.userdate(time(), '%Y%m%d'), $format, $columns, $data);
        exit;
    } else if ($download === 'delete' && !empty($result->delete)) {
        foreach ($result->delete as $course) {
            $category = \core_course_category::get($course->category);
            $data[] = array(
                'fullname' => format_string($course->fullname),
                'category' => $category->get_nested_name(false),
                'id' => $course->id,
                'url' => (new moodle_url('/course/view.php', array('id' => $course->id)))->out(false)
            );
        }
        \core\dataformat::download_data('gudelete_delete_'.userdate(time(), '%Y%m%d'), $format, $columns, $data);
        exit;
    } else {
        // Nothing to export.
        redirect(new moodle_url('/admin/tool/gudelete/archiving_courses.php'));
    }
}

// Execute the form
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot);
} else if ($genparams = $mform->get_data() && $mform->is_submitted()) {
    $config = get_config('tool_gudelete');
    $archivement = new tool_gudelete\course_archiving_helper();
    $a = $archivement->process_archivment($config);
    notice(get_string('notice', 'tool_gudelete', $a), $CFG->wwwroot);
}

$header = get_string('title', 'tool_gudelete');
$PAGE->set_heading($header);

echo $OUTPUT->header();
$mform->display();
// Render download links.
$downloadarchiveurl = new moodle_url($PAGE->url, array('download' => 'archive', 'format' => 'excel'));
$downloaddeleteurl = new moodle_url($PAGE->url, array('download' => 'delete', 'format' => 'excel'));
echo html_writer::div(
    html_writer::link($downloadarchiveurl, get_string('download') . ' (Archive)') . ' | ' .
    html_writer::link($downloaddeleteurl, get_string('download') . ' (Delete)'),
    'mb-3'
);
echo $OUTPUT->footer();
