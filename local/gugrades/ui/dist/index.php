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
 * @package    local_gugrades
 * @copyright  2022
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/local/gugrades/locallib.php');

$courseid = required_param('id', PARAM_INT);

$url = new moodle_url('/local/gugrades/ui/dist/index.php', ['id' => $courseid]);
$PAGE->set_url($url);
$PAGE->add_body_class("gugrades");
$PAGE->set_pagelayout('report');

// Set docroot to our own location
$helpurl = get_config('local_gugrades', 'helpurl');
$helptext = get_config('local_gugrades', 'helptext');
$CFG->docroot = $helpurl;

// Stuff to include.
$PAGE->requires->js_call_amd('local_gugrades/interface', 'init', [['courseid' => $courseid]]);
$PAGE->requires->css('/local/gugrades/ui/dist/assets/style.css');

echo '<script type="module" crossorigin src="' . $CFG->wwwroot . '/local/gugrades/ui/dist/assets/entry.js"></script>';

// Security.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/gugrades:view', $context);

// Navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$mygradesnode = $coursenode->add(get_string('staffmygrades', 'local_gugrades'));
$mygradesnode->make_active();

// Log.
$event = \local_gugrades\event\view_gugrades::create([
    'objectid' => $courseid,
    'context' => context_course::instance($courseid),
]);
$event->trigger();

// Check that the MyGrade custom course field exists
custom_course_field();

// VueJS stuff gets injected here.
echo $OUTPUT->header();

echo "<div id=\"app\"></div>";

// LISU Link
$lisuurl = "https://gla.sharepoint.com/sites/learning-innovation/SitePages/LISU-Guides-MyGrades.aspx";
echo '<div class="text-center my-3">
          <a class="btn btn-info px-5" href="' . $helpurl . '" target="_blank">' . $helptext . '</a>
      </div>';
echo $OUTPUT->footer();

