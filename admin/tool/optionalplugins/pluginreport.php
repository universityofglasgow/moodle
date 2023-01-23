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
 * Report that displays information about when the optional plugins feature was run.
 *
 * This script fetches the log data captured when optional plugins were installed.
 * Limited to the last [x] records, however, an improvement would be to include
 * additional filters (date, user etc) in order to further narrow down results.
 *
 * @package tool_optionalplugins
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_once(__DIR__ . '/classes/pluginreport_form.php');

admin_externalpage_setup('reportoptionalplugins');

$title = get_string('reportpagetitle', 'tool_optionalplugins');
$context = context_system::instance();

$PAGE->set_url(new moodle_url("/admin/tool/optionalplugins/pluginreport.php"));
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$selectedid = optional_param('reportid', 0, PARAM_INT);

$report = new pluginreport_form(null, ['selectedid' => $selectedid]);
if ($report->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    $redirect = new moodle_url("/admin/search.php", [], 'linkreports');
    redirect($redirect);
}

echo $OUTPUT->header();

if ($report->is_submitted() && $data = $report->get_data()) {

    // Print headers and things.
    echo $OUTPUT->box(get_string('reportintro', 'tool_optionalplugins'));
    $report->display();

    // Fetch the log data...
    $id = $data->reportid;
    $logrecords = $DB->get_records('tool_optionalplugins_log', array('id' => $id), '',
        'userid, timecreated, installed, alreadyinstalled, notinstalled'
    );

    // Display it...
    $logrenderer = $PAGE->get_renderer('tool_optionalplugins');
    echo $logrenderer->render_installation_log($logrecords);

    echo $OUTPUT->footer();
    exit(0);
}

// Print headers and things.
echo $OUTPUT->box(get_string('reportintro', 'tool_optionalplugins'));
$report->display();
echo $OUTPUT->footer();
