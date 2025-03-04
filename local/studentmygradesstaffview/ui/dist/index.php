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
 * Vue CLI Index file.
 *
 * @package     local_studentmygradesstaffview
 * @author      Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright   2025 University of Glasgow
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');

$courseid = required_param('id', PARAM_INT);

$url = new moodle_url('/local/studentmygradesstaffview/ui/dist/index.php', ['id' => $courseid]);
$PAGE->set_url($url);
$PAGE->add_body_class("studentmygradesstaffview");

// Stuff to include.
$PAGE->requires->js_call_amd('local_studentmygradesstaffview/interface', 'init', [['courseid' => $courseid]]);
$PAGE->requires->css('/local/studentmygradesstaffview/ui/dist/assets/style.css');

echo '<script type="module" crossorigin src="' . $CFG->wwwroot . '/local/studentmygradesstaffview/ui/dist/assets/entry.js"></script>';

// Security.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/studentmygradesstaffview:view', $context);

// VueJS stuff gets injected here.
echo $OUTPUT->header();
echo "<div id=\"app\"></div>";

// LISU Link
$lisuurl = "https://gla.sharepoint.com/sites/learning-innovation/SitePages/LISU-Guides-MyGrades.aspx";
echo '<div class="text-center my-3">
          <a class="btn btn-info px-5" href="' . $lisuurl . '" target="_blank">LISU MyGrades help and support</a>
      </div>';
echo $OUTPUT->footer();
