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
 * tool_rollover
 *
 * @package    tool_rollover
 * @copyright  2019 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('toolrollover');

// Params
$action = optional_param('action', '', PARAM_ALPHA);

if ($action == 'build') {
    tool_rollover\lib::build_course_table();
    redirect(new moodle_url('/admin/tool/rollover/index.php'));
}

$output = $PAGE->get_renderer('tool_rollover');
echo $output->header();
echo $output->heading(get_string('pluginname', 'tool_rollover'));

// Count stuff
$counts = tool_rollover\lib::get_current_status();

$build = new tool_rollover\output\build($counts);
echo $output->render_build($build);

echo $output->footer();