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

$courseid = required_param('id', PARAM_INT);

$url = new moodle_url('/local/gugrades/ui/dist/index.php', ['id' => $courseid]);
$PAGE->set_url($url);

// Stuff to include.
$PAGE->requires->js_call_amd('local_gugrades/interface', 'init', [['courseid' => $courseid]]);
$PAGE->requires->css('/local/gugrades/ui/dist/css/app.css');
$PAGE->requires->js('/local/gugrades/ui/dist/js/chunk-vendors.js');
$PAGE->requires->js('/local/gugrades/ui/dist/js/app.js');

// Security.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/gugrades:view', $context);

// Log.
$event = \local_gugrades\event\view_gugrades::create([
    'objectid' => $courseid,
    'context' => context_course::instance($courseid),
]);
$event->trigger();

// VueJS stuff gets injected here.
echo $OUTPUT->header();

// MyGrades logo.
// (Not in Vue to save pissing around with PublicPath).
$logo = $OUTPUT->image_url('MyGradesLogoSmall', 'local_gugrades');
echo '<div class="text-center pb-1">';
echo '<img id="mygradeslogo" src="' . $logo . '" alt="MyGrades logo"></img>';
echo '</div>';

echo "<div id=\"app\"></div>";
echo $OUTPUT->footer();
